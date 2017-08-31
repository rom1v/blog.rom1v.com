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


## Expériences

Je liste pour chaque technologie quelques expériences significatives.
Pour les projets libres, plus de détails sont disponibles sur ma page de
[contributions][].

[contributions]: /contrib

### C++

- Refonte complète d'une application C++/Qt (2 ans) (chez [Genymobile][])
- Écriture d'un [wrapper C++][] pour certaines parties de [`libusb`][libusb]
- Mécanisme d'annulation d'exécution en environnement multithread
- Utilisation de `libqi` pour la création de l'API Android du robot [Pepper][]
- Talk (interne) sur la gestion des ressources sans pointeurs en C++
  ([article][nopointers])
- [Mini-projet][meta] de métaprogrammation par templates

[genymobile]: https://www.genymobile.com/
[wrapper C++]: {% post_url 2017-01-12-cpp-sans-pointeurs %}#libusb-wrappers
[libusb]: http://libusb.info/
[pepper]: https://fr.wikipedia.org/wiki/Pepper_%28robot%29
[nopointers]: {% post_url 2017-01-12-cpp-sans-pointeurs %}
[meta]: {% post_url 2015-03-27-executer-un-algorithme-lors-de-la-compilation-templates-c %}


### C

- Modifications du protocole MDP du projet [Serval][] (équivalent UDP sur réseau
  Mesh)
- Parallélisation de [Rhizome][] pour un partage décentralisé de données
- PoC de [mixage audio][mixpoc-github] ([article][mixpoc])
- Projet [`delay`][delay-github] (un _pipe_ à retardement) ([article][delay])
- Utilisation de [`libusb`][libusb] pour _forwarder_ à partir d'un pc les
  événements HID d'une souris vers un device Android
- [Solveur][snakesolver-c-github] de serpent-cube ([article][snakesolver-c])

[serval]: /contrib#servalbatphone
[rhizome]: /contrib#paralllisation-de-rhizome
[mixpoc-github]: https://github.com/rom1v/mixpoc
[mixpoc]: {% post_url 2013-01-29-le-mixage-audio %}
[delay-github]: https://github.com/rom1v/delay
[delay]: {% post_url 2014-01-20-lecture-differee-de-la-webcam-dun-raspberry-pi %}
[snakesolver-c-github]: https://github.com/rom1v/csnakesolver
[snakesolver-c]: {% post_url 2011-10-18-resoudre-le-cube-serpent-300-fois-plus-rapidement-en-c %}


### Java/Android

Java est mon language maternel ;-)

- Développement d'une application de [_reverse tethering_][gnirehtet-contrib]
  pour Android ([article][gnirehtet])
