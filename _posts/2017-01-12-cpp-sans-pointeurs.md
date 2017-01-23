---
layout: post
title: C++ sans *pointeurs
date: 2017-01-12 19:33:14+01:00
tags:
- planet-libre
---

Les [pointeurs][pointeur] sont utilisés plus souvent que nécessaire en [C++][].

Je voudrais présenter ici comment caractériser les utilisations abusives et par
quoi les remplacer.

[c++]: https://fr.wikipedia.org/wiki/C%2B%2B
[pointeur]: https://fr.wikipedia.org/wiki/Pointeur_%28programmation%29

## Objectifs

La décision d'utiliser des pointeurs dépend en grande partie de l'[API][] des
objets utilisés.

_API est à comprendre dans un sens très large : je considère que des classes
utilisées dans une autre partie d'une même application exposent une API._

[API]: https://fr.wikipedia.org/wiki/Interface_de_programmation

L'objectif est donc de concevoir des API de manière à ce que leur utilisation ne
nécessite pas de manipuler de pointeurs, ni même si possible de _smart
pointers_.

Cela peut paraître surprenant, mais c'est en fait ainsi que vous utilisez les
classes de la [STL][] ou de [Qt][] : vos méthodes ne retournent jamais
un _raw pointer_ ni un _smart pointer_ vers une _string_ nouvellement créée.

[STL]: https://fr.wikipedia.org/wiki/Standard_Template_Library
[Qt]: https://fr.wikipedia.org/wiki/Qt

De manière générale, vous n'écririez pas ceci :

{% highlight cpp %}
// STL version
string *getName() {
    return new string("my name");
}

// Qt version
QString *getName() {
    return new QString("my name");
}
{% endhighlight %}

ni ceci :

{% highlight cpp %}
// STL version
shared_ptr<string> getName() {
    return make_shared<string>("my name");
}

// Qt version
QSharedPointer<QString> getName() {
    return QSharedPointer<QString>::create("my name");
}
{% endhighlight %}

À la place, vous écririez sûrement :

{% highlight cpp %}
// STL version
string getName() {
    return "my name";
}

// Qt version
QString getName() {
    return "my name";
}
{% endhighlight %}

Notre objectif est d'écrire des classes qui s'utiliseront de la même manière.


## Ownership

Il faut distinguer deux types de _raw pointers_ :

 1. ceux qui détiennent l'objet pointé (**_owning_**), qui devront être
libérés ;
 2. ceux qui ne le détiennent pas (**_non-owning_**).

Le plus simple est de les comparer sur un exemple.

### Owning

{% highlight cpp %}
Info *getInfo() {
    return new Info(/* … */);
}

void doSomething() {
    Info *info = getInfo();
    // info must be deleted
}
{% endhighlight %}

Ici, nous avons la responsabilité de supprimer `info` au bon moment.

**C'est ce type de pointeurs dont nous voulons nous débarrasser.**

### Non-owning

{% highlight cpp %}
void writeDataTo(QBuffer *buffer) {
    buffer->write("c++");
}

void doSomething() {
    QBuffer buffer;
    writeDataTo(&buffer);
}
{% endhighlight %}

Ici, le pointeur permet juste de passer l'adresse de l'objet, mais la méthode
`writeDataTo(…)` ne doit pas gérer sa durée de vie : elle ne le _détient_ donc
pas.

**Cet usage est tout-à-fait légitime, nous souhaitons le conserver.**

Pour savoir si un pointeur est _owning_ ou non, il suffit de se poser la
question suivante : est-ce que lui affecter `nullptr` provoquerait une [fuite
mémoire][] ?

[fuite mémoire]: https://en.wikipedia.org/wiki/Memory_leak


## Pourquoi ?

Voici quelques exemples illustrant pourquoi nous voulons éviter les _owning raw
pointers_.

### Fuite mémoire

Il est facile d'oublier de supprimer un pointeur dans des cas particuliers.

Par exemple :

{% highlight cpp %}
bool parse() {
    Parser *parser = createParser();
    QFile file("file.txt");
    if (!file.open(QIODevice::ReadOnly))
        return false;
    bool result = parser->parse(&file);
    delete parser;
    return result;
    // parser leaked if open failed
}
{% endhighlight %}

Ici, si l'ouverture du fichier a échoué, `parser` ne sera jamais libéré.

L'exemple suivant est encore plus significatif :

{% highlight cpp %}
Result *execute() {
    // …
    return new Result(/* … */);
}

void doWork() {
    execute();
    // result leaked
}
{% endhighlight %}

Appeler une méthode sans s'occuper du résultat peut provoquer des fuites
mémoires.


### Double suppression

