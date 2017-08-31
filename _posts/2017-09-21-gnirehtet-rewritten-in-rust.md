---
layout: post
title: Gnirehtet rewritten in Rust
date: 2017-09-21 17:00:00+02:00
lang: en
langref:
- lang: fr
  url: /2017/09/gnirehtet-reecrit-en-rust
  internal: true
---

Several months ago, I introduced [Gnirehtet][medium], a reverse tethering tool
for Android I wrote in Java.

Since then, **I rewrote it in [Rust]**.

And it's also open source! [Download it][gnirehtet], plug an Android device,
and execute:

    ./gnirehtet run

_(adb must be installed)_

[medium]: https://medium.com/genymobile/gnirehtet-reverse-tethering-android-2afacdbdaec7
[Rust]: https://www.rust-lang.org/
[gnirehtet]: https://github.com/Genymobile/gnirehtet
[release]: https://github.com/Genymobile/gnirehtet/releases/latest

[`dev`]: https://github.com/Genymobile/gnirehtet/tree/dev
[`DEVELOP`]: https://github.com/Genymobile/gnirehtet/blob/dev/DEVELOP.md

* TOC
{:toc}

## Why Rust?

At Genymobile, we wanted _Gnirehtet_ not to require the _Java Runtime
Environment_, so the main requirement was to compile the application to a
_native_ executable binary.

Therefore, I first considered rewriting it in C or C++. But at that time (early
May), I was interested in learning Rust, after vaguely hearing what it
[provided][slogans], namely:
 - [memory safety without garbage collection][memory-concurrency],
 - [concurrency without data races][memory-concurrency],
 - [abstraction without overhead][zerocost].

[slogans]: https://blog.rust-lang.org/2017/02/06/roadmap.html
[memory-concurrency]: https://blog.rust-lang.org/2015/04/10/Fearless-Concurrency.html
[zerocost]: https://blog.rust-lang.org/2015/05/11/traits.html

However, I had never written a line of Rust code nor heard about Rust
[ownership], [borrowing] or [lifetimes].

[ownership]: https://doc.rust-lang.org/book/first-edition/ownership.html
[borrowing]: https://doc.rust-lang.org/book/first-edition/references-and-borrowing.html
[lifetimes]: https://doc.rust-lang.org/book/first-edition/lifetimes.html

But I am convinced that the best way to learn a programming language is to work
full-time on a real project in that language.

I was motivated, so after checking that it could fit our requirements
(basically, I wrote a sample using the [async I/O] library [mio], and
executed it on both Linux and Windows), I decided to rewrite _Gnirehtet_ in
Rust.

[async I/O]: https://en.wikipedia.org/wiki/Asynchronous_I/O
[mio]: https://crates.io/crates/mio


## Learning Rust

During the rewriting, I _devoured_ successively the [Rust book], [Rust by
example] and the [Rustonomicon]. I learned a lot, and _Rust_ is an awesome
language. I now miss many of its features when I work on a C++ project,
including:
 - [advanced type inference][inference],
 - [enums],
 - [patterns],
 - [trait bounds],
 - [`Option<T>`] (like [`std::optional<T>`] in C++17, but benefiting from enums
   and patterns),
 - [hygienic macros][macros],
 - the absence of header files,
 - the (so simple) build system, _and of course_
 - guaranteed memory safety.

[Rust book]: https://doc.rust-lang.org/book/first-edition/
[Rust by example]: https://rustbyexample.com/
[Rustonomicon]: https://doc.rust-lang.org/nomicon/
[inference]: https://rustbyexample.com/cast/inference.html
[enums]: https://doc.rust-lang.org/book/first-edition/enums.html
[patterns]: https://doc.rust-lang.org/book/first-edition/patterns.html
[`Option<T>`]: https://doc.rust-lang.org/std/option/
[macros]: https://doc.rust-lang.org/book/first-edition/macros.html
[`std::optional<T>`]: https://github.com/tvaneerd/cpp17_in_TTs/blob/master/ALL_IN_ONE.md#stdoptionalt
[trait bounds]: https://doc.rust-lang.org/book/first-edition/traits.html

About learning, Paul Graham [wrote][quote]:

> **Reading and experience train your model of the world.** And even if you
> forget the experience or what you read, its effect on your model of the world
> persists. Your mind is like a compiled program you've lost the source of. It
> works, but you don't know why.

Some of Rust concepts (like [lifetimes] or [move semantics] by default)
provided a significantly different new _training set_ which definitely affected
my model of the world (of programming).

