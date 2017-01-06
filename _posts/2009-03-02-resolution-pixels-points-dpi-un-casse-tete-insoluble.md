---
layout: post
title: 'Résolution, pixels, points, dpi : un casse-tête insoluble ?'
date: 2009-03-02 20:33:28+01:00
---

Ce billet fait suite à une [question][] que je me posais sur la résolution _dpi_
des écrans (notamment la valeur erronée détectée par défaut sous **Gnome**), qui
m'avait amené à effectuer un [rapport de bug][bugreport], tout en pointant du
doigt certains problèmes qui surviendraient si **Gnome** détectait correctement
le _dpi_. Ce bug est maintenant corrigé dans la nougelle version de **Gnome**,
celle embarquée dans la future **Ubuntu 9.04 Jaunty Jackalope**. C'est
l'occasion de faire le point.

[question]: http://forum.ubuntu-fr.org/viewtopic.php?pid=2457643
[bugreport]: https://bugs.launchpad.net/ubuntu/+bug/253072


## Définitions

Afin de comprendre les problèmes, il est important de définir chacun des
concepts (je limite ici leur définition au cas d'une image numérique affichée
sur un écran).


### pixel

Le [pixel][], abrégé **px**, est une unité de surface permettant de définir la
base d'une image numérique. Son nom provient de la locution anglaise « picture
element », qui signifie « élément d'image » ou « point élémentaire ». Il n'a _a
priori_ pas de taille « réelle ».

[pixel]: http://fr.wikipedia.org/wiki/Pixel


### point

Le [point (Pica)][pointpica], abrégé **pt**, est une unité de longueur. Un point
pica mesure 1/72e de pouce (1 pouce = 2,54 cm), c'est-à-dire environ 0,03528 cm,
soit un peu plus d'un tiers de millimètre.

[pointpica]: http://fr.wikipedia.org/wiki/Point_(unit%C3%A9)#Le_point_DTP_.28Pica.29


### résolution

La [résolution][] permet de donner une taille réelle à un pixel. Elle est
souvent exprimée en **DPI** (Dot Per Inch : Point Par Pouce). Attention, dans
cette unité, le **point** signifie **pixel** (et non **point Pica**, puisque par
définition, le nombre de **points Pica** par pouce est toujours 72, tout comme
le nombre de millimètres dans un centimètre est toujours 10).

[résolution]: http://fr.wikipedia.org/wiki/R%C3%A9solution_num%C3%A9rique


### définition

La [définition][] d'une image ou d'un écran est le nombre de pixels qui
composent l'image ou que peut afficher un écran. Elle est souvent donnée sous la
forme _nombre de pixels horizontalement × nombre de pixels verticalement_, par
exemple _640×480_.

[définition]: http://fr.wikipedia.org/wiki/D%C3%A9finition_d%27%C3%A9cran


## Résolution dpi de l'écran

### Intérêt du dpi

_Combien mesure sur l'écran une image en 640×480 ?_  
_Quelle est la hauteur en pixels d'un texte en taille 18 pt ?_  
Cela dépend de la taille des pixels ! Par exemple, une image en 640×480 sera 4
fois plus petite (2 fois dans chaque dimension) sur écran 7" que sur un écran
14", si les deux écrans ont la même définition (disons 1024×768). Pareil pour la
taille d'un texte.

_Quelle taille en pixels doit avoir un rectangle de 13×8 cm ?_  
Cela dépend également de la taille des pixels : le rectangle sera « plus petit »
(en pixels) sur un écran de moindre définition.

Pour déterminer la réponse à ces questions, la connaissance de la
**résolution**, exprimée en **dpi**, est nécessaire : elle permet de faire la
conversion entre une mesure réelle (homogène aux centimètres) et une mesure en
nombre de pixels.


### Valeur réelle du dpi de l'écran

#### En ligne de commande

Pour connaître le _dpi_ d'un écran, il suffit de taper :

{% highlight bash %}
xdpyinfo | grep resolution
{% endhighlight %}


#### À la main

Il est également possible d'effectuer le calcul à la main (comme je l'ai fait
sur le post et sur le bug report cités au début), en connaissant d'une part la
définition de l'écran, et d'autre part :

  * soit la diagnole d'écran (15,4" par exemple) ainsi que le ratio (16/10) ;
  * soit la hauteur et la largeur (en centimètres par exemple).

