---
layout: post
title: Taper des caractères spéciaux sous GNU/Linux
date: 2009-02-01 10:21:54+01:00
tags:
- planet-libre
- puf
---

Ceux qui ont déjà utilisé _Windows_ savent peut-être qu'il est possible d'entrer
des caractères en appuyant sur la touche _Alt_ suivi du code ASCII en décimal.

Par exemple :

  * `É` : _Alt+144_
  * `A` : _Alt+65_
  * `®` : _Alt+169_

_**Note :** Le code ASCII est utilisé pour les nombres inférieurs à 128. Pour
les autres, je ne sais pas quel codage est utilisé, d'autant que rajouter un 0
devant le nombre change le code (Alt+169 c'est `®` alors que Alt+0169 c'est `©`,
et `®` peut s'écrire aussi Alt+0174). Si quelqu'un a une explication…_

Sous _Ubuntu_, on a peu de raisons d'utiliser une telle méthode, car le layout
**France (Alternative)**, par défaut sous _Gnome_, permet d'utiliser bon nombre
de caractères spéciaux, comme vous pouvez le voir sur ce schéma :

{: .center}
![french-alternative-layout]({{ site.assets }}/caracteres_speciaux/french-alternative-layout.png)

Il y a 4 caractères possibles par touche. Voici comment écrire les caractères
possibles d'une touche, en fonction de leur position sur le schéma :

  * **en bas à gauche :** _touche_
  * **en haut à gauche :** _Shift+touche_
  * **en bas à droite :** _AltGr+touche_
  * **en haut à droite :** _AltGr+Shift+touche_

Ainsi, pour écrire `®` (dans _®om_ par exemple), il suffit de faire
_AltGr+Shift+C_, tandis que _AltGr+C_ donne `©`. De même, _AltGr+Shift+2_ génère
un `É`. Pour les espagnols, le `ñ` peut être obtenu en pressant _AltGr+^, n_, le
`¿` et le `¡` respectivement grâce à _AltGr+?_ et _AltGr+!_.

_**Note :** Pour les caractères majuscules se trouvant sur les touches
numériques, il est également possible d'activer la touche **CapsLock** : avec
**CapsLock** activé, la touche **2** génère un **É**._

Il est donc très simple de faire rapidement des « guillemets » ou des flèches
(`←`, `↑`, `↓`, `→`), d'écrire le mot « œuf » correctement, d'insérer un vrai
signe de mutiplication (2×3) ou d'utiliser de vrais points de suspension…

Par ailleurs, certaines combinaisons de touches donnent naturellement des
caractères spéciaux, par exemple _^_ suivi d'un chiffre le met en exposant :
`¹²³⁴⁵⁶⁷⁸⁹⁰`.

Il est possible également d'utiliser la _composition de caractères_ :

  * **ae** → `æ`
  * **oe** → `œ`
  * **'e** → `é`
  * **~n** → `ñ`

Pour cela, il faut définir une touche du clavier qui permettra d'activer la
composition : _Système → Préférences → Clavier → Agencements → Autres options… →
Position de la touche Compose_.

{: .center}
![layout-compose]({{ site.assets }}/caracteres_speciaux/layout-compose.png)

Ensuite, il suffit de laisser appuyée cette touche pendant l'écriture des
caractères à composer. Sur la capture, j'ai utilisé la touche _Menu_, qui se
trouve à droite de _AltGr_ ; quand je laisse enfoncée cette touche et que
j'écris _ae_, le caractère `æ` est généré.

En dernier recours, il est aussi possible d'écrire un caractère directement à
partir de son code _Unicode_ en hexadécimal. Pour cela, il suffit de taper :
_Ctrl+Shift+u+**code**_.

Par exemple, le code hexadécimal de `®` est `AE` (voir [ici][unicode]). Ainsi,
_Ctrl+Shift+uae_ insère un `®`.

[unicode]: http://fr.wikipedia.org/wiki/Table_des_caract%C3%A8res_Unicode_(0000-0FFF)

L'outil `gucharmap` (_Applications → Accessoires → Table des caractères_) donne,
en bas de la fenêtre, le code _Unicode_ d'un caractère sélectionné. Par exemple
`‰` affiche **U+2030** : pour écrire ce caractère, on peut donc taper
_Ctrl+Shift+u2030_.

Si on veut écrire que **x∊ℝ**, on fait : _x_, puis _Ctrl+Shift+u220a_, suivi de
_Ctrl+Shift+u211d_.

Il ne reste plus qu'à apprendre la table _Unicode_, bon courage !
