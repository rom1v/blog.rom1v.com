---
layout: post
title: Installer Debian Sid
date: 2011-08-28 18:04:31+02:00
---

Je viens de migrer mon PC principal vers **Debian Sid** (_unstable_), qui
remplace **Ubuntu**, après 5 ans de bons et loyaux services.

{: .center}
![debian]({{ site.assets }}/debian/debian.png)

Il y a de nombreuses manières d'installer _Debian_, plusieurs versions, plein
d'architectures… L'objectif de cet article est de décrire l'installation _telle
que je l'ai réalisée_.

Dans l'ordre :

  * le téléchargement ;
  * la copie sur une clé USB ;
  * l'installation directe de _Sid_ à partir de la clé USB ;
  * la conservation du [_home_ chiffré mis en place par _Ubuntu_][ecryptfs] ;
  * l'installation des pilotes _NVIDIA_ et WiFi…

[ecryptfs]: {% post_url 2010-05-16-chiffrer-son-dossier-personnel-home-sous-ubuntu %}

_Bien sûr, avant tout, faites des sauvegardes de toutes vos données importantes.
Cet avertissement est sûrement inutile, j'imagine que vous faites, comme tout le
monde, plusieurs backups par semaine… ;-)_


## Téléchargement

Sur la [page d'accueil de Debian][debian], dans _"Obtenir Debian"_, c'est la
version _stable_.

Ce qui nous intéresse, c'est la version _testing_, à partir de laquelle on peut
passer en _unstable_ dès l'installation.  Celle-ci est disponible dans _"Le coin
du développeur"_, [Installateur de Debian][installer].

[debian]: http://www.debian.org/
[installer]: http://www.debian.org/devel/debian-installer/

Ici, il faut regarder la partie _"images de CD d'installation par le réseau (en
général 135 à 175 Mo) et au format carte de visite (en général 20 à 50 Mo)"_, et
cliquer sur l'architecture souhaitée. Typiquement, il faut prendre [amd64][]
pour du 64 bits et [i386][] pour du 32 bits.

[amd64]: http://cdimage.debian.org/cdimage/daily-builds/daily/arch-latest/amd64/iso-cd/
[i386]: http://cdimage.debian.org/cdimage/daily-builds/daily/arch-latest/i386/iso-cd/

Choisir l'image _businesscard_ (la plus petite). Pour moi :
[`debian-testing-amd64-businesscard.iso`][businesscard.iso].

[businesscard.iso]: http://cdimage.debian.org/cdimage/daily-builds/daily/arch-latest/amd64/iso-cd/debian-testing-amd64-businesscard.iso

**EDIT :** En fait, autant utiliser l'[iso du CD1][iso-cd1] qu'on pourra
installer sur une clé USB, l'installation n'en sera que plus rapide.

[iso-cd1]: http://cdimage.debian.org/cdimage/wheezy_di_alpha1/amd64/iso-cd/


## Clé USB


### Connaître l'emplacement

Nous avons besoin de connaître l'emplacement de la clé, sous la forme
`/dev/sdX`. Une méthode parmi d'autres est de consulter `/var/log/syslog`
lors du branchement. Pour cela, insérer la clé USB et exécuter :

    tail /var/log/syslog

Vous devriez obtenir plusieurs lignes qui ressemblent à ceci :

    Aug 28 00:54:27 rom-laptop kernel: [ 1868.930100] sd 4:0:0:0: [sdb] 2015232 512-byte logical blocks: (1.03 GB/984 MiB)

Sur cet exemple, nous voyons `[sdb]`, nous en concluons que l'emplacement de la clé est `/dev/sdb`.

Alternativement, si la clé est montée, il est possible d'obtenir cet emplacement
dans le résultat de :

    df -h

_**Ne vous trompez surtout pas d'emplacement, vous risqueriez d'écraser toutes
les données de votre disque dur !**_


### Préparer

Si vous avez une clé réservée pour vos installations de systèmes d'exploitation
(sans données à conserver), je vous conseille la méthode la plus simple, qui
écrase tout ce qu'il y a sur la clé ([4.3.1][dd]) :

    $ sudo -s
    # cat debian-testing-amd64-businesscard.iso > /dev/sdb
    # sync

[dd]: http://www.debian.org/releases/stable/amd64/ch04s03.html.fr#usb-copy-isohybrid

Ensuite, il faut redémarrer, et configurer le BIOS pour qu'il boote sur clé USB
(souvent, les clés USB sont reconnues comme un disque dur, il faut donc régler
la priorité entre les disques durs).


## Installation

Pour l'installation, l'ordinateur doit être connecté à Internet par un câble
Ethernet.

L'ordinateur boote sur la clé USB, et affiche un menu d'installation de
_Debian_. Sélectionner _"Advanced Options"_.  Ici, il est possible changer
l'environnement de bureau (_Gnome_, _KDE_, _XFCE_…). Par défaut, c'est _Gnome_.
Ensuite, sélectionner _"Expert Install"_ pour lancer l'installation (afin de
pouvoir choisir _sid/unstable_ au lieu de _testing_ dès l'installation).

{: .center}
![debian-installer]({{ site.assets }}/debian/debian-installer.png)

Lors de l'étape de partitionnement, dans l'hypothèse où le disque dur utilise
une partition séparée pour le `home`, ne pas oublier de configurer les points de
montage (`/` et `/home`), et ne pas formater `/home` (pour conserver les données
personnelles).

