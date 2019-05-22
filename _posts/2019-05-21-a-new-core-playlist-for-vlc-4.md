---
layout: post
title: A new core playlist for VLC 4
date: 2019-05-21 09:25:00+02:00
lang: en
---

The [core] playlist in VLC was started [a long time
ago][playlist-initial-commit]. Since then, it has grown to handle too many
different things, to the point it became a kind of [god object].

In practice, the playlist was also controlling playback (start, stop, change
volume…), configuring audio and video outputs, storing media detected by
_discovery_…

For [VLC 4][fosdem], we wanted a new playlist API, containing a simple list of
items (instead of a tree), acting as a _media provider_ for a _player_, without
unrelated responsabilities.

I [wrote it][commits-playlist] several months ago (at [Videolabs]). Now that the
old one has been [removed][remove-playlist-legacy], it's time to give some
technical details.

[core]: https://wiki.videolan.org/Hacker_Guide/Core/
[playlist-initial-commit]: https://git.videolan.org/?p=vlc.git;a=commit;h=57e189eb5d1d387f2036c31720e1e9aa8cb3ea78
[god object]: https://en.wikipedia.org/wiki/God_object
[fosdem]: https://www.youtube.com/watch?v=jzvC-0WCjKU&t=312
[commits-playlist]: https://code.videolan.org/videolan/vlc/commits/76e575307494a1f8ddf6f30266f5e6d7466a7013
[Videolabs]: https://videolabs.io/
[remove-playlist-legacy]: https://code.videolan.org/videolan/vlc/commit/c67934b0b4fc9298cb0784c07f701392589e61b7

