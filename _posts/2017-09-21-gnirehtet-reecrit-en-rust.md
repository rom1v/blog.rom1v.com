---
layout: post
title: Gnirehtet réécrit en Rust
date: 2017-09-21 17:00:00+02:00
tags:
- planet-libre
lang: fr
langref:
- lang: en
  url: /2017/09/gnirehtet-rewritten-in-rust
  internal: true
---

Il y a quelques mois, j'ai présenté [Gnirehtet][blog-gnirehtet], un outil de
_reverse tethering_ pour Android que j'ai écrit en Java.

Depuis, **je l'ai réécrit en [Rust]**.

Et il est également open source ! [Téléchargez-le][gnirehtet], branchez un
téléphone ou une tablette Android, et exécutez :

    ./gnirehtet run

_(adb doit être installé)_

[blog-gnirehtet]: {% post_url 2017-03-30-gnirehtet %}
[Rust]: https://www.rust-lang.org/
[gnirehtet]: https://github.com/Genymobile/gnirehtet
[release]: https://github.com/Genymobile/gnirehtet/releases/latest

[`dev`]: https://github.com/Genymobile/gnirehtet/tree/dev
[`DEVELOP`]: https://github.com/Genymobile/gnirehtet/blob/dev/DEVELOP.md

* TOC
{:toc}

## Pourquoi Rust?

À Genymobile, nous voulions que _Gnirehtet_ ne nécessite pas d'environnement
d'exécution Java (JRE), donc le besoin principal était de compiler l'application
vers un binaire exécutable _natif_.

Par conséquent, j'ai d'abord pensé la réécrire en C ou C++. Mais à ce moment-là
(début mai), apprendre Rust m'intéressait, après avoir vaguement entendu parler
de ses fonctionnalités:
 - [sûreté mémoire sans _garbage collection_][memory-concurrency],
 - [concurrence sans _data races_][memory-concurrency],
 - [abstraction sans coût][zerocost].

[slogans]: https://blog.rust-lang.org/2017/02/06/roadmap.html
[memory-concurrency]: https://blog.rust-lang.org/2015/04/10/Fearless-Concurrency.html
[zerocost]: https://blog.rust-lang.org/2015/05/11/traits.html

Cependant, je n'avais jamais écrit une ligne de Rust ni entendu parler de son
système de [possession][ownership], d'[emprunt][borrowing] ou de [durées de
vie][lifetimes].

[ownership]: https://doc.rust-lang.org/book/first-edition/ownership.html
[borrowing]: https://doc.rust-lang.org/book/first-edition/references-and-borrowing.html
[lifetimes]: https://doc.rust-lang.org/book/first-edition/lifetimes.html

Mais je suis convaincu que le meilleur moyen d'apprendre un langage de
programmation est de travailler à plein temps sur un projet dans ce langage.

J'étais motivé, donc après avoir vérifié que ça pouvait convenir (en gros, j'ai
écrit un exemple utilisant la bibliothèque d'[I/O asynchrone][async I/O] [mio],
et je l'ai exécuté à la fois sur Linux et Windows), j'ai décidé de réécrire
_Gnirehtet_ en Rust.

[async I/O]: https://en.wikipedia.org/wiki/Asynchronous_I/O
[mio]: https://crates.io/crates/mio


## Apprendre Rust

Pendant la réécriture, j'ai _dévoré_ successivement le [Rust book], [Rust by
example] et le [Rustonomicon]. J'ai beaucoup appris, et j'aime énormément ce
langage. Beaucoup de ses fonctionnalités me manquent maintenant quand je
travaille sur un projet C++ :
 - [inférence de type avancée][inference],
 - [enums],
 - [patterns],
 - [trait bounds],
 - [`Option<T>`] (comme [`std::optional<T>`] en C++17, mais tirant bénéfice des
   enums et des patterns),
 - [macros hygiéniques][macros],
 - l'absence de fichiers d'en-têtes,
 - le (si simple) système de _build_, _et bien sûr_
 - la garantie de sûreté mémoire.

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

