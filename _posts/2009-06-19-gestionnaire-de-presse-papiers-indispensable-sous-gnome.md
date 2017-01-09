---
layout: post
title: 'Gestionnaire de presse-papiers : indispensable sous Gnome !'
date: 2009-06-19 22:43:30+02:00
tags:
- planet-libre
- puf
---

La gestion du [presse-papiers][] de _Gnome_ par défaut est assez rudimentaire.

[presse-papiers]: https://fr.wikipedia.org/wiki/Presse-papier_%28informatique%29

Faites le test :

  * ouvrez **firefox** ;
  * copiez un bout de texte sur une page internet ou l'url de la barre d'adresse
    (sélectionnez puis Ctrl+C) ;
  * fermez **firefox** (toutes les fenêtres) ;
  * ouvrez un éditeur de texte (**gedit** par exemple) ;
  * collez-y le contenu que vous avez copié (Ctrl+V) ;
  * **:-?**

Eh oui, le contenu du presse-papiers est perdu en même temps que la fermeture du
programme d'où il provient…

Pour éviter cela, il faut un _Gestionnaire de presse-papiers_. _KDE_ en a un par
défaut : `klipper`.

Sous _Gnome_, il faut en installer un. Il en existe plusieurs, mais
personnellement je vous conseille `parcellite`. Il suffit de l'installer, il se
lancera tout seul à chaque démarrage. Il ajoute une icône dans le systray, à
partir de laquelle il est possible de le configurer, mais le principal, c'est
qu'il permette de ne pas perdre le contenu copié… Il mémorise aussi les _n_
derniers contenus (configurable). Petit inconvénient, il n'est pas en français,
mais de toute façon une fois lancé, on n'y touche plus.

{: .center }
![parcellite]({{ site.assets }}/presse-papier/parcellite.png)

Sinon, il existe également `glipper` sous la forme d'un applet à ajouter au
tableau de bord, mais le problème est qu'il plante à quasiment chaque démarrage
du système (même si une fois lancé il fonctionne bien).
