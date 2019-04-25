---
layout: post
title: Exécuter un algorithme lors de la compilation (templates C++)
date: 2015-03-27 19:53:08+01:00
---

{: .center}
![hanoi]({{ site.assets }}/metahanoi/hanoi.jpg)

En général, pour résoudre un problème donné, nous écrivons un algorithme dans un
langage source, puis nous le compilons (dans le cas d'un langage compilé). La
compilation consiste à traduire le code source en un code exécutable par une
machine cible. C'est lors de l'exécution de ce fichier que l'algorithme est
déroulé.

Mais certains langages, en l'occurrence C++, proposent des mécanismes permettant
un certain contrôle sur la phase de compilation, tellement expressifs qu'ils
permettent la [métaprogrammation][]. Nous pouvons alors faire exécuter un
algorithme directement par le compilateur, qui se contente de produire un
fichier exécutable affichant le résultat.

[métaprogrammation]: https://fr.wikipedia.org/wiki/M%C3%A9taprogrammation

À titre d'exemple, je vais présenter dans ce billet comment résoudre le problème
des [tours de Hanoï][] (généralisé, c'est-à-dire quelque soit la position
initiale des disques) lors de la phase de compilation.

[tours de Hanoï]: https://fr.wikipedia.org/wiki/Tours_de_Hano%C3%AF

Les programmes complets décrits dans ce billet sont disponibles ici:
[metahanoi].

[metahanoi]: https://github.com/rom1v/metahanoi


## Problème des tours de Hanoï généralisé

La résolution naturelle du problème [généralisé][] des tours de Hanoï est
récursive.

[généralisé]: https://fr.wikipedia.org/wiki/Tours_de_Hano%C3%AF#Algorithme_g.C3.A9n.C3.A9ralis.C3.A9_.C3.A0_une_position_quelconque

Pour déplacer _n_ disques vers la tour _T_, il faut:

 1. déterminer sur quelle tour _S_ se trouve le plus grand des _n_ disques ;
 2. déplacer les _n-1_ premiers disques sur la tour intermédiaire _I_ (ni _S_ ni
    _T_) ;
 3. déplacer le plus grand disque de _S_ vers _T_ ;
 4. redéplacer les _n-1_ premiers disques vers _I_ vers _T_.

En voici une implémentation _classique_ en C++ (le compilateur va générer le code permettant d'exécuter l'algorithme) :

{% highlight cpp %}
#include <iterator>
#include <iostream>
#include <vector>

class Hanoi {
    using tower = unsigned int;
    using size = unsigned int;

    std::vector<tower> state; // disk number i is on tower state[i]

public:
    Hanoi(std::initializer_list<tower> init) : state(init) {}

    void solve(tower target) {
        printState(); // initial state
        solveRec(state.size(), target);
    }

private:
    void solveRec(size disks, tower target) {
        if (disks == 0) {
            return;
        }
        // the tower of the largest disk at this depth of recursion
        tower &largest = state[state.size() - disks];
        if (largest == target) {
            // the largest disk is already on the target tower
            solveRec(disks - 1, target);
        } else {
            // move disks above the largest to the intermediate tower
            solveRec(disks - 1, other(largest, target));
            // move the largest disk to the target
            largest = target;
            printState();
            // move back the disks on the largest
            solveRec(disks - 1, target);
        }
    }

    void printState() {
        std::copy(state.cbegin(), state.cend(),
                  std::ostream_iterator<tower>(std::cout));
        std::cout << std::endl;
    }

    static inline tower other(tower t1, tower t2) {
        return 3 - t1 - t2;
    }

};

int main() {
    Hanoi{ 0, 0, 0, 0, 0 }.solve(2);
    return 0;
}
{% endhighlight %}

([source][hanoi.cpp] – [commit][commit-runtime] – tag `runtime`)

[hanoi.cpp]: https://github.com/rom1v/metahanoi/blob/runtime/hanoi.cpp
[commit-runtime]: https://github.com/rom1v/metahanoi/commit/runtime

À compiler avec :

{% highlight bash %}
g++ -std=c++11 hanoi.cpp -o hanoi
{% endhighlight %}

L'algorithme utilise un simple vecteur de positions des disques, indexés du plus
grand au plus petit, pour stocker l'état courant.

Par exemple, l'état `{ 0, 0, 0, 0 }` représente 4 disques sur la tour 0 :

    state = { 0, 0, 0, 0 };
    
        0         1         2
    
        |         |         |
       -+-        |         |
      --+--       |         |
     ---+---      |         |
    ----+----     |         |

L'état `{ 1, 1, 0, 2 }`, quant-à lui, représente ces positions:


    state = { 1, 1, 2, 1 };
    
        0         1         2
    
        |         |         |
        |         |         |
        |        -+-        |
        |      ---+---      |
        |     ----+----   --+--

Dans cette version, pour déplacer le disque `i`, il suffit alors de changer la
valeur de `state[i]`.

Il serait possible d'utiliser une structure de données différente, comme un
vecteur par tour stockant les numéros des disques présents, mais celle que j'ai
choisie sera beaucoup plus adaptée pour la suite.

Étant données 2 tours _T1_ et _T2_, il est très facile d'en déduire la 3e : _3 -
T1 - T2_. Ce calcul est extrait dans la fonction _inlinée_ `other(…)`.

Le contexte étant présenté, essayons maintenant d'implémenter le même algorithme
pour le faire exécuter par le compilateur.


## Structure de données constante

`std::vector` est une structure de données utilisable uniquement à l'exécution :
il n'est pas possible d'en créer ou d'en utiliser une instance lors de la
compilation. Même l'utilisation d'un simple tableau d'entiers nous poserait des
problèmes par la suite.

Nous allons donc grandement simplifier le stockage des positions des disques,
**en utilisant un seul entier**. En effet, à chaque disque est associée une tour
qui peut être 0, 1 ou 2. Par conséquent, un chiffre en [base 3][] (un _trit_)
suffit pour stocker la position d'une tour.

[base 3]: https://fr.wikipedia.org/wiki/Syst%C3%A8me_trinaire

Ainsi, nous pouvons représenter l'état `{ 1, 2, 1, 0, 2 }` par l'entier 146
_(1×81 + 2×27 + 1×9 + 0×3 + 2×1)_ :

| 3<sup>4</sup> | 3<sup>3</sup> | 3<sup>2</sup> | 3<sup>1</sup> | 3<sup>0</sup> |
|:-------------:|:-------------:|:-------------:|:-------------:|:-------------:|
| 1             | 2             | 1             | 0             | 2             |

Au passage, voici comment convertir rapidement un nombre dans une autre base en
_shell_ (pratique pour débugger) :

    $ echo 'ibase=3; 12102' | bc
    146
    $ echo 'obase=3; 146' | bc
    12102

Nous allons utiliser le type entier le plus long, à savoir [`unsigned long
long`][c types], stockant au minimum 64 bits, soit 64 digits en base 2. En base
3, cela représente _64 × ln(2)/ln(3) ≃_ 40 digits : nous pouvons donc stocker la
position de 40 disques dans un seul entier.

[c types]: https://fr.wikipedia.org/wiki/C_%28langage%29#Types

Pour cela, définissons un type `state` :

{% highlight cpp %}
using state = unsigned long long;
{% endhighlight %}


## Expressions constantes généralisées

Nous allons écrire une première ébauche n'utilisant que des [expressions
constantes][], clairement indiquées et vérifiées depuis C++11 grâce au mot-clé
[`constexpr`][constexpr]. Une fonction
`constexpr` doit, en gros, n'être composé que d'une instruction `return`.

