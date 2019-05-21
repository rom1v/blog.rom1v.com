---
layout: page
title: CV
date: 2016-11-25 21:25:00+01:00
order: 3
permalink: cv/
---


## Formation

- Ingénieur en **Informatique et Mathématiques Appliquées** (ENSEEIHT, 2008)
- Diplôme Universitaire de Technologie Informatique (IUT du Havre, 2005)


## Expériences professionnelles

### Videolabs _(depuis 2018)_

#### VLC

Je participe principalement au développement du lecteur multimédia VLC
([contrib]({{ site.baseurl }}/contrib/#vlc)).

En particulier, j'ai implémenté la nouvelle playlist (voir [article]({%
post_url 2019-05-21-a-new-core-playlist-for-vlc-4 %})).

_C, C++._


#### rav1e

Je participe au développement de rav1e, l'encodeur AV1 de Mozilla/Xiph
([contrib]({{ site.baseurl }}/contrib/#rav1e)).

En particulier, j'ai ajouté le support de _tile encoding_ (voir [article]({%
post_url 2019-04-25-implementing-tile-encoding-in-rav1e %})).

_Rust._


### Genymobile _(2013 → 2018)_

#### Clone d'écran Android sur le PC

J'ai développé une application qui permet de voir et de contrôler des devices
Android à partir du PC.

