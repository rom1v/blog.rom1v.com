---
layout: post
title: Résoudre le cube-serpent en Python
date: 2011-09-27 21:10:34+02:00
---

{: .center}
![cube-serpent]({{ site.assets }}/snake_solver/cube-serpent.jpg)

Je me suis amusé à écrire un petit programme en [Python][] qui résout le
[cube-serpent][] (ainsi nous pouvons dire qu'un serpent en résout un autre).

[python]: http://fr.wikipedia.org/wiki/Python_%28langage%29
[cube-serpent]: http://fr.wikipedia.org/wiki/Cube_serpent

Mon but était surtout d'apprendre le langage _Python_, avec un problème
intéressant, pas trop compliqué (c'est de la [force brute][]). Il m'a permis de
découvrir différents aspects de _Python_.

[force brute]: http://fr.wikipedia.org/wiki/Recherche_par_force_brute

**EDIT :** Je l'ai également [implémenté en C][snake-solver-c].

[snake-solver-c]: {% post_url 2011-10-18-resoudre-le-cube-serpent-300-fois-plus-rapidement-en-c %}

L'algorithme proposé résout un cube-serpent généralisé. En effet, il sait
trouver des solutions pour obtenir un cube de 3×3×3, mais également d'autres
tailles, comme 2×2×2 ou 4×4×4. Il sait également résoudre des volumes non
cubiques, comme 2×3×4. Et pour être totalement générique, il fonctionne pour un
nombre quelconque de dimensions (2×2, 3×5×4×2, 2×3×2×2×4). Comme ça, vous saurez
replier un serpent en [hypercube][]…

[hypercube]: http://fr.wikipedia.org/wiki/Hypercube

Je vais d'abord présenter le problème et décrire l'algorithme de résolution et
survoler l'implémentation, puis je vais m'attarder sur certaines fonctionnalités
de _Python_ qui m'ont semblé très intéressantes.


## Résolution


### Problème

Le but est de replier la structure du serpent pour qu'elle forme un volume, par exemple un cube.

La structure peut être vue comme une liste de vecteurs orthogonaux consécutifs,
ayant chacun une norme (une longueur). Elle peut donc être caractérisée par la
liste des normes de ces vecteurs. Ainsi, la structure du serpent présenté sur
[Wikipedia][cube-serpent] est la suivante :

{: .center}
![snake]({{ site.assets }}/snake_solver/640px-Snakecube_1.jpg)

{% highlight python %}
[2, 1, 1, 2, 1, 2, 1, 1, 2, 2, 1, 1, 1, 2, 2, 2, 2]
{% endhighlight %}

Le volume cible peut être représenté par un graphe, un ensemble de sommets
reliés par des arêtes, auquel on ajoute la notion d'orientation dans l'espace
(il est important de distinguer les arêtes orthogonales entre elles). En clair,
chaque sommet représente une position dans le cube : il y a donc 27 sommets pour
un cube 3×3×3.

L'objectif est de trouver dans le graphe ainsi formé par le cube un [chemin
hamiltonien][] (un chemin qui passe par tous les sommets une fois et une seule)
qui respecte la contrainte de la structure du serpent.

[chemin hamiltonien]: http://fr.wikipedia.org/wiki/Graphe_hamiltonien#Chemin_hamiltonien


### Principe

Pour trouver les solutions, il suffit de partir d'un sommet et tenter de placer
les vecteurs de la structure consécutivement un à un, en respectant trois
contraintes :

  * rester dans le volume (évidemment) ;
  * le n<sup>ième</sup> vecteur doit être orthogonal au (n-1)<sup>ième</sup>
    vecteur (cette règle ne s'applique pas pour le tout premier vecteur) ;
  * le vecteur "placé" dans le cube ne doit pas passer par une position déjà
    occupée (physiquement, il n'est pas possible de faire passer une partie du
    serpent à travers une autre).

Il faut donc placer récursivement tous les vecteurs possibles, c'est-à-dire tous
les vecteurs orthogonaux au précédent, qui ne sortent pas du cube et qui ne
passent pas par une position déjà occupée. Jusqu'à arriver soit à une
impossibilité (plus aucun vecteur ne respecte ces 3 contraintes), soit à une
solution (tous les vecteurs sont placés).

Pour ne manquer aucune solution, il faut répéter cet algorithme en démarrant
avec chacun des points de départ (donc les 27 sommets pour un cube 3×3×3).


### Limites

Cet algorithme ne détecte pas les symétries ni les rotations, il donne alors
plusieurs solutions "identiques". Une amélioration serait de les détecter "au
plus tôt" et de ne pas les construire.

**EDIT :** La version 0.2 gère les symétries et les rotations, pour éviter de
calculer plusieurs solutions identiques. Plus d'explications dans [ce
commentaire](#comment-11) et [le suivant](#comment-12).


## Implémentation

Voici une explication succincte des différentes parties du programme (pour plus
d'informations, lire les commentaires dans le code).


### Vector

Nous avons besoins de vecteurs, mais pas n'importe lesquels : seulement ceux qui
ont une et une seule composante non-nulle, c'est-à-dire des multiples des
vecteurs de la base. En effet, par exemple en 3 dimensions, la direction de
chacun des vecteurs sera soit droite-gauche, soit dans haut-bas, soit
avant-arrière, mais jamais en diagonale avant-droite vers arrière-gauche.

Ainsi, au lieu de stocker toutes les composantes, le `Vector` ne contient que la
valeur de la composante non-nulle ainsi que sa position (plus facile à
manipuler):

{% highlight python %}
Vector(position, value)
{% endhighlight %}


### VolumeHelper

Cette classe définit l'outil que va utiliser le solveur pour noter tout ce qu'il
fait : le chemin emprunté et les sommets déjà visités. À chaque fois qu'il place
un vecteur dans le volume, il "allume les petites lumières" associées aux
sommets visités, et quand il revient en arrière (pour chercher d'autres
solutions), il éteint ces petites lumières (par _lumières_, comprenez
_booléens_).


### SymmetryHelper

Cette classe a été ajoutée dans la version 0.2. Elle définit l'outil que va
utiliser le solveur pour n'explorer que les solutions nécessaires, en ignorant
les symétries et les rotations.


### Solver

Le solveur place récursivement les vecteurs de la structure dans toutes les
positions possibles (en s'aidant du `VolumeHelper`) afin de trouver toutes les
solutions.


### Solutions


Lors de l'exécution du script, les solutions s'affichent au fur et à mesure :

    $ ./snakesolver.py
    ([0, 0, 0], [2x, y, -x, 2z, y, -2z, x, z, -2y, -2x, y, -z, y, 2z, -2y, 2x, 2y])
    ([0, 0, 0], [2x, z, -x, 2y, z, -2y, x, y, -2z, -2x, z, -y, z, 2y, -2z, 2x, 2z])
    ([0, 0, 0], [2y, x, -y, 2z, x, -2z, y, z, -2x, -2y, x, -z, x, 2z, -2x, 2y, 2x])
    ...

Considérons la première solution :

    ([0, 0, 0], [2x, y, -x, 2z, y, -2z, x, z, -2y, -2x, y, -z, y, 2z, -2y, 2x, 2y])

Le point de départ est `[0, 0, 0]`. On se déplace d'abord de `2` sur l'axe `x`,
puis de `1` sur l'axe `y`, de `-1` sur l'axe `x`, etc.

Voici la représentation graphique de cette solution :

{: .center}
![solution-cube-serpent]({{ site.assets }}/snake_solver/solution-cube-serpent.png)


## Fonctionnalités de Python

Maintenant, voici quelques éléments essentiels du langage _Python_ dont je me
suis servi pour ce programme.


### Compréhension de liste

La [compréhension de liste][] (ou [liste en compréhension][]) est très pratique.
Je l'ai utilisée plusieurs fois dans l'algorithme. Je vais détailler deux
exemples.

[compréhension de liste]: http://docs.python.org/tutorial/datastructures.html#list-comprehensions
[liste en compréhension]: https://fr.wikipedia.org/wiki/Liste_en_compr%C3%A9hension

D'abord, dans la classe `VolumeHelper` :

{% highlight python %}
def all_points(self, index=0):
    if index == len(self.dimensions):
        return [[]]
    return ([h] + t for h in xrange(self.dimensions[index])
            for t in self.all_points(index + 1))
{% endhighlight %}

Ce qui est retourné à la fin signifie :

> tous les éléments de la forme `[h] + t` (l'élément `h` en tête de liste suivie
> de la queue de la liste) pour `h` compris entre `0` et
> `self.dimensions[index]` (un entier) et pour tout `t` compris dans les
> résultats de l'appel récursif

Ça ne vous éclaire pas ? Dit plus simplement :

> le résultat de la concaténation de chacun des nombres de `0` à `n` (avec `n =
> self.dimensions[index]`) à chacune des listes fournies par l'appel récursif

En fait, cette fonction fournit tous les points possibles pour les dimensions
données. Par exemple, si `dimensions = [2, 2]`, alors le résultat sera :

{% highlight python %}
[[0, 0], [0, 1], [1, 0], [1, 1]]
{% endhighlight %}

Pour `dimensions = [2, 2, 3]`, le résultat sera :

{% highlight python %}
[[0, 0, 0], [0, 0, 1], [0, 0, 2], [0, 1, 0], [0, 1, 1], [0, 1, 2], [1, 0, 0],
[1, 0, 1], [1, 0, 2], [1, 1, 0], [1, 1, 1], [1, 1, 2]]
{% endhighlight %}

Sans compréhension de liste, il serait difficile d'écrire le corps de cette
fonction en 3 lignes !

_Remarque : la compréhension de liste retourne une liste si elle est définie
entre `[]`, alors qu'elle retourne un [générateur][] (un _itérateur_) si elle
est définie entre `()`._

[générateur]: http://fr.wikipedia.org/wiki/G%C3%A9n%C3%A9rateur_%28informatique%29

Second exemple, dans `Solver.__solve_rec(…)` :

{% highlight python %}
for possible_vector in ( Vector(i, v)
                         for v in [ norm, -norm ]
                         for i in xrange(len(self.dimensions))
                         if i != previous_position ):
{% endhighlight %}

Cette partie fournit un ensemble de `Vector(i, v)`, pour toutes les combinaisons
de `i` et de `v` dans leurs domaines respectifs, qui vérifient la condition (qui
ici ne porte que sur `i`).

En clair, ici nous récupérons tous les vecteurs possibles, c'est-à-dire
orthogonaux au précédent et avec la norme (la longueur) imposée par la
structure.


### Itérateur

La notion d'[itérateur][itérateur-wikipedia] est présente dans beaucoup d'autres
langages. Un [itérateur][] retourne un nouvel élément à chaque appel à la
méthode `next()`. En pratique, il est souvent utilisé de manière transparente
dans une boucle `for _variable_ in _iterator_` :

{% highlight python %}
for i in xrange(10):
    print i
{% endhighlight %}

[itérateur-wikipedia]: http://fr.wikipedia.org/wiki/It%C3%A9rateur
[itérateur]: http://docs.python.org/library/stdtypes.html#iterator-types

[`xrange(…)`][xrange] retourne un _itérateur_ et fournit les valeurs au fur et à
mesure, alors que [`range(…)`][range] crée la liste de toutes les valeurs, qui
est ensuite parcourue.

[xrange]: http://docs.python.org/2/library/functions.html#xrange
[range]: http://docs.python.org/2/library/functions.html#range


### Yield


Les [expressions yield][yield] permettent de créer un _itérateur_ très
simplement.

[yield]: http://docs.python.org/reference/expressions.html#yieldexpr

Pour résoudre le cube-serpent, il est préférable d'une part de **fournir les
solutions au fur et à mesure qu'elles sont trouvées**, et d'autre part **de
pouvoir ne calculer que les _k_ premières solutions**.

La première contrainte est souvent résolue grâce à des [callbacks][] : la
fonction de calcul prend en paramètre une fonction, qui sera appelée à chaque
résultat trouvé, le passant alors en paramètre.

[callbacks]: http://en.wikipedia.org/wiki/Callback_%28computer_science%29

La seconde est plus délicate : elle implique que l'algorithme s'arrête dès qu'il
trouve une solution, et que lors d'un prochain appel il reprenne le calcul là où
il s'était arrêté, afin calculer les solutions suivantes. Cela nécessite de
conserver un état. Pour un _itérateur_ simple comme celui d'une liste, il suffit
de stocker l'index courant de parcours, et de l'incrémenter à chaque appel à
`next()`. Gérer manuellement l'itération sur les solutions du cube-serpent
semble beaucoup plus complexe, d'autant plus que les solutions sont trouvées
dans des appels récursifs.

C'est là qu'interviennent les expressions _yield_, qui répondent aux deux
besoins en même temps. Utiliser une expression _yield_ dans le corps d'une
fonction suffit à transformer cette fonction en un _générateur_. Il n'est donc
plus possible de retourner de valeur grâce à `return`.

Dès que l'expression _yield_ est rencontrée, la valeur est transmise et
l'exécution de la fonction s'arrête. Elle reprendra lors du prochain appel.

Afin d'utiliser ce principe pour la génération des solutions, les fonctions
`SnakeCubeSolver.solve()` et `SnakeCubeSolver.__solve_rec(…)` ne sont donc pas
des fonctions ordinaires, mais des _générateurs_ :

{% highlight python %}
if step == len(self.structure):
    yield init_cursor, self.volume_helper.path[:]
{% endhighlight %}

Grâce à cette implémentation, il est possible de parcourir toutes les
solutions :

{% highlight python %}
for solution in solver.solve():
    print solution
{% endhighlight %}

ou alors de ne générer que les `k` premières :

{% highlight python %}
max_solutions = 5
solutions = solver.solve()
for i in xrange(max_solutions):
    try:
        print solutions.next()
    except StopIteration:
        break
{% endhighlight %}


### Lambdas

_Python_ supporte aussi les expressions [lambda][], issues du [lambda-calcul][],
qui permettent d'écrire des fonctions anonymes simplement.

[lambda]: http://docs.python.org/reference/expressions.html#lambda
[lambda-calcul]: http://fr.wikipedia.org/wiki/Lambda-calcul

J'utilise cette fonctionnalité une fois dans le programme :

{% highlight python %}
needed_length = reduce(lambda x, y: x * y, self.dimensions) - 1
{% endhighlight %}

Il s'agit de la déclaration d'une fonction avec deux arguments, qui retourne
leur produit.

La fonction [`reduce(function, iterable, …)`][reduce] permet d'appliquer
cumulativement la fonction aux éléments de l'_iterable_, de gauche à droite, de
manière à réduire l'_iterable_ en une seule valeur.

[reduce]: http://docs.python.org/2/library/functions.html#reduce

_Même si "[ce qui se conçoit bien s'énonce clairement][boileau]", la fonction
`reduce` est bien plus facile à comprendre qu'à expliquer en quelques mots…_

[boileau]: http://fr.wikipedia.org/wiki/Nicolas_Boileau

Ici, donc, `needed_length` contient le produit de tous les éléments de la liste
`self.dimensions`.


## Conclusion

La résolution du cube-serpent est intéressante, tout comme sa généralisation à
n'importe quel volume de dimensions quelconque. Je me suis arrêté là
_(**EDIT :** finalement, non)_, mais la détection des symétries et des rotations
"au plus tôt" serait une amélioration non négligeable (et pas si évidente).

Débutant tout juste en _Python_, ce micro-projet m'a permis de beaucoup
apprendre, et de découvrir quelques bonnes surprises comme les expressions
_yield_ que je ne connaissais pas.

J'espère que ça vous a amusé aussi.


## Script

Le code source est disponible sur ce dépôt _git_ :

    git clone http://git.rom1v.com/snakesolver.git

(ou sur [github](https://github.com/rom1v/snakesolver)).