[expressions constantes]: http://www.cprogramming.com/c++11/c++11-compile-time-processing-with-constexpr.html
[constexpr]: http://en.cppreference.com/w/cpp/language/constexpr

C'est le cas à l'évidence pour notre fonction `other(…)` :

{% highlight cpp %}
constexpr tower other(tower t1, tower t2) {
    return 3 - t1 - t2;
}
{% endhighlight %}

Grâce à l'[opérateur ternaire][] et à la [récursivité][], nous pouvons cependant
en écrire de plus complexes.

[opérateur ternaire]: https://en.wikipedia.org/wiki/%3F:
[récursivité]: https://fr.wikipedia.org/wiki/R%C3%A9cursivit%C3%A9#R.C3.A9cursivit.C3.A9_en_informatique_et_en_logique

Dans notre programme classique, le déplacement d'un disque _i_ se résumait à
changer la valeur de `state[i]`. Maintenant que l'état du système est compacté
dans un seul entier, l'opération est moins directe.

Soit l'état courant `{ 0, 1, 2, 0, 0 }` (ou plus simplement `01200`). Supposons
que nous souhaitions déplacer le disque au sommet de la tour 2 vers la tour 1.
Cela consiste, en fait, à remplacer le dernier `2` par un `1` (rappelez-vous,
les disques sont indexés du plus grand au plus petit). Le résultat attendu est
donc `01100`.

Notez que ce déplacement n'est pas toujours autorisé. Par exemple, le disque au
sommet de la tour 2 est plus grand (i.e. a un plus petit index) que celui au
sommet de la tour 0, il n'est donc pas possible de le déplacer vers la tour 0.
C'est à l'algorithme que revient la responsabilité de n'effectuer que des
déplacements _légaux_.

Remplacer le dernier digit _x_ de la représentation d'un nombre _N_ (dans une
base quelconque) par _y_ peut s'implémenter récursivement :

  * si le dernier digit _d_ de _N_ vaut _x_, le remplacer par _y_ ;
  * sinon, remplacer _x_ par _y_ dans _N_ privé de son dernier digit, puis
    _recoller_ le dernier digit à la fin.

Concrètement :

          N   N\d d    { x=2, y=1 }
      01200  0120 0    // d != x : remplacer x par y dans N\d
       0120   012 0    //   d != x : remplacer x par y dans N\d
        012    01 2    //     d == x : remplacer x par y
        011    01 1    //     d = y
       0110   011 0    //   recoller d au résultat
      01100  0110 0    // recoller d au résultat

Il reste à expliciter l'étape du remplacement du digit. De manière générale,
remplacer par un chiffre _d_ le dernier digit d'un nombre _N_ exprimé dans une
base _b_ consiste à calculer, soit `N / b * b + d`, soit de manière équivalente
`N - N % b + d` (`/` représentant la [division entière][] et `%` le
[modulo][]). Dans les deux cas, l'opération annule le dernier digit puis ajoute
son remplaçant.