Nous l'avons publié en open source :
[scrcpy](https://github.com/Genymobile/scrcpy)
([contrib]({{ site.baseurl }}/contrib/#scrcpy)).

_C, libav/FFmpeg, SDL, Java, Android._


#### Application de _reverse tethering_

J'ai développé un outil qui permet aux devices Android d'utiliser la connexion
du PC sur lequel ils sont branchés (reverse tethering), sans accès root.

D'abord écrit en Java, je l'ai réimplémenté en Rust pour obtenir un binaire
natif.

Nous l'avons publié en open source :
[gnirehtet](https://github.com/Genymobile/gnirehtet)
([contrib]({{ site.baseurl }}/contrib/#gnirehtet)).

À cette occasion, j'ai contribué à la _library_ d'I/O asynchrone que j'ai
utilisée : [mio]({{ site.baseurl }}/contrib/#rustmio).

_Java, Android, Rust._


#### Replay d'évéments souris et clavier par USB

J'ai développé un outil permettant de contrôler un périphérique Android en
utilisant une souris ou un clavier USB branché sur le PC. Il permet notamment
d'enregistrer une séquence pouvant être rejouée sur plusieurs périphériques
simultanément.

À cette occasion, j'ai corrigé un [bug sur libusb]({{ site.baseurl
}}/contrib/#libusb).

J'en ai profité pour faire une présentation en interne sur la gestion des
ressources sans pointeurs en C++, dont j'ai repris le contenu pour écrire un
[article]({% post_url 2017-01-12-cpp-sans-pointeurs %}). J'y présente notamment
l'API du [wrapper C++ de libusb]({% post_url 2017-01-12-cpp-sans-pointeurs
%}#libusb-wrappers) que j'ai implémentée.

_C, C++, libusb, Qt._


#### Refonte complète d'une application C++/Qt

Pendant plus de 2 ans, j'ai travaillé sur
[Genydeploy](https://www.genymotion.com/genydeploy/), d'abord pour sa refonte
complète (la version 3), puis pour le développement de nouvelles fonctionnalités
(les projets présentés ci-dessus ont en fait été réalisés dans le cadre de
Genydeploy).

_C++11, Qt5, Java, Android._


#### API Java/Android pour le robot Pepper

En mission chez [Softbank Robotics](https://www.ald.softbankrobotics.com/fr)
(anciennement Aldebaran), j'ai conçu l'API Android du robot
[Pepper](https://fr.wikipedia.org/wiki/Pepper_%28robot%29).

Pour cela, j'ai d'abord implémenté des _bindings_ Java/JNI pour exposer les
`promise`/`future` C++ de leur _library_ [libqi] en Java. Cette partie est open
source : [libqi-java].

J'ai ensuite développé une API Android de plus haut niveau qui expose plus
simplement les fonctionnalités du robot (chaînage automatique d'appels
asynchrones…).

[libqi]: https://github.com/aldebaran/libqi
[libqi-java]: https://github.com/aldebaran/libqi-java/commits/5bb55a47b7b495a7a7fe31571e7be89aa843ff20

_C++, Java, JNI, Android, NDK, AOSP._


#### Système vidéo sur set-top box

J'ai travaillé au développement d'un système vidéo pour set-top box Android,
destiné à être installé dans toutes les chambres d'une chaîne d'hôtels.

J'ai intégré [LibVLC](https://wiki.videolan.org/LibVLC/) dans une application
Android pour lire des flux vidéos UDP multicast.

J'ai traqué et [contourné]({{ site.baseurl }}/contrib/#vlc) un _deadlock_
pouvant survenir lors de l'arrêt du flux UDP sur VLC.

J'ai également intégré un outil permettant la lecture de flux vidéos UDP
multicast avec le lecteur natif d'Android (voir [article]({% post_url
2014-03-18-compiler-un-executable-pour-android %})).

_C, Java, Android, NDK, AOSP, CyanogenMod, LibVLC._


#### Système vidéo pour babyfoot

Pour notre babyfoot au travail, mes collègues ont développé une application
Android qui comptait les points automatiquement (grâce des capteurs branchés sur
un Raspberry Pi).

Je me suis occupé du système vidéo.

J'ai développé un outil permettant de décaler le flux vidéo _live_ quelques
secondes, afin de revoir les buts facilement (voir [article]({% post_url
2014-01-20-lecture-differee-de-la-webcam-dun-raspberry-pi %})).

Pour Android, j'ai développé un lecteur vidéo qui supportait à la fois le
lecteur natif et LibVLC, et à la fois sur `SurfaceView` et `TextureView` (pour
des raisons de compatibilité avec différentes tablettes).

[`SurfaceView`]: https://developer.android.com/reference/android/view/SurfaceView.html
[`TextureView`]: https://developer.android.com/reference/android/view/TextureView.html

_C, Java, Android, Bash._


#### ChromeCast dans CanalPlay

J'ai intégré la gestion du
[ChromeCast](https://fr.wikipedia.org/wiki/Chromecast) dans l'application
Android
[CanalPlay](https://play.google.com/store/apps/details?id=com.canalplus.canalplay.prod).

À cette occasion, j'ai [corrigé]({{ site.baseurl }}/contrib/#googlecast) une
petite fuite mémoire sur la _library_ de Google pour communiquer avec un ChromeCast.

_Java, Android._


#### Autres missions

J'ai aussi participé à d'autres missions chez des clients :

 - intégration du timeshifting vidéo dans [AOSP] pour
   [ExpWay](http://www.expway.com/) ;
 - analyse et modifications d'architecture du SDK de
   [FollowAnalytics](http://followanalytics.com/) pour
   corriger des bugs récurrents ou aléatoires ;
 - modifications sur les applications Android et Android TV de
   [MolotovTV](https://www.molotov.tv/).

[AOSP]: https://source.android.com/

_Java, Android, C++._


### AtoS Open Source Center / SmartMobility _(2012 → 2013)_

#### Application de communication sur réseau Mesh

En utilisant le projet [Serval](http://www.servalproject.org/), j'ai développé
une application Android permettant la communication entre téléphones Android
(rootés) sans réseau mobile ni Wifi, avec les fonctionnalités suivantes :

 - communication audio (talkie-walkie) à plusieurs ;
 - messagerie textuelle ;
 - géolocalisation sur des cartes [IGN] téléchargées intégrées avec [osmdroid]
   (OpenStreetMap) ;
 - partage décentralisé de fichiers.

[ign]: https://www.geoportail.gouv.fr/donnees/carte-ign
[osmdroid]: https://github.com/osmdroid/osmdroid

Ce projet a été l'occasion de [contribuer]({{ site.baseurl
}}/contrib/#servalbatphone) à _Serval_.

Par ailleurs, pour implémenter le talkie-walkie à plusieurs utilisateurs, j'ai
développé une solution simple de mixage audio, dont j'explique les principes
dans cet [article][mix].

[mix]: {% post_url 2013-01-29-le-mixage-audio %}

_Java, C, Android, NDK._


#### Applications pour tableau de bord de voiture

Lors de mes premiers pas sur Android, j'ai développé des applications e-mail et
Twitter pour la tablette intégrée au tableau de bord de la voiture [Renault
Zoé](https://www.renault.fr/vehicules/vehicules-electriques/zoe.html).

_Java, Android._


### Logica _(2008 → 2012)_

J'ai principalement réalisé une mission de 3 ans chez Sanofi-Aventis, pour
développer une application Java/Flex de recherche de molécules.

_Java EE, Adobe Flex, ActionScript._


## Autres développements

En dehors des langages principaux listés dans mes expériences profesionnelles,
j'en utilise quelques autres.

Je développe en _Python_ de manière très occasionnelle. J'ai par exemple écrit
[SHAdow]({% post_url 2017-03-01-shadow %}) pour générer des fichiers différents
ayant le même SHA-1, ou encore un [solveur de serpent-cube][snakesolver].

[snakesolver]: {% post_url 2011-09-27-resoudre-le-cube-serpent-en-python %}

_Bash_ est mon shell par défaut. Il m'arrive d'écrire des scripts, par exemple
un [prompt pour git]({% post_url 2012-04-04-prompt-bash-pour-git %}) ou un outil
de [reverse sshfs]({% post_url 2014-06-15-sshfs-inverse-rsshfs %}).

Je m'intéresse également au développement _kernel_ : je participe au challenge
[eudyptula] sur mon temps libre _(mais avec 2 enfants, le temps libre n'existe
pas)_. Pour l'instant, j'ai validé les 8 premiers exercices sur 20.

[eudyptula]: http://eudyptula-challenge.org/

Vous trouverez sans doute d'autres choses dont je n'ai pas parlé ici dans mes
[articles] ou mes [contributions open source][contrib].

[articles]: {{ site.baseurl }}/articles/
[contrib]: {{ site.baseurl }}/contrib/


## Mes préférences

J'aime beaucoup les problèmes de multithreading et synchronisation (mutexes,
atomics, barrières mémoire, réordonnancement des instructions par le compilateur
ou le CPU…) et les conséquences des [undefined behaviors][ub].

[ub]: {% post_url 2014-10-22-comportement-indefini-et-optimisation %}

J'aime bien les casse-têtes de programmation ([1][metahanoi] [2][reveng]
[3][quines] [4][snakesolver]…).

[metahanoi]: {% post_url 2015-03-27-executer-un-algorithme-lors-de-la-compilation-templates-c %}
[reveng]: {% post_url 2015-07-21-challenge-reverse-engineering %}
[quines]: {% post_url 2011-11-14-programmes-auto-reproducteurs-quines %}

Les problèmes faisant intervenir des mathématiques sont les plus intéressants
(par exemple, j'ai beaucoup aimé trouver une solution pour [mixer][mix]
plusieurs sources audio en une seule).

J'aime moins développer des interfaces utilisateur et je suis nul en design
graphique.

Mes ordinateurs sont sous _Debian_. Je travaille sous _Linux_ (m'imposer un
poste de travail principal sous _Windows_ ou _Mac_ est rédhibitoire).


## Mes lectures informatiques

- [Thinking in Java](https://www.amazon.com/Thinking-Java-4th-Bruce-Eckel/dp/0131872486/)
- [The C Programming Language](https://www.amazon.com/Programming-Language-Brian-W-Kernighan/dp/0131103628/)
- [C++ Primer](https://www.amazon.com/Primer-5th-Stanley-B-Lippman/dp/0321714113/)
- [Effective Modern C++](https://www.amazon.com/Effective-Modern-Specific-Ways-Improve/dp/1491903996/)
- [The Rust Programming Language](https://doc.rust-lang.org/book/)
- [The Rustonomicon](https://doc.rust-lang.org/nomicon/)
- [The Art of Multiprocessor Programming](https://www.amazon.com/Art-Multiprocessor-Programming-Maurice-Herlihy/dp/0123705916/) (la partie _Principles_, soit les 6 premiers chapitres)
- [Clean Code](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882/)
- [Digital Signal Processing](https://www.amazon.com/Scientist-Engineers-Digital-Signal-Processing/dp/0966017633/)
- [Pro Git](https://git-scm.com/book/en/v2)
- [Linux Device Drivers](https://www.amazon.com/Linux-Device-Drivers-Jonathan-Corbet/dp/0596005903/)
- [Linux Kernel Development](https://www.amazon.com/Linux-Kernel-Development-Robert-Love/dp/0672329468/)
- [The Linux Programming Interface](https://www.amazon.com/Linux-Programming-Interface-System-Handbook/dp/1593272200/)