[move semantics]: https://doc.rust-lang.org/book/first-edition/ownership.html#move-semantics
[quote]: http://paulgraham.com/know.html

I am not going to present all these features (just click on the links to the
documentation if you are interested). Instead, I will try to explain where and
why Rust resisted to the design I wanted to implement, and how to rethink the
problems within Rust constraints.

_The following part requires some basic knowledge of Rust. You may want to skip
directly to the [stats](#stats)._


## Difficulties

The [design] of the Java application was pretty effective, so I wanted to
reproduce the global architecture in the Rust version (with adaptations to make
it more _Rust_ idiomatic if necessary).

But I struggled on the details, especially to make the [_borrow
checker_][borrowing] happy. The [rules] are simple:

> First, any borrow must last for a scope no greater than that of the owner.
> Second, you may have one or the other of these two kinds of borrows, but not
> both at the same time:
>  - one or more references (`&T`) to a resource,
>  - exactly one mutable reference (`&mut T`).

However, it took me some time to realize how they conflict with some patterns or
principles.

Here are my feedbacks. I selected 4 subjects which are general enough to be
independent of this particular project:
 - the conflicts with [encapsulation](#encapsulation);
 - the [observer](#observer) pattern;
 - how to [share mutable data](#mutable-data-sharing);
 - a quick note about annoying [compiler limitations](#compiler-limitations).

[design]: https://github.com/Genymobile/gnirehtet/blob/master/DEVELOP.md#relay-server
[rules]: https://doc.rust-lang.org/book/first-edition/references-and-borrowing.html#the-rules


### Encapsulation

**The borrowing rules constrain encapsulation.** This was the first consequence
I realized.

Here is a canonical sample:

{% highlight rust %}
pub struct Data {
    header: [u8; 4],
    payload: [u8; 20],
}

impl Data {
    pub fn new() -> Self {
        Self {
            header: [0; 4],
            payload: [0; 20],
        }
    }

    pub fn header(&mut self) -> &mut [u8] {
        &mut self.header
    }

    pub fn payload(&mut self) -> &mut [u8] {
        &mut self.payload
    }
}

fn main() {
    let mut data = Data::new();
    let header = data.header();
    let payload = data.payload();
}
{% endhighlight %}

We just create a new instance of `Data`, then bind mutable references to the
`header` and `payload` arrays to local variables, through accessors.

However, this does not compile:

```
$ rustc sample.rs
error[E0499]: cannot borrow `data` as mutable more than once at a time
  --> sample.rs:21:19
   |
25 |     let header = data.header();
   |                  ---- first mutable borrow occurs here
26 |     let payload = data.payload();
   |                   ^^^^ second mutable borrow occurs here
27 | }
   | - first borrow ends here
```

The compiler may not assume that `header()` and `payload()` return references to
disjoint data in the `Data` struct. Therefore, each one borrows the whole `data`
structure. Since the borrowing rules forbid to get two mutables references to
the same resource, it rejects the second call.

Sometimes, we face temporary limitations because the compiler is not smart
enough (yet). This is not the case here: the implementation of `header()` might
actually return a reference to `payload`, or write to the `payload` array,
violating the borrowing rules. And the validity of a method call may not depend
on the method implementation.

To fix the problem, the compiler must be able to know that the local variables
`header` and `payload` reference **disjoint data**, for example by accessing
the fields directly:

{% highlight rust %}
    let header = &mut data.header;
    let payload = &mut data.payload;
{% endhighlight %}

or by exposing a method providing both references simultaneously:

{% highlight rust %}
struct Data {
    fn header_and_payload(&mut self) -> (&mut [u8], &mut [u8]) {
        (&mut self.header, &mut self.payload)
    }
}

fn main() {
    let mut data = Data::new();
    let (header, payload) = data.header_and_payload();
}
{% endhighlight %}

Similarly, inside a struct implementation, the borrowing rules also prevent
factoring code into a private method easily. Consider this (artificial) example:

{% highlight rust %}
pub struct Data {
    buf: [u8; 20],
    prefix_length: usize,
    sum: u32,
    port: u16,
}

impl Data {
    pub fn update_sum(&mut self) {
        let content = &self.buf[self.prefix_length..];
        self.sum = content.iter().cloned().map(u32::from).sum();
    }

    pub fn update_port(&mut self) {
        let content = &self.buf[self.prefix_length..];
        self.port = (content[2] as u16) << 8 | content[3] as u16;
    }
}
{% endhighlight %}

Here, the `buf` field is an array storing some prefix and content contiguously.

We want to factorize the way we retrieve the `content` slice, so that
the `update_*()` methods are not bothered with the details. Let's try:

{% highlight diff %}
 impl Data {
     pub fn update_sum(&mut self) {
-        let content = &self.buf[self.prefix_length..];
+        let content = self.content();
         self.sum = content.iter().cloned().map(u32::from).sum();
     }

     pub fn update_port(&mut self) {
-        let content = &self.buf[self.prefix_length..];
+        let content = self.content();
         self.port = (content[2] as u16) << 8 | content[3] as u16;
     }
+
+    fn content(&mut self) -> &[u8] {
+        &self.buf[self.prefix_length..]
+    }
 }
{% endhighlight %}

Unfortunately, this does not compile:

```
error[E0506]: cannot assign to `self.sum` because it is borrowed
  --> facto2.rs:11:9
   |
10 |         let content = self.content();
   |                       ---- borrow of `self.sum` occurs here
11 |         self.sum = content.iter().cloned().map(u32::from).sum();
   |         ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ assignment to borrowed `self.sum` occurs here

error[E0506]: cannot assign to `self.port` because it is borrowed
  --> facto2.rs:16:9
   |
15 |         let content = self.content();
   |                       ---- borrow of `self.port` occurs here
16 |         self.port = (content[2] as u16) << 8 & content[3] as u16;
   |
```

As in the previous exemple, retrieving the reference through a method borrows
the whole struct (here, `self`).

To workaround the problem, we can explain to the compiler that the fields are
disjoint:

{% highlight rust %}
impl Data {
    pub fn update_sum(&mut self) {
        let content = Self::content(&self.buf, self.prefix_length);
        self.sum = content.iter().cloned().map(u32::from).sum();
    }

    pub fn update_port(&mut self) {
        let content = Self::content(&self.buf, self.prefix_length);
        self.port = (content[2] as u16) << 8 | content[3] as u16;
    }

    fn content(buf: &[u8], prefix_length: usize) -> &[u8] {
        &buf[prefix_length..]
    }
}
{% endhighlight %}

This compiles, but totally defeats the purpose of factorization: the caller has
to provide the necessary fields.

As an alternative, we can use a [macro][macros] to _inline_ the code:

{% highlight rust %}
macro_rules! content {
    ($self:ident) => {
        &$self.buf[$self.prefix_length..]
    }
}

impl Data {
    pub fn update_sum(&mut self) {
        let content = content!(self);
        self.sum = content.iter().cloned().map(u32::from).sum();
    }

    pub fn update_port(&mut self) {
        let content = content!(self);
        self.port = (content[2] as u16) << 8 | content[3] as u16;
    }
}
{% endhighlight %}

But this seems far from ideal.

I think we must just live with it: encapsulation sometimes conflicts with the
borrowing rules. After all, this is not so surprising: enforcing the borrowing
rules requires to follow every concrete access to resources, while
encapsulation aims to abstract them away.


### Observer

The [_observer_ pattern][observer] is useful for registering event listeners on
an object.

In some cases, **this pattern may not be straightforward to implement in Rust**.

For simplicity, let's consider that the events are `u32` values. Here
is a possible implementation:

[observer]: https://en.wikipedia.org/wiki/Observer_pattern

{% highlight rust %}
pub trait EventListener {
    fn on_event(&self, event: u32);
}

pub struct Notifier {
    listeners: Vec<Box<EventListener>>,
}

impl Notifier {
    pub fn new() -> Self {
        Self { listeners: Vec::new() }
    }

    pub fn register<T: EventListener + 'static>(&mut self, listener: T) {
        self.listeners.push(Box::new(listener));
    }

    pub fn notify(&self, event: u32) {
        for listener in &self.listeners {
            listener.on_event(event);
        }
    }
}
{% endhighlight %}

For convenience, make closures implement our `EventListener` trait:

{% highlight rust %}
impl<F: Fn(u32)> EventListener for F {
    fn on_event(&self, event: u32) {
        self(event);
    }
}
{% endhighlight %}

Thus, its usage is simple:

{% highlight rust %}
    let mut notifier = Notifier::new();
    notifier.register(|event| println!("received [{}]", event));
    println!("notifying...");
    notifier.notify(42);
{% endhighlight %}

This prints:

```
notifying...
received [42]
```

So far, so good.

However, things get a bit more complicated if we want to mutate a state when an
event is received. For example, let's implement a struct storing all the events
we received:

{% highlight rust %}
pub struct Storage {
    events: Vec<u32>,
}

impl Storage {
    pub fn new() -> Self {
        Self { events: Vec::new() }
    }

    pub fn store(&mut self, value: u32) {
        self.events.push(value);
    }

    pub fn events(&self) -> &Vec<u32> {
        &self.events
    }
}
{% endhighlight %}

To be able to fill this `Storage` on each event received, we somehow have to
pass it along with the event listener, which will be stored in the `Notifier`.
Therefore, we need a single instance of `Storage` to be **shared** between the
caller code and the `Notifier`.

Holding two mutable references to the same object obviously violates the
borrowing rules, so we need a [reference-counting pointer][rc].

However, such a pointer is read-only, so we also need a [`RefCell`][refcell]
for [interior mutability].

Thus, we will use an instance of `Rc<RefCell<Storage>>`. It may seem too
verbose, but using `Rc<RefCell<T>>` (or `Arc<Mutex<T>>` for thread-safety) is
very common in Rust. And [there is worse][worse].

[rc]: https://doc.rust-lang.org/std/rc/
[interior mutability]: https://ricardomartins.cc/2016/06/08/interior-mutability
[refcell]: https://doc.rust-lang.org/std/cell/index.html
[worse]: https://www.reddit.com/r/rust/comments/33jv62/vecrcrefcellboxtrait_is_there_a_better_way/

Here is the resulting client code:

{% highlight rust %}
    use std::cell::RefCell;
    use std::rc::Rc;

    let mut notifier = Notifier::new();

    // first Rc to the Storage
    let rc = Rc::new(RefCell::new(Storage::new()));

    // second Rc to the Storage
    let rc2 = rc.clone();

    // register the listener saving all the received events to the Storage
    notifier.register(move |event| rc2.borrow_mut().store(event));

    notifier.notify(3);
    notifier.notify(141);
    notifier.notify(59);
    assert_eq!(&vec![3, 141, 59], rc.borrow().events());
{% endhighlight %}

That way, the `Storage` is correctly mutated from the event listener.

All is not solved, though. In this example, we had access to the
`Rc<RefCell<Storage>>` instance. What if we only have access to the `Storage`,
e.g. if we want `Storage` to register itself from one of its methods, without
requiring the caller to provide the `Rc<RefCell<Storage>>` instance?

{% highlight rust %}
impl Storage {
    pub fn register_to(&self, notifier: &mut Notifier) {
        notifier.register(move |event| {
            /* how to retrieve a &mut Storage from here? */
        });
    }
}
{% endhighlight %}

We need to retrieve the `Rc<RefCell<Storage>>` from the `Storage` in some way.

To do so, the idea consists in making the `Storage` aware of its
reference-counting pointer. _Of course, this only makes sense if `Storage` is
constructed inside a `Rc<RefCell<Storage>>`._

This is exactly what [`enable_shared_from_this`] provides in C++, so we can draw
inspiration from [how it works][esft_stackoverflow]: just store a
`Weak<RefCell<…>>`, [downgraded] from the `Rc<RefCell<…>>`, into the structure
itself. That way, we can use it to get a `&mut Storage` reference back in the
event listener:

{% highlight rust %}
use std::rc::{Rc, Weak};
use std::cell::RefCell;

pub struct Storage {
    self_weak: Weak<RefCell<Storage>>,
    events: Vec<u32>,
}

impl Storage {
    pub fn new() -> Rc<RefCell<Self>> {
        let rc = Rc::new(RefCell::new(Self {
            self_weak: Weak::new(), // initialize empty
            events: Vec::new(),
        }));
        // set self_weak once we get the Rc instance
        rc.borrow_mut().self_weak = Rc::downgrade(&rc);
        rc
    }

    pub fn register_to(&self, notifier: &mut Notifier) {
        let rc = self.self_weak.upgrade().unwrap();
        notifier.register(move |event| rc.borrow_mut().store(event))
    }
}
{% endhighlight %}

Here is how to use it:

{% highlight rust %}
    let mut notifier = Notifier::new();
    let rc = Storage::new();
    rc.borrow().register_to(&mut notifier);
    notifier.notify(3);
    notifier.notify(141);
    notifier.notify(59);
    assert_eq!(&vec![3, 141, 59], rc.borrow().events());
{% endhighlight %}


[`enable_shared_from_this`]: http://en.cppreference.com/w/cpp/memory/enable_shared_from_this
[esft_stackoverflow]: https://stackoverflow.com/a/34062114/1987178
[downgraded]: https://doc.rust-lang.org/std/rc/struct.Rc.html#method.downgrade

So it is possible to implement the _observer_ pattern in Rust, but this is a bit
more challenging than in Java ;-)

When possible, it might be preferable to avoid it.


### Mutable data sharing

> Mutable references cannot be [aliased][refnomicon].

How to share mutable data, then?

We saw that we can use `Rc<RefCell<…>>` (or `Arc<Mutex<…>>`), that enforces the
borrowing rules at runtime. However, this is not always desirable:
 - it forces a new allocation on the heap,
 - each access has a runtime cost,
 - it always borrows the whole resource.

Alternatively, we could use [raw pointers] manually inside [unsafe] code, but
then this would be _unsafe_.

[refnomicon]: https://doc.rust-lang.org/nomicon/references.html
[unsafe]: https://doc.rust-lang.org/book/first-edition/unsafe.html
[raw pointers]: https://doc.rust-lang.org/book/first-edition/raw-pointers.html

And there is another way, which consists in exposing **temporary borrowing
views** of an object. Let me explain.

In _Gnirehtet_, a packet contains a reference to the raw data (stored in some
buffer elsewhere) along with the [IP] and [TCP]/[UDP] header fields values
(parsed from the raw data). We could have used a flat structure to store
everything:

[ip]: https://en.wikipedia.org/wiki/IPv4#Packet_structure
[tcp]: https://en.wikipedia.org/wiki/Transmission_Control_Protocol#TCP_segment_structure
[udp]: https://en.wikipedia.org/wiki/User_Datagram_Protocol#Packet_structure

{% highlight rust %}
pub struct Packet<'a> {
    raw: &'a mut [u8],
    ipv4_source: u32,
    ipv4_destination: u32,
    ipv4_protocol: u8,
    // + other ipv4 fields
    transport_source: u16,
    transport_destination: u16,
    // + other transport fields
}
{% endhighlight %}

The `Packet` would provide _setters_ for all the header fields (updating both
the packet fields and the raw data). For example:

{% highlight rust %}
impl<'a> Packet<'a> {
    pub fn set_transport_source(&mut self, transport_source: u16) {
        self.transport_source = transport_source;
        let transport = &mut self.raw[20..];
        BigEndian::write_u16(&mut transport[0..2], port);
    }
}
{% endhighlight %}

But this would be poor design (especially since TCP and UDP header fields
are different).

Instead, we would like to extract IP and transport headers to separate structs,
managing their own part of the raw data:

{% highlight rust %}
// violates the borrowing rules

pub struct Packet<'a> {
    raw: &'a mut [u8], // the whole packet (including headers)
    ipv4_header: Ipv4Header<'a>,
    transport_header: TransportHeader<'a>,
}

pub struct Ipv4Header<'a> {
    raw: &'a mut [u8], // slice related to ipv4 headers
    source: u32,
    destination: u32,
    protocol: u8,
    // + other ipv4 fields
}