[division entière]: https://fr.wikipedia.org/wiki/Division_euclidienne
[modulo]: https://fr.wikipedia.org/wiki/Modulo_%28op%C3%A9ration%29

{: .center}
[![base10][]](http://cowbirdsinlove.com/43)

[base10]: {{ site.assets }}/metahanoi/base10.png

Sur un exemple en base 10, c'est évident. Remplaçons le dernier chiffre de 125
par 7 selon les deux méthodes :

         N /  b *  b + v         N -   N %  b + d
       125 / 10 * 10 + 7       125 - 125 % 10 + 7
             12 * 10 + 7       125 -        5 + 7
                 120 + 7                  120 + 7
                     127                      127

Arbitrairement, nous utiliserons la première méthode pour implémenter notre
fonction `constexpr` `move(…)` :

{% highlight cpp %}
constexpr state move(state s, tower src, tower target) {
    return s % 3 == src
        ? s / 3 * 3 + target
        : move(s / 3, src, target) * 3 + s % 3;
}
{% endhighlight %}

De la même manière, définissons une fonction `getTower(s, i)` qui retourne la
tour sur laquelle se trouve le _i_ème plus petit disque :

{% highlight cpp %}
constexpr tower getTower(state s, size disk) {
    return disk == 1
        ? s % 3
        : getTower(s / 3, disk - 1);
}
{% endhighlight %}

Attaquons-nous maintenant à la conversion de la fonction `solveRec(…)`. Elle
contenait deux branchements (`if`) et plusieurs instructions séquentielles, que
nous allons devoir transformer en une seule instruction `return`.

Pour cela, nous allons remplacer les branchements par des opérateurs ternaires :

{% highlight cpp %}
if (C) {
    X = A;
} else {
    X = B;
}
{% endhighlight %}

devient :

{% highlight cpp %}
X = C ? A : B;
{% endhighlight %}

Notez que contrairement à la version `if`/`else`, cela implique que les
expressions retournent une valeur. Cela tombe bien, comme une fonction
`constexpr` ne peut pas modifier une variable, notre fonction va retourner
l'état résultant de la transformation.

Concernant les instructions séquentielles, remarquons qu'elles dépendent
successivement les unes des autres. De manière générale, nous pouvons
remplacer :


{% highlight cpp %}
A = f();
B = g(A);
X = h(B);
{% endhighlight %}

par:

{% highlight cpp %}
X = h(g(f()));
{% endhighlight %}

En combinant ces principes, la méthode `solve(…)` peut être écrite ainsi (afin
de bien voir l'imbrication des appels, je ne la découpe volontairement pas en
plusieurs méthodes) :

{% highlight cpp %}
constexpr state solve(size disks, state s, tower target) {
    return disks == 0
        ? s
        : getTower(s, disks) == target
            ? solve(disks - 1, s, target)
            : solve(disks - 1,
                    move(solve(disks - 1,
                               s,
                               other(getTower(s, disks), target)),
                         getTower(s, disks),
                         target),
                    target);
}
{% endhighlight %}

Les appels les plus _profonds_ sont effectués en premier.

Ajoutons une méthode d'affichage pour obtenir la représentation de l'état du
système en base 3 :

{% highlight cpp %}
std::ostream &print_state(std::ostream &os, size ndisks, state s) {
    return ndisks ? print_state(os, ndisks - 1, s / 3) << s % 3 : os;
}
{% endhighlight %}

([source][metahanoi.cpp-constexpr] – [commit][commit-constexpr] – tag
`constexpr`)

[metahanoi.cpp-constexpr]: https://github.com/rom1v/metahanoi/blob/constexpr/metahanoi.cpp
[commit-constexpr]: https://github.com/rom1v/metahanoi/commit/constexpr

Compilons et exécutons le programme :

    $ g++ -std=c++11 metahanoi.cpp && ./metahanoi
    22222

L'état `22222` (soit l'entier _242_) est bien écrit en dur dans le binaire
généré :

    $ g++ -std=c++11 -S metahanoi.cpp && grep 242 metahanoi.s
        movq    $242, -16(%rbp)
        movl    $242, %edx

Le compilateur est donc bien parvenu à la solution.

Mais avouons que le résultat est un peu décevant : l'état final, nous le
connaissions déjà ; ce qui nous intéresse, c'est le cheminement pour y parvenir.
Nous souhaiterions donc qu'en plus, le compilateur nous indique, d'une manière
ou d'une autre, les étapes intermédiaires décrivant la solution du problème.


## Templates

Pour cela, nous allons utiliser les [templates][].

[templates]: https://fr.wikipedia.org/wiki/Template_%28programmation%29

Pour comprendre comment les templates vont nous aider, commençons par quelques
précisions.

Les [classes template][] sont souvent utilisées avec des paramètres
[_types_][template type arguments]. Par exemple, `std::vector<int>` définit un
nouveau type paramétré par le type `int`. Mais il est également possible de
passer des paramètres [_non-types_][template non-type arguments], qui sont alors
des valeurs "simples".

