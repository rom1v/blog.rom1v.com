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

  * [`shadow`](https://github.com/rom1v/shadow) ([article][blog-shadow])
  * [`metahanoi`](https://github.com/rom1v/metahanoi) ([article][blog-metahanoi])
  * [`AImageView`](https://github.com/rom1v/AImageView)/[`AImageViewSample`](https://github.com/rom1v/AImageViewSample) ([article][blog-aimageview])
  * [`rsshfs`](https://github.com/rom1v/rsshfs) ([article][blog-rsshfs])
  * [`vlc-udp-deadlock`](https://github.com/rom1v/vlc-udp-deadlock) (sample pour ce [patch](https://mailman.videolan.org/pipermail/vlc-devel/2014-May/098020.html))
  * [`andudpxy`](https://github.com/rom1v/andudpxy)/[`andudpxy-sample`](https://github.com/rom1v/andudpxy-sample) ([article][blog-andudpxy])
  * [`mdbeamer`](https://github.com/rom1v/mdbeamer) ([article][blog-mdbeamer])
  * [`delay`](https://github.com/rom1v/delay) ([article][blog-delay])
  * [`pibuf`](https://github.com/rom1v/pibuf)
  * [`rogpoc`](https://github.com/rom1v/rogpoc) ([topic](https://groups.google.com/forum/#!topic/serval-project-developers/D4Vt7nBd_7A))
  * [`mixpoc`](https://github.com/rom1v/mixpoc) ([article][blog-mixpoc])
  * [`gitbashprompt`](https://github.com/rom1v/gitbashprompt) ([article][blog-gitbashprompt])
  * [`csnakesolver`](https://github.com/rom1v/csnakesolver) ([article][blog-csnakesolver])
  * [`snakesolver`](https://github.com/rom1v/snakesolver) ([article][blog-snakesolver])
  * [`pluzz`](https://github.com/rom1v/pluzz) ([article][blog-pluzz])

[blog-shadow]: {% post_url 2017-03-01-shadow %}
[blog-metahanoi]: {% post_url 2015-03-27-executer-un-algorithme-lors-de-la-compilation-templates-c %}
[blog-aimageview]: {% post_url 2014-10-20-aimageview-composant-android %}
[blog-rsshfs]: {% post_url 2014-06-15-sshfs-inverse-rsshfs %}
[blog-andudpxy]: {% post_url 2014-03-18-compiler-un-executable-pour-android %}
[blog-mdbeamer]: {% post_url 2014-02-15-des-slides-beamer-en-markdown %}
[blog-delay]: {% post_url 2014-01-20-lecture-differee-de-la-webcam-dun-raspberry-pi %}
[blog-mixpoc]: {% post_url 2013-01-29-le-mixage-audio %}
[blog-gitbashprompt]: {% post_url 2012-04-04-prompt-bash-pour-git %}
[blog-csnakesolver]: {% post_url 2011-10-18-resoudre-le-cube-serpent-300-fois-plus-rapidement-en-c %}
[blog-snakesolver]: {% post_url 2011-09-27-resoudre-le-cube-serpent-en-python %}
[blog-pluzz]: {% post_url 2010-07-06-pluzz-fr-france-televisions-lance-son-service-de-tv-de-rattrapage-non-lisible %}

## scrcpy

J'ai développé une application qui permet de voir et de contrôler des devices
Android à partir du PC.  
[`scrcpy`] \| [article en][blog-scrcpy]

[`scrcpy`]: https://github.com/Genymobile/scrcpy
[blog-scrcpy]: {% post_url 2018-03-08-introducing-scrcpy %}


## gnirehtet

J'ai développé un outil qui permet aux devices Android d'utiliser la connexion
du PC sur lequel ils sont branchés (_reverse tethering_), sans accès _root_.  
[`gnirehtet`] \| [article fr][blog-gnirehtet] \| [article en][gnirehtet-medium]
\| [LinuxFR][gnirehtet-linuxfr] \| [reddit][gnirehtet-reddit] \|
[Hacker News][gnirehtet-hn]

[`gnirehtet`]: https://github.com/Genymobile/gnirehtet
[blog-gnirehtet]: {% post_url 2017-03-30-gnirehtet %}
[gnirehtet-medium]: https://medium.com/genymobile/gnirehtet-reverse-tethering-android-2afacdbdaec7
[gnirehtet-linuxfr]: https://linuxfr.org/users/rom1v/journaux/du-reverse-tethering-sur-android-sans-root
[gnirehtet-reddit]: https://www.reddit.com/r/Android/comments/62lc8z/a_reverse_tethering_tool_for_android_no_root/
[gnirehtet-hn]: https://news.ycombinator.com/item?id=14011590

Je l'ai ensuite réécrit en Rust.  
[article fr][blog-gnirehtet-2-fr] \| [article en][blog-gnirehtet-2-en] \|
[annonce][gnirehtet-2-medium] \| [LinuxFR][gnirehtet-2-linuxfr] \|
[reddit][gnirehtet-2-reddit] \| [Hacker News][gnirehtet-2-hn]

[blog-gnirehtet-2-fr]: {% post_url 2017-09-21-gnirehtet-reecrit-en-rust %}
[blog-gnirehtet-2-en]: {% post_url 2017-09-21-gnirehtet-rewritten-in-rust %}
[gnirehtet-2-medium]: https://medium.com/genymobile/gnirehtet-2-our-reverse-tethering-tool-for-android-now-available-in-rust-999960483d5a
[gnirehtet-2-linuxfr]: https://linuxfr.org/users/rom1v/journaux/du-reverse-tethering-en-rust
[gnirehtet-2-reddit]: https://www.reddit.com/r/rust/comments/71ks57/gnirehtet_a_reverse_tethering_tool_for_android/
[gnirehtet-2-hn]: https://news.ycombinator.com/item?id=15326106


## Rust/mio

[mio] est une _library_ d'[I/O asynchrone] pour Rust.

J'ai supprimé l'interdiction d'enregistrer un _handle_ avec un ensemble
d'intérêts vide, étant donné que les _backends_ le supportent. Cette
modification était nécessaire au bon fonctionnement de _Gnirehtet_ :
 - [Allow registration with empty interest](https://github.com/carllerche/mio/pull/640) <em style="color: green;">mergé</em>

Quelques opérateurs manquaient sur `mio::Ready` :
 - [Implement assignment operators for Ready](https://github.com/carllerche/mio/pull/627) <em style="color: green;">mergé</em>

J'ai aussi ouvert quelques rapports de bugs :
 - [Poll.deregister() has no effect on Windows](https://github.com/carllerche/mio/issues/633)
 - [NotConnected on read() on Windows](https://github.com/carllerche/mio/issues/648)

[mio]: https://docs.rs/mio/0.6.10/mio/
[I/O asynchrone]: https://en.wikipedia.org/wiki/Asynchronous_I/O


## VLC

J'ai corrigé quelques bugs sur [VLC] :

 - [demux: avformat: fix tracks initialization to prevent
   crash](https://mailman.videolan.org/pipermail/vlc-devel/2017-November/116037.html)
   <em style="color: green;">mergé</em> ([`8a3958c`])
 - [vout: snapshot: fix snapshots
   cropping](https://mailman.videolan.org/pipermail/vlc-devel/2017-November/116070.html)
   <em style="color: green;">mergé</em> ([`7a46fc4`])
 - [qt: playlist: fix stack
   overflow](https://mailman.videolan.org/pipermail/vlc-devel/2017-November/116098.html)
   <em style="color: green;">mergé</em> ([`991ed98`])
 - [mtp: fix initialization to avoid segfault on
   close](https://mailman.videolan.org/pipermail/vlc-devel/2017-November/116145.html)
   <em style="color: green;">mergé</em> ([`3ff283b`])
 - [gui/qt: bookmarks: fix psz_name
   lifetime](https://mailman.videolan.org/pipermail/vlc-devel/2017-November/116144.html)
   <em style="color: green;">mergé</em> ([`ea0a32e`])
 - [core: fix vlc_alloc() overflow
   detection](https://mailman.videolan.org/pipermail/vlc-devel/2017-November/116171.html)
   <em style="color: green;">corrigé</em> (via [helpers][unsigned_helpers])
 - [core: fix vlc_obj_alloc_common() overflow
   detection](https://mailman.videolan.org/pipermail/vlc-devel/2017-November/116172.html)
   <em style="color: green;">corrigé</em> (via [helpers][unsigned_helpers])
 - [android: Clear list filter on small search query
   string](https://mailman.videolan.org/pipermail/android/2017-November/012398.html)
   <em style="color: green;">mergé</em> ([`ea0a32e`])


[vlc]: https://www.videolan.org/
[`8a3958c`]: http://git.videolan.org/?p=vlc.git;a=commitdiff;h=8a3958ca57d641ef17d94ade001b01c5a2e4bac7
[`7a46fc4`]: http://git.videolan.org/?p=vlc.git;a=commitdiff;h=7a46fc430b090fc6b2b32216d0d5527fcf7be68b
[`991ed98`]: http://git.videolan.org/?p=vlc.git;a=commitdiff;h=991ed989345c28d5fced32a9a5e60f0e793fcab1
[`3ff283b`]: http://git.videolan.org/?p=vlc.git;a=commitdiff;h=3ff283bc176b2dd2e83bb7cd7358eb2a0104124c
[`ea0a32e`]: http://git.videolan.org/?p=vlc.git;a=commitdiff;h=ea0a32e1c1cbf3a2ad786d46b46a3d9a449ec274
[`947cbaa`]: https://code.videolan.org/videolan/vlc-android/commit/947cbaaebd5444ffa424bbfd5383d9784f1e07de

[unsigned_helpers]: https://mailman.videolan.org/pipermail/vlc-devel/2017-November/116176.html


Il y a quelques années, j'avais corrigé un problème de [deadlock][] pouvant
survenir lors de l'arrêt d'un flux UDP sur le lecteur vidéo [VLC][] :

 - [Avoid deadlock on UDP stop](https://mailman.videolan.org/pipermail/vlc-devel/2014-May/098020.html) <em style="color: red;">refusé</em>

_Ce patch [ne corrigeait pas la cause profonde du problème][vlc-answer]. Le
deadlock se produit lorsque les appels rapprochés à deux fonctions surviennent
dans un certain ordre. Mon patch empêchait le deadlock dans ce cas. Un
développeur de VLC considère plutôt que la cause du problème est que ces deux
fonctions ne devraient jamais être appelées dans cet ordre._

[deadlock]: https://fr.wikipedia.org/wiki/Deadlock
[vlc]: http://www.videolan.org/
[vlc-answer]: https://mailman.videolan.org/pipermail/vlc-devel/2014-May/098136.html

## libusb

[libusb][] est une bibliothèque C pour communiquer sur USB.

J'ai corrigé un bug qui pouvait provoquer l'arrêt de l'event thread [udev][]
lors d'un [signal][]. En particulier, il s'arrêtait à chaque fois qu'une
[FileDialog][] [Qt][]/[QML][] était ouverte :
 - [linux_udev: Retry poll() on EINTR](https://github.com/libusb/libusb/pull/220) <em style="color: green;">mergé</em> ([`0a02d12`])
 - [mail](https://sourceforge.net/p/libusb/mailman/message/35466045/)

[`0a02d12`]: https://github.com/libusb/libusb/commit/0a02d1212bfb7ff2e9f3fc603655b0220b7d6889

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
information erronée) :
 - [Refresh AppDetails header on package state change](https://gitlab.com/fdroid/fdroidclient/merge_requests/56) <em style="color: green;">mergé</em>

L'application plantait lors de la désinstallation d'applications :
 - [Do not crash on app uninstall](https://gitlab.com/fdroid/fdroidclient/merge_requests/57) <em style="color: green;">mergé</em>

L'application pouvait également planter pour une autre raison si l'activité
avait été détruite par le système pendant l'installation ou la désinstallation
d'une application :
 - [Do not manually call onChange() (fix NPE)](https://gitlab.com/fdroid/fdroidclient/merge_requests/58) <em style="color: green;">mergé</em>


## Android Universal Image Loader

[Android-Universal-Image-Loader][] est une bibliothèque facilitant le chargement
d'images dans les applications Android.

[Android-Universal-Image-Loader]: https://github.com/nostra13/Android-Universal-Image-Loader

J'ai corrigé un bug aspect-ratio sur les images circulaires :
 - [Make circle displayer preserve aspect-ratio](https://github.com/nostra13/Android-Universal-Image-Loader/pull/1257) _non mergé_


## Android Open Source Project

J'ai rapporté un [bug][loader-bug] constaté sur l'utilisation [loaders][] lors
de la rotation d'écran, pour lequel j'ai ensuite proposé un patch :
 - [Avoid duplicate call to
   onLoadFinished()](https://android-review.googlesource.com/#/c/71461/) _non mergé_

[loader-bug]: https://code.google.com/p/android/issues/detail?id63179
[loaders]: http://developer.android.com/guide/components/loaders.html

J'ai également supprimé une petite erreur dans la documentation de
`SharedPreferences` :
 - [Remove wrong javadoc comment](https://android-review.googlesource.com/#/c/100349/) <em style="color: green;">mergé</em>



## GoogleCast

J'ai corrigé une fuite mémoire sur la [`CastCompanionLibrary`][ccl] (la
bibliothèque de Google pour communiquer avec un _chromecast_) :
 - [Remove all references to a listener (leak fix)](https://github.com/googlecast/CastCompanionLibrary-android/pull/1) <em style="color: green;">accepté</em> (mais réimplémentée par [naddaf][])

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
rend possible l'utilisation de plusieurs services simultanés :
 - [MDP sockets support](https://github.com/servalproject/serval-dna/pull/39) _première version_
 - [MDP sockets support (suite)](https://github.com/servalproject/serval-dna/pull/53) <em style="color: green;">mergé</em> (cf [`commit 954a8a0`][954a8a0])

[954a8a0]: https://github.com/servalproject/serval-dna/commit/954a8a01a4cf4164fd093dfb5a95e483c7afc704

La seconde implémente les "bindings Java" permettant d'utiliser les sockets MDP
en Java, de manière similaire à l'API socket Java.
 - [Native-part of MDP JNI
   bindings](https://github.com/servalproject/serval-dna/pull/39#commits-pushed-1b81ad7) _non mergé_
 - [Java-part of MDP JNI bindings](https://github.com/servalproject/batphone/pull/51) _non mergé_


### Talkie-walkie mesh

J'ai ensuite développé, en utilisant le protocole MDP ainsi modifié, un mode
talkie-walkie (communication _n-to-n_) sur réseau mesh :
 - [Walkie-talkie PoC](https://groups.google.com/forum/#!topic/serval-project-developers/K-EH2RxtPbs)
 - [branche](https://github.com/rom1v/batphone/commits/walkietalkie)
 - [commits principaux](https://github.com/rom1v/batphone/commits/af46c718bb76056db2c0e58abdb77d293264eeae)
 - [Walkie-talkie service](https://github.com/rom1v/batphone/commit/3059bc4cb78c3f537f5fdfcfbd9a05a67a17b7c7)

_non destiné à être mergé en l'état_


### Support du Galaxy Nexus et de la Nexus 7 avec ROM modifiée

[Thinktube][] a modifié Android pour y ajouter le support du mode ad-hoc. En
particulier, ils ont codé le pilote pour le faire fonctionner sur _Galaxy
Nexus_. Cependant, tel quel, _Serval_ ne pouvait pas piloter le wifi sur ce
téléphone. J'ai donc ajouté la "colle" manquante. J'ai ensuite fait de même pour
la _Nexus 7_ :
 - [Re: Android IBSS mode](https://groups.google.com/d/msg/serval-project-developers/JGN00i00nFk/AsDRQzWHVI0J)
 - [branche](https://github.com/rom1v/batphone/commits/gnexus)
 - [Make serval work on Android-IBSS](https://github.com/rom1v/batphone/commit/b1e00d190dfa7720fcb8e481bf4c55816c5fa4e6) _non destiné à être mergé en l'état (mais [partiellement intégré][405e915])_
 - [Fix wifi configuration for nl80211](https://github.com/servalproject/batphone/pull/85) <em style="color: green;">mergé</em>

[thinktube]: http://www.thinktube.com/android-tech/46-android-wifi-ibss
[405e915]: https://github.com/servalproject/batphone/commit/405e915397a7b5feef37c87961646273d299526d


### Parallélisation de Rhizome

Tous les traitements de Serval sont effectués dans un seul thread, ce qui pose
problème avec [Rhizome][], qui exécute des actions longues, bloquant tous
traitements liés au routage et au transfert de données.

J'ai proposé une implémentation de parallélisation de _Rhizome_ basée sur les
[threads POSIX](http://fr.wikipedia.org/wiki/Threads_POSIX) :
 - [Rhizome parallelization](https://github.com/servalproject/serval-dna/pull/68) _non mergé_

[rhizome]: http://developer.servalproject.org/dokuwiki/doku.php?id=content:tech:rhizome


### Réflexions sur Rhizome

Ce n'est pas du code, mais des réflexions sur le fonctionnement de [Rhizome][] :
 - [Reflections on Rhizome protocol](https://groups.google.com/forum/#!topic/serval-project-developers/PiVOZvhngdA)
 - [Reflections on Rhizome store-and-forward protocol](http://dl.rom1v.com/rhizome/rhizome.html)
 - [Reflections on Rhizome store-and-forward protocol (part 2)](http://dl.rom1v.com/rhizome/rhizome_2.html)


### Rhizome over Git

J'ai aussi implémenté un PoC pour explorer le principe d'implémenter la partie
stockage de _Rhizome_ par-dessus _git_ :
 - [Rhizome over git (PoC)](https://groups.google.com/forum/#!topic/serval-project-developers/D4Vt7nBd_7A)
 - [`rogpoc`](https://github.com/rom1v/rogpoc)


### Autres contributions

 * [Making it work on devices without sdcards](https://github.com/servalproject/batphone/pull/32)
   <em style="color: green;">mergé</em>
 * [Peer list concurrent access crash (fix #71)](https://github.com/servalproject/batphone/pull/81)
   <em style="color: green;">mergé</em>
 * [Fix varargs use](https://github.com/servalproject/serval-dna/pull/63)
   <em style="color: green;">mergé</em>
 * [Always notify completion](https://github.com/servalproject/batphone/pull/83)
   <em style="color: green;">mergé</em>
 * [Disconnected phones don't disappear (bugfix)](https://github.com/servalproject/serval-dna/pull/65)
  _cause [corrigée](https://github.com/servalproject/serval-dna/commit/c6241c6634088c6e9c60d7681e288821052be687)_
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
séquentielles :
 - [Serial execution in background](https://github.com/excilys/androidannotations/pull/564) <em style="color: green;">mergé</em>

J'ai ensuite ajouté la possibilité d'annuler des tâches exécutées en
arrière-plan :
 - [Cancel background tasks](https://github.com/excilys/androidannotations/pull/569) <em style="color: green;">mergé</em>
 - [Serialized @Background task cancellation bugfix](https://github.com/excilys/androidannotations/pull/624) <em style="color: green;">mergé</em>

Une régression a été introduite plus tard, que j'ai également corrigée :

 - [Fix @Background serial execution](https://github.com/androidannotations/androidannotations/pull/1803) <em style="color: green;">mergé</em>

### Intégration plus simple avec Ant

Le [wiki][aa-wiki] propose une intégration d'_AndroidAnnotations_ avec _Ant_
trop compliquée et intrusive. J'en propose une autre, plus simple et plus
naturelle :
 - [Easier AndroidAnnotation Ant integration](https://groups.google.com/forum/#!topic/androidannotations/pVIOgQ-r31g)
 - [Ant + AndroidAnnotations](http://dl.rom1v.com/androidannotations/ant.html)

[aa-wiki]: https://github.com/excilys/androidannotations/wiki/Building-Project-Ant/de46913c73dc879977f6e709da005631526e4a05


## K9mail

[K9mail][] est un client mail _Android_.

J'ai effectué une minuscule modification d'optimisation des performances :
 - [Create the database in a transaction (better performances)](https://github.com/k9mail/k-9/pull/150) <em style="color: green;">mergé</em>

[k9mail]: http://code.google.com/p/k9mail/


## MyFreeTV

Un petit logiciel pour regarder la TV avec la _Freebox_ que j'ai commis en 2005.
[myfreetv.sourceforge.net](http://myfreetv.sourceforge.net)
_maintenant obsolète_