pub struct TransportHeader<'a> {
    raw: &'a mut [u8], // slice related to transport headers
    source: u16,
    destination: u16,
    // + other transport fields
}
{% endhighlight %}

You immediately spotted the problem: **there are several references to the
same resource, the `raw` byte array, at the same time**.

_Note that [splitting] the array is not a possibility here, since the `raw`
slices overlap: we need to write the whole packet at once to the network, so the
`raw` array in `Packet` must include the headers._

[splitting]: https://doc.rust-lang.org/std/primitive.slice.html#method.split_at_mut

We need a solution compatible with the borrowing rules.

Here is the one I came up with:

 - store the header data separately, without the `raw` slices,
 - create _view_ structs for IP and transport headers, with [lifetime bounds],
 - expose `Packet` methods returning _view_ instances.

[lifetime bounds]: https://doc.rust-lang.org/book/first-edition/lifetimes.html#in-structs

And here is a simplification of the actual implementation:

{% highlight rust %}
pub struct Packet<'a> {
    raw: &'a mut [u8],
    ipv4_header: Ipv4HeaderData,
    transport_header: TransportHeaderData,
}

pub struct Ipv4HeaderData {
    source: u32,
    destination: u32,
    protocol: u8,
    // + other ipv4 fields
}

pub struct TransportHeaderData {
    source: u16,
    destination: u16,
    // + other transport fields
}