- Conception et réalisation de l'API Android du robot [Pepper] (pour
  [Aldebaran][])
    - bindings des `promise`/`future` C++ de `libqi` en Java (à travers JNI)
    - API Android de plus haut niveau qui expose les fonctionnalités du robot
      avec simplicité (chaînage automatique d'appels asynchrones…)
- Développement d'un talkie-walkie (pour une conversation à plusieurs) avec
  [mixage audio][mix] utilisant le réseau Mesh de [Serval][]
- Intégration de cartes IGN avec [osmdroid][] (OpenStreetMap)
- Ajout de fonctionnalités d'exécution concurrente dans [AndroidAnnotations][]
- Système vidéo sur set-top box
    - modifications de la rom CyanogenMod
    - application Android de lecture vidéo (utilisant LibVLC)
    - compilation et intégration d'`udpxy` pour permettre la lecture d'un flux
      UDP multicast par le lecteur natif d'Android (voir [article][udpxy])
- Application Android de lecture vidéo provenant d'un flux UDP envoyé par une
  caméra de Raspberry Pi (qui filmait un babyfoot), supportant à la fois le
  lecteur natif et LibVLC, et à la fois sur `SurfaceView` et `TextureView` (pour
  des raisons de compatibilité avec différents devices)
- Aide à l'intégration du timeshifting vidéo dans [AOSP] (pour [ExpWay][])
- Intégration de [ChromeCast] dans l'application Android CanalPlay (Canal+)
- Analyse et modifications d'architecture du SDK de [FollowAnalytics][] pour
  corriger des bugs récurrents ou aléatoires
- Applications e-mail et Twitter pour le tableau de bord de la Renault Zoé

[gnirehtet-contrib]: /contrib#gnirehtet
[gnirehtet]: {% post_url 2017-03-30-gnirehtet %}
[Aldebaran]: https://www.ald.softbankrobotics.com/fr
[osmdroid]: https://github.com/osmdroid/osmdroid
[AndroidAnnotations]: /contrib#androidannotations
[AOSP]: https://source.android.com/
[ExpWay]: http://www.expway.com/
[ChromeCast]: https://fr.wikipedia.org/wiki/Chromecast
[FollowAnalytics]: http://followanalytics.com/
[udpxy]: {% post_url 2014-03-18-compiler-un-executable-pour-android %}
[mix]: {% post_url 2013-01-29-le-mixage-audio %}


### Python

J'utilise _Python_ de manière très occasionnelle,

- [SHAdow][shadow-github] (pour générer des collisions SHA1) ([article][shadow])
- [Solveur][snakesolver-github] de serpent-cube ([article][snakesolver])

[shadow-github]: https://github.com/rom1v/shadow
[shadow]: {% post_url 2017-03-01-shadow %}
[snakesolver-github]: https://github.com/rom1v/snakesolver
[snakesolver]: {% post_url 2011-09-27-resoudre-le-cube-serpent-en-python %}


### Bash

_Bash_ est mon shell par défaut. Il m'arrive d'écrire des scripts, en voici
quelques uns :

- [Prompt][gitbashprompt-github] pour _git_ ([article][gitbashprompt])
- [`rsshfs`][rsshfs-github] ([article][rsshfs])
- Un script de récupération et d'encodage de vidéos ([article][vod])

[gitbashprompt-github]: https://github.com/rom1v/gitbashprompt
[gitbashprompt]: {% post_url 2012-04-04-prompt-bash-pour-git %}
[rsshfs-github]: https://github.com/rom1v/rsshfs
[rsshfs]: {% post_url 2014-06-15-sshfs-inverse-rsshfs %}
[vod]: {% post_url 2010-04-24-aggreger-differentes-sources-de-vod-en-oggtheora %}


### Rust

J'ai réécrit [Gnirehtet en Rust].

J'ai également apporté quelques contributions à [mio].

[gnirehtet en Rust]: {% post_url 2017-09-21-gnirehtet-reecrit-en-rust %}
[mio]: /contrib#rustmio


### Linux Kernel

Je participe au challenge [eudyptula][] sur mon temps libre.

Pour l'instant j'ai validé les 8 premiers exercices sur 20.

[eudyptula]: http://eudyptula-challenge.org/


## Divers

J'utilise évidemment `git` au quotidien, mon shell est `bash` et mon éditeur
est `vim` (sauf pour le _Java_).

J'aime beaucoup les problèmes de synchronisation (mutexes, atomics, barrières
mémoire, réordonnancement des instructions par le compilateur ou le CPU…) et les
conséquences des [undefined behaviors][ub].

[ub]: {% post_url 2014-10-22-comportement-indefini-et-optimisation %}

J'aime bien les casse-tête de programmation ([1][reveng] [2][quines]
[3][serpent]…).

Les problèmes faisant intervenir des mathématiques sont les plus intéressants
(par exemple, j'ai beaucoup aimé trouver une solution pour [mixer][mix]
plusieurs sources audio en une seule).

[reveng]: {% post_url 2015-07-21-challenge-reverse-engineering %}
[quines]: {% post_url 2011-11-14-programmes-auto-reproducteurs-quines %}
[serpent]: {% post_url 2011-09-27-resoudre-le-cube-serpent-en-python %}

J'aime moins développer des interfaces utilisateur et je suis nul en design
graphique.

Mes ordinateurs sont sous _Debian_. Je travaille sous _Linux_ (m'imposer un
poste de travail sous _Windows_ ou _Mac_ est rédhibitoire).

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
