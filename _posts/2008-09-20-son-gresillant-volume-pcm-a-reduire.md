---
layout: post
title: 'Son grésillant : volume PCM à réduire'
date: 2008-09-20 17:48:00+01:00
---

Lorsque je lisais de la musique sur mon PC fixe, branché sur l'ampli, le son
n'était vraiment pas bon, alors que le même fichier audio sur mon pc portable
branché sur l'ampli fonctionnait nickel.

J'ai donc conclu que ma carte son était morte, et je me renseignais pour aller
en acheter une nouvelle.

À tout hasard, j'ai regardé la [doc ubuntu][], et je suis tombé sur :

> Veillez à ne pas augmenter PCM à plus de 80 % pour préserver un son
d'une bonne qualité.

[doc ubuntu]: http://doc.ubuntu-fr.org/son

J'ai donc baissé le volume PCM, monté le son de l'ampli, et le problème a
disparu :-)

{: .center}
![volume]({{ site.assets }}/son_gresillant/volume.png)

Pourtant, sur mon PC portable, même avec le volume PCM à 100%, il n'y a aucun
problème.

Si vous avez une mauvaise qualité sonore, avant d'investir dans une carte son,
pensez à réduire le volume PCM.