pub struct Ipv4Header<'a> {
    raw: &'a mut [u8],
    data: &'a mut Ipv4HeaderData,
}

pub struct TransportHeader<'a> {
    raw: &'a mut [u8],
    data: &'a mut TransportHeaderData,
}

impl<'a> Packet<'a> {
    pub fn ipv4_header(&mut self) -> Ipv4Header {
        Ipv4Header {
            raw: &mut self.raw[..20],
            data: &mut self.ipv4_header,
        }
    }

    pub fn transport_header(&mut self) -> TransportHeader {
        TransportHeader {
            raw: &mut self.raw[20..40],
            data: &mut self.transport_header,
        }
    }
}
{% endhighlight %}

The _setters_ are implemented on the views, where they hold a mutable reference
to the raw array:

{% highlight rust %}
impl<'a> TransportHeader<'a> {
    pub fn set_source(&mut self, source: u16) {
        self.data.source = source;
        BigEndian::write_u16(&mut raw[0..2], source);
    }

    pub fn set_destination(&mut self, destination: u16) {
        self.data.destination = destination;
        BigEndian::write_u16(&mut raw[2..4], destination);
    }
}
{% endhighlight %}

That way, the borrowing rules are respected, and the API is elegant:

{% highlight rust %}
    let mut packet = …;
    // "transport_header" borrows "packet" during its scope
    let mut transport_header = packet.transport_header();
    transport_header.set_source(1234);
    transport_header.set_destination(1234);
{% endhighlight %}