Il est également possible, par inattention, de supprimer plusieurs fois le même
pointeur (ce qui entraîne un [_undefined behavior_][ub]).

[ub]: {% post_url 2014-10-22-comportement-indefini-et-optimisation %}

Par exemple, si `device` fait partie de la liste `devices`, ce code le supprime
deux fois :

{% highlight cpp %}
delete device;
qDeleteAll(devices);
// device is deleted twice
{% endhighlight %}


### Utilisation après suppression

L'utilisation d'un pointeur après sa suppression est également indéfinie.

Je vais prendre un exemple réel en Qt.

Supposons qu'une classe `DeviceMonitor` surveille le branchement de
périphériques, et crée pour chacun un objet `Device`.

<a id="suppression-complexe" />
Lorsqu'un périphérique est débranché, un [signal Qt][qt-signals] provoque
l'exécution du _slot_ `DeviceMonitor::onDeviceLeft(Device *)`. Nous voulons
alors signaler au reste de l'application que le device est parti (_signal_
`DeviceMonitor::deviceLeft(Device *)`), puis supprimer l'object `device`
correspondant :

[qt-signals]: http://doc.qt.io/qt-5/signalsandslots.html

{% highlight cpp %}
void DeviceMonitor::onDeviceLeft(Device *device) {
    emit deviceLeft(device);
    delete device;
    // slots may use the device after its deletion
    // device->deleteLater() not sufficient
}
{% endhighlight %}

Mais c'est loin d'être trivial.

Si nous le supprimons immédiatement comme ceci, et qu'un _slot_ est branché à
`DeviceMonitor::deviceLeft(Device *)` en
[`Qt::QueuedConnection`][ConnectionType], alors il est possible que le pointeur
soit déjà supprimé quand ce _slot_ sera exécuté.

[ConnectionType]: http://doc.qt.io/qt-5/qt.html#ConnectionType-enum

Un proverbe dit que quand ça crashe avec un `delete`, _"il faut appeller
[`deleteLater()`][deleteLater] pour corriger le problème"_ :

[deleteLater]: http://doc.qt.io/qt-5/qobject.html#deleteLater

{% highlight cpp %}
device->deleteLater();
{% endhighlight %}

Mais malheureusement, ici, c'est faux : si le _slot_ branché au _signal_
`DeviceMonitor::deviceLeft(Device *)` est associé à un [`QObject`][QObject]
vivant dans un autre [thread][thread-affinity], rien ne garantit que son
exécution aura lieu avant la suppression du pointeur.

[QObject]: http://doc.qt.io/qt-5/qobject.html
[thread-affinity]: http://doc.qt.io/qt-5/qobject.html#thread-affinity

L'utilisation des _owning raw pointers_ n'est donc pas seulement vulnérable aux
erreurs d'inattention (comme dans les exemples précédents) : dans des cas plus
complexes, il devient **difficile de déterminer quand supprimer le pointeur**.


### Responsabilité

De manière plus générale, lorsque nous avons un pointeur, nous ne savons pas
forcément qui a la responsabilité de le supprimer, ni comment le supprimer :

{% highlight cpp %}
Data *data = getSomeData();
delete data; // ?
free(data); // ?
custom_deleter(data); // ?
{% endhighlight %}

_Qt fournit un mécanisme pour supprimer automatiquement les `QObject *` quand
leur parent est détruit. Cependant, cette fonctionnalité ne s'applique qu'aux
[relations de composition]._

[qobject-tree]: http://doc.qt.io/qt-5/objecttrees.html
[relations de composition]: https://fr.wikipedia.org/wiki/Composition_%28programmation%29


Résumons les inconvénients des _owning raw pointeurs_ :

 - la gestion mémoire est manuelle ;
 - leur utilisation est propice aux erreurs ;
 - la responsabilité de suppression n'est pas apparente ;
 - déterminer quand supprimer le pointeur peut être difficile.


## Valeurs

Laissons de côté les pointeurs quelques instants pour observer ce qu'il se passe
avec de simples _valeurs_ (des _objets_ plutôt que des _pointeurs vers des
objets_) :

{% highlight cpp %}
struct Vector {
    int x, y, z;
};

Vector transform(const Vector &v) {
    return { -v.x, v.z, v.y };
}

void compute() {
    Vector vector = transform({ 1, 2, 3 });
    emit finished(transform(vector));
}
{% endhighlight %}

C'est plus simple : la gestion mémoire est automatique, et le code est plus sûr.
Par exemple, les fuites mémoire et les double suppressions sont impossibles.

**Ce sont des avantages dont nous souhaiterions bénéficier également pour les
pointeurs.**


### Privilégier les valeurs