[classes template]: http://en.cppreference.com/w/cpp/language/class_template
[template type arguments]: http://en.cppreference.com/w/cpp/language/template_parameters#Template_type_arguments
[template non-type arguments]: http://en.cppreference.com/w/cpp/language/template_parameters#Template_non-type_arguments

Une _classe template_ ne définit pas un type, mais permet de générer des types
selon les paramètres qui lui sont affectés. Concrètement, `std::vector<int>` et
`std::vector<double>` sont des types totalement différents, comme s'ils étaient
implémentés par deux classes écrites séparément.

Dit autrement, **la classe est au template ce que l'objet est à la classe : une
instance**. Mais c'est une _instance_ qui existe lors de la phase de
compilation !

         +----------------+
         | CLASS TEMPLATE |
         +----------------+
              | template
              | instantiation
              v                     COMPILE TIME
         +----------------+
         |  CLASS / TYPE  |    -----------------------
         +----------------+
              | class                 RUNTIME
              | instantiation
              v
         +----------------+
         |     OBJECT     |
         +----------------+

De la même manière qu'une variable d'instance existe pour chaque objet (instance
de classe), une variable `static` existe pour chaque classe (instance de
template).

Pour conserver les états successifs de la résolution du problème des tours de
Hanoï, nous allons donc créer une classe par étape, dans laquelle nous allons
pouvoir y stocker un état `static`. Nous voulons donc remplacer notre fonction
`solve(…)` par des _classes template_.

Pour illustrer comment, commençons par un template simple effectuant la somme de
deux entiers :


{% highlight cpp %}
template <int I, int J>
struct Sum {
    static constexpr int result = I + J;
};
{% endhighlight %}

Ainsi, l'expression :

{% highlight cpp %}
Sum<3, 4>::result
{% endhighlight %}
est calculée à la compilation et vaut 7.

Prenons maintenant l'exemple d'un calcul récursif : la [factorielle][] d'un
entier. Il nous faut implémenter à la fois le cas général et la condition
d'arrêt. Nous pourrions penser à utiliser l'opérateur ternaire ainsi :

[factorielle]: https://fr.wikipedia.org/wiki/Factorielle

{% highlight cpp %}
template <int N>
struct Fact {
    // does not work!
    static constexpr int result = N == 0 ? 1 : N * Fact<N - 1>::result;
};
{% endhighlight %}

Malheureusement, ceci ne peut pas fonctionner, car pour calculer la valeur d'une
expression, le compilateur doit d'abord calculer le type de chacun des
opérandes. Par conséquent, `Fact<N - 1>` sera généré même si `N == 0`. La
récursivité ne s'arrête donc jamais, provoquant une erreur à la compilation :

    error: template instantiation depth exceeds maximum of 900

Comment faire alors ? La clé réside dans la [spécialisation de templates][], qui
permet de sélectionner l'implémentation de la classe en fonction des
paramètres :

[spécialisation de templates]: http://www.cprogramming.com/tutorial/template_specialization.html

{% highlight cpp %}
template <int N>
struct Fact {
    static constexpr int result = N * Fact<N-1>::result;
};

template <>
struct Fact<0> {
    static constexpr int result = 1;
};
{% endhighlight %}

Lorsque `Fact` est instancié avec le paramètre `0`, la classe est générée à
partir du template spécialisé, stoppant ainsi la récursivité.

Appliquons ce principe à notre algorithme des tours de Hanoï. Nous allons
définir une classe template `Solver` avec 3 paramètres de template, les mêmes
que notre fonction `solve(…)` :

{% highlight cpp %}
template <size DISKS, state S, tower TARGET>
struct SolverRec { /* … */ };
{% endhighlight %}

Puis nous allons en définir une spécialisation pour le cas où `DISKS` vaut 0 :

{% highlight cpp %}
template <state S, tower TARGET>
struct SolverRec<0, S, TARGET> { /* … */ };
{% endhighlight %}

Nous avons ainsi implémenté le premier branchement sur la condition `DISKS ==
0`.

Un second branchement reste à réaliser : le calcul à effectuer est différent
selon si le plus grand disque parmi les `DISKS` derniers est déjà sur la tour
cible ou non. Celui-ci est plus compliqué, car les paramètres de template
présents ne permettent pas d'évaluer sa condition.

Nous allons donc devoir ajouter en paramètre la position du disque `SRC` afin de
pouvoir sélectionner la bonne implémentation en fonction de la condition `SRC ==
TARGET`. Sa valeur étant déterminée par celle des autres paramètres, l'ajout de
`SRC` ne va pas entraîner la création de nouveaux types. Par contre, il
multiplie les cas à implémenter :

{% highlight cpp %}
// cas général
template <size DISKS, state S, tower SRC, tower TARGET>
struct SolverRec { /* … */ };

// quand SRC == TARGET (le disque est déjà bien placé)
template <size DISKS, state S, tower TOWER>
struct SolverRec<DISKS, S, TOWER, TOWER> { /* … */ };

// quand il ne reste plus qu'un seul disque, mal placé
template <state S, tower SRC, tower TARGET>
struct SolverRec<1, S, SRC, TARGET> { /* … */ };

// quand il ne reste plus qu'un seul disque, déjà bien placé
template <state S, tower TOWER>
struct SolverRec<1, S, TOWER, TOWER> { /* … */ };
{% endhighlight %}