À propos de l'apprentissage, Paul Graham [a écrit][quote]:

> **Reading and experience train your model of the world.** And even if you
> forget the experience or what you read, its effect on your model of the world
> persists. Your mind is like a compiled program you've lost the source of. It
> works, but you don't know why.

Pour les non-anglophones, ma propre traduction :

> **La lecture et l'expérience entraînent votre modèle du monde.** Et même si
> vous oubliez l'expérience ou ce que vous avez lu, son effet sur votre modèle
> du monde persiste. Votre esprit est comme un programme compilé dont vous
> auriez perdu le code source. Ça fonctionne, mais vous ne savez pas pourquoi.

Certains des concepts de Rust (comme les [durées de vie][lifetimes] ou la
[sémantique de mouvement][move semantics] par défaut) m'ont fourni un _jeu de
données_ significativement différent, qui a sans aucun doute affecté mon modèle
du monde (de la programmation).

[move semantics]: https://doc.rust-lang.org/book/first-edition/ownership.html#move-semantics
[quote]: http://paulgraham.com/know.html

Je ne vais pas présenter toutes ces fonctionnaliés (cliquez sur les liens de la
documentation si ça vous intéresse). À la place, je vais essayer d'expliquer où
et pourquoi Rust a resisté au _design_ que je voulais implémenter, et comment
repenser les problèmes dans le périmètre des contraintes de Rust.

_La partie suivant nécessite une certaine connaissance de Rust. Vous pourriez
vouloir la passer pour aller directement aux [stats](#stats)._


## Difficultés

Je trouvais la conception de l'application Java plutôt réussie, donc je voulais
reproduire l'architecture globale dans la version Rust (avec d'éventuelles
adaptations pour la _rustifier_).

Mais j'ai lutté sur les détails, en particulier pour satisfaire le [_borrow
checker_][borrowing]. Les [règles][rules] sont simples:

> First, any borrow must last for a scope no greater than that of the owner.
> Second, you may have one or the other of these two kinds of borrows, but not
> both at the same time:
>  - one or more references (`&T`) to a resource,
>  - exactly one mutable reference (`&mut T`).

En français :

> Premièrement, aucun emprunt ne doit avoir une portée plus grande que celle de
> son propriétaire.
> Deuxièmement, vous pouvez avoir l'un ou l'autre de ces types d'emprunts, mais
> pas les deux à la fois:
> - une ou plusieurs références (`&T`) vers une ressource,
> - exactement une référence mutable (`&mut T`).

Cependant, il m'a fallu un peu de temps pour réaliser comment elles entrent en
conflit avec certains principes ou modèles de conception.