Dans les cas où les pointeurs sont utilisés uniquement pour éviter de retourner
des copies (et non pour partager des objets), il est préférable de **retourner
les objets par valeur** à la place.

Par exemple, si vous avez une classe :

{% highlight cpp %}
struct Result {
    QString message;
    int code;
};
{% endhighlight %}

Évitez :

{% highlight cpp %}
Result *execute() {
    // …
    return new Result { message, code };
}
{% endhighlight %}

Préférez :

{% highlight cpp %}
Result execute() {
    // …
    return { message, code };
}
{% endhighlight %}

Certes, dans certains cas, il est moins efficace de passer un objet par valeur
qu'à travers un pointeur (car il faut le copier).

Mais cette inefficacité est à relativiser.

D'abord parce que dans certains cas _(quand l'objet est copié à partir d'une
[rvalue reference][])_, la copie sera remplacée par un [_move_][move]. Le _move_
d'un [`vector`][vector] par exemple n'entraîne aucune copie (ni _move_) de ses
éléments.

[rvalue reference]: http://thbecker.net/articles/rvalue_references/section_01.html
[move]: http://www.cprogramming.com/c++11/rvalue-references-and-move-semantics-in-c++11.html
[vector]: http://en.cppreference.com/w/cpp/container/vector

Ensuite parce que les compilateurs optimisent le retour par valeur
([RVO][]), ce qui fait qu'en réalité dans les exemples ci-dessus, aucun `Result`
ni `Vector` n'est jamais copié ni _mové_ : ils sont directement créés à
l'endroit où ils sont affectés _(sauf si vous compilez avec le paramètre
`-fno-elide-constructors`)_.

[rvo]: https://en.wikipedia.org/wiki/Return_value_optimization

Mais évidemment, il y a des cas où nous ne pouvons pas simplement remplacer un
_pointeur_ par une _valeur_, par exemple quand un même objet doit être partagé
entre différentes parties d'un programme.

**Nous voudrions les avantages des _valeurs_ également pour ces cas-là.** C'est
l'objectif de la suite du billet.


## Idiomes C++

Pour y parvenir, nous avons besoin de faire un détour par quelques _idiomes_
couramment utilisés en C++.

Ils ont souvent un nom étrange. Par exemple :

 - [RAII][] _(Resource Acquisition Is Initialization)_
 - [PIMPL][] _(Pointer to IMPLementation)_
 - [CRTP][] _(Curiously Recurring Template Pattern)_
 - [SFINAE][] _(Substitution Failure Is Not An Error)_
 - [IIFE][] _(Immediately-Invoked Function Expression)_

[RAII]: https://en.wikipedia.org/wiki/Resource_acquisition_is_initialization
[PIMPL]: https://en.wikipedia.org/wiki/Opaque_pointer
[CRTP]: https://en.wikipedia.org/wiki/Curiously_recurring_template_pattern
[SFINAE]: https://en.wikipedia.org/wiki/Substitution_failure_is_not_an_error
[IIFE]: https://en.wikipedia.org/wiki/Immediately-invoked_function_expression

Nous allons étudier les deux premiers.

### RAII

Prenons un exemple simple :

{% highlight cpp %}
bool submit() {
    if (!validate())
        return false;
    return something();
}
{% endhighlight %}

[exception]: http://en.cppreference.com/w/cpp/language/exceptions

Nous souhaitons rendre cette méthode [thread-safe][] grâce à un [mutex][]
([`std::mutex`][std::mutex] en STL ou [`QMutex`][QMutex] en Qt).

Supposons que `validate()` et `something()` puissent lever une [exception][].

Le _mutex_ doit être déverrouillé à la fin de l'exécution de la méthode. Le
problème, c'est que cela peut se produire à différents endroits, donc nous
devons gérer tous les cas :

[thread-safe]: https://fr.wikipedia.org/wiki/Thread_safety
[mutex]: https://fr.wikipedia.org/wiki/Exclusion_mutuelle
[std::mutex]: http://en.cppreference.com/w/cpp/thread/mutex
[QMutex]: http://doc.qt.io/qt-5/qmutex.html

{% highlight cpp %}
bool submit() {
    mutex.lock();
    try {
        if (!validate()) {
            mutex.unlock();
            return false;
        }
        bool result = something();
        mutex.unlock();
        return result;
    } catch (...) {
        mutex.unlock();
        throw;
    }
}
{% endhighlight %}

Le code est beaucoup plus complexe et propice aux erreurs.

Avec des classes utilisant RAII ([`std::lock_guard`][std::lock_guard] en STL ou
[`QMutexLocker`][QMutexLocker] en Qt), c'est beaucoup plus simple :

