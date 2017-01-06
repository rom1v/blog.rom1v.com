---
layout: post
title: Résoudre le cube-serpent 300 fois plus rapidement en C
date: 2011-10-18 22:33:00+01:00
---

Il y a 3 semaines, j'avais écrit un [solveur de cube-serpent en
_Python_][snakesolver].

[snakesolver]: {% post_url 2011-09-27-resoudre-le-cube-serpent-en-python %}

Un [commentaire][], en apparence anodin, m'a mis dans la tête une question que
je ne pouvais pas laisser sans réponse : **combien de fois plus rapidement
s'exécuterait le même algorithme implémenté en [C][] que celui en [Python][]
(interprêté) ?** 2× ? 10× ? 50× ?

[commentaire]: {% post_url 2011-09-27-resoudre-le-cube-serpent-en-python%}#comment-13
[c]: http://fr.wikipedia.org/wiki/C_%28langage%29
[python]: http://fr.wikipedia.org/wiki/Python_%28langage%29

Pour y répondre, il fallait donc implémenter le même algorithme en _C_. En plus,
c'était l'occasion de rendre hommage à [Dennis Ritchie][]. Après avoir découvert
_Python_, j'ai donc (ré)appris le _C_ (et ça fait drôle de rejouer avec les
pointeurs après plusieurs années !).

[Dennis Ritchie]: http://www.pcinpact.com/actu/news/66368-dennis-ritchie-deces-langage-c-unix.htm


## Implémentation

Je ne vais pas m'attarder sur l'algorithme, c'est exactement le même principe
que sur mon billet précédent, et j'ai essayé de garder les mêmes noms de
fonctions.

La structure du cube et ses dimensions (à modifier selon votre cube-serpent)
sont définies par [macros][] (les fameux `#define`). Par rapport au programme
_Python_, il faut en plus préciser la taille des tableaux.

[macros]: https://gcc.gnu.org/onlinedocs/cpp/Macros.html

La seule partie que j'ai réimplémentée complètement différemment est la fonction
`get_useful_points` de la version _Python_ (souvenez-vous, avec des [yield][]s
dans une [fonction récursive][]). La fonction équivalente dans la version _C_
s'appelle `symmetry_helper_inc_cursor(int cursor[])` : au lieu de retourner au
fur et à mesure chacun des points à traiter, elle donne le point "suivant" de
celui passé en paramètre.

[yield]: http://docs.python.org/reference/expressions.html#yieldexpr
[fonction récursive]: http://fr.wikipedia.org/wiki/Fonction_r%C3%A9cursive

De même, les solutions trouvées sont données dans un [callback][] (la fonction
`solution`), toujours dans l'objectif de supprimer simplement les `yield`s.

[callback]: http://en.wikipedia.org/wiki/Callback_%28computer_science%29

J'ai tout mis dans un seul fichier `csnakesolver.c` (par simplicité, même si
plusieurs fichiers `.c` avec leurs `.h` auraient été préférables).


## Performances

Passons maintenant à ce qui nous intéresse : les performances.


### Exemples de référence


Je fais mes tests sur 3 exemples : un rapide, un [moyen][] et un [long][].

[moyen]: {% post_url 2011-09-27-resoudre-le-cube-serpent-en-python%}#comment-8
[long]: {% post_url 2011-09-27-resoudre-le-cube-serpent-en-python%}#comment-5

Le rapide est un 3×3×3, les deux autres sont des 4×4×4. Voici leurs structures
respectives :

{% highlight python %}
// (R)apide
{2, 1, 1, 2, 1, 2, 1, 1, 2, 2, 1, 1, 1, 2, 2, 2, 2}
// (M)oyen
{2, 1, 2, 1, 1, 3, 1, 2, 1, 2, 1, 2, 1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 1, 1, 1, 1,
1, 2, 3, 1, 1, 1, 3, 1, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 3, 1}
// (L)ong
{1, 1, 2, 1, 1, 3, 1, 2, 1, 2, 1, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1 ,1, 2, 2, 1, 1,
1, 1, 1, 2, 3, 1, 1, 1, 3, 1, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 3}
{% endhighlight %}


### Protocole

Je vais comparer, grâce à la commande `time`, le temps nécessaire pour trouver
une solution :

    time ./csnakesolver

Le programme doit s'arrêter après avoir trouvé la première. Les programmes
_Python_ et _C_ trouveront forcément les mêmes et dans le même ordre, vu qu'ils
implémentent le même algorithme.


### Compilation

Il est intéressant de tester les performances en compilant sans optimisations :

    gcc csnakesolver.c -o csnakesolver

et avec :

    gcc -O3 csnakesolver.c -o csnakesolver


### Résultats

(au format `h:mm:ss.ms`)

|   | Python         | C (`gcc`)             | C (`gcc -O3`)          |
|:-:|:--------------:|:---------------------:|:----------------------:|
| R | ~`0:00:00.000` | ~`0:00:00.000`        | ~`0:00:00.000`         |
| M | `0:05:06.903`  | `0:00:03.715` _(×83)_ | `0:00:00.826` _(×372)_ |
| L | `3:53:17.012`  | `0:05:04.533` _(×46)_ | `0:00:50.681` _(×276)_ |


Le gain est loin d'être négligeable : ×276 dans un cas et ×372 dans l'autre !
Honnêtement, je ne m'y attendais pas. Tout au plus, j'espérais peut-être
×10 ou ×50, sans trop y croire.



### Origines des gains de performances