Utiliser le même nom d'utilisateur et mot de passe que celui d'_Ubuntu_ (c'est
important pour accéder au répertoire _home_ chiffré).

Je ne détaille pas les autres étapes d'installation, il suffit de lire.


## Déchiffrer le home

Une fois l'installation terminée et le système démarré, il n'est pas possible de
se connecter graphiquement avec le compte utilisateur, car le _home_ est chiffré
et par défaut, _eCryptFS_ n'est pas installé. Il faut donc l'installer.

Pour cela, ouvrir un TTY (_Ctrl+Alt+F1_), se connecter en `root` (ou avec le
compte utilisateur si vous avez interdit la connexion de `root`, dans ce cas
utiliser `sudo`), puis installer `ecryptfs-utils` :

    apt-get install ecryptfs-utils

Si lors de l'installation vous n'avez pas choisi le même mot de passe que sur
_Ubuntu_, profitez-en pour le rétablir :

    passwd monlogin

Maintenant, il est possible de se connecter graphiquement, en retournant dans le
TTY graphique (_Ctrl+Alt+F7_).


## Gestionnaire de composite

Pour moi, il est indispensable d'utiliser un gestionnaire de [composite][]. Pour
au moins 3 raisons :

  * éviter les trainées lors du déplacement de fenêtres ;
  * activer les ombres sous les fenêtres (très important pour le confort
    visuel) ;
  * les performances…

[composite]: http://doc.ubuntu-fr.org/tutoriel/composite

Par défaut, _Metacity_ (le gestionnaire de fenêtres de _Gnome_) n'en utilise
pas. C'est la raison pour laquelle _Compiz_ se révèle souvent indispensable.
Cependant, je viens de découvrir que _Metacity_ savait gérer le _compositing_,
grâce à une option bien cachée. Pour l'activer :

    gconftool-2 -s -t boolean /apps/metacity/general/compositing_manager true

Il est également possible d'utiliser `gconf-editor` :

{: .center}
![gconf]({{ site.assets }}/debian/gconf-editor-compositing-manager.png)

Il n'est pas configurable, et ne permet pas de faire tout ce que fait _Compiz_,
mais pour moi c'est suffisant.


## Pilotes NVIDIA

J'ai la malchance d'avoir une carte graphique _NVIDIA_, qui nécessite dans
certains cas d'avoir recours à des pilotes privateurs. Sans eux, impossible de
faire fonctionner _Compiz_ ni certains jeux.

Cependant, le pilote libre _Nouveau_ (installé par défaut) est assez
impressionnant par rapport à l'ancien (_nv_). Et même s'il ne permet pas de
démarrer _Compiz_, il supporte le _compositing_ de _Metacity_ avec de bonnes
performances.

En installant le paquet `libgl1-mesa-dri-experimental`, le pilote Nouveau_ sait
faire fonctionner _Compiz_ et surtout _Gnome-Shell_. Il faut simplement prendre
soin d'avoir supprimé toute trace éventuelle du pilote propriétaire :_

    apt-get remove nvidia-*

Pour néanmoins installer les pilotes privateurs (les dépôts `non-free` doivent
être activés) :

    apt-get install nvidia-kernel-dkms nvidia-xconfig nvidia-settings
    nvidia-xconfig

_Remplacer `nvidia-kernel-dkms` par `nvidia-kernel-legacy-_VERSION_-dkms` pour
une carte graphique nécessitant [des pilotes plus anciens][nvidia]._

[nvidia]: http://wiki.debian.org/NvidiaGraphicsDrivers#Choose_a_driver_version

Puis rebooter.


## Pilotes WiFi

J'ai également dû installer des pilotes pour ma carte WiFi :

    $ lspci | grep Network
    03:00.0 Network controller: Intel Corporation WiFi Link 5100

Il suffit d'installer le paquet non-libre `firmware-iwlwifi` :

    apt-get install firmware-iwlwifi

_Il y a plusieurs paquets en `firmware-_quelquechose_`, selon votre matériel._


## Agencement du clavier

Avec la version actuelle, _Debian Sid_ installe par défaut l'agencement du
clavier _"France (Obsolète) Autre"_ au lieu de _"France Autre"_. Je vous
conseille de le changer dans _Système → Préférences → Clavier → Agencements_,
sinon vous risquez d'avoir des surprises (notamment si vous utilisez des
[pipes][] dans un terminal)…

_**EDIT :** Cela ne suffit pas, pour que le réglage soit conservé, il faut en
fait le changer dans GDM (l'écran de connexion), une liste déroulante en bas
permet de changer la disposition du clavier._

[pipes]: http://fr.wikipedia.org/wiki/Tube_%28shell%29


## Conclusion

Avant la migration, j'avais un peu peur pour la conservation du _home_ chiffré… Mais finalement, aucun souci.

Par rapport à _Ubuntu_, j'apprécie beaucoup d'avoir des versions plus à jour des logiciels sans passer par des PPA. Et aussi d'avoir plus de logiciels dans les dépôts par défaut (`pino` par exemple). L'installation est cependant un peu moins simple qu'_Ubuntu_ (il faut avouer qu'il est difficile de faire plus simple).

Pour finir, voici une capture d'écran juste après l'installation (avec, comme le veut la tradition, un terminal ouvert) :

{: .center}
[![debian-screenshot]({{ site.assets }}/debian/debian-screenshot.thumb.jpg)][debian-screenshot]

[debian-screenshot]: {{ site.assets }}/debian/debian-screenshot.jpg