[std::lock_guard]: http://en.cppreference.com/w/cpp/thread/lock_guard
[QMutexLocker]: http://doc.qt.io/qt-5/qmutexlocker.html

{% highlight cpp %}
bool submit() {
    QMutexLocker locker(&mutex);
    if (!validate())
        return false;
    return something();
}
{% endhighlight %}

En ajoutant une seule ligne, la méthode est devenue _thread-safe_.

Cette technique consiste à utiliser le cycle de vie d'un objet pour acquérir une
ressource dans le constructeur (ici verrouiller le _mutex_) et la relâcher dans
le destructeur (ici le déverrouiller).

Voici une implémentation simplifiée possible de [`QMutexLocker`][QMutexLocker] :

{% highlight cpp %}
class QMutexLocker {
    QMutex *mutex;
public:
    explicit QMutexLocker(QMutex *mutex) : mutex(mutex) {
        mutex->lock();
    }
    ~QMutexLocker() {
        mutex->unlock();
    }
};
{% endhighlight %}

Comme l'objet est détruit lors de la sortie du _scope_ de la méthode (que ce
soit par un `return` ou par une exception survenue n'importe où), le _mutex_
sera **toujours** déverrouillé.

Au passage, dans l'exemple ci-dessus, nous remarquons que la variable `locker`
n'est jamais utilisée. RAII complexifie donc la détection des _variables
inutilisées_, car le compilateur doit détecter les effets de bords. Mais il s'en
sort bien : ici, il n'émet pas de _warning_.


## Smart pointers

Les [smart pointers] utilisent RAII pour gérer automatiquement la durée de vie
des pointeurs. Il en existe plusieurs.

[smart pointers]: https://en.wikipedia.org/wiki/Smart_pointer

Dans la STL :

 - [`std::unique_ptr`][std::unique_ptr]
 - [`std::shared_ptr`][std::shared_ptr]
 - [`std::weak_ptr`][std::weak_ptr]
 - [`std::auto_ptr`][std::auto_ptr] _(à bannir)_

[std::unique_ptr]: http://en.cppreference.com/w/cpp/memory/unique_ptr
[std::shared_ptr]: http://en.cppreference.com/w/cpp/memory/shared_ptr
[std::weak_ptr]: http://en.cppreference.com/w/cpp/memory/weak_ptr
[std::auto_ptr]: http://en.cppreference.com/w/cpp/memory/auto_ptr

Dans Qt :

 - [`QSharedPointer`][QSharedPointer] (équivalent de `std::shared_ptr`)
 - [`QWeakPointer`][QWeakPointer] (équivalent de `std::weak_ptr`)
 - [`QScopedPointer`][QScopedPointer] (ersatz de `std::unique_ptr`)
 - [`QScopedArrayPointer`][QScopedArrayPointer]
 - [`QPointer`][QPointer]
 - [`QSharedDataPointer`][QSharedDataPointer]
 - [`QExplicitlySharedDataPointer`][QExplicitlySharedDataPointer]

[QSharedPointer]: http://doc.qt.io/qt-5/qsharedpointer.html
[QWeakPointer]: http://doc.qt.io/qt-5/qweakpointer.html
[QScopedPointer]: http://doc.qt.io/qt-5/qscopedpointer.html
[QScopedArrayPointer]: http://doc.qt.io/qt-5/qscopedarraypointer.html
[QPointer]: http://doc.qt.io/qt-5/qpointer.html
[QSharedDataPointer]: http://doc.qt.io/qt-5/qshareddatapointer.html
[QExplicitlySharedDataPointer]: http://doc.qt.io/qt-5/qexplicitlyshareddatapointer.html


### Scoped pointers

Le _smart pointer_ le plus simple est le _scoped pointer_. L'idée est vraiment
la même que [`QMutexLocker`][QMutexLocker], sauf qu'au lieu de vérouiller et
déverrouiller un _mutex_, il stocke un _raw pointer_ et le supprime.

En plus de cela, comme tous les _smart pointers_, il [redéfinit certains
opérateurs][operator overloading] pour pouvoir être utilisé comme un _raw
pointer_.

[operator overloading]: https://en.wikipedia.org/wiki/Operator_overloading

Par exemple, voici une implémentation simplifiée possible de
[`QScopedPointer`][QScopedPointer] :

{% highlight cpp %}
template <typename T>
class QScopedPointer {
    T *p;
public:
    explicit QScopedPointer(T *p) : p(p) {}
    ~QScopedPointer() { delete p; }
    T *data() const { return p; }
    operator bool() const { return p; }
    T &operator*() const { return *p; }
    T *operator->() const { return p; }
private:
    Q_DISABLE_COPY(QScopedPointer)
};
{% endhighlight %}

