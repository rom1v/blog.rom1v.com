---
layout: page
title: Contributions
date: 2013-01-03 15:59:57+00:00
order: 2
permalink: contrib/
---

Une liste des quelques contributions que j'ai effectuées sur des projets libres,
avec une description succincte.


## Mes mini-projets

  * [`pluzz`](https://github.com/rom1v/pluzz) ([article][blog-pluzz])
  * [`snakesolver`](https://github.com/rom1v/snakesolver) ([article][blog-snakesolver])
  * [`csnakesolver`](https://github.com/rom1v/csnakesolver) ([article][blog-csnakesolver])
  * [`gitbashprompt`](https://github.com/rom1v/gitbashprompt) ([article][blog-gitbashprompt])
  * [`mixpoc`](https://github.com/rom1v/mixpoc) ([article][blog-mixpoc])
  * [`rogpoc`](https://github.com/rom1v/rogpoc) ([topic](https://groups.google.com/forum/#!topic/serval-project-developers/D4Vt7nBd_7A))
  * [`pibuf`](https://github.com/rom1v/pibuf)
  * [`delay`](https://github.com/rom1v/delay) ([article][blog-delay])
  * [`mdbeamer`](https://github.com/rom1v/mdbeamer) ([article][blog-mdbeamer])
  * [`andudpxy`](https://github.com/rom1v/andudpxy)/[`andudpxy-sample`](https://github.com/rom1v/andudpxy-sample) ([article][blog-andudpxy])
  * [`vlc-udp-deadlock`](https://github.com/rom1v/vlc-udp-deadlock) (sample pour ce [patch](https://mailman.videolan.org/pipermail/vlc-devel/2014-May/098020.html))
  * [`rsshfs`](https://github.com/rom1v/rsshfs) ([article][blog-rsshfs])
  * [`AImageView`](https://github.com/rom1v/AImageView)/[`AImageViewSample`](https://github.com/rom1v/AImageViewSample) ([article][blog-aimageview])
  * [`metahanoi`](https://github.com/rom1v/metahanoi) ([article][blog-metahanoi])
  * [`shadow`](https://github.com/rom1v/shadow) ([article][blog-shadow])

[blog-pluzz]: {% post_url 2010-07-06-pluzz-fr-france-televisions-lance-son-service-de-tv-de-rattrapage-non-lisible %}
[blog-snakesolver]: {% post_url 2011-09-27-resoudre-le-cube-serpent-en-python %}
[blog-csnakesolver]: {% post_url 2011-10-18-resoudre-le-cube-serpent-300-fois-plus-rapidement-en-c %}
[blog-gitbashprompt]: {% post_url 2012-04-04-prompt-bash-pour-git %}
[blog-mixpoc]: {% post_url 2013-01-29-le-mixage-audio %}
[blog-delay]: {% post_url 2014-01-20-lecture-differee-de-la-webcam-dun-raspberry-pi %}
[blog-mdbeamer]: {% post_url 2014-02-15-des-slides-beamer-en-markdown %}
[blog-andudpxy]: {% post_url 2014-03-18-compiler-un-executable-pour-android %}
[blog-rsshfs]: {% post_url 2014-06-15-sshfs-inverse-rsshfs %}
[blog-aimageview]: {% post_url 2014-10-20-aimageview-composant-android %}
[blog-metahanoi]: {% post_url 2015-03-27-executer-un-algorithme-lors-de-la-compilation-templates-c %}
[blog-shadow]: {% post_url 2017-03-01-shadow %}


## libusb

[libusb][] est une bibliothèque C pour communiquer sur USB.

J'ai corrigé un bug qui pouvait provoquer l'arrêt de l'event thread [udev][]
lors d'un [signal][]. En particulier, il s'arrêtait à chaque fois qu'une
[FileDialog][] [Qt][]/[QML][] était ouverte.  
[✎](https://github.com/libusb/libusb/pull/220)
[✎](https://sourceforge.net/p/libusb/mailman/message/35466045/)
<em style="color: green;">contribution acceptée et mergée</em> (cf [`commit
0a02d12`][0a02d12])

[0a02d12]: https://github.com/libusb/libusb/commit/0a02d1212bfb7ff2e9f3fc603655b0220b7d6889

[libusb]: http://libusb.info/
[usb]: https://en.wikipedia.org/wiki/USB
[udev]: https://en.wikipedia.org/wiki/Udev
[signal]: https://en.wikipedia.org/wiki/Unix_signal
[filedialog]: http://doc.qt.io/qt-5/qml-qtquick-dialogs-filedialog.html
[qt]: https://en.wikipedia.org/wiki/Qt
[qml]: https://en.wikipedia.org/wiki/QML


## F-Droid

[F-Droid][] est un _[store][]_ d'applications libres pour Android. Cet [article
LinuxFR][fdroid_linuxfr] fournit un bon résumé.

[F-Droid]: https://f-droid.org/
[store]: https://fr.wikipedia.org/wiki/Store_%28informatique%29
[fdroid_linuxfr]: http://linuxfr.org/news/plus-de-1000-applications-dans-f-droid

J'ai corrigé quelques bugs qui m'agaçaient lors de l'utilisation de
l'application.

L'état de l'application n'était pas mis à jour immédiatement lors d'une
installation ou désinstallation d'une application (l'écran affichait donc une
information erronée).  
[✎](https://gitlab.com/fdroid/fdroidclient/merge_requests/56)
<em style="color: green;">contribution acceptée et mergée</em>

L'application plantait lors de la désinstallation d'applications.  
[✎](https://gitlab.com/fdroid/fdroidclient/merge_requests/57)
<em style="color: green;">contribution acceptée et mergée</em>

L'application pouvait également planter pour une autre raison si l'activité
avait été détruite par le système pendant l'installation ou la désinstallation
d'une application.  
[✎](https://gitlab.com/fdroid/fdroidclient/merge_requests/58)
<em style="color: green;">contribution acceptée et mergée</em>


## Android Universal Image Loader

[Android-Universal-Image-Loader][] est une bibliothèque facilitant le chargement
d'images dans les applications Android.

[Android-Universal-Image-Loader]: https://github.com/nostra13/Android-Universal-Image-Loader

J'ai corrigé un bug aspect-ratio sur les images circulaires.  
[✎](https://github.com/nostra13/Android-Universal-Image-Loader/pull/1257)
_contribution en attente d'acceptation_



## VLC

J'ai corrigé un problème de [deadlock][] pouvant survenir lors de l'arrêt d'un
flux UDP sur le lecteur vidéo [VLC][].  
[✎](https://mailman.videolan.org/pipermail/vlc-devel/2014-May/098020.html)
<em style="color: red;">contribution refusée</em> ([ne corrige pas la cause
profonde du problème][vlc-answer])  
_Le deadlock se produit lorsque les appels rapprochés à deux fonctions surviennent dans un certain ordre. Mon patch empêchait le deadlock dans ce cas. Un développeur de VLC considère plutôt que la cause du problème est que ces deux fonctions ne devraient jamais être appelées dans cet ordre._

[deadlock]: https://fr.wikipedia.org/wiki/Deadlock
[vlc]: http://www.videolan.org/
[vlc-answer]: https://mailman.videolan.org/pipermail/vlc-devel/2014-May/098136.html



## Android Open Source Project

J'ai rapporté un [bug][loader-bug] constaté sur l'utilisation [loaders][] lors
de la rotation d'écran, pour lequel j'ai ensuite proposé un patch.  
[✎](https://android-review.googlesource.com/#/c/71461/)
_contribution en attente d'acceptation_

[loader-bug]: https://code.google.com/p/android/issues/detail?id63179
[loaders]: http://developer.android.com/guide/components/loaders.html

J'ai également supprimé une petite erreur dans la documentation de
`SharedPreferences`.  
[✎](https://android-review.googlesource.com/#/c/100349/)
<em style="color: green;">contribution acceptée et mergée</em>



## GoogleCast

J'ai corrigé une fuite mémoire sur la [`CastCompanionLibrary`][ccl] (la
bibliothèque de Google pour communiquer avec un _chromecast_).  
[✎](https://github.com/googlecast/CastCompanionLibrary-android/pull/1)
<em style="color: green;">contribution acceptée</em> (mais réimplémentée par
[naddaf][])

[ccl]: https://github.com/googlecast/CastCompanionLibrary-android
[naddaf]: https://github.com/googlecast/CastCompanionLibrary-android/pull/1



## Serval/Batphone

Le projet [Serval][], en particulier son
application _Batphone_ pour _Android_, a pour but de faire communiquer les
téléphones sur un réseau maillé (sans infrastructure). Voir par exemple les
articles du [Monde][serval-lemonde], de [Next Inpact][serval-nextinpact] ou de
[Korben][serval-korben].

[serval]: http://www.servalproject.org/
[serval-lemonde]: http://www.lemonde.fr/international/article/2012/04/21/le-logiciel-de-telephonie-mobile-qui-defie-le-controle-des-etats_1688852_3210.html
[serval-nextinpact]: http://www.nextinpact.com/archive/70426-serval-communications-maillage-android-sim.htm
[serval-korben]: http://korben.info/serval-rendre-les-communications-mobiles-totalement-libres.html


### Protocole MDP

J'ai effectué deux modifications concernant le protocole MDP (l'équivalent de
l'UDP sur réseau _Mesh_).

La première rend l'utilisation du MDP similaire à l'API socket standard C, et
rend possible l'utilisation de plusieurs services simultanés.  
[✎](https://github.com/servalproject/serval-dna/pull/39)
[✎](https://github.com/servalproject/serval-dna/pull/53)
<em style="color: green;">contribution acceptée et mergée</em> (cf [`commit
954a8a0`][954a8a0])

[954a8a0]: https://github.com/servalproject/serval-dna/commit/954a8a01a4cf4164fd093dfb5a95e483c7afc704

La seconde implémente les "bindings Java" permettant d'utiliser les sockets MDP
en Java, de manière similaire à l'API socket Java.  
[✎](https://github.com/servalproject/serval-dna/pull/39#commits-pushed-1b81ad7)
[✎](https://github.com/servalproject/batphone/pull/51)
_non mergée_


### Talkie-walkie mesh

J'ai ensuite développé, en utilisant le protocole MDP ainsi modifié, un mode
talkie-walkie (communication _n-to-n_) sur réseau mesh.  
[✎](https://groups.google.com/forum/#!topic/serval-project-developers/K-EH2RxtPbs)
[☞](https://github.com/rom1v/batphone/commits/walkietalkie)
([☛](https://github.com/rom1v/batphone/commits/af46c718bb76056db2c0e58abdb77d293264eeae),
[★](https://github.com/rom1v/batphone/commit/3059bc4cb78c3f537f5fdfcfbd9a05a67a17b7c7))
_non destiné à être mergé en l'état_


### Support du Galaxy Nexus et de la Nexus 7 avec ROM modifiée

[Thinktube][] a modifié Android pour y ajouter le support du mode ad-hoc. En
particulier, ils ont codé le pilote pour le faire fonctionner sur _Galaxy
Nexus_. Cependant, tel quel, _Serval_ ne pouvait pas piloter le wifi sur ce
téléphone. J'ai donc ajouté la "colle" manquante. J'ai ensuite fait de même pour
la _Nexus 7_.  
[✎](https://groups.google.com/d/msg/serval-project-developers/JGN00i00nFk/AsDRQzWHVI0J)
[☞](https://github.com/rom1v/batphone/commits/gnexus)
([★](https://github.com/rom1v/batphone/commit/b1e00d190dfa7720fcb8e481bf4c55816c5fa4e6))
_non destiné à être mergé en l'état (mais [partiellement intégré][405e915])_  
[✎](https://github.com/servalproject/batphone/pull/85)
<em style="color: green;">contribution acceptée et mergée</em>

[thinktube]: http://www.thinktube.com/android-tech/46-android-wifi-ibss
[405e915]: https://github.com/servalproject/batphone/commit/405e915397a7b5feef37c87961646273d299526d


### Parallélisation de Rhizome

Tous les traitements de Serval sont effectués dans un seul thread, ce qui pose
problème avec [Rhizome][], qui exécute des actions longues, bloquant tous
traitements liés au routage et au transfert de données.

J'ai proposé une implémentation de parallélisation de _Rhizome_ basée sur les
[threads POSIX](http://fr.wikipedia.org/wiki/Threads_POSIX).  
[✎](https://github.com/servalproject/serval-dna/pull/68)
_contribution non mergée_

[rhizome]: http://developer.servalproject.org/dokuwiki/doku.php?id=content:tech:rhizome


### Réflexions sur Rhizome

Ce n'est pas du code, mais des réflexions sur le fonctionnement de [Rhizome][].  
[✎](https://groups.google.com/forum/#!topic/serval-project-developers/PiVOZvhngdA)
[✎](http://dl.rom1v.com/rhizome/rhizome.html)
[✎](http://dl.rom1v.com/rhizome/rhizome_2.html)


### Rhizome over Git

J'ai aussi implémenté un PoC pour explorer le principe d'implémenter la partie
stockage de _Rhizome_ par-dessus _git_.  
[✎](https://groups.google.com/forum/#!topic/serval-project-developers/D4Vt7nBd_7A)
[☞](https://github.com/rom1v/rogpoc)


### Autres contributions

 * [Making it work on devices without sdcards](https://github.com/servalproject/batphone/pull/32)
   <em style="color: green;">mergée</em>
 * [Peer list concurrent access crash (fix #71)](https://github.com/servalproject/batphone/pull/81)
   <em style="color: green;">mergée</em>  
 * [Fix varargs use](https://github.com/servalproject/serval-dna/pull/63)
   <em style="color: green;">mergée</em>
 * [Always notify completion](https://github.com/servalproject/batphone/pull/83)
   <em style="color: green;">mergée</em>
 * [Disconnected phones don't disappear (bugfix)](https://github.com/servalproject/serval-dna/pull/65)
  _underlying issue [fixed](https://github.com/servalproject/serval-dna/commit/c6241c6634088c6e9c60d7681e288821052be687)_
 * [ob_dup() did not preserve source position](https://github.com/servalproject/serval-dna/pull/66)
   _annulée_


## AndroidAnnotations

[AndroidAnnotations][] est un _framework_ qui aide au développement
d'applications Android en générant du code à partir d'[annotations][] lors de la
compilation.

[AndroidAnnotations]: http://androidannotations.org/
[annotations]: http://fr.wikipedia.org/wiki/Annotation_%28informatique%29


### Annotation `@Background`

J'ai ajouté la possibilité de sérialiser, à la demande, l'exécution de tâches en
arrière-plan. Concrètement, cela permet d'écrire `@Background(serial="some_id")`
pour garantir que toutes les actions ayant le même identifiant soient
séquentielles.  
[✎](https://github.com/excilys/androidannotations/pull/564)
<em style="color: green;">contribution acceptée et mergée</em>

J'ai ensuite ajouté la possibilité d'annuler des tâches exécutées en
arrière-plan.  
[✎](https://github.com/excilys/androidannotations/pull/569)
[✎](https://github.com/excilys/androidannotations/pull/624)
<em style="color: green;">contribution acceptée et mergée</em>


### Intégration plus simple avec Ant

Le [wiki](https://github.com/excilys/androidannotations/wiki/Building-Project-Ant/de46913c73dc879977f6e709da005631526e4a05) propose une intégration d'_AndroidAnnotations_ avec _Ant_ trop compliquée et intrusive. J'en propose une autre, plus simple et plus naturelle.  
[✎](https://groups.google.com/forum/#!topic/androidannotations/pVIOgQ-r31g)
[✎](http://dl.rom1v.com/androidannotations/ant.html)


### Autres contributions

 * [@ItemClick and type parameters workaround](https://github.com/excilys/androidannotations/pull/570)
   _<span style="color: red;">refusée</span>
   ([fix yDelouis](https://github.com/excilys/androidannotations/pull/627))_
 * [Fix @Background serial execution](https://github.com/excilys/androidannotations/pull/1803) (regression)
   <em style="color: green;">mergée</em>



## K9mail

[K9mail][] est un client mail _Android_.

J'ai effectué une minuscule modification d'optimisation des performances.  
[✎](https://github.com/k9mail/k-9/pull/150)
<em style="color: green;">contribution acceptée et mergée</em>

[k9mail]: http://code.google.com/p/k9mail/



## XBMC-remote

[XBMC-remote][] est un client _Android_ pour [XBMC][].

Pour générer un programme exécutable à partir des sources, j'ai généré les
scripts de _build_ _Ant_ pour le projet et pour une bibliothèque utilisée en
dépendance.  
[✎](https://github.com/freezy/android-xbmcremote/pull/74)
[✎](https://github.com/freezy/xbmc-jsonrpclib-android/pull/4)
<em style="color: green;">contribution acceptée et mergée</em>

[xbmc-remote]: https://github.com/freezy/android-xbmcremote
[xbmc]: http://fr.wikipedia.org/wiki/XBMC_Media_Center



## MPDroid

[MPDroid][] est un client _Android_ pour [MPD][].

Pour générer un programme exécutable à partir des sources, j'ai généré les
scripts de _build_ _Ant_.  
[✎](https://github.com/abarisain/dmix/issues/97)
<em style="color: green;">contribution acceptée et mergée</em>

[mpdroid]: https://github.com/abarisain/dmix
[mpd]: {% post_url 2010-09-28-music-player-daemon-mpd-la-musique-a-distance %}



## MyFreeTV

Un petit logiciel pour regarder la TV avec la _Freebox_ que j'ai commis en 2005.
[myfreetv.sourceforge.net](http://myfreetv.sourceforge.net)
_maintenant obsolète_
