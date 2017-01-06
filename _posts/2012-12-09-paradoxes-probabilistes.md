---
layout: post
title: Paradoxes probabilistes
date: 2012-12-09 20:11:20+01:00
---

Ce sont des cas d'école, mais j'adore ces quelques paradoxes. La simplicité de
leurs énoncés et l'évidence de leur solution nous permettent de répondre en
quelques secondes, sans aucune hésitation. Mais en nous trompant.


## Deux enfants

> Un couple a deux enfants dont l'un d'eux (au moins) est une fille. Quelle est
> la probabilité que l'autre soit (aussi) une fille ?

_Par [hypothèse][], la probabilité à chaque naissance d'avoir un garçon est
égale à celle d'avoir une fille (50%), et les naissances sont
[indépendantes][]._

[hypothèse]: http://fr.wikipedia.org/wiki/Hypoth%C3%A8se
[indépendantes]: http://fr.wikipedia.org/wiki/Ind%C3%A9pendance_%28probabilit%C3%A9s%29

La réponse 1/2 est évidente. Mais **fausse**. La _bonne_ réponse est **1/3**.

En effet, un couple ayant deux enfants a 4 possiblités [équiprobables][] :

[équiprobables]: http://fr.wikipedia.org/wiki/%C3%89quiprobabilit%C3%A9

  1. garçon-garçon
  2. garçon-fille
  3. fille-garçon
  4. fille-fille

Sachant que l'un des deux est une fille, le cas _1_ est exclu : il reste trois
possibilités équiprobables, dont une seule correspond au cas _fille-fille_. Il y
a donc une chance sur trois que les deux enfants soient des filles. [CQFD][].

[cqfd]: http://fr.wikipedia.org/wiki/CQFD_%28math%C3%A9matiques%29


Pour vous en convaincre, considérez les deux phrases équivalentes suivantes :

  * Un couple a deux enfants, dont l'un d'eux (au moins) est une fille.
  * Un couple a deux enfants qui ne sont pas deux garçons.

Pour chacune d'elles, demandez-vous quelle est la probabilité que les deux
enfants soient des filles.

_Attendez d'être convaincus de ce résultat avant de passer à la suite._


### Fausse implication

Une fois ce résultat compris, considérons l'énoncé suivant :

> Un couple a deux enfants. Je le croise dans la rue avec l'un de ses enfants,
> qui est une fille. Quelle est la probabilité que l'autre enfant (celui qui est
> absent) soit (aussi) une fille.

_Il est possible de répondre avec certitude : s'**il** est absent, c'est un
garçon, si **elle** est absente, c'est une fille. (Ça, c'est fait !)_


Alors vous appliquez le même raisonnement, et répondez **1/3**. Après tout, nous
sommes exactement dans le cas de l'énoncé précédent : un couple a deux enfants
et je sais que l'un d'eux (au moins) est une fille.

Mais non, c'est faux. Ici, la réponse est **1/2**.

Pour le comprendre, il faut voir que le raisonnement menant à la réponse **1/3**
n'est en fait tout-à-fait valide qu'en levant une légère ambiguïté de l'énoncé,
celle de l'_acquisition de l'information_ : comment savons-nous que le couple
ayant deux enfants a au moins une fille (pour déterminer la probabilité qu'il en
ait deux) ?

Si nous avons demandé à l'un des parents "avez-vous (au moins) une fille ?" et
qu'il a répondu "oui", alors la probabilité que les deux soient des filles est
bien **1/3**.

Par contre, si nous lui avons demandé "indiquez-moi le sexe de l'un de vos
enfants" et qu'il a répondu "j'ai (au moins) une fille", alors la probabilité
que les deux soient des filles est **1/2**. En effet, le fait que le parent
puisse répondre _garçon_ à cette question lorsqu'il a deux enfants de sexes
différents fait baisser la probabilité conditionnelle des cas _2_ et _3_, et une
fois le cas _1_ exclu, l'union des _2_ et _3_ et le cas _4_ sont équiprobables.
_Relisez la phrase précédente plusieurs fois. Comme elle n'est pas claire,
consultez le calcul sur [Wikipedia][acquisition]._