Et un exemple d'utilisation :

{% highlight cpp %}
// bad design (owning raw pointer)
DeviceInfo *Device::getDeviceInfo() {
    return new DeviceInfo(/* … */);
}

void Device::printInfo() {
    QScopedPointer<DeviceInfo> info(getDeviceInfo());
    // used like a raw pointer
    if (info) {
        qDebug() << info->getId();
        DeviceInfo copy = *info;
    }
    // automatically deleted
}
{% endhighlight %}


### Shared pointers

Les _shared pointers_ permettent de partager l'_ownership_ (la responsabilité de
suppression) d'une ressource.

Ils contiennent un [compteur de références][refcount], indiquant le nombre
d'instances partageant le même pointeur. Lorsque ce compteur tombe à 0, le
pointeur est supprimé (il faut donc éviter les [cycles][]).

[refcount]: https://en.wikipedia.org/wiki/Reference_counting
[cycles]: https://en.wikipedia.org/wiki/Reference_counting#Dealing_with_reference_cycles

En pratique, voici ce à quoi ressemblerait une liste de `Device`s _partagés_ par
des [`QSharedPointer`][QSharedPointer]s :

{% highlight cpp %}
class DeviceList {
    QList<QSharedPointer<Device>> devices;
public:
    QSharedPointer<Device> getDevice(int index) const;
    void add(const QSharedPointer<Device> &device);
    void remove(Device *device);
};
// devices are automatically deleted when necessary
{% endhighlight %}

Le _partage_ d'un pointeur découle toujours de la copie d'un _shared pointer_.
C'est la raison pour laquelle `getDevice(…)` et `add(…)` manipulent un
[`QSharedPointer`][QSharedPointer].

Le piège à éviter est de créér plusieurs _smart pointers_ indépendants sur le
même _raw pointer_. Dans ce cas, il y aurait deux _refcounts_ à 1 plutôt qu'un
_refcount_ à 2, et le pointeur serait supprimé dès la destruction du premier
_shared pointer_, laissant l'autre [_pendouillant_][dangling].

[dangling]: https://fr.wikipedia.org/wiki/Dangling_pointer

_Petite parenthèse : la signature des méthodes `add` et `remove` sont
différentes car une suppression ne nécessite pas de [manipuler la durée de
vie][no copy no cry] du `Device` passé en paramètre._

> Refcounted smart pointers are about managing te owned object's lifetime.
>
> Copy/assign one only when you intend to manipulate the owned object's
> lifetime.

[no copy no cry]: https://www.youtube.com/watch?v=xnqTKD8uD64&t=18m38s

Au passage, si en Qt vous passez vos objets de la couche C++ à la couche
[QML][], il faut aussi passer les _shared pointers_ afin de ne pas casser le
partage, ce qui implique d'enregistrer le type :

[QML]: https://en.wikipedia.org/wiki/QML

{% highlight cpp %}
void registerQml() {
    qRegisterMetaType<QSharedPointer<Device>>();
}
{% endhighlight %}

Listons donc les avantages des _shared pointers_ :

 - la gestion mémoire est automatique ;
 - l'_ownership_ est géré automatiquement ;
 - l'utilisation est moins propice aux erreurs (à part la possibilité de créer
   des _smart pointers_ indépendants sur le même _raw pointer_) ;

Cependant, si la gestion mémoire est **automatique**, elle n'est pas
**transparente** : elle nécessite de manipuler explicitement des
`QSharedPointer`, ce qui est verbeux.

Il est certes possible d'utiliser un [alias][] ([typedef][]) pour atténuer la
verbosité :

[alias]: http://en.cppreference.com/w/cpp/language/type_alias
[typedef]: https://en.wikipedia.org/wiki/Typedef

{% highlight cpp %}
using DevicePtr = QSharedPointer<Device>;

class DeviceList {
    QList<DevicePtr> devices;
public:
    DevicePtr getDevice(int index) const;
    void add(const DevicePtr &device);
    void remove(Device *device);
};
{% endhighlight %}

Mais quoi qu'il en soit, **cela reste plus complexe que des _valeurs_**.

Pour aller plus loin, nous allons devoir faire un détour inattendu, par un
_idiome_ qui n'a a priori rien à voir.


## PImpl

[PImpl][] sert à réduire les dépendances de compilation.

> Opaque pointers are a way to hide the implementation details of an interface
> from ordinary clients, so that the implementation may be changed without the
> need to recompile the modules using it.

Prenons la classe `Person` suivante (`person.h`) :