Les plus observateurs auront remarqué que désormais, la récursivité s'arrête à 1
disque, et non plus 0 comme précédemment. En effet, maintenant que le paramètre
`SRC` est ajouté, il va falloir le calculer (grâce à `getTower(…)`) avant
d'utiliser le type. Or, cela n'a pas de sens de récupérer la position d'un
disque lorsque nous n'avons pas de disques. D'ailleurs, l'exécution de
`getTower(…)` avec `disk == 0` provoquerait une erreur… de compilation (vu que
l'exécution se déroule à la compilation).

Nous avons maintenant 4 versions de la classe template `SolverRec` à écrire.
Chacune devra contenir l'état final résultant du déplacement de `DISKS` disques
de la tour `SRC` vers la tour `TARGET` à partir de l'état `S`. Cet état sera
stocké dans une constante `final_state`, présente dans chacune des
spécialisations.

Voici mon implémentation :

{% highlight cpp %}
template <size DISKS, state S, tower SRC, tower TARGET>
struct SolverRec {
    static constexpr tower nextSrc = getTower(S, DISKS - 1);
    static constexpr tower inter = other(SRC, TARGET);
    using rec1 = SolverRec<DISKS - 1, S, nextSrc, inter>;
    static constexpr state value = move(rec1::final_state, SRC, TARGET);
    using rec2 = SolverRec<DISKS - 1, value, inter, TARGET>;
    static constexpr state final_state = rec2::final_state;
};

template <size DISKS, state S, tower TOWER>
struct SolverRec<DISKS, S, TOWER, TOWER> {
    static constexpr tower nextSrc = getTower(S, DISKS - 1);
    using rec = SolverRec<DISKS - 1, S, nextSrc, TOWER>;
    static constexpr state final_state = rec::final_state;
};

template <state S, tower SRC, tower TARGET>
struct SolverRec<1, S, SRC, TARGET> {
    static constexpr state final_state = move(S, SRC, TARGET);
};

template <state S, tower TOWER>
struct SolverRec<1, S, TOWER, TOWER> {
    static constexpr state final_state = S;
};
{% endhighlight %}

Le type (déterminé par les arguments des templates) correspondant aux appels
récursifs dépend des valeurs `constexpr` calculées dans la classe. C'est à
l'appelant de calculer la tour source pour renseigner la valeur du paramètre
`SRC`.

Par commodité, nous pouvons aussi ajouter une _classe template_ `Solver`, qui
calcule elle-même la tour `SRC` du plus grand disque lors du démarrage.

{% highlight cpp %}
template <size DISKS, state S, tower TARGET>
struct Solver {
    static constexpr tower src = getTower(S, DISKS);
    using start = SolverRec<DISKS, S, src, TARGET>;
    static constexpr state final_state = start::final_state;
};
{% endhighlight %}

([source][metahanoi.cpp-templates] – [commit][commit-templates] – tag `templates`)

[metahanoi.cpp-templates]: https://github.com/rom1v/metahanoi/blob/templates/metahanoi.cpp
[commit-templates]: https://github.com/rom1v/metahanoi/commit/templates

De cette manière, pour calculer l'état résultant du déplacement de 5 disques à
l'état `00000` (l'entier 0) vers la tour 2, il suffit de lire la variable :

{% highlight cpp %}
Solver<5, 0, 2>::final_state
{% endhighlight %}

Nous avons donc converti notre implementation d'une simple fonction `constexpr`
en _classes template_. Fonctionnellement équivalente pour l'instant, cette
nouvelle version met en place les fondations pour récupérer, à l'exécution, les
étapes de la résolution du problème calculées à la compilation.


## État initial

Mais avant cela, dotons-nous d'un outil pour décrire l'état initial facilement,
qui pour l'instant doit être exprimé grâce à un `state`, c'est-à-dire un entier.

L'idée serait que l'état et le nombre de disques soit calculé automatiquement à
la compilation à partir de la liste des positions des disques :

{% highlight cpp %}
Disks<1, 2, 0, 1, 2>
{% endhighlight %}

Contrairement à précedemment, ici, nous avons besoin d'un nombre indéterminé de
paramètres. Nous allons donc utiliser les [variadic templates][] :

[variadic templates]: https://en.wikipedia.org/wiki/Variadic_template

{% highlight cpp %}
template <tower ...T>
struct Disks;
{% endhighlight %}

Remarquez que ce template est juste _déclaré_ et non _défini_.