Voici donc mes retours. J'ai sélectionné 4 sujets qui sont suffisamment généraux
pour être indépendants de ce projet particulier :

 - les conflits avec l'[encapsulation](#encapsulation) ;
 - le _design pattern_ [observateur](#observateur) ;
 - comment [partager des données mutables](#partage-de-donnes-mutables) ;
 - un retour rapide sur les [limitations ennuyeuses du
   compilateur](#limitations-du-compilateur).

[design]: https://github.com/Genymobile/gnirehtet/blob/master/DEVELOP.md#relay-server
[rules]: https://doc.rust-lang.org/book/first-edition/references-and-borrowing.html#the-rules


### Encapsulation

**Les règles d'emprunt contraignent l'encapsulation.** C'est la première
conséquence que j'ai réalisée.

Voici un exemple canonique :

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

Nous créons juste une nouvelle instance de `Data`, puis associons à des
variables locales des références mutables vers les tableaux `header` et
`payload`, en passant par des accesseurs.

Cependant, cela ne compile pas :

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

Le compilateur ne peut pas faire l'hypothèse que `header()` et `payload()`
retournent des références vers des données disjointes dans la structure `Data`.
Par conséquent, chacun _emprunte_ la structure `data` entièrement. Vu que les
règles d'emprunt interdisent d'obtenir deux références mutables vers la même
ressource, il rejette le second appel.

Parfois, nous faisons face à des limitations temporaires parce que le
compilateur n'est pas (encore) assez malin. Ce n'est pas le cas ici :
l'implémentation de `header()` pourrait très bien retourner une référence vers
`payload`, ou écrire dans le tableau `payload`, enfreignant ainsi les règles
d'emprunt. Et la validité d'un appel d'une méthode ne peut pas dépendre de
l'implementation de la méthode.

Pour corriger le problème, le compilateur doit être capable de savoir que les
variables locales `header` et `payload` référencent des **données disjointes**,
par exemple en accédant aux champs directement :

{% highlight rust %}
    let header = &mut data.header;
    let payload = &mut data.payload;
{% endhighlight %}

ou en exposant une méthode fournissant les deux références simultanément :

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

De même, dans l'implémentation d'une structure, les règles d'emprunt empêchent
de factoriser du code dans une méthode privée facilement. Prenons cet exemple
(artificiel) :

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

Ici, le champ `buf` est un tableau stockant un préfixe et un contenu de manière
contiguë.

Nous voulons factoriser la manière dont nous récupérons la _slice_ `content`,
pour que les méthodes `update_*()` n'aient pas à se préoccuper des détails.
Essayons :

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

Malheureusement, cela ne compile pas :

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

Comme dans l'exemple précédent, récupérer une référence à travers une méthode
_emprunte_ la structure complète (ici, `self`).

Pour contourner le problème, nous pouvons expliquer au compilateur que les
champs sont disjoints :

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

Ça compile, mais cela va totalement à l'encontre de la factorisation :
l'appelant doit fournir les champs nécessaires.

Comme alternative, nous pouvons utiliser une [macro][macros] pour _inliner_ le
code :

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

Mais c'est loin d'être idéal.

Je pense que nous devons juste l'accepter : l'encapsulation entre parfois en
conflit avec les règles d'emprunt. Après tout, ce n'est pas si surprenant :
imposer les règles d'emprunt nécessite de suivre chaque accès concret aux
ressources, alors que l'encapsulation vise à les abstraire.


### Observateur

Le _design pattern_ [observateur][observer] est utile pour enregistrer des
événements sur un objet.

Dans certains cas, **ce pattern pose des difficultés d'implémentation en Rust**.

Pour faire simple, considérons que les événements sont des valeurs `u32`. Voici
une implémentation possible :

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

Par commodité, implémentons notre trait `EventListener` pour les closures :

{% highlight rust %}
impl<F: Fn(u32)> EventListener for F {
    fn on_event(&self, event: u32) {
        self(event);
    }
}
{% endhighlight %}

Ainsi, son utilisation est simple :

{% highlight rust %}
    let mut notifier = Notifier::new();
    notifier.register(|event| println!("received [{}]", event));
    println!("notifying...");
    notifier.notify(42);
{% endhighlight %}

Cela affiche :

```
notifying...
received [42]
```

Jusqu'ici, tout va bien.

Cependant, les choses se compliquent si nous voulons modifier un état sur la
réception d'un événement. Par exemple, implémentons une structure pour stocker
tous les événements reçus :

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

Pour pouvoir remplir ce `Storage` sur chaque événement reçu, nous devons d'une
manière ou d'une autre le passer avec l'_event listener_, qui sera stocké dans
le `Notifier`. Par conséquent, nous avons besoin qu'une instance de `Storage`
soit **partagée** entre le code appelant et le `Notifier`.

Avoir deux références mutables vers le même objet enfreint évidemment les règles
d'emprunt, donc nous avons besoin d'un [pointeur à compteur de références][rc].

Cependant, un tel pointeur est en lecture seul, donc nous avons également besoin
d'un [`RefCell`][refcell] pour la [_mutabilité intérieure_][interior
mutability].

Ainsi, nous allons utiliser une instance de `Rc<RefCell<Storage>>`. Cela peut
sembler trop verbeux, mais utiliser `Rc<RefCell<T>>` (ou `Arc<Mutex<T>>` pour
la _thread-safety_) est très courant en Rust. Et [il y a pire][worse].

[rc]: https://doc.rust-lang.org/std/rc/
[interior mutability]: https://ricardomartins.cc/2016/06/08/interior-mutability
[refcell]: https://doc.rust-lang.org/std/cell/index.html
[worse]: https://www.reddit.com/r/rust/comments/33jv62/vecrcrefcellboxtrait_is_there_a_better_way/

Voici ce que donne le code client :

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

De cette manière, le `Storage` est correctement modifié à partir de l'_event
listener_.

Tout n'est pas résolu pour autant. Dans cet exemple, c'était facile, nous avions
accès à l'instance `Rc<RefCell<Storage>>`. Comment faire si nous avons seulement
accès au `Storage`, par exemple si nous voulons que le `Storage` s'enregistre
lui-même à partir de l'une de ses méthodes, sans que l'appelant n'ait à fournir
l'instance `Rc<RefCell<Storage>>` ?

{% highlight rust %}
impl Storage {
    pub fn register_to(&self, notifier: &mut Notifier) {
        notifier.register(move |event| {
            /* how to retrieve a &mut Storage from here? */
        });
    }
}
{% endhighlight %}

Nous devons trouver un moyen de récupérer le `Rc<RefCell<Storage>>` à partir du
`Storage`.

Pour cela, l'idée consiste à rendre `Storage` conscient de son pointeur à
compteur de références. _Bien sûr, cela n'a du sens que si `Storage` est
construit dans un `Rc<RefCell<Storage>>`._

C'est exactement ce que [`enable_shared_from_this`] fournit en C++, donc nous
pouvons nous inspirer de [son fonctionnement][esft_stackoverflow] : juste
stocker un `Weak<RefCell<…>>`, [_downgradé_][downgraded] à partir du
`Rc<RefCell<…>>`, dans la structure elle-même. De cette manière, nous pouvons
l'utiliser pour récupérer une référence `&mut Storage` à partir de l'_event
listener_ :

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

Voici comment l'utiliser :

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

Il est donc possible d'implémenter le design pattern _observateur_ en Rust, mais
c'est un peu plus difficile qu'en Java ;-)

Lorsque c'est possible, il est probablement préférable de l'éviter.


### Partage de données mutables

> Mutable references cannot be [aliased][refnomicon].

En français :

> Les références mutables ne peuvent pas être [aliasées][refnomicon].

Comment partager des données mutables, alors ?

Nous avons vu que nous pouvions utiliser `Rc<RefCell<…>>` (ou `Arc<Mutex<…>>`),
qui impose les règles d'emprunt à l'exécution. Cependant, ce n'est pas toujours
désirable :
 - cela force une nouvelle allocation sur le tas,
 - chaque accès a un coût à l'exécution,
 - l'emprunt concerne toujours la ressource entière.

Au lieu de cela, nous pourrions utiliser des pointeurs _bruts_ manuellement dans
du code [non-sûr][unsafe], mais alors ce serait _non-sûr_.

[refnomicon]: https://doc.rust-lang.org/nomicon/references.html
[unsafe]: https://doc.rust-lang.org/book/first-edition/unsafe.html
[raw pointers]: https://doc.rust-lang.org/book/first-edition/raw-pointers.html

Et il y a une autre solution, qui consiste à exposer des **vues temporaires
d'emprunt** d'un objet. Laissez-moi expliquer.

Dans _Gnirehtet_, un paquet contient une référence vers les données brutes
(stockées dans un buffer quelque part) ainsi que les valeur des champs des
en-têtes [IP] et [TCP]/[UDP] (parsées à partir du tableau d'octets brut). Nous
aurions pu utiliser une structure à plat pour tout stocker :

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

Le `Packet` aurait fourni des _setters_ pour tous les champs d'en-têtes
(modifiant à la fois les champs du paquet et le tableau d'octets). Par exemple :

{% highlight rust %}
impl<'a> Packet<'a> {
    pub fn set_transport_source(&mut self, transport_source: u16) {
        self.transport_source = transport_source;
        let transport = &mut self.raw[20..];
        BigEndian::write_u16(&mut transport[0..2], port);
    }
}
{% endhighlight %}

Mais cette conception ne serait pas terrible (surtout que les champs d'en-têtes
TCP et UDP sont différents).

À la place, nous voudrions extraire les en-têtes d'IP et de transport vers des
structures séparées, gérant leur propre partie du tableau d'octets :

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

Vous avez immédiatement repéré le problème : **il y a plusieurs références vers
la même ressource, le tableau d'octets `raw`, en même temps**.

_Remarquez que [diviser][splitting] le tableau n'est pas une possibilité ici, vu
que les slices de `raw` se chevauchent : nous avons besoin d'écrire le paquet
complet en une seule fois vers la couche réseau, donc le tableau `raw` dans
`Packet` doit inclure les headers._

[splitting]: https://doc.rust-lang.org/std/primitive.slice.html#method.split_at_mut

Nous avons besoin d'une solution compatible avec les règles d'emprunt.

Voici celle à laquelle je suis parvenu :
 - stocker les données des en-têtes séparément, sans les _slices_ de `raw`,
 - créer des structures de _vues_ pour les en-têtes d'IP et de transport, liées
   à une [durée de vie][lifetime bounds],
 - exposer des méthodes de `Packet` retournant des instances de _vues_.

[lifetime bounds]: https://doc.rust-lang.org/book/first-edition/lifetimes.html#in-structs

Et voici une simplification de l'implémentation réelle :

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

Les _setters_ sont implémentés sur les vues, où ils détiennent une référence
mutable vers le tableau brut :

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

De cette manière, les règles d'emprunt sont respectées, et l'API est élégante :

{% highlight rust %}
    let mut packet = …;
    // "transport_header" borrows "packet" during its scope
    let mut transport_header = packet.transport_header();
    transport_header.set_source(1234);
    transport_header.set_destination(1234);
{% endhighlight %}


### Limitations du compilateur

Rust est un langage jeune, et le compilateur a quelques problèmes ennuyeux.

Le pire, d'après moi, est lié aux [durées de vie non-lexicales][non-lexical
lifetimes], qui provoque des [erreurs inattendues][unexpected errors] :

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

Heureusement, [cela devrait être corrigé prochainement][nll].

[nll]: http://smallcultfollowing.com/babysteps/blog/2017/07/11/non-lexical-lifetimes-draft-rfc-and-prototype-available/

La fonctionnalité d'[_Impl Trait_][conservative], permettant aux fonctions de
retourner des types abstraits _non-boxés_, devrait aussi améliorer l'expérience
(il y a aussi une proposition [étendue][expanded]).

[conservative]: https://github.com/rust-lang/rfcs/blob/master/text/1522-conservative-impl-trait.md
[expanded]: https://github.com/rust-lang/rfcs/blob/master/text/1951-expand-impl-trait.md

Le compilateur produit généralement des messages d'erreur très utiles. Mais
quand ce n'est pas le cas, ils peuvent être très [déroutants][confusing].

[confusing]: https://stackoverflow.com/questions/44003622/implementing-trait-for-fnsomething-in-rust

## Sûreté et pièges

Le [premier chapitre du _Rustonomicon_][nomicon-safe] dit :

[nomicon-safe]: https://doc.rust-lang.org/nomicon/meet-safe-and-unsafe.html

> Safe Rust is For Reals Totally Safe.
>
> […]
>
> Safe Rust is the true Rust programming language. If all you do is write Safe
> Rust, you will never have to worry about type-safety or memory-safety. You
> will never endure a null or dangling pointer, or any of that Undefined
> Behavior nonsense.

En français :

> La partie Sûre de Rust est Réellement Totallement Sûre.
>
> […]
>
> Le Rust Sûr est le vrai langage de programmation Rust. Si vous n'écrivez que
> du Rust Sûr, vous n'aurez jamais à vous inquiétez de la sûreté des types ou de
> la mémoire. Vous n'aurez jamais à supporter un pointeur null ou [_dangling_],
> ou l'un de ces [_comportements indéfinis_][ub] insensés.

[_dangling_]: https://fr.wikipedia.org/wiki/Dangling_pointer
[ub]: {% post_url 2014-10-22-comportement-indefini-et-optimisation %}

C'est le but. Et c'est _presque_ vrai.


### Leakpocalypse

Dans le passé, il a été [possible][issue24292] d'écrire du code _Rust sûr_
**accédant à de la mémoire libérée**.

Cette "[leakpocalypse]" a conduit à la [clarification][rfc-safe] des guaranties
de sûreté : ne pas exécuter un destructeur est maintenant [considéré
_sûr_][rfc-safe-pr]. En d'autres termes, **la sûreté mémoire ne peut plus
reposer sur [RAII]** (en fait, elle n'a jamais pu, mais cela n'a été remarqué
que tardivement).

En conséquence, [`std::mem::forget`] est maintenant _sûr_, et [`JoinGuard`] a
été déprécié et supprimé de la bibliothèque standard (il a été déplacé vers un
[crate séparé][separate crate]).

Les autres outils s'appuyant sur RAII (comme [`Vec::drain()`]) doivent prendre
des [précautions particulières][take special care] pour garantir que la mémoire
ne sera pas corrompue.

Ouf, la _sûreté mémoire_ est (maintenant) sauvée.

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


### Infinité indéfinie

En C et C++, les [boucles infinies][ubloops-so] sans effets de bords sont un cas
d'[_undefined behavior_][ubloops]. À cause de cela, il est possible d'écrire des
programmes qui, de façon inattendue, [réfutent le dernier théorème de
Fermat][fermat].

En pratique, le compilateur Rust s'appuie sur LLVM, qui (actuellement) applique
ses optimisations en faisant l'hypothèse que les boucles infinies sans effets de
bords ont un _comportement indéfini_. En conséquence, de tels _undefined
behaviors_ se produisent également en Rust.

[fermat]: https://blog.regehr.org/archives/140
[ubloops-so]: https://stackoverflow.com/questions/3592557/optimizing-away-a-while1-in-c0x
[ubloops]: http://www.open-std.org/jtc1/sc22/wg14/www/docs/n1528.htm

Voici un exemple minimal pour l'observer :

{% highlight rust %}
fn infinite() {
    loop {}
}

fn main() {
    infinite();
}
{% endhighlight %}

Quand on l'exécute sans optimisations, il se comporte comme "attendu" :

```
$ rustc ub.rs && ./ub
^C                    (infinite loop, interrupt it)
```

Mais activer les optimisations fait _paniquer_ le programme :

```
$ rustc -O ub.rs && ./ub
thread 'main' panicked at 'assertion failed: c.borrow().is_none()', /checkout/src/libstd/sys_common/thread_info.rs:51
note: Run with `RUST_BACKTRACE=1` for a backtrace.
```

Nous pouvons aussi produire des résultats inattendus sans plantage :

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

Mais avec optimisations :

```
$ rustc -O ub.rs && ./ub
end
```

C'est un cas particulier, qui sera probablement corrigé dans le futur. En
pratique, **les garanties de sûreté de Rust sont très fortes** (au prix d'être
contraignantes).


## Stats

C'est tout pour mes retours sur le langage lui-même.

En supplément, comparons les versions _Java_ et _Rust_ du serveur relais.


### Nombre de lignes

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

Le projet Rust est significativement plus gros, pour plusieurs raisons :
 - il y a beaucoup de classes de [_vues d'emprunt_][borrowing views] ;
 - la version Rust définit sa propre classe de _selecteur_ d'I/O asynchrone,
   encapsulant [`Poll`][rust-Poll] de plus bas niveau, alors que la version Java
   utilise le [`Selector`][java-Selector] standard ;
 - la [gestion d'erreur][error handling] pour l'analyse de la ligne de commande
   est plus verbeuse.

[rust-Poll]: https://docs.rs/mio/0.6.10/mio/struct.Poll.html
[java-Selector]: https://docs.oracle.com/javase/8/docs/api/java/nio/channels/Selector.html
[borrowing views]: #partage-de-donnes-mutables
[error handling]: https://doc.rust-lang.org/book/first-edition/error-handling.html

La version Java contient plus de fichiers car les tests unitaires sont séparés,
alors qu'en Rust ils se trouvent dans le même fichier que les classes qu'ils
testent.

Juste pour information, voici les résultats pour le client Android :

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


### Taille des binaires

```
--------------------------------------------
Java     gnirehtet.jar                   61K
--------------------------------------------
Rust     gnirehtet                      3.0M
         after "strip -g gnirehtet"     747K
         after "strip gnirehtet"        588K
--------------------------------------------
```

Le binaire Java lui-même est bien plus petit. La comparaison n'est pas juste
cependant, vu qu'il nécessite l'_environnement d'exécution Java_ :

    $ du -sh /usr/lib/jvm/java-1.8.0-openjdk-amd64/
    156M	/usr/lib/jvm/java-1.8.0-openjdk-amd64/


### Utilisation mémoire

Avec une seule connection TCP ouvert, voici la consommation mémoire pour le
serveur relais en Java :

    $ sudo pmap -x $RELAY_JAVA_PID
                      Kbytes     RSS   Dirty
    total kB         4364052   86148   69316

_(résultat filtré)_

Et pour le serveur relais en Rust :

    $ sudo pmap -x $RELAY_RUST_PID
                      Kbytes     RSS   Dirty
    total kB           19272    2736     640

_Regardez la valeur [RSS], qui indique la mémoire réellement utilisée._

Comment on pouvait s'y attendre, la version Java consomme plus de mémoire
(86Mo) que la version Rust (moins de 3Mo). De plus, sa valeur est instable à
cause de l'allocation de petits objets et leur [_garbage collection_], qui
génère aussi davantage de _dirty pages_. La valeur pour Rust, quant à elle, est
très stable : une fois la connection créée, il n'y a plus d'allocations mémoire
_du tout_.

[RSS]: https://en.wikipedia.org/wiki/Resident_set_size
[_garbage collection_]: https://en.wikipedia.org/wiki/Garbage_collection_(computer_science)


### Utilisation CPU

Pour comparer l'utilisation CPU, voici mon scénario : un fichier de 500Mo est
hébergé par Apache sur mon ordinateur, je démarre le serveur relais avec `perf
stat`, puis je télécharge le fichier à partir de Firefox sur Android. Dès que le
fichier est téléchargé, je stoppe le serveur relais (Ctrl+C).

Voici les résultats pour la version Java :

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

Et pour la version Rust :

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

Je ne suis pas un expert pour analyser les résultats, mais de ce que je
comprends de la valeur `task-clock:u`, la version Rust consomme 4× moins de
temps CPU.


## Conclusion

Réécrire _Gnirehtet_ en Rust a été une formidable expérience, où j'ai appris un
super langage et de nouveaux concepts de programmation. Et maintenant, nous
avons une application native avec de meilleures performances.

Bon reverse tethering !

_Discussions sur [reddit]._

[reddit]: https://www.reddit.com/r/rust/comments/71ks57/gnirehtet_a_reverse_tethering_tool_for_android/