{% highlight cpp %}
class Person {
    QString name;
    long birth;
public:
    Person(const QString &name, long birth);
    QString getName() const;
    void setName(const QString &name);
    int getAge() const;
private:
    static long countYears(long from, long to);
};
{% endhighlight %}

Elle contient juste un _nom_ et un _âge_. Elle définit par ailleurs une méthode
privée, `countYears(…)`, qu'on imagine appelée dans `getAge()`.

Chaque classe désirant utiliser la classe `Person` devra l'inclure :

{% highlight cpp %}
#include "person.h"
{% endhighlight %}

Par conséquent, à chaque modification de ces parties privées (qui sont pourtant
que des détails d'implémentation), **toutes les classes incluant `person.h`
devront être recompilées**.

C'est ce que _PImpl_ permet d'éviter, en séparant la classe en deux :

 - une interface publique ;
 - une implémentation privée.

Concrètement, la classe `Person` précédente est la partie privée. Renommons-la :

{% highlight cpp %}
class PersonPrivate {
    QString name;
    long birth;
public:
    PersonPrivate(const QString &name, long birth);
    QString getName() const;
    void setName(const QString &name);
    int getAge() const;
private:
    static long countYears(long from, long to);
};
{% endhighlight %}

Créons la partie publique, définissant l'interface souhaitée :

{% highlight cpp %}
class PersonPrivate; // forward declaration
class Person {
    PersonPrivate *d;
public:
    Person(const QString &name, long birth);
    Person(const Person &other);
    ~Person();
    Person &operator=(const Person &other);
    QString getName() const;
    void setName(const QString &name);
    int getAge() const;
};
{% endhighlight %}

Elle contient un pointeur vers `PersonPrivate`, et lui délègue tous les appels.

Évidemment, `Person` ne doit pas inclure `PersonPrivate`, sinon nous aurions les
mêmes dépendances de compilation, et nous n'aurions rien résolu. Il faut
utiliser à la place une [_forward declaration_][forward declaration].

[forward declaration]: https://en.wikipedia.org/wiki/Forward_declaration

Voici son implémentation :

{% highlight cpp %}
Person::Person(const QString &name, long birth) :
        d(new PersonPrivate(name, birth)) {}

Person::Person(const Person &other) :
        d(new PersonPrivate(*other.d)) {}

Person::~Person() { delete d; }

Person &Person::operator=(const Person &other) {
    *d = *other.d;
    return *this;
}

QString Person::getName() const {
    return d->getName();
}

void Person::setName(const QString &name) {
    d->setName(name);
}

int Person::getAge() const {
    return d->getAge();
}
{% endhighlight %}

Le pointeur vers la classe privée est souvent nommé `d` car il s'agit d'un
[d-pointer][].

[d-pointer]: https://wiki.qt.io/D-Pointer

Donc comme prévu, tout cela n'a rien à voir avec notre objectif d'éviter
d'utiliser des pointeurs.


## Partage

Mais en fait, si. _PImpl_ permet de séparer les classes manipulées explicitement
de l'objet réellement modifié :

{: .center}
![graph_pimpl]({{ site.assets }}/nopointers/pimpl.png)

Il y a une relation 1-1 entre la classe publique et la classe privée
correspondante. Mais nous pouvons imaginer d'autres [cardinalités][].

[cardinalités]: https://fr.wikipedia.org/wiki/Cardinalit%C3%A9_%28programmation%29

Par exemple, Qt [partage implicitement][qt-implicit-sharing] les parties privées
d'un grand nombre de [classes][qt-implicit-sharing-classes]. Il ne les copie que
lors d'une écriture ([CoW][]) :

{: .center}
![graph_pimpl_shareddata]({{ site.assets }}/nopointers/pimpl_shareddata.png)

[qt-implicit-sharing]: http://doc.qt.io/qt-5/implicit-sharing.html
[qt-implicit-sharing-classes]: http://doc.qt.io/qt-5/implicit-sharing.html#list-of-classes
[CoW]: https://fr.wikipedia.org/wiki/Copy-on-write

Par exemple, lorsqu'une [`QString`][QString] est copiée, la même zone mémoire
sera utilisée pour les différentes instances, jusqu'à ce qu'une modification
survienne.

[QString]: http://doc.qt.io/qt-5/qstring.html

Cependant, il ne s'agit que d'un détail d'implémentation utilisé pour améliorer
les performances. Du point de vue utilisateur, tout se passe comme si les
données étaient réellement copiées :

{% highlight cpp %}
QString s1 = "ABC";
QString s2 = s1;
s2.append("DEF");
Q_ASSERT(s2 == "ABCDEF");
Q_ASSERT(s1 == "ABC");
{% endhighlight %}

En d'autres termes, **les classes publiques ci-dessus ont une [sémantique de
valeur][value semantics]**.