Pour parcourir les paramètres, nous avons besoin de deux spécialisations (une
pour la récursion et une pour la condition d'arrêt) :

{% highlight cpp %}
template <tower H, tower ...T>
struct Disks<H, T...> { /* … */ };

template <>
struct Disks<> { /* … */ };
{% endhighlight %}

Chacune des deux spécialisent le template déclaré, mais remarquez que l'une
n'est pas une spécialisation de l'autre. C'est la raison pour laquelle nous
avons besoin de déclarer un template (sans le définir) dont ces deux définitions
sont une spécialisation.

Voici l'implémentation :

{% highlight cpp %}
template <tower H, tower ...T>
struct Disks<H, T...> {
    static constexpr state count = 1 + sizeof...(T);
    static constexpr state pack(state tmp) {
        return Disks<T...>::pack(tmp * 3 + H);
    }
    static constexpr state packed = pack(0);
};

template <>
struct Disks<> {
    static constexpr state count = 0;
    static constexpr state pack(state tmp) { return tmp; };
    static constexpr state packed = 0;
};
{% endhighlight %}

Le nombre de disques est initialisé en comptant les paramètres grâce à
l'opérateur [`sizeof...`][sizeof...].

[sizeof...]: http://en.cppreference.com/w/cpp/language/sizeof...

L'état compacté est stocké dans la variable `packed`. Étant donné que les
premiers paramètres traités seront les digits _de poids fort_, la multiplication
devra être effectuée par les appels récursifs plus profonds. C'est la raison
pour laquelle j'utilise une fonction temporaire qui permet d'initialiser
`packed`.

Nous pouvons maintenant initialiser notre `solver` ainsi :

{% highlight cpp %}
using disks = Disks<1, 2, 0, 1, 2>;
using solver = Solver<disks::count, disks::packed, 2>;
{% endhighlight %}

([source][metahanoi.cpp-disks] – [commit][commit-disks] – tag `disks`)

[metahanoi.cpp-disks]: https://github.com/rom1v/metahanoi/blob/disks/metahanoi.cpp
[commit-disks]: https://github.com/rom1v/metahanoi/commit/disks


## Affichage récursif

Attaquons-nous maintenant à l'affichage des états successifs.

Le plus simple consiste à implémenter une méthode `print(…)` dans chacune des
classes `SolverRec`, affichant l'état associé et/ou appellant récursivement les
méthodes `print(…)` sur les instances de `SolverRec` utilisées pour la
résolution du problème.

Pour cela, nous devons auparavant déterminer, pour chaque template instancié,
quel état afficher. Par exemple, pour les classes crées à partir de
l'implémentation du template non spécialisé, il y a plusieurs états
accessibles :

  * l'état initial (`S`) ;
  * l'état après le premier appel récursif (`rec1::final_state`) ;
  * l'état après le déplacement (`value`) ;
  * l'état après le second appel récursif (`final_state`).

C'est en fait l'état `value` qu'il faut afficher :

  * c'est le seul endroit où le déplacement du disque entre les deux appels
    récursif est connu ;
  * les états correspondant aux appels récursifs seront gérés par leurs classes
    correspondantes.

Il est important de différencier, pour chaque `SolverRec`, l'état final,
représentant l'état après l'application de tous les déplacements demandés, de
l'état suivant le seul déplacement unitaire (s'il existe) associé à la classe.
C'est ce dernier que nous voulons afficher.

Nous allons donc ajouter dans les 4 versions du template `SolverRec` une
méthode :

{% highlight cpp %}
static std::ostream &print(std::ostream &os, size ndisks);
{% endhighlight %}

Le paramètre `std::ostream &os` permet juste de préciser sur quel flux écrire
(`std::cout` par exemple) ; il est retourné pour pouvoir le chaîner avec
d'autres écritures (comme `<< std::endl`).

Cette méthode a besoin de connaître le nombre total de disques, afin d'afficher
le bon nombre de digits. Notez que cette valeur est différente du paramètre de
template `DISKS`, qui correspond au nombre de disques à déplacer pour le niveau
de récursivité courant.

{% highlight cpp %}
template <size DISKS, state S, tower SRC, tower TARGET>
struct SolverRec {
    static constexpr tower nextSrc = getTower(S, DISKS - 1);
    static constexpr tower inter = other(SRC, TARGET);
    using rec1 = SolverRec<DISKS - 1, S, nextSrc, inter>;
    static constexpr state value = move(rec1::final_state, SRC, TARGET);
    using rec2 = SolverRec<DISKS - 1, value, inter, TARGET>;
    static constexpr state final_state = rec2::final_state;

    static std::ostream &print(std::ostream &os, size ndisks) {
        rec1::print(os, ndisks);
        print_state(os, ndisks, value) << std::endl;
        rec2::print(os, ndisks);
        return os;
    }
};

template <size DISKS, state S, tower TOWER>
struct SolverRec<DISKS, S, TOWER, TOWER> {
    static constexpr tower nextSrc = getTower(S, DISKS - 1);
    using rec = SolverRec<DISKS - 1, S, nextSrc, TOWER>;
    static constexpr state final_state = rec::final_state;

    static std::ostream &print(std::ostream &os, size ndisks) {
        rec::print(os, ndisks);
        return os;
    }
};

template <state S, tower SRC, tower TARGET>
struct SolverRec<1, S, SRC, TARGET> {
    static constexpr state value = move(S, SRC, TARGET);
    static constexpr state final_state = value;

    static std::ostream &print(std::ostream &os, size ndisks) {
        print_state(os, ndisks, value) << std::endl;
        return os;
    }
};

template <state S, tower TOWER>
struct SolverRec<1, S, TOWER, TOWER> {
    static constexpr state value = S;
    static constexpr state final_state = value;

    static std::ostream &print(std::ostream &os, size ndisks) {
        return os;
    }
};
{% endhighlight %}

Seules les versions du template pour lesquelles `SRC != TARGET` affichent un
état. Les autres n'ont rien à afficher directement.

