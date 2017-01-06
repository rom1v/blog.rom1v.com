---
layout: post
title: "Achat d'ordinateur portable pour GNU/Linux : mode d'emploi"
date: 2009-02-26 08:16:55+01:00
---

Lors de l'achat d'un ordinateur portable destiné à faire tourner une
distribution **GNU/Linux**, deux problèmes se posent :

  * la **compatibilité matérielle** de tous les composants (son, wifi,
    bluetooth, webcam…) ;
  * la **vente forcée** d'une (n-ième) licence _Microsoft Windows_ inutile.


Nous pouvons distinguer deux types de revendeurs informatique :

  * ceux qui revendent des ordinateurs portables provenant directement des
    constructeurs, qui forcent l'achat d'une version de la licence du système
    d'exploitation _Windows_ et éventuellement d'autres logiciels (c'est le cas
    de l'immense majorité) ;
  * les autres, qui permettent l'achat d'un ordinateur sans achat forcé de
    licences de logiciels, et qui proposent même parfois le choix des
    composants matériels…


Pour comparer les vendeurs d'ordinateurs, l'[AFUL][] a mis en place un site :
[bons-vendeurs-ordinateurs.info][bvo].

[AFUL]: http://www.aful.org/
[bvo]: http://bons-vendeurs-ordinateurs.info/


## Acheter chez un bon revendeur

Naturellement, il est préférable de se tourner vers un _bon revendeur_, lorsque
cela est possible. Certains proposent des ordinateurs sans système
d'exploitation, d'autres proposent des ordinateurs avec _Ubuntu_ ou d'autres
distributions _GNU/Linux_ pré-installées. D'autres encore laissent le choix
total (_Windows XP_, _Windows Vista_, _Ubuntu_, _Mandriva_, _Fedora_… ou sans
OS), où le client ne paye la licence que pour les logiciels qu'il désire (et
éventuellement le coût d'installation si le client désire avoir un système
pré-installé).


### Avantages