[value semantics]: https://en.wikipedia.org/wiki/Value_semantics


### Resource handles

À la place, nous pouvons décider de partager inconditionnellement la partie
privée, y compris après une écriture :

{: .center}
![graph_pimpl_shared]({{ site.assets }}/nopointers/pimpl_shared.png)

Dans ce cas, **la classe publique a sémantique d'entité**. Elle est qualifiée de
_resource handle_.

C'est bien sûr le cas des _smart pointers_ :

{% highlight cpp %}
QSharedPointer<Person> p1(new Person("ABC", 42));
QSharedPointer<person> p2 = p1;
p2->setName("DEF");
Q_ASSERT(p1.getName() == "DEF");
Q_ASSERT(p2.getName() == "DEF");
{% endhighlight %}

Mais aussi d'autres classes, comme l'[API Dom de Qt][QDomDocument] :

{% highlight cpp %}
void addItem(const QDomDocument &document, const QDomElement &element) {
    QDomElement root = document.documentElement();
    root.insertAfter(element, {});
    // the document is modified
}
{% endhighlight %}

[QDomDocument]: http://doc.qt.io/qt-5/qdomdocument.html#details


### PImpl avec des _smart pointers_

Tout-à-l'heure, j'ai présenté _PImpl_ en utilisant un _owning raw pointer_ :

{% highlight cpp %}
class PersonPrivate; // forward declaration
class Person {
    // this is a raw pointer!
    PersonPrivate *d;
public:
    // …
};
{% endhighlight %}

Mais en fait, à chaque type de relation correspond un type de _smart pointer_
directement utilisable pour _PImpl_.

Pour une relation 1-1 classique :

 - [`std::unique_ptr`][std::unique_ptr]
 - [`QScopedPointer`][QScopedPointer]

Pour une relation 1-N à sémantique de valeur (CoW) :

 - [`QSharedDataPointer`][QSharedDataPointer]

Pour une relation 1-N à sémantique d'entité :

 - [`std::shared_ptr`][std::shared_ptr]
 - [`QSharedPointer`][QSharedPointer]

Par exemple, donnons à notre classe `Person` une sémantique d'entité :

{% highlight cpp %}
class PersonPrivate; // forward declaration
class Person {
    QSharedPointer<PersonPrivate> d;
public:
    Person() = default; // a "null" person
    Person(const QString &name, long birth);
    QString getName() const;
    // shared handles should expose const methods
    void setName(const QString &name) const;
    int getAge() const;
    operator bool() const { return d; }
};
{% endhighlight %}

`Person` se comporte maintenant _comme un pointeur_.

{% highlight cpp %}
Person p1("ABC", 42);
Person p2 = p1;
p2.setName("DEF");
Q_ASSERT(p1.getName() == "DEF");
Q_ASSERT(p2.getName() == "DEF");
{% endhighlight %}

`p1` et `p2` sont alors des _resource handles_ vers `PersonPrivate` :

{: .center}
![graph_shared_person]({{ site.assets }}/nopointers/shared_person.png)

Évidemment, ce n'est pas approprié pour la classe `Person`, car le comportement
est trop inattendu.

Mais je vais présenter un cas réel où ce _design_ est approprié.


## En pratique

<a id="libusb-wrappers" />
Pour l'entreprise dans laquelle je suis salarié, j'ai implémenté une
fonctionnalité permettant d'utiliser une souris USB branchée sur un PC pour
contrôler un téléphone Android connecté en USB.

Concrètement, cela consiste à tranférer (grâce à [`libusb`][libusb]), à partir
du PC, les événements [HID][] reçus de la souris vers le téléphone Android.

[libusb]: http://libusb.info
[HID]: https://en.wikipedia.org/wiki/Human_interface_device

J'ai donc (entre autres) créé des _resources handles_ `UsbDevice` et
`UsbDeviceHandle` qui wrappent les structures C [`libusb_device`][libusb_device]
et [`libusb_device_handle`][libusb_device_handle], suivant les principes
détaillés dans ce billet.

[libusb_device]: http://libusb.sourceforge.net/api-1.0/group__dev.html#ga77eedd00d01eb7569b880e861a971c2b
[libusb_device_handle]: http://libusb.sourceforge.net/api-1.0/group__dev.html#ga7df95821d20d27b5597f1d783749d6a4

Leur utilisation illustre bien, d'après moi, les bénéfices d'une telle
conception.

{% highlight cpp %}
class UsbDeviceMonitor {
    QList<UsbDevice> devices;
public:
    // …
    UsbDevice getAnyDroid() const;
    UsbDevice getAnyMouse() const;
signals:
    void deviceArrived(const UsbDevice &device);
    void deviceLeft(const UsbDevice &device);
};
{% endhighlight %}