Ajoutons également, par commodité, une méthode similaire dans le template
`Solver` (sans le paramètre `ndisks`, car ici il est toujours égal à `DISKS`) :

{% highlight cpp %}
template <size DISKS, state S, tower TARGET>
struct Solver {
    static constexpr tower src = getTower(S, DISKS);
    using start = SolverRec<DISKS, S, src, TARGET>;
    static constexpr state final_state = start::final_state;

    static std::ostream &print(std::ostream &os) {
        print_state(std::cout, DISKS, S) << std::endl; // initial state
        return start::print(os, DISKS);
    }
};
{% endhighlight %}

([source][metahanoi.cpp-print] – [commit][commit-print] – tag `print`)

[metahanoi.cpp-print]: https://github.com/rom1v/metahanoi/blob/print/metahanoi.cpp
[commit-print]: https://github.com/rom1v/metahanoi/commit/print

Cette nouvelle version affiche bien lors de l'exécution les états calculés lors
de la compilation.

Cependant, les appels récursifs nécessaires à la résolution du problème ne sont
pas supprimés : ils sont nécessaires à l'affichage des résultats. Il est dommage
de résoudre le problème à la compilation si c'est pour que l'exécution repasse
par chacune des étapes de la résolution pour l'affichage.


## Liste chaînée

Pour éviter cela, nous allons générer à la compilation une liste chaînée des
étapes qu'il ne restera plus qu'à parcourir à l'exécution. Chaque classe qui
_affichait_ un état va désormais _stocker_ un nœud de la liste chainée,
implémenté ainsi :

{% highlight cpp %}
struct ResultNode {
    state value;
    ResultNode *next;
};
{% endhighlight %}

Le défi est maintenant d'initialiser les champs `next` de chacun des nœuds à
l'adresse du nœud suivant dans l'ordre de résolution du problème des tours de
Hanoï, et non dans l'ordre des appels récursifs, qui est différent. Par exemple,
l'état (`value`) associé à une instance du template `SolverRec` non spécialisé
(correspondant au cas général) devra succéder à tous les états générés par
l'appel récursif `rec1`, pourtant appelé après.

Pour cela, chaque classe doit être capable d'indiquer à son appelant quel est le
premier nœud qu'elle peut atteindre (`first`) et passer à chaque classe appelée
le nœud qui devra suivre son nœud final (`AFTER`). Ces informations suffisent à
déterminer dans tous les cas le nœud suivant d'une classe donnée, ce qui permet
de constituer la liste chaînée complète en mémoire :

{% highlight cpp %}
template <size DISKS, state S, tower SRC, tower TARGET, ResultNode *AFTER>
struct SolverRec {
    static ResultNode node;
    static constexpr tower nextSrc = getTower(S, DISKS - 1);
    static constexpr tower inter = other(SRC, TARGET);
    using rec1 = SolverRec<DISKS - 1, S, nextSrc, inter, &node>;
    static constexpr state value = move(rec1::final_state, SRC, TARGET);
    using rec2 = SolverRec<DISKS - 1, value, inter, TARGET, AFTER>;
    static constexpr state final_state = rec2::final_state;
    static constexpr ResultNode *first = rec1::first;
    static constexpr ResultNode *next = rec2::first;
};

template <size DISKS, state S, tower SRC, tower TARGET, ResultNode *AFTER>
ResultNode SolverRec<DISKS, S, SRC, TARGET, AFTER>::node = { value, next };

template <size DISKS, state S, tower TOWER, ResultNode *AFTER>
struct SolverRec<DISKS, S, TOWER, TOWER, AFTER> {
    static constexpr tower nextSrc = getTower(S, DISKS - 1);
    using rec = SolverRec<DISKS - 1, S, nextSrc, TOWER, AFTER>;
    static constexpr state final_state = rec::final_state;
    static constexpr ResultNode *first = rec::first;
};

template <state S, tower SRC, tower TARGET, ResultNode *AFTER>
struct SolverRec<1, S, SRC, TARGET, AFTER> {
    static ResultNode node;
    static constexpr state value = move(S, SRC, TARGET);
    static constexpr state final_state = value;
    static constexpr ResultNode *first = &node;
    static constexpr ResultNode *next = AFTER;
};

template <state S, tower SRC, tower TARGET, ResultNode *AFTER>
ResultNode SolverRec<1, S, SRC, TARGET, AFTER>::node = { value, next };

template <state S, tower TOWER, ResultNode *AFTER>
struct SolverRec<1, S, TOWER, TOWER, AFTER> {
    static constexpr state value = S;
    static constexpr state final_state = value;
    static constexpr ResultNode *first = AFTER;
};

template <size DISKS, state S, tower TARGET>
struct Solver {
    static ResultNode list;
    static constexpr tower src = getTower(S, DISKS);
    using start = SolverRec<DISKS, S, src, TARGET, nullptr>;
};

template <size DISKS, state S, tower TARGET>
ResultNode Solver<DISKS, S, TARGET>::list = { S, start::first };
{% endhighlight %}

La variable `static` `node` n'étant pas `constexpr` (elle doit être adressable à
l'exécution pour former la liste chaînée), elle doit être initialisée en dehors
de la classe.