### Compiler limitations

Rust is a young language, and the compiler has some annoying pitfalls.


The worst, in my opinion, is related to [non-lexical lifetimes], which leads to
[unexpected errors]:

[non-lexical lifetimes]: http://smallcultfollowing.com/babysteps/blog/2016/04/27/non-lexical-lifetimes-introduction/#problem-case-2-conditional-control-flow
[unexpected errors]: https://stackoverflow.com/questions/44417491/non-lexical-lifetime-workaround-failure

{% highlight rust %}
struct Container {
    vec: Vec<i32>,
}

impl Container {
    fn find(&mut self, v: i32) -> Option<&mut i32> {
        None // we don't care the implementation
    }

    fn get(&mut self, v: i32) -> &mut i32 {
        if let Some(x) = self.find(v) {
            return x;
        }
        self.vec.push(v);
        self.vec.last_mut().unwrap()
    }
}
{% endhighlight %}

```
error[E0499]: cannot borrow `self.vec` as mutable more than once at a time
  --> sample.rs:14:9
   |
11 |         if let Some(x) = self.find(v) {
   |                          ---- first mutable borrow occurs here
...
14 |         self.vec.push(v);
   |         ^^^^^^^^ second mutable borrow occurs here
15 |         self.vec.last_mut().unwrap()
16 |     }
   |     - first borrow ends here
```