`UsbDevice` peut être retourné par valeur, et passé en paramètre d'un _signal_
par _const reference_ (exactement comme nous le ferions avec un
[`QString`][QString]).

{% highlight cpp %}
UsbDevice UsbDeviceMonitor::getAnyMouse() const {
    for (const UsbDevice &device : devices)
        if (device.isMouse())
            return device;
    return {};
}
{% endhighlight %}

Si une souris est trouvée dans la liste, on la retourne simplement ; sinon, on
retourne un `UsbDevice` "[_null_][null]".

[null]: http://doc.qt.io/qt-5/qstring.html#isNull

{% highlight cpp %}
void UsbDeviceMonitor::onHotplugDeviceArrived(const UsbDevice &device) {
    devices.append(device);
    emit deviceArrived(device);
}
{% endhighlight %}

La gestion mémoire est totalement automatique et transparente. Les [problèmes
présentés](#suppression-complexe) sont résolus.

{% highlight cpp %}
void registerQml() {
    qRegisterMetaType<UsbDevice>();
}
{% endhighlight %}

{% highlight javascript %}
// QML
function startForwarding() {
    var mouse = usbDeviceMonitor.getAnyMouse()
    var droid = usbDeviceMonitor.getAnyDroid()
    worker = hid.forward(mouse, droid)
}
{% endhighlight %}

`UsbDevice` peut naviguer entre la couche C++ et QML.

{% highlight cpp %}
bool HID::forward(const UsbDevice &mouse, const UsbDevice &droid) {
    UsbDeviceHandle droidHandle = droid.open();
    if (!droidHandle)
        return false;
    UsbDeviceHandle mouseHandle = mouse.open();
    if (!mouseHandle)
        return false;
    // …
}
{% endhighlight %}

Grâce à RAII, les connexions (`UsbDeviceHandle`) sont fermées automatiquement.

En particulier, si la connexion à la souris échoue, la connexion au téléphone
Android est automatiquement fermée.


## Résultat

Dans ces différents exemples, `new` et `delete` ne sont jamais utilisés, et
**par construction, la mémoire sera correctement gérée**. Ou plus précisément,
si un problème de gestion mémoire existe, il se situera dans l'implémentation de
la classe elle-même, et non partout où elle est utilisée.

Ainsi, nous manipulons des _handles_ se comportant comme des _pointeurs_, ayant
les mêmes avantages que les _valeurs_ :

 - gestion mémoire **automatique** et **transparente** ;
 - simple ;
 - efficace ;
 - sûr et robuste.

Ils peuvent par contre présenter quelques limitations.

Par exemple, ils sont incompatibles avec [`QObject`][QObject]. En effet,
techniquement, la classe d'un _resource handle_ doit pouvoir être copiée (pour
supporter le passage par _valeur_), alors qu'un [`QObject`][QObject] [n'est pas
copiable][identity-vs-value] :

> `QObject`s are _identities_, not _values_.

[identity-vs-value]: http://doc.qt.io/qt-5/object.html#identity-vs-value

Très concrètement, cela implique que `UsbDevice` ne pourrait pas supporter de
_signaux_ (en tout cas, pas directement). C'est d'ailleurs le cas de beaucoup de
classes de Qt : par exemple [`QString`][QString] et [`QList`][QList] n'héritent
pas de [`QObject`][QObject].

[QList]: http://doc.qt.io/qt-5/qlist.html


## Résumé

{% highlight cpp %}
auto decide = [=] {
    if (semantics == VALUE) {
        if (!mustAvoidCopies)
            return "just use values";
        return "use PImpl + QSharedDataPointer";
    }
    // semantics == ENTITY
    if (entitySemanticsIsObvious)
        return "use PImpl + QSharedPointer";
    return "use smart pointers explicitly";
};
{% endhighlight %}

_C'est juste une heuristique…_


## Conclusion

En suivant ces principes, nous pouvons nous débarrasser des _owning
raw pointers_ et des `new` et `delete` "nus". Cela contribue à rendre le code
plus simple et plus robuste.

Ce sont d'ailleurs des objectifs qui guident les évolutions du langage C++ :

 - [A brief introduction to C++'s model for type and resource-safety](http://www.stroustrup.com/resource-model.pdf)
 - [Writing good C++14](https://github.com/isocpp/CppCoreGuidelines/blob/master/talks/Stroustrup%20-%20CppCon%202015%20keynote.pdf)
 - [Elements of Modern C++ Style](https://herbsutter.com/elements-of-modern-c-style/)

`return 0;`