Deux différences expliquent ces gains :

  * _Python_ est [interprêté][], alors que _C_ est [compilé][].
  * En tant que langage de haut-niveau, _Python_ subit le [coût de
    l'abstraction][].

[interprêté]: http://fr.wikipedia.org/wiki/Langage_interpr%C3%A9t%C3%A9_%28informatique%29
[compilé]: http://fr.wikipedia.org/wiki/C_%28langage%29#Compilation
[coût de l'abstraction]: http://fr.wikipedia.org/wiki/Langage_de_haut_niveau#Co.C3.BBt_de_cette_abstraction

Il serait intéressant de savoir dans quelle mesure les gains proviennent de la
compilation et dans quelle mesure ils proviennent de l'abstraction _(nous savons
déjà que le facteur de gain entre les programmes compilés avec et sans `-O3`
provient uniquement de la compilation)_.

Une approche intéressante pour répondre à cette question serait de compiler le
programme _Python_ en code natif (je n'ai encore jamais fait).


## Débogueur

Travailler sur un mini-projet personnel permet toujours d'apprendre des choses.
En dehors du langage lui-même, j'ai découvert le
[débogueur][] [`gdb`][gdb].

[débogueur]: http://fr.wikipedia.org/wiki/D%C3%A9bogueur
[gdb]: http://www.gnu.org/s/gdb/

N'ayant toujours utilisé des débogueurs qu'en mode graphique (pour d'autres
langages), je m'attendais à ce qu'il soit un peu long à prendre en main. Mais en
fait, pas du tout, j'ai été agréablement surpris par sa simplicité
d'utilisation.

Avec certains langages, on peut se passer de débogueur pour de petits programmes. _C_ ne fait pas partie de ceux-là, par exemple dans ce cas précis :

    $ ./csnakesolver
    Erreur de segmentation

Il faut d'abord compiler le programme avec l'option de _debug_ :

    gcc -g csnakesolver.c -o csnakesolver

Puis lancer le programme avec le débogueur :

    gdb csnakesolver

Un prompt permet d'entrer des commandes :

    (gdb) 

Pour placer un point d'arrêt à la ligne 215 :

    (gdb) break 215

Pour démarrer le programme :

    (gdb) run

Le programme s'arrête sur le point d'arrêt :

    Breakpoint 1, volume_helper_can_move (vector=...) at csnakesolver.c:215
    215	    int cursor_position_value = volume_helper.cursor[vector.position];

Pour afficher le bout de code source concerné :

    (gdb) l
    210	void volume_helper_set_flag(int cursor[], bool value) {
    211	    * volume_helper_get_flag_pointer(cursor) = value;
    212	}
    213	
    214	bool volume_helper_can_move(vector_s vector) {
    215	    int cursor_position_value = volume_helper.cursor[vector.position];
    216	    int new_value = cursor_position_value + vector.value;
    217	    int future_cursor[DIMENSIONS_COUNT];
    218	    int sign, i, abs_value;
    219	    if (new_value < 0 || new_value >= dimensions[vector.position]) {


Il est possible de consulter les valeurs des variables (éventuellement en [les
formattant][gdb-formats]) grâce à `p` (_print_) :

    (gdb) p vector.position
    $1 = 0
    (gdb) p vector
    $2 = {position = 0, value = 1}

[gdb-formats]: http://www.delorie.com/gnu/docs/gdb/gdb_55.html

Pour afficher des [tableaux][], il faut indiquer le pointeur et la longueur du
tableau à afficher (ici 3) :

    (gdb) p * volume_helper.cursor @ 3
    $3 = {0, 0, 0}

[tableaux]: http://www.chemie.fu-berlin.de/chemnet/use/info/gdb/gdb_9.html#SEC54

Pour obtenir la [pile d'exécution][] :

    (gdb) bt
    #0  volume_helper_can_move (vector=...) at csnakesolver.c:215
    #1  0x0000000000401294 in solve_rec (init_cursor=0x7fffffffe210, step=0)
        at csnakesolver.c:438
    #2  0x0000000000401191 in solve () at csnakesolver.c:407
    #3  0x00000000004013de in main () at csnakesolver.c:475

[pile d'exécution]: http://fr.wikipedia.org/wiki/Pile_d%27ex%C3%A9cution

Pour avancer dans le programme, 3 commandes sont indispensables :

  * `c` (_continue_) pour dérouler le programme jusqu'au prochain point
    d'arrêt ;
  * `n` (_next_) pour exécuter la ligne suivante complètement ;
  * `s` (_step_) pour rentrer dans la fonction sur la ligne suivante et
    l'exécuter ligne à ligne.

Ces commandes essentielles permettent déjà de se sortir de beaucoup de
situations.


## Conclusion

Un programme écrit en _C_ est plus rapide qu'un programme écrit en _Python_ (ah
bon ?), dans une proportion plus importante que je ne l'imaginais. Ce n'est
qu'un test sur un exemple particulier, mais il donne déjà une petite idée.

La morale de l'histoire est qu'il faut bien choisir son langage suivant le
programme à réaliser. Et pour du calcul brut, évidemment un langage bas niveau
est préférable (même si le développement est plus laborieux). Dans certains cas
cependant, où les performances brutes ne sont pas cruciales, _Python_ sera
préféré à _C_.


## Code source

Le code source est disponible sur ce dépôt _git_ :

    git clone http://git.rom1v.com/csnakesolver.git

(ou sur [github](https://github.com/rom1v/csnakesolver)).

_Par contre, désolé, cette version est beaucoup moins commentée que la version
Python._