Hopefully, [it should be fixed soon][nll].

[nll]: http://smallcultfollowing.com/babysteps/blog/2017/07/11/non-lexical-lifetimes-draft-rfc-and-prototype-available/

The [_Impl Trait_][conservative] feature, allowing to return _unboxed_ abstract
types from functions, should also improve the experience (there is also an
[expanded] proposal).

[conservative]: https://github.com/rust-lang/rfcs/blob/master/text/1522-conservative-impl-trait.md
[expanded]: https://github.com/rust-lang/rfcs/blob/master/text/1951-expand-impl-trait.md

The compiler generally produces very helpful error messages. But when it does
not, they can be very [confusing].

[confusing]: https://stackoverflow.com/questions/44003622/implementing-trait-for-fnsomething-in-rust

## Safety pitfalls

The [first chapter of the _Rustonomicon_][nomicon-safe] says:

[nomicon-safe]: https://doc.rust-lang.org/nomicon/meet-safe-and-unsafe.html

> Safe Rust is For Reals Totally Safe.
>
> […]
>
> Safe Rust is the true Rust programming language. If all you do is write Safe
> Rust, you will never have to worry about type-safety or memory-safety. You
> will never endure a null or dangling pointer, or any of that Undefined
> Behavior nonsense.

That's the goal. And that's _almost_ true.


### Leakpocalypse

In the past, it was [possible][issue24292] to write _safe-Rust_ code **accessing
freed memory**.