{: .center}
![vlc](https://raw.githubusercontent.com/rom1v/blog.rom1v.com/master/assets/vlc_playlist/vlc.png)

* TOC
{:toc}


## Objectives

One major design goal is to expose what **UI frameworks** need. Several user
interfaces, like Qt, Mac OS and Android[^1], will use this API to display and
interact with the main VLC playlist.

The playlist must be **performant** for common use cases and usable from
**multiple threads**.

Indeed, in VLC, user interfaces are implemented as _modules_ loaded dynamically.
In general, there is exactly one user interface, but there may be none or (in
theory) several. Thus, the playlist may not be bound to the [event loop] of
some specific user interface. Moreover, the playlist may be modified from a
_player_ thread; for example, playing a zip archive will replace the item by its
content automatically.

As a consequence, the playlist will use a [mutex]; to avoid [ToCToU] issues, it
will also expose public functions to lock and unlock it. But as we will see
later, there will be other consequences.

[event loop]: https://en.wikipedia.org/wiki/Event_loop
[mutex]: https://en.wikipedia.org/wiki/Lock_(computer_science)
[ToCToU]: https://en.wikipedia.org/wiki/Time_of_check_to_time_of_use


## Data structure

User interfaces need [random access] to the playlist items, so a _vector_ is the
most natural structure to store the items. A _vector_ is provided by the
standard library of many languages ([`vector`] in _C++_, [`Vec`] in _Rust_,
[`ArrayList`] in _Java_…).  But here, we're in _C_, so there is nothing.

In the playlist, we only need a vector of _pointers_, so I first [proposed
improvements][vlc_array-ml] to an existing type, `vlc_array_t`, which only
supports `void *` as items. But it was considered useless
([1][vlc_array-courmisch], [2][vlc_array-typx]) because it is too limited and
not type-safe.

Therefore, I wrote [`vlc_vector`]. It is implemented using macros so that it's
generic over its item type. For example, we can use a vector of `int`s as
follow:

{% highlight c %}
// declare and initialize a vector of int
struct VLC_VECTOR(int) vec = VLC_VECTOR_INITIALIZER;

// append 0, 10, 20, 30 and 40
for (int i = 0; i < 5; ++i) {
    if (!vlc_vector_push(&vec, 10 * i)) {
        // allocation failure...
    }
}

// remove item at index 2
vlc_vector_remove(2);

// the vector now contains [0, 10, 30, 40]

int first = vec.data[0]; // 0
int last = vec.data[vec.size - 1]; // 40

// free resources
vlc_vector_destroy(&vec);
{% endhighlight %}

Internally, the playlist uses a [vector of playlist items][playlist-items]:

{% highlight c %}
typedef struct VLC_VECTOR(struct vlc_playlist_item *) playlist_item_vector_t;

struct vlc_playlist {
    playlist_item_vector_t items;
    // ...
};
{% endhighlight %}


[vlc_array-ml]: https://mailman.videolan.org/pipermail/vlc-devel/2018-July/120434.html
[vlc_array-courmisch]: https://mailman.videolan.org/pipermail/vlc-devel/2018-July/120466.html
[vlc_array-typx]: https://mailman.videolan.org/pipermail/vlc-devel/2018-July/120509.html

[random access]: https://en.wikipedia.org/wiki/Random_access
[`vector`]: https://en.cppreference.com/w/cpp/container/vector
[`Vec`]: https://doc.rust-lang.org/std/vec/struct.Vec.html
[`ArrayList`]: https://docs.oracle.com/javase/10/docs/api/java/util/ArrayList.html

[`vlc_vector`]: https://code.videolan.org/videolan/vlc/commit/983c43f05928032a14f201c506d6b9c51d0c5145?expanded=1
[playlist-items]: https://code.videolan.org/videolan/vlc/blob/051237a0ef4fe39d2f01850db28f9306dd4a7b83/src/playlist/playlist.h#L54


## Interaction with UI

UI frameworks typically use _list models_ to bind items to a _list view_
component. A _list model_ must provide:

 - the **total number of items**,
 - the **item at a given index**.

In addition, the model must notify its view when items are **inserted**,
**removed**, **moved** or **updated**, and when the model is **reset** (the
whole content should be invalidated).

For example, Qt list views use [`QAbstractItemModel`]/[`QAbstractListModel`] and
the Android [recycler view] uses [`RecyclerView.Adapter`].

The playlist API exposes the functions and callbacks providing these features.

[`QAbstractItemModel`]: http://doc.qt.io/qt-5/qabstractitemmodel.html
[`QAbstractListModel`]: http://doc.qt.io/qt-5/qabstractlistmodel.html
[recycler view]: https://developer.android.com/reference/androidx/recyclerview/widget/RecyclerView.html
[`RecyclerView.Adapter`]: https://developer.android.com/reference/androidx/recyclerview/widget/RecyclerView.Adapter.html

[^1]: Actually, the _Android_ app will maybe continue to implement its own
      playlist in Java/Kotlin, to avoid additional layers (Java/JNI and LibVLC).


### Desynchronization

However, **the core playlist may not be used as a direct data source for a list
model**. In other word, the functions of a list model must not delegate the
calls to the core playlist.

To understand why, let's consider a typical sequence of calls executed by a view
on its model, from the UI thread:

{% highlight cpp %}
model.count();
model.get(0);
model.get(1);
model.get(2);
model.get(3);
model.get(4);
{% endhighlight %}

If we implemented `count()` and `get(index)` by delegating to the playlist, we
would have to lock each call individually:

{% highlight cpp %}
// in some artificial UI framework in C++

int MyModel::count() {
    // don't do this
    vlc_playlist_Lock(playlist);
    int count = vlc_playlist_Count();
    vlc_playlist_Unlock(playlist);
    return count;
}

vlc_playlist_item_t *MyModel::get(int index) {
    // don't do this
    vlc_playlist_Lock(playlist);
    vlc_playlist_item_t *item = vlc_playlist_Get(playlist, index);
    vlc_playlist_Unlock(playlist);
    return item;
}
{% endhighlight %}

Note that locking and unlocking from the UI thread for every playlist item is
not a good idea for responsiveness, but this is a minor issue here.

The real problem is that locking is not sufficient to guarantee correctness: the
_list view_ expects its model to return consistent values. Our implementation
can break this assumption, because the playlist content could change
asynchronously between calls. Here is an example:

{% highlight cpp %}
// the playlist initially contains 5 items: [A, B, C, D, E]
model.count(); // 5
model.get(0);  // A
model.get(1);  // B
                    // the first playlist item is removed from another thread:
                    //     vlc_playlist_RemoveOne(playlist, 0);
                    // the playlist now contains [B, C, D, E]
model.get(2);  // D
model.get(3);  // E
model.get(4);  // out-of-range, undefined behavior (probably segfault)
{% endhighlight %}

The view could not process any notification of the item removal before the end
of the current execution in its event loop… that is, at least after
`model.get(4)`. To avoid this problem, **the data provided by view models must
always _live_ in the UI thread**.

This implies that the UI has to manage **a copy of the playlist content**. The
UI playlist should be considered as a remote out-of-sync view of the core
playlist.

Note that the copy must be not limited to the list of _pointers_ to playlist
items: the content which is displayed and susceptible to change asynchronously
(media metadata, like _title_ or _duration_) must also be copied. The UI needs a
**deep copy**; otherwise, the content could change (and be exposed) before the
_list view_ was notified… which, again, would break assumptions about the
model.



### Synchronization

The core playlist and the UI playlist are out-of-sync. So we need to
"synchronize" them:

 - the changes on the core playlist must be reflected to the UI views,
 - the user, via the UI, may request changes to the core playlist (to add, move
   or remove items for example).


#### Core to UI

The core playlist is the _source of truth_.

Every change to the UI playlist must occur in the UI thread, yet the core
playlist notification handlers are executed from any thread. Therefore, playlist
callback handlers must retrieve appropriate data from the playlist, then _post_
an event to the UI event loop[^2], which will be handled from the UI thread.
From there, the core playlist will be out-of-sync, so it would be incorrect to
access it.

The **order of events** forwarded to the UI thread **must be preserved**. That
way, the indices notified by the core playlist are necessarily valid within the
context of the _list model_ in the UI thread. The core playlist events can be
understood as a sequence of "patches" that the UI playlist must apply to its own
copy.

[^2]: Even in the case where a core playlist callback is executed from the UI
      thread, the event must be posted to the event queue, to avoid breaking
      the order. Concretely, in Qt, this means connecting signals to slots using
      [`Qt::QueuedConnection`] instead of the default `Qt::AutoConnection`.

[`Qt::QueuedConnection`]: http://doc.qt.io/qt-5/qt.html#ConnectionType-enum

This only works if **only the core playlist callbacks modify the _list model_
content**.


#### UI to core

Since the _list model_ can only be modified by the core playlist callbacks, it
is incorrect to modify it on user actions. As a consequence, the changes must be
requested to the core playlist, which will, in turn, notify the actual changes.


The synchronization is more tricky in that direction.

To understand why, suppose the user selects items 10 to 20, then drag & drop to
move them to index 42. Once the user releases the mouse button to "drop" the
items, we need to lock the core playlist to apply the changes.

The problem is that, before we successfully acquired the lock, another client
may have modified the playlist: it may have cleared it, or shuffled it, or
removed items 5 to 15… As a consequence, we cannot apply the "move" request as
is, because it was created from a previous playlist state.

To solve the issue, we need to adapt the request to make it fit the current
playlist state. In other words, resolve conflicts: find the items if they had
been moved, ignore the items not found for removal…

For that purpose, in addition to functions modifying the content directly, the
playlist exposes functions to [request][playlist-request] "desynchronized"
changes, which automatically resolve conflicts and generate an appropriate
sequence of events to notify the clients of the _actual_ changes.

Let's take an example. Initially, our playlist contains 10 items:

    [A, B, C, D, E, F, G, H, I, J]

The user selects `[C, D, E, F, G]` and press the `Del` key to remove the items.
To apply the change, we need to lock the core playlist.

But at that time, another thread was holding the lock to apply some other
changes. It removed `F` and `I`, and shuffled the playlist:

    [E, B, D, J, C, G, H, A]

Once the other thread unlocks the playlist, our lock finally succeeds. Then, we
call `request_remove([C, D, E, F, G])` (this is pseudo-code, the real function
is [`vlc_playlist_RequestRemove`]).

Internally, it triggers several calls:

{% highlight c %}
// [E, B, D, J, C, G, H, A]
remove(index = 4, count = 2)   // remove [C, G]
// [E, B, D, J, H, A]
remove(index = 2, count = 1)   // remove [D]
// [E, B, J, H, A]
remove(index = 0, count = 1)   // remove [E]
// [B, J, H, A]
{% endhighlight %}

Thus, every client (including the UI from which the user requested to remove the
items), will receive a sequence of 3 events [`on_items_removed`], corresponding
to each removed slice.

The slices are removed in descending order for both optimization (it minimizes
the number of shifts) and simplicity (the index of a removal does not depend on
previous removals).

In practice, it is very likely that the request will apply exactly to the
current state of the playlist. To avoid unnecessary linear searches to find the
items, these functions accept an additional `index_hint` parameter, giving the
index of the items when the request was created. It should (hopefully) almost
always be the same as the index in the current playlist state.


[playlist-request]: https://code.videolan.org/videolan/vlc/blob/051237a0ef4fe39d2f01850db28f9306dd4a7b83/include/vlc_playlist.h#L548
[`vlc_playlist_RequestRemove`]: https://code.videolan.org/videolan/vlc/blob/051237a0ef4fe39d2f01850db28f9306dd4a7b83/include/vlc_playlist.h#L597
[`on_items_removed`]: https://code.videolan.org/videolan/vlc/blob/051237a0ef4fe39d2f01850db28f9306dd4a7b83/include/vlc_playlist.h#L204


## Random playback

Contrary to [shuffle], random playback does not move the items within the
playlist; instead, it does not play them sequentially.

To select the next item to play, we could just pick one at random.

But this is not ideal: some items will be selected several times (possibly in a
row) while some others will not be selected at all. And if _loop_ is disabled,
when should we stop? After all _n_ items have been selected at least once or
after _n_ playbacks?

Instead, we would like some desirable properties that work both with _loop_
enabled and disabled:

 - an item must never be selected twice (within a cycle, if _loop_ is enabled),
 - we should be able to navigate back to the previously selected items,
 - we must able to force the selection of a specific item (typically when the
   user double-click on an item in the playlist),
 - insertions and removals must be taken into account at any time,

In addition, if _loop_ is enabled:

 - the random order must be recomputed for very cycle (we don't always want the
   [same random] order),
 - it should be possible to navigate back to previous items from the previous
   cycle,
 - an item must never be selected twice in a row (in particular, it may not be
   the last item of one cycle and the first item of the next cycle).

[shuffle]: https://code.videolan.org/videolan/vlc/blob/051237a0ef4fe39d2f01850db28f9306dd4a7b83/include/vlc_playlist.h#L623
[same random]: https://xkcd.com/221/


### Randomizer

I wrote a [`randomizer`] to select items "randomly" within all these
constraints.

To get an idea of the results, here is a sequence produced for a playlist
containing 5 items (`A`, `B`, `C`, `D` and `E`), with _loop_ enabled (so that it
continues indefinitely):

```
E D A B C E B C A D C B E D A C E A D B A D C E B A B D E C B C A E D E D B C A
E C B D A C A E B D C D E A B E D B A C D C B A E D A B C E B D C A E D C A B E
B A E C D C E D A B C E B A D E C B D A D B A C E C E B A D B C E D A E A C B D
A D E B C D C A E B E A D C B C D B A E C E A B D C D E A B D A E C B C A D B E
A B E C D A C B E D E D A B C D E C A B C A E B D E B D C A C A E D B D B E C A
```

[`randomizer`]: https://code.videolan.org/videolan/vlc/blob/051237a0ef4fe39d2f01850db28f9306dd4a7b83/src/playlist/randomizer.c

Here is how it works.

The _randomizer_ stores a single _vector_ containing all the items of the
playlist. This _vector_ is not shuffled at once. Instead, steps of the
[Fisher-Yates] algorithm are executed one-by-one on demand. This has several
advantages:
 - on insertions and removals, there is no need to reshuffle or shift the
   whole array;
 - if _loop_ is enabled, the history of the last cycle can be kept in place.

It also maintains 3 indexes:
 - `head` indicates the end of the items already determinated for the current
cycle (if _loop_ is disabled, there is only one cycle),
 - `next` points to the item after the current one[^3],
 - `history` points to the first item of ordered history from the last cycle.

[^3]: We use `next` instead of `current` so that all indexes are unsigned, while
      `current` could be `-1`.

```
0              next  head          history       size
|---------------|-----|.............|-------------|
 <------------------->               <----------->
  determinated range                 history range
```


_Let's reuse the example I wrote in the [documentation][randomizer-doc]._

[Fisher-Yates]: https://en.wikipedia.org/wiki/Fisher%E2%80%93Yates_shuffle
[randomizer-doc]: https://code.videolan.org/videolan/vlc/blob/051237a0ef4fe39d2f01850db28f9306dd4a7b83/src/playlist/randomizer.c#L87

Here is the initial state with our 5 items:

```
                                         history
                next                     |
                head                     |
                |                        |
                A    B    C    D    E
```

The playlist calls `Next()` to retrieve the next random item. The randomizer
picks one item (say, `D`), and swaps it with the current head (`A`). `Next()`
returns `D`.

```
                                         history
                     next                |
                     head                |
                     |                   |
                D    B    C    A    E
              <--->
           determinated range
```

The playlist calls `Next()` one more time. The randomizer selects one item
outside the determinated range (say, `E`). `Next()` returns `E`.

```
                                         history
                          next           |
                          head           |
                          |              |
                D    E    C    A    B
              <-------->
           determinated range
```

The playlist calls `Next()` one more time. The randomizer selects `C` (already
in place). `Next()` returns `C`.

```
                                         history
                               next      |
                               head      |
                               |         |
                D    E    C    A    B
              <------------->
            determinated range
```

The playlist then calls `Prev()`. Since the "current" item is `C`, the previous
one is `E`, so `Prev()` returns `E`, and `next` moves back.

```
                                         history
                          next           |
                          |    head      |
                          |    |         |
                D    E    C    A    B
              <------------->
            determinated range
```

The playlist calls `Next()`, which returns `C`, as expected.

```
                                         history
                               next      |
                               head      |
                               |         |
                D    E    C    A    B
              <------------->
            determinated range
```

The playlist calls `Next()`, the randomizer selects `B`, and returns it.

```
                                         history
                                    next |
                                    head |
                                    |    |
                D    E    C    B    A
              <------------------>
               determinated range
```

The playlist calls `Next()`, the randomizer selects the last item (it has no
choice). `next` and `head` now point one item past the end (their value is
the vector size).

```
                                         history
                                         next
                                         head
                                         |
                D    E    C    B    A
              <----------------------->
                 determinated range
```

At this point, if _loop_ is disabled, it is not possible to call `Next()`
anymore (`HasNext()` returns `false`). So let's enable it by calling
`SetLoop()`, then let's call `Next()` again.

This will start a new loop cycle. Firstly, `next` and `head` are reset, and
the whole vector belongs to the last cycle history.

```
                 history
                 next
                 head
                 |
                 D    E    C    B    A
              <------------------------>
                    history range
```

Secondly, to avoid to select `A` twice in a row (as the last item of the
previous cycle and the first item of the new one), the randomizer will
immediately determine another item in the vector (say `C`) to be the first of
the new cycle. The items that belong to the history are kept in order.
`head` and `history` move forward.

```
                     history
                next |
                |    head
                |    |
                C    D    E    B    A
              <---><------------------>
      determinated     history range
             range
```

Finally, it will actually select and return the first item (`C`).

```
                     history
                     next
                     head
                     |
                C    D    E    B    A
              <---><------------------>
      determinated     history range
             range
```

Then, the user adds an item to the playlist (`F`). This item is added in front
of history.

```
                          history
                     next |
                     head |
                     |    |
                C    F    D    E    B    A
              <--->     <------------------>
      determinated          history range
             range
```

The playlist calls `Next()`, the randomizer randomly selects `E`. `E`
"disappears" from the history of the last cycle. This is a general property:
each item may not appear more than one in the "history" (both from the last
and the new cycle). The history order is preserved.

```
                               history
                          next |
                          head |
                          |    |
                C    E    F    D    B    A
              <-------->     <-------------->
             determinated     history range
                range
```

The playlist then calls `Prev()` 3 times, that yield `C`, then `A`, then `B`.
`next` is decremented (modulo size) on each call.

```
                               history
                               |    next
                          head |    |
                          |    |    |
                C    E    F    D    B    A
              <-------->     <-------------->
             determinated     history range
                range
```

Hopefully, the resulting randomness will match what people expect in practice.


## Sorting

The playlist can be [sorted][sort] by an ordered list of criteria (a
[key][sort-key] and a [order][sort-order]).

The implementation is complicated by the fact that items metadata can change
asynchronously (for example if the player is parsing it), making the comparison
function inconsistent.

To avoid the problem, a first pass builds a [list of metadata] for all items, then
this list is [sorted], and finally the resulting order is [applied back] to the
playlist.

As a benefit, the items are locked only once to retrieved their metadata.

[sort]: https://code.videolan.org/videolan/vlc/blob/051237a0ef4fe39d2f01850db28f9306dd4a7b83/include/vlc_playlist.h#L631
[sort-key]: https://code.videolan.org/videolan/vlc/blob/051237a0ef4fe39d2f01850db28f9306dd4a7b83/include/vlc_playlist.h#L128
[sort-order]: https://code.videolan.org/videolan/vlc/blob/051237a0ef4fe39d2f01850db28f9306dd4a7b83/include/vlc_playlist.h#L143
[list of metadata]: https://code.videolan.org/videolan/vlc/blob/051237a0ef4fe39d2f01850db28f9306dd4a7b83/src/playlist/sort.c#L374
[sorted]: https://code.videolan.org/videolan/vlc/blob/051237a0ef4fe39d2f01850db28f9306dd4a7b83/src/playlist/sort.c#L381
[applied back]: https://code.videolan.org/videolan/vlc/blob/051237a0ef4fe39d2f01850db28f9306dd4a7b83/src/playlist/sort.c#L383


## Interaction with the player

For VLC 4, [Thomas][tguillem] wrote a [new player API].

A _player_ can be used without a _playlist_: we can set its [current media] and
the player can request, when necessary, the next media to play from a [media
provider].

A _playlist_, on the other hand, needs a _player_, and registers itself as its
media provider. They are tightly coupled:
 - the playlist controls the player on user actions;
 - the player events may update the playlist state.

To keep them synchronized:

 - on user actions, we need to lock the playlist, then the player;
 - on player events, we need to lock the player, then the playlist.

This poses a lock-order inversion problem: for example, if thread A locks the
playlist then waits for the player lock, while thread B locks the player then
waits for the playlist lock, then thread A and B are [deadlocked].

To avoid the problem, the _player_ and the _playlist_ share the same lock.
Concretely, `vlc_playlist_Lock()` delegates to `vlc_player_Lock()`. In practice,
the lock should be held only for short periods of time.

[tguillem]: https://twitter.com/tguill3m
[new player API]: https://code.videolan.org/videolan/vlc/blob/051237a0ef4fe39d2f01850db28f9306dd4a7b83/include/vlc_player.h
[current media]: https://code.videolan.org/videolan/vlc/blob/051237a0ef4fe39d2f01850db28f9306dd4a7b83/include/vlc_player.h#L1082
[media provider]: https://code.videolan.org/videolan/vlc/blob/051237a0ef4fe39d2f01850db28f9306dd4a7b83/include/vlc_player.h#L354
[deadlocked]: https://en.wikipedia.org/wiki/Deadlock


## Media source

A separate API ([_media source_ and _media tree_][media-source]) was necessary
to expose what is called _services discovery_ (used to detect media from various
sources like [Samba] or [MTP]), which were previously managed by the old
playlist.

Thus, we could [kill][remove-playlist-legacy] the old playlist.

[media-source]: https://code.videolan.org/videolan/vlc/commit/3e0cc1942a963693cf97c99a5ab1e9c6171fe6b1
[Samba]: https://fr.wikipedia.org/wiki/Server_Message_Block
[MTP]: https://en.wikipedia.org/wiki/Media_Transfer_Protocol


## Conclusion

The new playlist and player API should help to implement UI properly _(spoiler:
a new [modern UI] is being developed)_, to avoid racy bugs and to implement new
features _(spoiler: [gapless])_.

[modern UI]: https://www.youtube.com/watch?v=jzvC-0WCjKU&t=841
[gapless]: https://en.wikipedia.org/wiki/Gapless_playback

_Discuss on [reddit] and [Hacker News]._

[reddit]: https://www.reddit.com/r/programming/comments/br7or7/a_new_core_playlist_for_vlc_4/
[Hacker News]: https://news.ycombinator.com/item?id=19978295

---
