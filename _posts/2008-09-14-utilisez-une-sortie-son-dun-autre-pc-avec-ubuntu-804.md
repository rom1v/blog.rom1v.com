---
layout: post
title: Utilisez une sortie son d'un autre PC avec Ubuntu 8.04 !
date: 2008-09-14 12:03:00+01:00
tags:
- planet-libre
- puf
---

Dans ma [présentation de SSH][ssh] (au chapitre 5), j'expliquais comment
exécuter un lecteur audio à distance, en ayant l'affichage en local. Pour
résumer, supposons qu'un PC fixe (qui joue un peu le rôle de serveur) contienne
toute notre audiothèque, et soit relié à un ampli. Il peut être pratique de
vouloir contrôler cette musique à distance avec le PC portable, tranquillement
installé dans le canapé. La redirection de l'affichage du lecteur distant sur le
PC local répond à ce problème.

[ssh]: {% post_url 2008-08-27-presentation-de-ssh %}

Maintenant, prenons un autre cas de figure : je veux que les sons qui sortent
actuellement sur le PC portable soient finalement redirigés vers l'ampli (par
exemple le son d'une vidéo lue dans un navigateur).

Ceci est possible grâce au serveur de son **PulseAudio** intégré à Ubuntu 8.04.
Et en plus, c'est très simple à mettre en œuvre.

Tout d'abord, installez le paquet `padevchooser` à la fois sur le PC serveur et
sur le PC client, puis lancez `padevchooser` en console (ou allez dans le menu
Applications → Son et vidéo → PulseAudio Device Chooser) : une icône apparaît
alors dans le *systray*.

Sur le serveur, cliquez sur cette icône, puis "Configure Local Sound Server…",
et dans l'onglet "Multicast/RTP", activez "**Enable Multicast/RTP receiver**".
Sur le client, faites de même, sauf que c'est "**Enable Multicast/RTP sender**"
qu'il faut activer.  Les deux machines peuvent avoir simultanément le rôle de
client et de serveur.

Ensuite, sur le client, cliquez sur l'icône de **PulseAudio** dans le *systray*,
puis "Volume Control". Pour chaque flux sortant (ici VLC et Rhythmbox), vous
pouvez choisir si le son doit sortir en local ou à distance :

{: .center}
![pavc]({{ site.assets }}/redirection_pulseaudio/pulse-audio-volume-control.png)

Le réglage est appliqué "à chaud" (le son change aussitôt de sortie audio).

Il est possible de configurer de manière plus précise, pour choisir sur quelle
carte son de quel PC le son doit sortir, ainsi que de définir une sortie son par
défaut. Je vous laisse fouiller les préférences :)

Merci à [Compte0][] qui m'a fait découvrir cette fonctionnalité.

[Compte0]: http://forum.ubuntu-fr.org/viewtopic.php?pid=2047197#p2047197

_Note : pour que cela fonctionne avec les vidéos Flash, il faut le paquet
`libflashsupport`, en attendant que *Flash* supporte nativement **PulseAudio**.
Attention cependant, il est possible avec ce paquet que des plantages de Firefox
surviennent aléatoirement sur des pages contenant du *Flash* ; si cela vous
arrive, désinstallez simplement le paquet. Vous pouvez également tester [la
version RC de Flash Player
10](http://labs.adobe.com/downloads/flashplayer10.html), qui supporte
**PulseAudio** en natif, ou attendre la version 8.10 d'Ubuntu, prévue pour le 30
octobre 2008._

_EDIT: Tout fonctionne correctement depuis la version 8.10._
