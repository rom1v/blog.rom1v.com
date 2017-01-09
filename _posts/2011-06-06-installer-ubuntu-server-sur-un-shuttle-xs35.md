---
layout: post
title: Installer Ubuntu Server sur un Shuttle XS35
date: 2011-06-06 22:21:48+02:00
tags:
- planet-libre
- puf
---

{: .center}
![XS35]({{ site.assets }}/shuttle_xs35/XS35.jpg)

Je viens de migrer mon [auto-hébergement][] vers cette [nouvelle
machine][shuttle]. Elle est très silencieuse (il n'y a pas de ventilateur) et
consomme peu.

[auto-hébergement]: {% post_url 2009-01-31-nouveau-blog-100-libre %}
[shuttle]: http://www.shuttle.eu/fr/produits/discontinued/barebones/xs35gt/apercu/

Je n'envisageais pas d'écrire un billet, mais l'installation d'_Ubuntu Server
11.04_ ne se déroule pas sans incidents :

    Aucune interface réseau n'a été détectée

C'est le genre de problèmes qu'on espère un jour ne plus connaître lorsqu'on
installe une distribution… Surtout lorsque ce problème en provoque d'autres…
Ceci est donc un _aide-mémoire_ qui me sera utile pour une future installation.


## Installation

Tout d'abord, il faut ignorer le message d'erreur, tant pis, l'installation sera
effectuée sans réseau.

Ensuite la section _Choisir et installer des logiciels_, il ne faut surtout pas
activer `Mail` (`postfix` et `dovecot`) dans la liste : cela ferait planter le
processus d'installation car il ne trouve pas d'interface réseau. En effet, dans
`/var/log/syslog`, on trouve une erreur du genre :

    postfix/sendmail: fatal: could not find any active network interfaces

On installera donc le serveur mail plus tard.

En suivant ces conseils, l'installation doit se dérouler correctement.


## Récupération des pilotes

À partir d'un autre ordinateur, récupérer la dernière version des [sources du
pilote][driver] (j'en fait une [copie][driver-copy] chez moi, au cas où).

[driver]: ftp://driver.jmicron.com.tw/Ethernet/Linux/
[driver-copy]: http://dl.rom1v.com/drivers-shuttle-xs35/jme-1.0.7.1.tbz2

Ensuite, on est un peu embêté, car on devrait extraire les sources sur le
serveur et exécuter `sudo make install`. Sauf que `make` n'est pas installé par
défaut sur _Ubuntu Server_ (merci _Ubuntu_ !), et pour l'installer, il faut le
réseau… qu'on aura une fois qu'on aura installé les pilotes…

Heureusement, on peut s'en sortir manuellement. Pour cela, sur un ordinateur qui
possède `make` (avec le même noyau pour la même architecture), extraire les
sources de l'archive dans un répertoire et exécuter :

    tar xvjf jme-1.0.7.1.tbz2
    cd jmebp-1.0.7.1
    make

Cela crée un fichier [`jme.ko`][jme.ko] (je suis gentil, je vous donne le
fichier déjà compilé pour le noyau `2.6.38-8-server` en `amd64`). Le copier sur
une clé USB.

[jme.ko]: http://dl.rom1v.com/drivers/jme.ko


## Installation des pilotes

Ensuite, brancher la clé USB sur le serveur, et déterminer son emplacement (sous
la forme `/dev/sdX1`). Pour cela, (une technique parmi d'autres) juste après
l'avoir branchée, exécuter :

    tail /var/log/syslog

La commande doit afficher plusieurs lignes ressemblant à ceci :

    Jun  6 22:33:19 rom-server kernel: [1046971.365046] sd 12:0:0:0: [sdb] Attached SCSI removable disk

Ici, l'emplacement est donc `/dev/sdb1`.

Monter la clé :

    sudo mount /dev/sdb1 /mnt

Puis installer le pilote compilé au bon endroit et l'activer :

    sudo install -m 644 /mnt/jme.ko /lib/modules/$(uname -r)/kernel/drivers/net
    sudo modprobe jme


## Finaliser l'installation

Ajouter à la fin de `/etc/network/interfaces` :

    auto eth0
    iface eth0 inet dhcp

Et rebooter :

    sudo reboot

Normalement, la carte devrait être détectée (on peut tester avec `ifconfig`).

Si tout est OK, on peut maintenant [installer le serveur mail][mail].

[mail]: {% post_url 2009-08-16-hebergez-vos-mails-sur-ubuntu-server-et-liberez-vous %}