Pour parcourir simplement la liste chaînée, rendons `ResultNode` itérable
(j'implémente ici uniquement le strict minimum pour que l'_iterator_
fonctionne) :

{% highlight cpp %}
struct ResultNode {
    state value;
    ResultNode *next;

    class iterator {
        const ResultNode *current;
    public:
        iterator(const ResultNode *current) : current(current) {};
        iterator &operator++() { current = current->next; return *this; }
        state operator*() { return current->value; }
        bool operator==(const iterator &o) { return current == o.current; }
        bool operator!=(const iterator &o) { return !(*this == o); }
    };

    iterator begin() const { return iterator(this); }
    iterator end() const { return iterator(nullptr); }
};
{% endhighlight %}

La liste peut être parcourue ainsi :

{% highlight cpp %}
using disks = Disks<0, 0, 0, 0, 0>;
using solver = Solver<disks::count, disks::packed, 2>; 
for (state s : solver::list) {
    print_state(std::cout, disks::count, s) << std::endl;
}
{% endhighlight %}

([source][metahanoi.cpp-nodes] – [commit][commit-nodes] – tag `nodes`)

[metahanoi.cpp-nodes]: https://github.com/rom1v/metahanoi/blob/nodes/metahanoi.cpp
[commit-nodes]: https://github.com/rom1v/metahanoi/commit/nodes

En observant le binaire généré, la liste chaînée est directement visible (ici
les octets sont en [little endian][]) :

[little endian]: https://fr.wikipedia.org/wiki/Endianness#Little_endian

    $ objdump -sj .data metahanoi
    
    metahanoi:     file format elf64-x86-64
    
    Contents of section .data:
     6012a0 00000000 00000000 00000000 00000000
     6012b0 00000000 00000000 00136000 00000000    n00: { 00000, &n05 }
     6012c0 ca000000 00000000 f0136000 00000000    n01: { 21111, &n20 }
     6012d0 35000000 00000000 70136000 00000000    n02: { 01222, &n12 }
     6012e0 16000000 00000000 30136000 00000000    n03: { 00211, &n08 }
     6012f0 05000000 00000000 10136000 00000000    n04: { 00012, &n06 }
     601300 02000000 00000000 f0126000 00000000    n05: { 00002, &n04 }
     601310 04000000 00000000 e0126000 00000000    n06: { 00011, &n03 }
     601320 18000000 00000000 40136000 00000000    n07: { 00220, &n09 }
     601330 15000000 00000000 20136000 00000000    n08: { 00210, &n07 }
     601340 1a000000 00000000 d0126000 00000000    n09: { 00222, &n02 }
     601350 24000000 00000000 a0136000 00000000    n10: { 01100, &n15 }
     601360 2e000000 00000000 80136000 00000000    n11: { 01201, &n13 }
     601370 34000000 00000000 60136000 00000000    n12: { 01221, &n11 }
     601380 2d000000 00000000 50136000 00000000    n13: { 01200, &n10 }
     601390 29000000 00000000 b0136000 00000000    n14: { 01112, &n16 }
     6013a0 26000000 00000000 90136000 00000000    n15: { 01102, &n14 }
     6013b0 28000000 00000000 c0126000 00000000    n16: { 01111, &n01 }
     6013c0 d8000000 00000000 60146000 00000000    n17: { 22000, &n27 }
     6013d0 c5000000 00000000 20146000 00000000    n18: { 21022, &n23 }
     6013e0 cc000000 00000000 00146000 00000000    n19: { 21120, &n21 }
     6013f0 c9000000 00000000 e0136000 00000000    n20: { 21110, &n19 }
     601400 ce000000 00000000 d0136000 00000000    n21: { 21122, &n18 }
     601410 be000000 00000000 30146000 00000000    n22: { 21001, &n24 }
     601420 c4000000 00000000 10146000 00000000    n23: { 21021, &n22 }
     601430 bd000000 00000000 c0136000 00000000    n24: { 21000, &n17 }
     601440 ee000000 00000000 90146000 00000000    n25: { 22211, &n30 }
     601450 dd000000 00000000 70146000 00000000    n26: { 22012, &n28 }
     601460 da000000 00000000 50146000 00000000    n27: { 22002, &n26 }
     601470 dc000000 00000000 40146000 00000000    n28: { 22011, &n25 }
     601480 f0000000 00000000 a0146000 00000000    n29: { 22220, &n31 }
     601490 ed000000 00000000 80146000 00000000    n30: { 22210, &n29 }
     6014a0 f2000000 00000000 00000000 00000000    n31: { 22222, nullptr }

La colonne de gauche correspond aux adresses des données. Les 4 colonnes
suivantes contiennent des blocs de 4 octets, les deux premiers de chaque ligne
représentant le champ `value` et les deux suivants le champ `next` de
`ResultNode`, que j'ai réécrits de manière plus lisible à droite.


## Possible ?

Cette représentation interpelle : pourquoi ne pas stocker plus simplement les
différents états dans l'ordre, plutôt que d'utiliser des indirections pour
former une liste chaînée ?

Malheureusement, je n'ai pas trouvé de solution pour stocker les états ordonnés
dans un seul tableau d'entiers dès la compilation.

Si quelqu'un y parvient, ça m'intéresse !