[acquisition]: http://fr.wikipedia.org/wiki/Paradoxe_des_deux_enfants#Acquisition_de_l.27information

Le fait de rencontrer un enfant de ce couple (ici, une fille) s'apparente à ce
dernier cas (car pour deux enfants de sexes différents, nous aurions pu
rencontrer le garçon). Ainsi, la probabilité que _l'autre_ soit une fille est
**1/2**.

_Si ce n'est pas clair, continuez, j'en reparle un peu plus loin lorsque
j'évoque la particularisation._


### Deux enfants, un jour

> Un couple a deux enfants dont l'un d'eux (au moins) est une fille **née un mardi**. Quelle est la probabilité que l'autre soit (aussi) une fille ?

La réponse n'est ni 1/2, ni 1/3, mais **13/27**.

Un [bash][] vaut mieux qu'un long discours :

[bash]: http://fr.wikipedia.org/wiki/Bourne-Again_shell

{% highlight bash %}
printf '%s\n' {G,F}{0..6}-{G,F}{0..6} | grep F1 | wc -l
printf '%s\n' {G,F}{0..6}-{G,F}{0..6} | grep F1 | grep F.-F. | wc -l
{% endhighlight %}

(Nous supposons avoir obtenu l'information en demandant à l'un des parents
"avez-vous une fille née un mardi ?". Comme dans le premier exemple, si nous lui
avions demandé "indiquez-moi le sexe d'un de vos enfants ainsi que son jour de
naissance", la probabilité que _l'autre_ soit une fille serait **1/2**.)

Il est également possible de différencier par autre chose qu'un jour de la
semaine, par exemple faire la différence entre _matin_ (entre minuit et midi) et
_après-midi_ (entre midi et minuit) :

> Un couple a deux enfants dont l'un d'eux (au moins) est une fille **née un matin**. Quelle est la probabilité que l'autre soit (aussi) une fille ?

La réponse est **3/7** :

{% highlight bash %}
printf '%s\n' {G,F}{M,A}-{G,F}{M,A} | grep FM | wc -l
printf '%s\n' {G,F}{M,A}-{G,F}{M,A} | grep FM | grep F.-F. | wc -l
{% endhighlight %}


### Deux enfants, un prénom

Supposons maintenant qu'aucun couple n'appelle deux de ses enfants par le même
prénom, et considérons l'énoncé suivant :

> Un couple a deux enfants dont l'un d'eux (au moins) est une fille **prénommée
> Sophie**. Quelle est la probabilité que l'autre soit (aussi) une fille ?

La réponse ici est **1/2**.

Si vous avez compris le résultat du script _bash_ précédent, cela revient à
supposer que les enfants ne peuvent pas être nés le même jour de la semaine (ce
qui est absurde pour un jour de la semaine, mais pas pour un prénom) :

{% highlight bash %}
printf '%s\n' {G,F}{0..6}-{G,F}{0..6} | grep -v '^.\(.\)-.\1' | grep F1 | wc -l
printf '%s\n' {G,F}{0..6}-{G,F}{0..6} | grep -v '^.\(.\)-.\1' | grep F1 |
    grep F.-F. | wc -l
{% endhighlight %}


### Synthèse et particularisation

Résumons. Sachant que l'un des deux enfants est une fille, la probabilité que
les deux soient des filles dépend de la capacité à _particulariser_ l'enfant
dont on connaît le sexe. Sans aucune information supplémentaire, la probabilité
est **1/3**.

Mais si nous savons par exemple que l'enfant en question est l'aîné, nous le
particularisons complètement : nous sommes sûrs que l'autre n'est pas l'aîné, et
donc la probabilité devient **1/2** (évidemment, puisque par hypothèse, les
naissances sont _indépendantes_). De même, si nous supposons qu'un couple ne
donne pas le même prénom à plusieurs de ses enfants, alors préciser le prénom
particularise complètement l'enfant dont on parle. Il en va de même si nous
rencontrons l'un des enfants dans la rue : c'est de celui qui est présent dont
on parle, pas n'importe lequel.

Et il existe des cas intermédiaires, où nous ne particularisons que
_partiellement_. Par exemple, en précisant que l'enfant est né un mardi, dans
certains cas l'information est différenciante (l'autre enfant n'est pas né un
mardi), dans certains cas non (les deux enfants sont nés un mardi). Le résultat
n'est donc ni 1/3, ni 1/2, mais entre les deux (**13/27** ici).