Juste pour illustrer le premier exemple, avec ces calculs on trouve qu'un écran
7" en 1024×768 a une résolution de 183 dpi, et donc qu'une image en 640×480
affichée à l'écran mesure précisément 3,5×2,625" (8,89×6,6675 cm). De même, un
écran 14" en 1024×768 a une résolution de 91,5dpi, et donc qu'une image en
640×480 affichée à l'écran mesure précisément 7×5,25" (17,78×13,335 cm).

On remarque que si l'on fait une capture d'écran de ces deux images, dont la
surface de l'une 4 fois plus petite que celle l'autre, le résultat sera
identique (en 640×480) : une capture d'écran (une image numérique) affichée à
l'écran ne prend en compte que la taille en pixels. Ceci a une conséquence
importante : une fenêtre _F1_ sur un écran _E1_ plus petite qu'une fenêtre _F2_
sur un écran _E2_ peut apparaître plus grande sur des captures d'écran.


### Valeur du dpi configurée

Le système, en connaissant le _dpi_, est donc capable d'afficher des objets dont
les mesures sont exprimées en centimètres (ou unités homogènes, comme les
pouces).

Le problème, c'est qu'il n'utilise pas toujours la valeur « réelle » du _dpi_ :
il utilise souvent une valeur pré-configurée (72 ou 96), indépendante de
l'écran. La résolution _dpi_, seule valeur permettant de faire le lien avec la
mesure réelle, est choisie arbitrairement : elle ne sert donc à rien. Tout cela
avait un sens physique à l'origine, où le matériel avait toujours une résolution
très proche de 72 ou de 96 (voir [cet article][affichage]).

[affichage]: http://terroirs.denfrance.free.fr/p/webmaster/affichage_mac_pc.html

Pour continuer à lui donner un sens, il faut que le système utilise le **dpi
réel** de l'écran : c'est le cas depuis **KDE4** et **Gnome 2.25** ; **Windows**
et **MacOS**, quant-à-eux, continuent à utiliser une valeur pré-configurée
dénuée de sens.

On a donc, dans les deux environnements de bureau principaux sous **GNU/Linux**,
une valeur correcte du _dpi_ : c'est donc gagné !

Malheureusement, ce n'est pas si simple.


## Problèmes

### Problèmes théoriques

Nous possèdons un écran 15,4" de définition 1024×768, et nous souhaitons le
remplacer par un nouvel écran 15,4" de définition 2048×1536 (donc de résolution
double dans chaque direction).

**Comment doit se comporter l'affichage des polices de caractères ?**

Deux solutions :

  * la taille des polices reste identique en pixels (_px_) : la taille apparente
    est donc deux fois plus petite (dans chaque dimension) ;
  * la taille des polices reste identique en points (_pt_) : la taille apparente
    ne change pas, mais chaque caractère est composée de plus de pixels.

La réponse n'est pas évidente.

La première proposition qui consiste à garder la taille identique en **pixels**
ne peut pas être absolument vraie : si nous utilisions un écran 15,4" de
définition 20480×15360 (pourquoi pas?), des lettres de 18 pixels seraient
totalement illisibles par l'œil humain.

La seconde semble donc plus valable, mais nous pouvons objecter que la taille
des caractères que nous avions sur notre écran 15,4" en 1024×768 est plus grande
que celle dont nous avons réellement besoin : nous ne pouvions pas la diminuer à
cause de la faible résolution de l'écran (une lettre représentée par 4 pixels
n'est qu'une tache noire). Un écran de meilleure résolution permettrait de
diminuer la taille du texte afin d'obtenir une taille réelle « meilleure ».

Nous pouvons également faire remarquer, de manière peut-être moins rigoureuse,
que si nous avons investi dans un écran de meilleure définition, c'est pour
« avoir plus de place ».

Mon point de vue est donc celui-ci : il existe un ensemble de tailles (une
taille pour les titres, une pour les sous-titres, une pour les paragraphes…),
exprimées en **points**, idéales. Plus nous nous en éloignons, plus nous avons
une impression de « trop gros » ou « trop petit ». La seconde proposition, qui
consiste à garder la taille des polices identique en **points** est d'après moi
_asymptotiquement_ vraie : que nous possédions un écran 20480×15360 ou
40960×30720 ne doit pas changer la taille apparente des polices.

