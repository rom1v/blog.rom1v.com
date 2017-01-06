---
layout: post
title: Lire des images et des vidéos sans serveur X (dans un TTY)
date: 2012-04-07 23:01:55+02:00
---

Saviez-vous qu'il était possible de lire des images et des vidéos dans un TTY,
sans [serveur X][] ? Je ne parle pas de les afficher en [ASCII-art][], mais bien
de les afficher "graphiquement" :

{: .center}
![bbb-tty]({{ site.assets }}/videos_tty/bbb-tty.jpg)

[ascii-art]: http://fr.wikipedia.org/wiki/Art_ASCII
[serveur X]: http://fr.wikipedia.org/wiki/X_Window_System

Je ne le savais pas jusqu'à [aujourd'hui][forum]. En fait, c'est possible grâce
à des programmes qui écrivent directement dans le [framebuffer][].

[forum]: http://forum.ubuntu-fr.org/viewtopic.php?pid=8739471#p8739471
[framebuffer]: http://fr.wikipedia.org/wiki/Framebuffer_Linux

Pour tester les outils suivants, lancez un TTY grâce aux raccourcis
_Ctrl+Alt+F[1-6]_. Pour revenir à votre session graphique, faites _Ctrl+Alt+F7_
(sur certaines distributions, par défaut la session graphique est plutôt
accessible avec _Ctrl+Alt+F1_, _Ctrl+Alt+F8_ ou _Ctrl+Alt+F9_, essayez…).


## Images

Pour afficher des images, il faut installer le paquet `fbi` (`f`rame`b`uffer
`i`mageviewer) :

    sudo apt-get install fbi

Puis simplement exécuter :

    fbi monimage.jpg

ou même

    fbi *.jpg

(_PgUp_ et _PgDown_ permettent de naviguer entre les images)

Cet outil est vraiment très rapide (sauf pour le zoom). C'est un peu
l'équivalent de [feh][] qui, lui, fonctionne en mode graphique.

[feh]: http://doc.ubuntu-fr.org/feh


## Vidéos

Pour les vidéos, nous avons besoin de [MPlayer][] :

[mplayer]: http://fr.wikipedia.org/wiki/MPlayer

    sudo apt-get install mplayer

En lançant dans un TTY :

    mplayer mavidéo.avi

_MPlayer_ choisit le pilote `fbdev`. Nous pouvons aussi le choisir
explicitement :

    mplayer -vo fbdev mavidéo.avi

Par contre, la vidéo s'affiche à sa taille originale, alors que nous la voulons
en plein écran. Il faut donc la _mettre à l'échelle_, grâce aux paramètres de
`mplayer`. Sur un écran 1680×1050 par exemple :

    mplayer -fs -vf scale=1680:-3 mavidéo.avi

`-3` permet de calculer la seconde composante à partir de la première et de
l'_aspect-ratio_. C'est dans le [man][man] :

[man]: http://man.cx/mplayer%281%29/fr

     0: largeur/hauteur dimmensionnées à d_width/d_height
    -1: largeur/hauteur originales
    -2: Calcule l/h en utilisant l'autre dimension et le rapport hauteur/largeur
        redimensionné.
    -3: Calcule l/h en utilisant l'autre dimension et le rapport hauteur/largeur
        original.
    -(n+8): Comme -n ci-dessus, mais en arrondissant les dimensions au plus
            proche multiple de 16.

Sur mon pc portable, j'arrive sans problème à lire dans un TTY une vidéo
[1080p][] (j'ai testé avec [Big Buck Bunny][] en MP4, redimensionnée lors de la
lecture à la taille de mon écran, 1680×1050).

[1080p]: http://fr.wikipedia.org/wiki/1080p
[Big Buck Bunny]: http://www.bigbuckbunny.org/index.php/download/

Par contre, sur une machine moins puissante (une [EeeBox][], qui [hébergeait ce
blog][blog] par le passé), _MPlayer_ saccade, même sur des vidéos basse
définition, que _VLC_ lit sans problèmes.  Pour améliorer les performances de
lecture de _MPlayer_, il est possible de changer l'algorithme de zoom logiciel,
grâce à l'option `-sws`. Par exemple, pour utiliser _bilinéaire rapide_ au lieu
de _bicubique_ :

    mplayer -fs -vf scale=1680:-3 -sws 0 mavidéo.avi

[eeebox]: http://www.asus.fr/event/Eeebox_B202/
[blog]: {% post_url 2009-01-31-nouveau-blog-100-libre %}

Avec ce paramètre, ça ne saccade plus.

_Cependant, sur la EeeBox, dans ce cas les couleurs sont incorrectes apparemment
à cause d'un bug de pilote vidéo Intel. J'ai donc quand même installé un serveur
X avec un gestionnaire de fenêtres minimaliste,
[awesome][]. Mais c'est une autre histoire…_

[awesome]: http://awesomewm.org


## ASCII-art

Je vous parlais d'_ASCII-art_ au début du billet, il est également possible de
lire les images ou les vidéos en _ASCII_ (c'est juste moins joli), grâce à des
commandes d'une élégance toute particulière.

Pour les images, nous pouvons installer le paquet `caca-utils` 

    sudo apt-get install caca-utils

Puis utiliser `cacaview` :

    cacaview monimage.jpg

Pour les vidéos :

    mplayer -vo caca mavidéo.avi

{: .center}
![bbb-ascii]({{ site.assets }}/videos_tty/bbb-ascii.png)


## Conclusion

Je n'en revenais pas qu'il soit possible de lire des vidéos sans serveur X. Sur
une machine destinée à une utilisation multimédia (branchée sur la TV par
exemple), il n'y a donc nullement besoin d'un serveur X (paradoxalement).
