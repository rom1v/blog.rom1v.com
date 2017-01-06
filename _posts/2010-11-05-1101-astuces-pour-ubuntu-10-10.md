---
layout: post
title: 1101 astuces pour Ubuntu 10.10
date: 2010-11-05 14:11:57+01:00
---

Dans ce billet, je vais partager avec vous quelques astuces pour des opérations
courantes sous _Ubuntu_ (_Gnome_, _Compiz_ et _Firefox_ plus précisément). Je me
suis aperçu que finalement beaucoup ne connaissaient pas certains de ces petits
détails bien pratiques.

_1101 est à lire en binaire, ça fait légèrement moins qu'en décimal ;-)_


## Gnome


### Positionnement d'un ascenseur

{: .center}
![position-scrollbar]({{ site.assets }}/1101_astuces/position-scrollbar.png)

Il y a plusieurs interactions possibles avec un "ascenseur" (horizontal ou
vertical) :

  * un clic sur les petites flèches permettent de déplacer le curseur ligne par
    ligne ;
  * un clic dans la partie grisée permet de déplacer le curseur page par page ;
  * un glisser-déposer (clic gauche maintenu sur le curseur pendant un
    déplacement) permet de placer le curseur à volonté.

Il y existe une 4e méthode, moins connue, mais bien plus pratique, qui permet de
positionner le curseur directement à une position (comme le glisser-déposer,
mais sans avoir besoin d'aller chercher le curseur) : il suffit de **cliquer
avec le bouton du milieu à la position désirée dans la barre**, le curseur va
s'y positionner aussitôt. En maintenant enfoncé le clic milieu, il est également
possible de déplacer le curseur.

Ceci fonctionne également pour les _sliders_, par exemple pour le contrôle du
volume dans l'applet de son de _Gnome_, ou pour la barre d'avancement d'un
lecteur vidéo (même si maintenant ils ont adopté ce comportement par défaut sur
le clic gauche).


### Contrôle du volume

{: .center}
![sound-applet]({{ site.assets }}/1101_astuces/sound-applet.png)

Lorsque l'on clique sur l'applet de son de _Gnome_, un _slider_ permettant de
changer le volume apparaît. Mais il est également possible de **survoler l'icône
de son et d'augmenter ou de diminuer le volume grâce à la molette de la
souris**, sans cliquer.


### Déplacement d'un applet

Sous _Gnome_, les barres du haut et du bas accueillent des _applets_. Avec un
clic-droit sur l'un d'entre eux, un menu contextuel permet, entre autres, de le
déverrouiller pour pouvoir le déplacer.

{: .center}
![gnome-panel]({{ site.assets }}/1101_astuces/gnome-panel.png)

Si l'applet est déverrouillé, ce même menu permet de le déplacer. Mais pour cela
il y a plus simple : **glisser-déposer l'applet en utilisant le clic milieu**
(cliquer et maintenir enfoncé le clic milieu et déplacer l'applet).

Les icônes de raccourcis étant des applets particuliers, ils sont déplaçables de
cette manière.


### Double-panneau Nautilus

Nautilus permet d'**afficher deux panneaux côte à côte en pressant la touche _F3_**.

{: .center}
![nautilus-f3]({{ site.assets }}/1101_astuces/nautilus-f3.png)

Une seconde pression sur _F3_ repasse en mode "un seul panneau" (le panneau
inactif est alors supprimé). Cette fonctionnalité est très pratique pour faire
des déplacements ou des copies de fichiers, de manière beaucoup plus directe que
par l'utilisation de plusieurs fenêtres ou même d'onglets.


### Renommage avec ou sans extension

Pour renommer un fichier dans _Nautilus_, vous connaissez sûrement la touche
_F2_, qui renomme en présélectionnant le nom du fichier _sans_ l'extension :

{: .center}
![rename]({{ site.assets }}/1101_astuces/rename.png)

Mais il est également possible de **renommer en présélectionnant le nom du
fichier _avec_ l'extension, grâce à _Shift+F2_**.

_**EDIT :** Ou alors, deux fois F2._



## Compiz


### Déplacement d'une fenêtre

Cette fonctionnalité est assez connue et utilisée je pense, puisqu'elle
fonctionne avec quasiment tous les gestionnaires de fenêtres : **le déplacement
d'une fenêtre grâce à _Alt+clic gauche_**. Elle est très pratique, car elle
évite d'aller chercher la barre de titre pour déplacer une fenêtre.


### Redimensionnement d'une fenêtre

De la même manière, il est possible de **redimensionner une fenêtre grâce à
_Alt+clic milieu_**. Celle-ci est quasiment indispensable, tellement le fait
d'aller chercher un bord de fenêtre est "coûteux".

La fenêtre est virtuellement découpée en 9 parties égales (3 horizontales et 3
verticales). Lorsque vous laissez enfoncée la touche _Alt_ et que vous appuyez
sur le _clic milieu_ au-dessus d'une fenêtre, le redimensionnement commence à
partir du bord le plus proche (dépendant de la "partie" de la fenêtre que vous
survolez).

{: .center}
![resize]({{ site.assets }}/1101_astuces/resize.png)


### Capture d'écran rapide par zone

Grâce à _Compiz_, il est possible de **capturer très simplement une zone de l'écran, grâce à _Super+clic gauche_** (la touche _Super_ est la touche _Windows_ sur la majorité des claviers) :

{: .center}
![quick-screenshot]({{ site.assets }}/1101_astuces/quick-screenshot.png)

Pour cela, il faut activer le plug-in "Capture d'écran" dans `ccsm`
(`compizconfig-settings-manager` doit être installé), et choisir un répertoire
de destination (le bureau par exemple, j'en avais [déjà parlé
ici][screenshots]).

[screenshots]: {% post_url 2008-08-27-screenshots-sous-ubuntu-plusieurs-methodes-a-connaitre %}


### Changement de bureau

Par défaut, le changement de bureau est désactivé lors du déplacement d'une
fenêtre sur un bord et lors d'un scroll avec la molette de la souris sur le
bureau. Personnellement, je préfère l'activer.

Cela se configure dans `ccsm` (là encore, `compizconfig-settings-manager` doit
être installé).

Pour changer de bureau lors d'un déplacement de fenêtre au bord de l'écran :
**_Bureaux sur un plan (version améliorée) → Changement de bureau aux bords
(dernier onglet)→ Changement en déplaçant une fenêtre au bord (2e case à
cocher)_**.

Pour changer de bureau lors d'un scroll : **_Changeur de bureau → Desktop-based
viewport switching → Bureau suivant = Button5 ; Bureau précédent = Button4_**.



## Firefox


### Ajout d'un lien dans gnome-panel

{: .center}
![firefox-gnome-panel]({{ site.assets }}/1101_astuces/firefox-gnome-panel.png)

Pour garder en mémoire une URL, la méthode la plus simple et la plus appropriée
est bien sûr l'utilisation de marque-pages. Mais je trouve pratique de mettre un
raccourci dans la barre de _Gnome_, pour une page que je veux lire plus tard.

Pour cela, il suffit de **glisser-déposer le petit icône** (le _favicon_) **à
gauche de la barre d'adresse vers la barre de _Gnome_**. Il est par contre
regrettable que l'icône du raccourci ainsi créé ne soit pas le _favicon_.


### Suppression d'un historique de liste déroulante

Lorsqu'une liste déroulante propose des résultats déjà entrés auparavant (à
partir de l'historique par exemple), il est possible de supprimer spécifiquement
une entrée rapidement, en **survolant avec la souris l'entrée correspondante et
en appuyant sur _Shift+Suppr_**.

{: .center}
![firefox-history]({{ site.assets }}/1101_astuces/firefox-history.png)

Cela fonctionne dans la barre d'adresse, dans la barre de recherche et dans
toute entrée de formulaire d'une page web.


### Chargement d'une URL par un clic milieu

Lorsqu'une URL est présente dans le presse-papier, il est possible de la charger
dans _Firefox_ avec un simple clic milieu. Pour activer cette fonctionnalité, il
faut taper `about:config` dans la barre d'adresse et passer la valeur de
`middlemouse.contentLoadURL` à `true`.

Il suffit alors de **surligner une URL** (dans un fichier texte par exemple)
**puis de cliquer milieu dans le contenu d'une page dans _Firefox_** (sur un
espace "vide", pas sur un lien ou dans un champ de formulaire).

_**EDIT :** Ou sans modifier la configuration par défaut, grâce à un clic milieu
sur le favicon à gauche de la barre d'adresse._


### Notifications intégrées

Les notifications de _Firefox_ ne sont pas intégrées au système : par défaut
c'est un rectangle qui s'ouvre en bas à droite.

Pour utiliser le système de notification d'_Ubuntu_, il suffit d'installer le
paquet `xul-ext-notify` (anciennement `firefox-notify`) et de redémarrer
_Firefox_. C'est dommage qu'il ne soit pas installé par défaut.

{: .center}
![firefox-notify]({{ site.assets }}/1101_astuces/firefox-notify.png)



## Conclusion

Voilà les quelques astuces que je pouvais partager avec vous. Si vous en avez
d'autres, n'hésitez pas à les détailler. ;-)