This "[leakpocalypse]" led to a [clarification][rfc-safe] of the safety
guarantees: not running a destructor is now [considered _safe_][rfc-safe-pr]. In
other words, **memory-safety may not rely on [RAII]** anymore (in fact, it never
could, but it has been noticed only belatedly).

As a consequence, [`std::mem::forget`] is now _safe_, and [`JoinGuard`] has been
deprecated and removed from the standard library (it has been moved to a
[separate crate]).

Other tools relying on RAII (like [`Vec::drain()`]) must [take special care] to
prevent memory corruption.

Whew, _memory-safety_ is (now) safe.

[issue24292]: https://github.com/rust-lang/rust/issues/24292
[leakpocalypse]: http://cglab.ca/~abeinges/blah/everyone-poops/
[rfc-safe]: https://github.com/alexcrichton/rfcs/blob/safe-mem-forget/text/0000-safe-mem-forget.md
[rfc-safe-pr]: https://github.com/rust-lang/rfcs/pull/1066
[RAII]: https://en.wikipedia.org/wiki/Resource_acquisition_is_initialization
[`std::mem::forget`]: https://doc.rust-lang.org/std/mem/fn.forget.html
[`JoinGuard`]: https://doc.rust-lang.org/1.0.0/std/thread/struct.JoinGuard.html
[separate crate]: http://arcnmx.github.io/thread-scoped-rs/thread_scoped/
[`Vec::drain()`]: https://doc.rust-lang.org/std/vec/struct.Vec.html#method.drain
[take special care]: https://github.com/rust-lang/rust/blob/1.20.0/src/liballoc/vec.rs#L1094-L1102


### Undefined infinity