Si vous avez du mal à vous convaincre que rencontrer l'un des enfants dans la
rue le _particularise_ (et donc donne une probabilité de **1/2** que _l'autre_
soit une fille ou un garçon), je vous propose l'expérience de pensée suivante
([par l'absurde][]).

Vous rencontrez le couple avec l'un de ses enfants, qui est une fille. D'après
le tout premier raisonnement, vous en concluez que la probabilité que _l'autre_
soit une fille est **1/3**. Vous lui parlez, et vous lui demandez quel jour de
la semaine elle est née, elle vous répond _mardi_. Vous savez maintenant que
c'est une fille née un _mardi_. D'après ce que nous venons de voir, vous en
concluez que la probabilité que _l'autre_ soit une fille est **13/27**. Mais le
résultat aurait été le même si elle avait répondu n'importe quel autre jour de
la semaine. Le fait d'avoir posé la question a donc _changé_ la probabilité, et
ceci, indépendemment de sa réponse. Comment pourrait-elle dépendre du simple
fait de poser la question ? C'est _incohérent_.

[par l'absurde]: http://fr.wikipedia.org/wiki/Raisonnement_par_l%27absurde

Cette _particularisation_ me fait d'ailleurs beaucoup penser au phénomène de
[décohérence quantique][].

[décohérence quantique]: http://fr.wikipedia.org/wiki/D%C3%A9coh%C3%A9rence_quantique


## Monty Hall

Il s'agit jeu télévisé avec trois portes, dont voici les [règles][] :

[règles]: http://fr.wikipedia.org/wiki/Probl%C3%A8me_de_Monty_Hall#Un_.C3.A9nonc.C3.A9_actuel_exempt_d.27ambigu.C3.AFt.C3.A9

> * Derrière chacune des trois portes se trouve soit une chèvre, soit une
>   voiture, mais une seule porte donne sur une voiture alors que deux portes
>   donnent sur une chèvre. La porte cachant la voiture a été choisie par tirage
>   au sort.
> * Le joueur choisit une des portes, sans que toutefois ce qui se cache
>   derrière (chèvre ou voiture) ne soit révélé à ce stade.
> * Le présentateur sait ce qu'il y a derrière chaque porte.
> * Le présentateur doit ouvrir l'une des deux portes restantes et doit
>   proposer au candidat la possibilité de changer de choix quant à la porte à
>   ouvrir définitivement.
> * Le présentateur ouvrira toujours une porte derrière laquelle se cache une
>   chèvre, en effet :
>     * Si le joueur choisit une porte derrière laquelle se trouve une chèvre,
>       le présentateur ouvrira l'autre porte où il sait que se trouve également
>       une chèvre.
>     * Si le joueur choisit la porte cachant la voiture, le présentateur
>       choisit au hasard parmi les deux portes cachant une chèvre. (on peut
>       supposer qu'un tirage au sort avant l'émission a décidé si ce serait la
>       plus à droite ou à gauche)
> * Le présentateur doit offrir la possibilité au candidat de rester sur son
>   choix initial ou bien de revenir dessus et d'ouvrir la porte qui n'a été
>   choisie ni par lui-même, ni par le candidat.

La question qui se pose alors est :

> **Le joueur augmente-t-il ses chances de gagner la voiture en changeant son
> choix initial ?**

Vu qu'il reste deux portes, nous pourrions nous dire que garder son choix
initial ou le changer n'a pas d'incidence sur les probabilités. Ce qui
évidemment est **faux** (sinon nous n'en parlerions pas). En réalité, il a une
probabilité de **1/3** de gagner s'il conserve son choix initial et **2/3** s'il
en change.

Lors de son choix initial, le joueur a une chance sur trois de sélectionner la
porte gagnante. S'il décide de toujours garder sa porte, il a donc une chance
sur trois de gagner. Comme à la fin il n'a que deux choix (_garder_ ou
_changer_), il aurait eu deux chances sur trois de gagner en changeant de porte.

Pour mieux comprendre, généralisons le principe du jeu :

  * Il y a 1 million de portes, avec une seule porte gagnante.
  * Le joueur sélectionne une porte.
  * Le présentateur retire 999 998 portes perdantes parmi les portes restantes.
  * **Le joueur doit-il changer de porte ?**

La réponse devient évidente, non ?

Si vous n'êtes pas convaincus, [développeur][] et que vous connaissez la [loi
des grands nombres][], ce programme devrait vous aider :

[développeur]: {{ site.assets }}/paradoxes_proba/dev.jpg
[loi des grands nombres]: http://fr.wikipedia.org/wiki/Loi_des_grands_nombres

{% highlight c %}
#include <stdio.h>
#include <stdlib.h>
#include <time.h>

int main()
{
  int i, winning, choice, elim, change;
  int keepwin = 0, changewin = 0;

  /* Initialise le seed pour la génération aléatoire */
  srand(time(NULL));

  for (i = 0; i < 10000; i++) {
    /* Tire au sort une porte gagnante et un choix du joueur */
    winning = rand() % 3;
    choice = rand() % 3;

    /* Présentateur */
    if (choice == winning)
      /* Choisit aléatoirement d'éliminer l'une des deux autres portes */
      elim = ~winning & (rand() % 2 + 1);
    else
      /* Désigne la porte restante perdante */
      elim = 3 - choice - winning;

    /* Compte les choix vainqueurs */
    change = 3 - choice - elim;
    keepwin += choice == winning;
    changewin += change == winning;
  }

  printf("Victoires en gardant son choix   : %d\n", keepwin);
  printf("Victoires en changeant son choix : %d\n", changewin);
  return 0;
}
{% endhighlight %}

_Notez que `rand() % 3` ne fournira pas une distribution strictement uniforme (3
n'étant pas une puissance de 2), mais la précision nous suffira ici._

Ce [problème][monty hall] est similaire au [paradoxe des prisonniers][].

[monty hall]: http://fr.wikipedia.org/wiki/Probl%C3%A8me_de_Monty_Hall
[paradoxe des prisonniers]: http://fr.wikipedia.org/wiki/Paradoxe_des_prisonniers


### Variante

Changeons un peu les règles : maintenant, le présentateur **ne sait pas** où se
trouve la porte gagnante.

Du coup, une fois que le joueur a choisi sa porte, le présentateur indique une
porte **au hasard** parmi les deux restantes. Si malheureusement il tombe sur la
porte gagnante, la partie est annulée et on recommence.

Ainsi nous _retirons_ toutes les parties où le présentateur a ouvert la porte
gagnante. Il ne reste donc plus que les parties où il désigne une porte
perdante, et nous nous retrouvons dans le même cas que précédement.


Eh bien, en fait, [non][variante]. Ce n'est pas le même cas que précédemment,
car maintenant le joueur va gagner avec un  probabilité est de **1/2** qu'il
garde sa porte ou qu'il en change. La preuve :

[variante]: http://fr.wikipedia.org/wiki/Probl%C3%A8me_de_Monty_Hall#Changeons_les_r.C3.A8gles_d.27ouverture

{% highlight c %}
#include <stdio.h>
#include <stdlib.h>
#include <time.h>

int main()
{
  int i = 0, winning, choice, elim, change;
  int keepwin = 0, changewin = 0;

  /* Initialise le seed pour la génération aléatoire */
  srand(time(NULL));

  while (i < 10000) {
    /* Tire au sort une porte gagnante et un choix du joueur */
    winning = rand() % 3;
    choice = rand() % 3;

    /* Présentateur */
    elim = ~choice & (rand() % 2 + 1);
    if (elim == winning)
      continue;

    /* Compte les choix vainqueurs */
    change = 3 - choice - elim;
    keepwin += choice == winning;
    changewin += change == winning;
    i++;
  }

  printf("Victoires en gardant son choix   : %d\n", keepwin);
  printf("Victoires en changeant son choix : %d\n", changewin);
  return 0;
}
{% endhighlight %}

Intuitivement, si c'est le hasard qui détermine à la fois la porte choisie par
le joueur et la porte laissée par le présentateur, et que nous supprimons toutes
les parties où le présentateur a éliminé la porte gagnante (en moyenne 1 partie
sur 3), tout se passe comme s'il n'y avait que 2 portes dès le début du jeu.


### Dissonance

_– "sonance"_

Nous disposons de pastilles de 3 couleurs (disons <span style="color:
red;">rouge</span>, <span style="color: blue;">bleu</span> et <span
style="color: green;">vert</span>).

Nous proposons à un singe de choisir parmi 2 de ces couleurs (par exemple <span
style="color: red;">rouge</span> et <span style="color: blue;">bleu</span>)
celle qu'il préfère (par une méthode quelconque). Il _répond_ "<span
style="color: red;">rouge</span>".

Nous lui demandons alors laquelle il préfère parmi la couleur qu'il n'a pas
choisie la première fois (<span style="color: blue;">bleu</span>) et celle qui
reste (<span style="color: green;">vert</span>). Et le plus souvent (environ 2
fois sur 3), les chercheurs ont observé qu'il rejetait encore la couleur qu'il
n'avait pas choisie la première fois (<span style="color: blue;">bleu</span>).

Cela [montre][dissonance] qu'une fois que nous rejetons quelque chose, nous le
_dévaluons_, ce qui nous amène à le rejeter de nouveau lors d'un second choix.

[dissonance]: http://www.nytimes.com/2007/11/06/science/06tier.html

[Ou pas][flaw]. En réalité, ce raisonnement souffre de la même erreur de
raisonnement qui nous induit en erreur dans le problème de _Monty Hall_.

[flaw]: http://www.nytimes.com/2008/04/08/science/08tier.html

Supposons que le singe sache trier les trois couleurs par ordre de préférence.
Nous lui en montrons deux. Cela revient à choisir au premier tour celle que nous
ne lui montrons pas.

Cette couleur non choisie peut être :

  1. celle que le singe préfère ;
  2. la deuxième ;
  3. celle qu'il aime le moins.

Parmi ces 3 possibilités _équiprobables_, seule la position 3 lui fera la
rejeter lors du second choix (c'est la seule moins bonne que la moins bonne du
premier choix).


## Test positif

> Une maladie X touche 1 personne sur 100 000 dans une population. Un test de la
> maladie X est fiable à 99%. Il se révèle positif pour vous. Quelle est la
> probabilité que vous soyez infecté ?

Aussi surprenant que cela puisse paraître, la réponse est **0,1%**.

Faisons le calcul sur une population de 10 millions de personnes. La maladie
touche 1 personne sur 100 000, donc 100 personnes en moyenne.

Le test est fiable à 99%, donc il provoque 1% d'erreur. Sur les 9 999 900
personnes non malades, il y a donc 99 999 erreurs (faux-positifs). Sur les 100
malades, il y a 1 erreur (faux-négatif).

En tout, en moyenne 99 999 + 99 = 100 098 personnes seront testées positives,
alors que seules 99 seront malades. Donc si votre test est positif, vous avez 99
chances sur 100 098 d'être malade, soit **moins de 0,1%**.

Le fait que ce résultat soit [surprenant][xkcd] pour notre cerveau provient d'un
[biais cognitif][] appelé l'[oubli de la fréquence de base][].

[xkcd]: http://xkcd.com/1132/
[biais cognitif]: http://fr.wikipedia.org/wiki/Biais_cognitif
[oubli de la fréquence de base]: http://fr.wikipedia.org/wiki/Oubli_de_la_fr%C3%A9quence_de_base


## Conclusion

Méfiez-vous de vos intuitions en probabilités.
