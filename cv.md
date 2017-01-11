---
layout: page
title: CV
date: 2016-11-25 21:25:00+01:00
order: 3
permalink: cv/
---


Je liste pour chaque technologie quelques expériences significatives.
Pour les projets libres, plus de détails sont disponibles sur ma page de
[contributions][].

[contributions]: /contrib


## C++

- Refonte complète d'une application C++/Qt (2 ans) (chez [Genymobile][])
- Écriture d'un [wrapper C++][] pour certaines parties de [`libusb`][libusb]
- Gestion générique de l'interruption de tâches asynchrones
- Utilisation de `libqi` pour la création de l'API Android du robot [Pepper][]
- Talk (interne) sur la gestion des ressources sans pointeurs en C++
- [Mini-projet][meta] de métaprogrammation par templates

[genymobile]: https://www.genymobile.com/
[wrapper C++]: {% post_url 2017-01-12-cpp-sans-pointeurs %}#libusb-wrappers
[libusb]: http://libusb.info/
[meta]: {% post_url 2015-03-27-executer-un-algorithme-lors-de-la-compilation-templates-c %}
[pepper]: https://fr.wikipedia.org/wiki/Pepper_%28robot%29


## C

- Modifications du protocole MDP et parallélisation de Rhizome (projet [Serval][])
- Projet `delay` (voir [article][delay])
- Utilisation de [`libusb`][libusb] pour _forwarder_ à partir d'un pc les événements HID d'une souris vers un device Android

[serval]: /contrib#servalbatphone
[delay]: {% post_url 2014-01-20-lecture-differee-de-la-webcam-dun-raspberry-pi %}


## Java/Android

Java est mon language maternel ;-)

- Conception et réalisation de l'API Android du robot [Pepper] (pour
  [Aldebaran][])
    - bindings des `promise`/`future` C++ de `libqi` en Java (à travers JNI)
    - API Android de plus haut niveau qui expose les fonctionnalités du robot
      avec simplicité (chaînage automatique d'appels asynchrones…)
- Développement d'un talkie-walkie (pour une conversation à plusieurs) avec
  [mixage audio][mix] utilisant le réseau Mesh de [Serval][]
- Ajout de fonctionnalités d'exécution concurrente dans [AndroidAnnotations][]
- Système vidéo sur set-top box pour une chaîne d'hôtels
    - modifications de la rom CyanogenMod
    - application Android de lecture vidéo (utilisant LibVLC)
    - compilation et intégration d'`udpxy` pour permettre la lecture d'un flux
      UDP multicast par le lecteur natif d'Android (voir [article][udpxy])

- Application Android de lecture vidéo provenant d'un flux UDP envoyé par une
  caméra de Raspberry Pi (qui filmait le babyfoot), supportant à la fois le
  lecteur natif et LibVLC, et à la fois sur `SurfaceView` et `TextureView` (pour
  des raisons de compatibilité avec différents devices)
- Aide à l'intégration du timeshifting vidéo dans AOSP (pour [ExpWay][])
- Intégration de ChromeCast dans l'application Android CanalPlay
- Analyse et modifications d'architecture du SDK de [FollowAnalytics][] pour
  corriger des bugs récurrents ou aléatoires
- Applications e-mail et Twitter pour le tableau de bord de la Renault Zoé

[Aldebaran]: https://www.ald.softbankrobotics.com/fr
[AndroidAnnotations]: /contrib#androidannotations
[ExpWay]: http://www.expway.com/
[FollowAnalytics]: http://followanalytics.com/
[udpxy]: {% post_url 2014-03-18-compiler-un-executable-pour-android %}
[mix]: {% post_url 2013-01-29-le-mixage-audio %}


## Linux Kernel

Je suis en train de participer au challenge [eudyptula][] sur mon temps libre
(pour l'instant j'ai validé les 8 premiers exercices sur 20).

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
- [The Art of Multiprocessor Programming](https://www.amazon.com/Art-Multiprocessor-Programming-Maurice-Herlihy/dp/0123705916/) (la partie _Principles_, soit les 6 premiers chapitres)
- [Clean Code](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882/)
- [Digital Signal Processing](https://www.amazon.com/Scientist-Engineers-Digital-Signal-Processing/dp/0966017633/)
- [Linux Device Drivers](https://www.amazon.com/Linux-Device-Drivers-Jonathan-Corbet/dp/0596005903/) (en cours)
- [Linux Kernel Development](https://www.amazon.com/Linux-Kernel-Development-Robert-Love/dp/0672329468/) (en cours)
