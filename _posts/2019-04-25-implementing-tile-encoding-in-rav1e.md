---
layout: post
title: Implementing tile encoding in rav1e
date: 2019-04-25 10:15:00+02:00
lang: en
---

During the last few months at [Videolabs], I added support for [tile
encoding][#1126] in [rav1e] (a Rust AV1 Encoder).

[Videolabs]: https://videolabs.io
[#1126]: https://github.com/xiph/rav1e/pull/1126
[rav1e]: https://github.com/xiph/rav1e

## What is this?

[AV1] is an open and royalty-free video coding format, concurrent with [HEVC]
(H.265).

[Rav1e][VDD-rav1e] is an encoder written in [Rust], developped by
[Mozilla][AV1-mozilla]/[Xiph]. As such, it takes an input video and encodes it
to produce a valid AV1 bitstream.

[rust]: https://www.rust-lang.org/
[AV1-mozilla]: https://research.mozilla.org/av1-media-codecs/
[xiph]: https://xiph.org/
[AV1]: https://en.wikipedia.org/wiki/AV1
[HEVC]: https://en.wikipedia.org/wiki/HEVC
[VDD-rav1e]: https://www.youtube.com/watch?v=ytsRYKQc6kQ


### Tile encoding

Tile encoding consists in splitting video frames into _tiles_ that can be
encoded and decoded independently in parallel (to use several CPUs), at the cost
of a small loss in compression efficiency.

This speeds up encoding and increases decoding frame rate.

{: .center}
[![tiles][tiles]][tiles]  
_8 tiles (4 colums × 2 rows)_

[tiles]: https://raw.githubusercontent.com/rom1v/blog.rom1v.com/master/assets/rav1e_tile_encoding/tiles.jpg


## Preliminary work

To prepare for tiling, some refactoring was necessary.

A _frame_ contains 3 _planes_ (one for each [YUV] component, possibly
[subsampled]). Each plane is stored in a contiguous array, rows after rows.

To illustrate it, here is a mini-plane containing 6×3 pixels. Padding is
added for alignment (and other details), so its physical size is 8×4 pixels:

{: .center .no-radius}
![plane]({{ site.assets }}/rav1e_tile_encoding/plane.png)

In memory, it is stored in a single array:

{: .center .no-radius}
![plane memory]({{ site.assets }}/rav1e_tile_encoding/plane_memory.png)

The number of array items separating one pixel to the one below is called the
[stride]. Here, the stride is 8.

The encoder often needs to process rectangular regions. For that purpose, many
functions received a slice of the plane array and the stride value:

{% highlight rust %}
pub fn write_forty_two(slice: &mut [u16], stride: usize) {
  for y in 0..2 {
    for x in 0..4 {
      slice[y * stride + x] = 42;
    }
  }
}
{% endhighlight %}

This works fine, but the plane slice spans multiple rows.

Let's split our planes into 4 tiles (2 columns × 2 rows):

{: .center .no-radius}
![plane regions]({{ site.assets }}/rav1e_tile_encoding/plane_regions.png)

In memory, the resulting plane regions are not contiguous:

{: .center .no-radius}
![plane regions memory]({{ site.assets }}/rav1e_tile_encoding/plane_regions_memory.png)

In Rust, it is not sufficient not to read/write the same memory from several
threads, it must be impossible to write (safe) code that could do it. More
precisely, a mutable reference may not alias any other reference to the same
memory.

As a consequence, passing **a mutable slice** (`&mut [u16]`) **spanning multiple
rows is incompatible with tiling**. Instead, we need some structure, implemented
with [unsafe] code, providing a view of the authorized region of the underlying
plane.

As a first step, I replaced every piece of code which used a raw slice and the
stride value by the existing [`PlaneSlice`][#1035] and [`PlaneMutSlice`][#1043]
structures (which first required to [make planes generic][#1002] after
[improving the `Pixel` trait][#996]).

After these changes, our function could be rewritten as follow:

{% highlight rust %}
pub fn write_forty_two<T: Pixel>(slice: &mut PlaneMutSlice<'_, T>) {
  for y in 0..2 {
    for x in 0..4 {
      slice[y][x] = 42;
    }
  }
}
{% endhighlight %}


[YUV]: https://en.wikipedia.org/wiki/YUV
[subsampled]: https://en.wikipedia.org/wiki/Chroma_subsampling
[stride]: https://en.wikipedia.org/wiki/Stride_of_an_array
[unsafe]: https://doc.rust-lang.org/book/ch19-01-unsafe-rust.html
[#996]: https://github.com/xiph/rav1e/pull/996
[#1002]: https://github.com/xiph/rav1e/pull/1002
[#1035]: https://github.com/xiph/rav1e/pull/1035
[#1043]: https://github.com/xiph/rav1e/pull/1043
[#1068]: https://github.com/xiph/rav1e/pull/1068
[u8-speedup]: https://github.com/xiph/rav1e/pull/1061#issuecomment-470092647
[barrbrain]: https://github.com/barrbrain



## Tiling structures

So now, all the code using a raw slice and a stride value has been replaced. But
if we look at the definition of [`PlaneMutSlice`], we see that it still borrows
the whole plane:

{% highlight rust %}
pub struct PlaneMutSlice<'a, T: Pixel> {
  pub plane: &'a mut Plane<T>,
  pub x: isize,
  pub y: isize
}
{% endhighlight %}

So the refactoring, in itself, does not solves the problem.

What is needed now is a structure that exposes a bounded region of the plane.

### Minimal example

For illustration purpose, let's consider a minimal example, solving a similar
problem: **split a matrix into columns**.

{: .center .no-radius}
![2D array]({{ site.assets }}/rav1e_tile_encoding/2d_array.png)

In memory, the matrix is stored in a single array:

{: .center .no-radius}
![2D array memory]({{ site.assets }}/rav1e_tile_encoding/2d_array_memory.png)

To do so, let's define a `ColumnMut` type, and split the raw array into columns:

{% highlight rust %}
use std::marker::PhantomData;
use std::ops::{Index, IndexMut};

pub struct ColumnMut<'a> {
    data: *mut u8,
    cols: usize,
    rows: usize,
    phantom: PhantomData<&'a mut u8>,
}

impl Index<usize> for ColumnMut<'_> {
    type Output = u8;
    fn index(&self, index: usize) -> &Self::Output {
        assert!(index < self.rows);
        unsafe { &*self.data.add(index * self.cols) }
    }
}

impl IndexMut<usize> for ColumnMut<'_> {
    fn index_mut(&mut self, index: usize) -> &mut Self::Output {
        assert!(index < self.rows);
        unsafe { &mut *self.data.add(index * self.cols) }
    }
}

pub fn columns(slice: &mut [u8], cols: usize) -> impl Iterator<Item = ColumnMut> {
    assert!(slice.len() % cols == 0);
    let rows = slice.len() / cols;
    (0..cols).map(move |col| ColumnMut {
        data: &mut slice[col],
        cols,
        rows,
        phantom: PhantomData,
    })
}
{% endhighlight %}

The [`PhantomData`][phantom] is necessary to bind the lifetime (in practice,
when we store a raw pointer, we often need a `PhantomData`).

We implemented [`Index`] and [`IndexMut`] traits to provide operator `[]`:

{% highlight rust %}
// via Index trait
let value = column[y];

// via IndexMut trait
column[y] = value;
{% endhighlight %}

The iterator returned by `columns()` yields a different column every time, so
the borrowing rules are respected.

Now, we can read from and write to a matrix via temporary column views:

{% highlight rust %}
fn main() {
    let mut data = [1, 5, 3, 2,
                    4, 2, 1, 7,
                    0, 0, 0, 0];

    // for each column, write the sum
    columns(&mut data, 4).for_each(|mut col| col[2] = col[0] + col[1]);

    assert_eq!(data, [1, 5, 3, 2,
                      4, 2, 1, 7,
                      5, 7, 4, 9]);
}
{% endhighlight %}

Even if the columns are interlaced in memory, from a `ColumnMut` instance, it is
not possible to access data belonging to another column.

_Note that `cols` and `rows` fields must be kept private, otherwise they could
be changed from safe code in such a way that breaks boundaries and violates
borrowing rules._

[`PlaneMutSlice`]: https://github.com/xiph/rav1e/blob/65ac94db7ba3c67c967b96e80c724e17b7414812/src/plane.rs#L582-L586
[phantom]: https://doc.rust-lang.org/nomicon/phantom-data.html
[`Index`]: https://doc.rust-lang.org/std/ops/trait.Index.html
[`IndexMut`]: https://doc.rust-lang.org/std/ops/trait.IndexMut.html



### In rav1e

A plane is split in a similar way, except that it provides _plane regions_
instead of _colums_.

The split is _recursive_. For example, a [`Frame`] contains 3 [`Plane`]s, so a
[`Tile`] contains 3 [`PlaneRegion`]s, using the same underlying memory.

In practice, more structures related to the encoding state are split into tiles,
provided both in _const_ and _mut_ versions, so there is a whole hierarchy of
tiling structures:

```
 +- FrameState → TileState
 |  +- Frame → Tile
 |  |  +- Plane → PlaneRegion
 |  +  RestorationState → TileRestorationState
 |  |  +- RestorationPlane → TileRestorationPlane
 |  |     +- FrameRestorationUnits → TileRestorationUnits
 |  +  FrameMotionVectors → TileMotionVectors
 +- FrameBlocks → TileBlocks
```

The split is done by a separate component (see [`tiler.rs`]), which yields a
_tile context_ containing an instance of the hierarchy of tiling views for each
tile.


### Relative offsets

A priori, there are mainly two possibilities to express offsets during tile
encoding:
 - relative to the tile;
 - relative to the frame (i.e. absolute).

The usage of tiling views strongly favors the first choice. For example, it
would be confusing if a bounded region could not be indexed from 0:

{% highlight rust %}
// region starting at (64, 64)
let row = &region[0]; // panic, out-of-bounds
let row = &region[64]; // ok :-/
{% endhighlight %}

Worse, this would not be possible at all for the second dimension:

{% highlight rust %}
// region starting at (64, 64)
let first_row = &region[64];
let first_column = row[64]; // wrong, a raw slice necessarily starts at 0
{% endhighlight %}

Therefore, offsets used in tiling views are relative to the tile (contrary to
_libaom_ and AV1 specification).


[`Frame`]: https://github.com/xiph/rav1e/blob/65ac94db7ba3c67c967b96e80c724e17b7414812/src/encoder.rs#L45-L47
[`Plane`]: https://github.com/xiph/rav1e/blob/65ac94db7ba3c67c967b96e80c724e17b7414812/src/plane.rs#L136-L139
[`Tile`]: https://github.com/xiph/rav1e/blob/65ac94db7ba3c67c967b96e80c724e17b7414812/src/tiling/tile.rs#L98-L100
[`PlaneRegion`]: https://github.com/xiph/rav1e/blob/65ac94db7ba3c67c967b96e80c724e17b7414812/src/tiling/plane_region.rs#L109-L115
[`tiler.rs`]: https://github.com/xiph/rav1e/blob/65ac94db7ba3c67c967b96e80c724e17b7414812/src/tiling/tiler.rs


## Tile encoding

Encoding a frame first involves frame-wise accesses (initialization), then
tile-wise accesses (to encode tiles in parallel), then frame-wise accesses using
the results of tile-encoding ([deblocking], [CDEF], [loop restoration], …).

All the frame-level structures have been replaced by tiling views where
necessary.

The tiling views exist only temporarily, during the calls to
[`encode_tile()`][encode_tile]. While they are alive, it is not possible to
access frame-level structures (the borrow checker statically prevents it).

Then the tiling structures vanish, and frame-level processing can continue.

[encode_tile]: https://github.com/xiph/rav1e/blob/65ac94db7ba3c67c967b96e80c724e17b7414812/src/encoder.rs#L2113-L2122
[deblocking]: https://en.wikipedia.org/wiki/Deblocking_filter
[cdef]: https://hacks.mozilla.org/2018/06/av1-next-generation-video-the-constrained-directional-enhancement-filter/
[loop restoration]: https://www.youtube.com/watch?v=On9VOnIBSEs&t=1335

This [schema][#1126] gives an overview:

```
                                \
      +----------------+         |
      |                |         |
      |                |         |  Frame-wise accesses
      |                |          >
      |                |         |   - FrameState<T>
      |                |         |   - Frame<T>
      +----------------+         |   - Plane<T>
                                /    - ...

              ||   tiling views
              \/
                                \
  +---+  +---+  +---+  +---+     |
  |   |  |   |  |   |  |   |     |  Tile encoding (possibly in parallel)
  +---+  +---+  +---+  +---+     |
                                 |
  +---+  +---+  +---+  +---+     |  Tile-wise accesses
  |   |  |   |  |   |  |   |      >
  +---+  +---+  +---+  +---+     |   - TileStateMut<'_, T>
                                 |   - TileMut<'_, T>
  +---+  +---+  +---+  +---+     |   - PlaneRegionMut<'_, T>
  |   |  |   |  |   |  |   |     |
  +---+  +---+  +---+  +---+     |
                                /

              ||   vanishing of tiling views
              \/
                                \
      +----------------+         |
      |                |         |
      |                |         |  Frame-wise accesses
      |                |          >
      |                |         |  (deblocking, CDEF, ...)
      |                |         |
      +----------------+         |
                                /
```

## Command-line

To enable tile encoding, parameters have been added to pass the (log2) number of
tiles `--tile-cols-log2` and `--tile-rows-log2`. For example, to request 2x2
tiles:

    rav1e video.y4m -o video.ivf --tile-cols-log2 1 --tile-rows-log2 1

_Currently, we need to pass the log2 of the number of tiles (like in libaom,
even if the `aomenc` options are called `--tile-columns` and `--tile-rows`), to
avoid any confusion.  Maybe we could find a better option which is both correct,
non-confusing and user-friendly later._

## Bitstream

Now that we can encode tiles, we must write them according to the [AV1
bitstream specification][spec], so that decoders can read the resulting file
correctly.

Before tile encoding (i.e. with a single tile), rav1e produced a correct
bitstream. Several changes were necessary to write multiple tiles.

[spec]: https://aomediacodec.github.io/av1-spec/

### Tile info

According to [Tile info syntax], the [frame header] signals the number of columns
and rows of tiles (it always signaled a single tile before).

In addition, when there are several tiles, it signals two more values, described
below.

[Tile info syntax]: https://aomediacodec.github.io/av1-spec/#tile-info-syntax
[frame header]: https://github.com/xiph/rav1e/blob/65ac94db7ba3c67c967b96e80c724e17b7414812/src/header.rs#L620-L649

#### CDF update

For [entropy coding], the encoder maintain and update a CDF (Cumulative
Distribution Function), representing the probabilities of symbols.

After a frame is encoded, the current CDF state is saved to be possibly used as
a starting state for future frames.

But with tile encoding, each tile finishes with its own CDF state, so which one
should we associate to the reference frame? The answer is: any of them. But we
must signal the one we choose, in `context_update_tile_id`; the decoder needs it
to decode the bitstream.

In practice, we keep the CDF from the [biggest tile][commit-biggest-tile].

[entropy coding]: https://en.wikipedia.org/wiki/Entropy_encoding
[commit-biggest-tile]: https://github.com/xiph/rav1e/commit/ec82d8016db737de51977effb7746eb1137d675b

#### Size of tiles size

The size of an encoded tile, in bytes, is variable (of course). Therefore, we
will need to signal the size of each tile.

To gain a few bytes, the number of bytes used to store the size itself is also
variable, and signaled by 2 bits in the frame header
(`tile_size_bytes_minus_1`).

Concretely, we must choose the [smallest size][commit-tile-size] that is
sufficient to encode all the tile sizes for the frame.

[commit-tile-size]: https://github.com/xiph/rav1e/commit/9a76ff083d97e39b3314f36576994ea99076f996


### Tile group

According to [General tile group OBU syntax], we need to [signal][tile group]
two values when there are more than 1 tile:
 - `tile_start_and_end_present_flag` (we always disable it);
 - `tile_size_minus_1`.

The tile size (minus 1) is written in [little endian][endianness], and use the
number of bytes we signaled in the frame header.

That's all. This is sufficient to produce a correct bitstream with multiple
tiles.

[General tile group OBU syntax]: https://aomediacodec.github.io/av1-spec/#general-tile-group-obu-syntax
[tile group]: https://github.com/xiph/rav1e/blob/65ac94db7ba3c67c967b96e80c724e17b7414812/src/encoder.rs#L2177-L2195
[endianness]: https://en.wikipedia.org/wiki/Endianness



## Parallelization

Thanks to [Rayon], [parallelizing] tile encoding is as easy as replacing
`iter_mut()` by `par_iter_mut()`.


[Rayon]: https://github.com/rayon-rs/rayon
[parallelizing]: https://github.com/xiph/rav1e/commit/156cc72edf03b5605844b4ecae84dee647fda221


I tested on my laptop (8 CPUs) several encodings to compare encoding performance
(this is not a good benchmark, but it gives an idea, you are encouraged to run
your own tests). Here are the [results][perfs]:

```
 tiles     time      speedup
   1    7mn02,336s    1.00×
   2    3mn53,578s    1.81×
   4    2mn12,995s    3.05×
   8    1mn57,533s    3.59×
```

_Speedups are quite good for 2 and 4 tiles._

### Limits

Why not 2×, 4× and 8× speedup? Mainly because of [Amdahl's law].

Tile encoding parallelizes only a part of the whole process: there are still
single-threaded processings at frame-level.

Suppose that a proportion _p_ (between 0 and 1) of a given task can be
parallelized. Then its theoretical speedup is `1 / ((p/n) + (1-p))`, where _n_
is the number of threads.

```
 tiles   speedup   speedup   speedup
         (p=0.9)   (p=0.95)  (p=0.98)
   2      1.82×     1.90×     1.96×
   4      3.07×     3.48×     3.77×
   8      4.71×     5.93×     7.02×
```

Maybe counterintuitively, **to increase the speedup brought by parallelization,
non-parallelized code must be optimized** (the more threads are used, the more
the non-parallelized code represents a significant part).

The (not-so-reliable) benchmark results for 2 and 4 tiles suggest that tile
encoding represents ~90% of the whole encoding process.

The speedup for 8 tiles in lower than expected, though. ~~I don't know why, but
we can make hypotheses.~~

One possible cause (suggested by [ycho]) is the difference in encoding time
between tiles. The frame-level processing can start only once _all_ the tiles
are encoded. The more tiles there are, the more likely that one is much longer
than the others.

The difference might also simply be explained by the fact that my laptop has 8
CPU, so none was left for any background execution.

**EDIT:** The reason is that my CPU has actually only 4 physical cores. See
[this reddit comment][reddit-cpu-1] and [this other one][reddit-cpu-2].

[perfs]: https://github.com/xiph/rav1e/pull/1126#issuecomment-484667610
[Amdahl's law]: https://en.wikipedia.org/wiki/Amdahl%27s_law#Parallel_programs
[ycho]: https://github.com/ycho
[reddit-cpu-1]: https://www.reddit.com/r/programming/comments/bh6sq8/implementing_tile_encoding_in_rav1e_a_rust_av1/elrl5yo/
[reddit-cpu-2]: https://www.reddit.com/r/rust/comments/bh8xnl/implementing_tile_encoding_in_rav1e_a_rust_av1/elrloye/


## Fixing bugs

Not everything worked the first time.

The most common source of errors while implementing tile encoding was related to
offsets.

When there was only one tile, all offsets were relative to the frame. With
several tiles, some offsets are relative to the current tile, but some others
are still relative to the whole frame. For example, during [motion estimation],
a motion vector can point outside tile boundaries in the reference frame, so we
must take care to convert offsets accordingly.

The most obvious errors were catched by _plane regions_ (which prevent access
outside boundaries), but some others were more subtle.

Such errors could produce interesting images. For example, here is a screenshot
of my first tiled video:

{: .center }
[![bbb][bbb]][bbb]

[bbb]: https://raw.githubusercontent.com/rom1v/blog.rom1v.com/master/assets/rav1e_tile_encoding/bbb_tiling.jpg

One of these offsets confusions had been quickly catched by [barrbrain] in
[intra-prediction][intrabug]. I then fixed a similar problem in
[inter-prediction][interbug].

But the [final boss] bug was way more sneaky: it corrupted the bitstream (so the
encoder was unable to decode), but not always, and never the first frame. When
an inter-frame could be decoded, it was sometimes [visually corrupted], but only
for some videos and for some encoding parameters.

After more than one week of investigations, I finally [found it][finalbug].
`\o/`

[barrbrain]: https://github.com/barrbrain/
[motion estimation]: https://en.wikipedia.org/wiki/Motion_estimation
[intrabug]: https://github.com/xiph/rav1e/commit/855b6d06cd2c321d50b7bab8a339c98833502bf3
[interbug]: https://github.com/xiph/rav1e/commit/bab3903425a1a9086613de5473bd4282c416c671
[final boss]: https://en.wikipedia.org/wiki/Boss_(video_gaming)#Final_boss
[visually corrupted]: https://github.com/xiph/rav1e/pull/1126#issuecomment-482597763
[finalbug]: https://github.com/xiph/rav1e/commit/4984e0737984fd0d31894d5d5ebc8e89a248c3ab


## Conclusion

Implementing this feature was an awesome journey. I learned a lot, both about
Rust and video encoding (I didn't even know what a tile was before I started).

Big thanks to the Mozilla/Xiph/Daala team, who has been very welcoming and
helpful, and who does amazing work!

_Discuss on [r/programming], [r/rust], [r/AV1] and [Hacker News]._

[r/programming]: https://www.reddit.com/r/programming/comments/bh6sq8/implementing_tile_encoding_in_rav1e_a_rust_av1/
[r/rust]: https://www.reddit.com/r/rust/comments/bh8xnl/implementing_tile_encoding_in_rav1e_a_rust_av1/
[r/AV1]: https://www.reddit.com/r/AV1/comments/bh8xsy/implementing_tile_encoding_in_rav1e/
[Hacker News]: https://news.ycombinator.com/item?id=19746392