In C and C++, [infinite loops][ubloops-so] without side-effects are [undefined
behavior][ubloops]. This makes it possible to write programs that unexpectedly
[disprove Fermat's Last Theorem][fermat].

In practice, the Rust compiler relies on LLVM, which (currently) applies its
optimizations assuming that infinite loops without side-effects are _undefined
behavior_. As a consequence, such _undefined behaviors_ also occur in Rust.

[fermat]: https://blog.regehr.org/archives/140
[ubloops-so]: https://stackoverflow.com/questions/3592557/optimizing-away-a-while1-in-c0x
[ubloops]: http://www.open-std.org/jtc1/sc22/wg14/www/docs/n1528.htm

Here is a minimal sample to trigger it:

{% highlight rust %}
fn infinite() {
    loop {}
}

fn main() {
    infinite();
}
{% endhighlight %}

Running without optimizations, it behaves as "expected":

```
$ rustc ub.rs && ./ub
^C                    (infinite loop, interrupt it)
```

Enabling optimizations makes the program panic:

```
$ rustc -O ub.rs && ./ub
thread 'main' panicked at 'assertion failed: c.borrow().is_none()', /checkout/src/libstd/sys_common/thread_info.rs:51
note: Run with `RUST_BACKTRACE=1` for a backtrace.
```

Alternatively, we can produce unexpected results without crashing:

{% highlight rust %}
fn infinite(mut value: u32) {
    // infinite loop unless value initially equals 0
    while value != 0 {
        if value != 1 {
            value -= 1;
        }
    }
}

fn main() {
    infinite(42);
    println!("end");
}
{% endhighlight %}

```
$ rustc ub.rs && ./ub
^C                    (infinite loop, interrupt it)
```

But with optimizations:

```
$ rustc -O ub.rs && ./ub
end
```

This is a corner case, that will probably be solved in the future. In practice,
**Rust safety guarantees are pretty strong** (at a cost of being constraining).


## Stats

That's all for my feedbacks about the language itself.

As an appendix, let's compare the current _Java_ and _Rust_ versions of the
relay server.


### Number of lines

```
$ cloc relay-{java,rust}/src
-------------------------------------------------------------------------------
Language                     files          blank        comment           code
-------------------------------------------------------------------------------
Rust                            29            687            655           4506
Java                            37            726            701           2931
-------------------------------------------------------------------------------
```

_(tests included)_

The Rust project is significantly bigger, for several reasons:
 - there are many [_borrowing views_] classes;
 - the Rust version contains its own _selector_ class, wrapping the lower-level
   [`Poll`][rust-Poll], while the Java version uses the standard
   [`Selector`][java-Selector];
 - the [error handling] for command-line parsing is more verbose.

[rust-Poll]: https://docs.rs/mio/0.6.10/mio/struct.Poll.html
[java-Selector]: https://docs.oracle.com/javase/8/docs/api/java/nio/channels/Selector.html
[_borrowing views_]: #mutable-data-sharing
[error handling]: https://doc.rust-lang.org/book/first-edition/error-handling.html

The Java version has more files because the unit tests are separate, while in
Rust they are in the same file as the classes they test.

Just for information, here are the results for the Android client:

```
$ cloc app/src
-------------------------------------------------------------------------------
Language                     files          blank        comment           code
-------------------------------------------------------------------------------
Java                            15            198            321            875
XML                              6              7              2             76
-------------------------------------------------------------------------------
SUM:                            21            205            323            951
-------------------------------------------------------------------------------
```


### Binary size

```
--------------------------------------------
Java     gnirehtet.jar                   61K
--------------------------------------------
Rust     gnirehtet                      3.0M
         after "strip -g gnirehtet"     747K
         after "strip gnirehtet"        588K
--------------------------------------------
```

The Java binary itself is far smaller. The comparison is not fair though, since
it requires the _Java Runtime Environment_:

    $ du -sh /usr/lib/jvm/java-1.8.0-openjdk-amd64/
    156M	/usr/lib/jvm/java-1.8.0-openjdk-amd64/


### Memory usage

With a single TCP connection opened, here is the memory consumption for the Java
relay server:

    $ sudo pmap -x $RELAY_JAVA_PID
                      Kbytes     RSS   Dirty
    total kB         4364052   86148   69316

_(output filtered)_

And for the Rust relay server:

    $ sudo pmap -x $RELAY_RUST_PID
                      Kbytes     RSS   Dirty
    total kB           19272    2736     640

_Look at the [RSS] value, which indicates the actual memory used._

As expected, the Java version consumes more memory (86Mb) than the Rust one
(less than 3Mb). Moreover, its value is unstable due to the allocation of tiny
objects and their [garbage collection], which also generates more dirty pages.
On the contrary, the Rust value is very stable: once the connection is created,
there are no memory allocations _at all_.

[RSS]: https://en.wikipedia.org/wiki/Resident_set_size
[garbage collection]: https://en.wikipedia.org/wiki/Garbage_collection_(computer_science)


### CPU usage

To compare CPU usage, here is my scenario: a 500Mb file is hosted by Apache on
my laptop, I start the relay server through `perf stat`, then I download the
file from Firefox on Android. As soon as the file is downloaded, I stop the
relay server (Ctrl+C).

Here are the results for the Java version:

```
$ perf stat -B java -jar gnirehtet.jar relay
 Performance counter stats for 'java -jar gnirehtet.jar relay':

      11805,458302      task-clock:u (msec)       #    0,088 CPUs utilized
                 0      context-switches:u        #    0,000 K/sec
                 0      cpu-migrations:u          #    0,000 K/sec
            28 618      page-faults:u             #    0,002 M/sec
    17 908 360 446      cycles:u                  #    1,517 GHz
    13 944 172 792      stalled-cycles-frontend:u #   77,86% frontend cycles idle
    18 437 279 663      instructions:u            #    1,03  insn per cycle
                                                  #    0,76  stalled cycles per insn
     3 088 215 431      branches:u                #  261,592 M/sec
        70 647 760      branch-misses:u           #    2,29% of all branches

     133,975117164 seconds time elapsed
```

And for the Rust version:

```
$ perf stat -B ./gnirehtet relay
 Performance counter stats for 'target/release/gnirehtet relay':

       2707,479968      task-clock:u (msec)       #    0,020 CPUs utilized
                 0      context-switches:u        #    0,000 K/sec
                 0      cpu-migrations:u          #    0,000 K/sec
             1 001      page-faults:u             #    0,370 K/sec
     1 011 527 340      cycles:u                  #    0,374 GHz
     2 033 810 378      stalled-cycles-frontend:u #  201,06% frontend cycles idle
       981 103 003      instructions:u            #    0,97  insn per cycle
                                                  #    2,07  stalled cycles per insn
        98 929 222      branches:u                #   36,539 M/sec
         3 220 527      branch-misses:u           #    3,26% of all branches

     133,766035253 seconds time elapsed
```

I am not an expert in analyzing the results, but as far as I understand from
the `task-clock:u` value, the Rust version consumes 4× less CPU-time.


## Conclusion

Rewriting _Gnirehtet_ in Rust was an amazing experience, where I learnt a great
language and new programming concepts. And now, we get a native application
showing better performances.

Happy reverse tethering!