Mais pour des résolutions trop petites (72 ou 96 dpi par exemple), une
contrainte intervient fortement : les caractères doivent avoir une forme
reconnaissable, et être « suffisamment lisses ». Impossible alors d'afficher des
caractères dont la taille réelle serait lisible, mais dont l'équivalent en
pixels est trop faible. ?ous sommes alors obligé d'augmenter artificiellement
la taille réelle des polices de caractères.

_**Remarque :** Ce raisonnement n'est valable que pour le changement de la
résolution sur un écran de même taille réelle. Si nous voulions définir comment
doit se comporter l'affichage sur un écran de résolution identique mais de
taille plus grande (donc de meilleure définition), il faudrait prendre en compte
l'éloignement des yeux par rapport à l'écran (nous sommes plus proches de
l'écran sur un écran 7" que sur un écran 45"). A priori, je dirais ce que qui
est asymptotiquement vrai n'est pas la conservation des tailles en **points**,
mais la conservation des **angles** que forment le haut et le bas d'un caractère
avec l'œil (ce qui est équivalent si on conserve un écran de même taille)._

_Si vous avez des remarques ou d'autres explications, je suis tout à fait
ouvert, car ceci n'est que mon intuition :-)_


### Problèmes pratiques

En pratique, c'est compliqué.

La majorité des applications utilise des tailles de polices de caractères
exprimées en **points**, mais des parties de l'interface sont exprimées en
**pixels**. Par exemple, la barre de menu de **Gnome** a par défaut une hauteur
de **24px**… et son texte a une taille de **10pt**. Ces mesures donnent un rendu
cohérent à **96 dpi**, mais plus le _dpi_ augmente, plus le texte est « gros »
par rapport à la barre (les variations de _dpi_ ne font varier que les mesures
exprimées en **points**). Voici le rendu par défaut sur mon écran 130 dpi :

{: .center}
![high-dpi]({{ site.assets }}/resolution_dpi/high-dpi.png)

Pour les applications, ce problème est contournable : on peut facilement les
configurer pour définir la taille des composants ou la taille des polices.

Mais le plus gros problème se pose sur les sites internet : certains sites
expriment leurs mesures en **pixels** et d'autres en **points**. Plus la valeur
du _dpi_ est élevée, plus les sites exprimés en **points** paraîtront gros par
rapport à ceux exprimées en **pixels**. Sans parler des nombreux sites qui
mélangent les unités de mesure. Et le pire, c'est qu'il n'y a pas de « bonne
manière de faire » : les tailles en **pixels** et les tailles en **points** ont
chacunes leurs avantages et leurs inconvénients sur les sites internet. On ne
peut donc pas espérer que ces problèmes soient gommés au fur et à mesure.


## Tout ça pour ça !

La solution que j'utilise pour éviter ces différences de tailles de texte est
de… faire croire à **Gnome** que mon écran est en **96 dpi**. Comme s'il avait
une diagonale de 20,85" (15,4×130/96) : il se comporte donc exactement comme un
écran 20,85" à **96 dpi** qui aurait été rétréci. Au lieu de définir mes polices
en **7pt** à **130 dpi**, je leur donne la valeur **10pt** à **96 dpi**, et ça
évite les incohérences de rendu.

Évidemment, comme je l'ai déjà signalé, cette solution n'est pas satisfaisante :
si au lieu de **130 dpi** j'avais un écran à **200 dpi**, les caractères
seraient beaucoup trop petits si je configurais **Gnome** à **96 dpi** (pour lui
faire croire que mon écran est un 32,08").

La valeur arbitraire donnée au _dpi_ par le système rendait la résolution
absolument dénuée de sens ; maintenant que la valeur est correcte, je la force à
une valeur incorrecte pour avoir un rendu cohérent. C'était bien la peine !

Et je ne vois aucune solution envisageable pour obtenir un rendu cohérent
actuellement sur d'éventuels écrans 200 ou 300 dpi. Pourtant, on pourrait penser
_a priori_ que les résolutions d'écran ne vont pas arrêter de s'améliorer dans
les années à venir : ces problèmes seront-ils un frein à leur développement ?