Acheter chez un de ces revendeurs résout les deux problèmes initiaux :

  * le matériel a parfois été testé avec la version actuelle du noyau _Linux_,
    ce qui garantit l'absence de problèmes (il suffit de le demander) ;
  * les licences pour un système d'exploitation et des logiciels non désirés
    (qui représentent une part non négligeable dans le prix d'un ordinateur,
    parfois jusqu'à 30%) ne sont pas vendues de force.

_**Remarque :** attention à bien distinguer « ça fonctionne sous Linux » et « ça
fonctionne sous Linux **out-of-the-box** ». Dans le premier cas, cela veut dire
qu'il est possible de faire fonctionner le matériel éventuellement en y
rajoutant un pilote (propriétaire) ; dans le second cas, tout fonctionne même en
LiveCD, sans aucune manipulation. Insistez pour bien vous faire préciser si tout
fonctionne sans installation de pilotes supplémentaires._

C'est ce que j'ai fait récemment en achetant un ordinateur [Clevo][] sans
logiciels, pour un prix dont la valeur entière tient sur 10 bits (pour les
non-geeks, comprenez environ 1000€), pour une configuration plutôt sympa pour le
prix (ce n'est pas pour jouer) :

  * Intel Core2Duo P8400 (2×2,26GHz)
  * écran 15,4" 1680×1050
  * nVidia GeForce 9300 256Mo
  * 320Go 7200trs/mn 16Mo de cache
  * 4Go de RAM

[clevo]: http://www.clevo.fr/

Tout est reconnu, même en **LiveCD** (seule la carte graphique _nVidia_
nécessite l'installation de pilotes propriétaires, en espérant qu'un jour il
soit possible de faire fonctionner les effets 3D avec le pilote libre, comme on
peut déjà le faire avec des _AMD/ATI_ ou avec les cartes _Intel_ intégrées). _Il
me reste encore à tester le bluetooth._

On pourrait donc conclure que tous les problèmes sont résolus, il suffit de se
tourner vers ces revendeurs, et c'est gagné. Malheureusement, ce raisonnement
serait un peu simpliste.


### Inconvénients

Si ces revendeurs respectent la loi et permettent d'acheter le matériel sans les
logiciels, ils n'en ont pas moins des défauts.

Le premier concerne la gamme disponible : ils ne possèdent pas tous les
ordinateurs (loin de là) existants dans le circuit de distribution classique (en
grande surface par exemple). Et la plupart des modèles spécifiques chez les
_bons revendeurs_ ont un design assez pauvre, qui ne rivalise pas avec celui des
ordinateurs de grandes marques.

Ensuite, le prix d'une machine équipée d'un système d'exploitation _GNU/Linux_
chez un _bon revendeur_ est parfois supérieur à celui d'une machine équipée de
_Microsoft Windows_ chez un _mauvais revendeur_, tout simplement parce qu'un
petit revendeur ne vend pas suffisamment d'ordinateurs pour pouvoir faire
baisser les coûts.

Enfin, le délai est souvent un peu plus élevé, le temps d'assembler l'ordinateur
si le revendeur propose le choix du matériel (en tout cas il est forcément plus
long qu'en repartant avec l'ordinateur sous le bras dans une grande surface).

Pour diverses raisons (design, prix, autre…), des clients peuvent donc désirer
tel modèle d'un matériel de telle marque, sans pour autant être obligés
d'acquérir les licences de logiciels qu'ils ne désirent pas. Dans ce cas,
certains préfèrent acheter chez un _mauvais revendeur_.


## Acheter chez un mauvais revendeur

Là, c'est un peu plus compliqué.


### Compatibilité matérielle

Pour la **compatibilité matérielle** avec le noyau _Linux_, il ne faut pas
compter sur les vendeurs pour obtenir la moindre information (au mieux, la
réponse sera _« demandez sur un forum, nous on ne fait pas Linux! »_, au pire
_« c'est quoi Linux? »_). Demander à un vendeur pour tester un _LiveCD_ ou une
_LiveUSB_ d'une distribution _GNU/Linux_ sur un ordinateur mènera presque
systématiquement à une réponse négative.

Une solution est de rechercher le modèle exact de l'ordinateur dans un moteur de
recherche, en espérant trouver quelqu'un indiquant si tout fonctionne ou non.
Dans le cas contraire, il est toujours possible de demander sur un forum
([ubuntu-fr][] par exemple) si quelqu'un fait fonctionner une distribution
_GNU/Linux_ sur l'ordinateur en question.

[ubuntu-fr]: http://forum.ubuntu-fr.org/

Sinon, il faut s'en remettre à la chance : le matériel est de plus en plus
souvent reconnu _out-of-the-box_ avec les nouvelles versions du noyau _Linux_.


### Vente liée

Chez les _mauvais revendeur_, **la vente de la licence** d'une version (basic,
home, premium, pro, ultimate…) de la génération actuelle de _Windows_ (_Vista_
en ce moment) **est imposée** (avec éventuellement d'autres logiciels) : il est
impossible d'acquérir le matériel sans les logiciels. **Ceci est illégal.**

**Si vous achetez un ordinateur pour y mettre exclusivement _GNU/Linux_, ou si
vous possédez déjà une licence valide et utilisable de _Windows_, ne vous
laissez pas faire !**

Suite à différents procès, les constructeurs proposent maintenant des procédures
de remboursement (après l'achat). Cependant, la plupart (actuellement toutes)
sont **abusives**.

**Non, Windows OEM ne coûte pas 25€ ! Non, le retour de l'ordinateur ne peut pas
conditionner le remboursement des licences logicielles !**

Je ne vais pas argumenter ou expliquer comment faire ici, je vous renvoie au
détail de [mes procédures racketiciel][procédures] en cours.

[procédures]: http://forum.ubuntu-fr.org/viewtopic.php?id=277078
[optionnalité]: http://www.racketiciel.info/situation/nos-demandes/
[racketiciel-droit]: http://www.racketiciel.info/documentation/droit

En attendant l'[optionnalité][], **n'hésitez pas à vous faire rembourser la
licence des logiciels que vous n'utilisez pas**, [comme plusieurs l'ont déjà
fait][racketiciel-droit].


## Conclusion

Pour résumer, la première chose à faire pour acheter un ordinateur destiné à
_GNU/Linux_ est de regarder si un modèle convient (selon les critères de chacun)
chez les _bons revendeurs_. Si ce n'est pas le cas, n'acceptez pas passivement
de payer une nouvelle fois des logiciels que vous n'utilisez pas.
