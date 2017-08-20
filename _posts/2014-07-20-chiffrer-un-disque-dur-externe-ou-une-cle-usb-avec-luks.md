---
layout: post
title: Chiffrer un disque dur externe (ou une clé USB) avec LUKS
date: 2014-07-20 21:03:33+02:00
tags:
- planet-libre
- puf
---

Un disque dur externe contenant vos données n'a pas de raisons de ne pas être
chiffré. Voici quelques commandes utiles pour l'utilisation de [LUKS][].

[luks]: http://fr.wikipedia.org/wiki/LUKS


## Prérequis

Le paquet `cryptsetup` doit être installé :

{% highlight bash %}
sudo apt-get install cryptsetup
{% endhighlight %}


## Initialisation


### Trouver le disque

Tout d'abord, il faut déterminer l'emplacement du disque dur dans `/dev`. Pour
cela, avant de le brancher, exécuter la commande :

{% highlight bash %}
sudo tail -f /var/log/messages
{% endhighlight %}

Lors du branchement du disque, plusieurs lignes similaires à celles-ci doivent
apparaître :

    Jul 20 21:25:29 pc kernel: [  678.139988] sd 7:0:0:0: [sdb] 976754645 4096-byte logical blocks: (4.00 TB/3.63 TiB)

Ici, `[sdb]` signifie que l'emplacement est `/dev/sdb`. Dans la suite, je
noterai cet emplacement `/dev/XXX`.

_Il est très important de ne pas se tromper d'emplacement, afin de ne pas
formater un autre disque…_


### Effacer le disque

Si des données étaient présentes sur ce disque, il est plus sûr de tout
supprimer physiquement :

{% highlight bash %}
sudo dd if=/dev/zero of=/dev/XXX bs=4K
{% endhighlight %}

_Cette commande peut prendre beaucoup de temps, puisqu'elle consiste à réécrire
physiquement tous les octets du disque dur._


### Créer la partition chiffrée

Pour initialiser la partition chiffrée :

{% highlight bash %}
sudo cryptsetup luksFormat -h sha256 /dev/XXX
{% endhighlight %}

La passphrase de déchiffrement sera demandée.

Maintenant que nous avons une partition chiffrée, ouvrons-la :

{% highlight bash %}
sudo cryptsetup luksOpen /dev/XXX lenomquevousvoulez
{% endhighlight %}

Cette commande crée un nouveau _device_ dans `/dev/mapper/lenomquevousvoulez`,
contenant la version déchiffrée (en direct).


### Formater

Pour formater cette partition en [ext4][] :

[ext4]: https://fr.wikipedia.org/wiki/Ext4

{% highlight bash %}
sudo mkfs.ext4 /dev/mapper/lenomquevousvoulez -L unlabel
{% endhighlight %}

Pour l'initialisation, c'est fini, nous pouvons fermer la vue déchiffrée :

{% highlight bash %}
sudo cryptsetup luksClose lenomquevousvoulez
{% endhighlight %}


## Montage manuel

Il est possible de déchiffrer et monter la partition manuellement en ligne de commande :

{% highlight bash %}
sudo cryptsetup luksOpen /dev/XXX lenomquevousvoulez
sudo mkdir -p /media/mydisk
sudo mount -t ext4 /dev/mapper/lenomquevousvoulez /media/mydisk
{% endhighlight %}

Le contenu est alors accessible dans `/media/mydisk`.

Pour démonter et fermer, c'est le contraire :
{% highlight bash %}
sudo umount /media/mydisk
sudo cryptsetup luksClose /dev/XXX lenomquevousvoulez
{% endhighlight %}

Mais c'est un peu fastidieux. Et je n'ai pas trouvé de solution pour permettre
le `luksOpen` par un utilisateur (non-root) en ligne de commande.


## Montage semi-automatique

Les environnement de bureau permettent parfois de monter un disque dur chiffré
simplement, avec la demande de la passphrase lors de l'ouverture du disque.
Voici ce que j'obtiens avec [XFCE][] :

[xfce]: http://www.xfce.org/

{: .center}
![luksOpen]({{ site.assets }}/luks/luksOpen.png)

Mais par défaut, le nom du point de montage est peu pratique : `/media/rom/ae74bc79-9efe-4325-8b4d-63d1506fa928`. Heureusement, il est possible de le changer. Pour cela, il faut déterminer le nom de la partition déchiffrée :

    $ ls /dev/mapper/luks-*
    /dev/mapper/luks-8b927433-4d4f-4636-8a76-06d18c09723e

Le nom très long correspond en fait à l'UUID du disque, qui peut aussi être récupéré grâce à :

{% highlight bash %}
sudo cryptsetup luksUUID /dev/XXX
{% endhighlight %}

ou encore :

{% highlight bash %}
sudo blkid /dev/XXX
{% endhighlight %}

L'emplacement désiré, ainsi que les options qui-vont-bien, doivent être rajoutés
dans `/etc/fstab` :

    /dev/mapper/luks-8b927433-4d4f-4636-8a76-06d18c09723e /media/mydisk ext4 user,noauto

Ainsi, le disque sera désormais monté dans `/media/mydisk`.

Si en plus, nous souhaitons spécifier un nom _user-friendly_ pour la partition
déchiffrée (celui dans `/dev/mapper/`), il faut ajouter une ligne dans
`/etc/crypttab` (en adaptant l'UUID) :

    mydisk UUID=8b927433-4d4f-4636-8a76-06d18c09723e none luks,noauto

Et utiliser celle-ci à la place dans `/etc/fstab` :

    /dev/mapper/mydisk /media/mydisk ext4 user,noauto


## Gestion des passphrases

Il est possible d'utiliser plusieurs passphrases (jusqu'à 8) pour déchiffrer le
même disque.

Pour en ajouter une :

{% highlight bash %}
sudo cryptsetup luksAddKey /dev/XXX
{% endhighlight %}

Pour en supprimer une :

{% highlight bash %}
sudo cryptsetup luksRemoveKey /dev/XXX
{% endhighlight %}

Pour changer une unique passphrase, il suffit d'en ajouter une nouvelle puis de
supprimer l'ancienne.

Ou alors d'utiliser :

{% highlight bash %}
sudo cryptsetup luksChangeKey /dev/XXX
{% endhighlight %}

mais `man cryptsetup` dit qu'il y a un risque.


## État

Pour consulter l'état d'une partition LUKS :

{% highlight bash %}
sudo cryptsetup luksDump /dev/XXX
{% endhighlight %}


## Gestion de l'en-tête

L'en-tête LUKS est écrit au début du disque. L'écraser empêche définivement le
déchiffrement de la partition.

Il est possible d'en faire une sauvegarde dans un fichier :

{% highlight bash %}
cryptsetup luksHeaderBackup /dev/XXX --header-backup-file fichier
{% endhighlight %}

Et de les restaurer :

{% highlight bash %}
cryptsetup luksHeaderRestore /dev/XXX --header-backup-file fichier
{% endhighlight %}

Pour supprimer l'en-tête (et donc rendre les données définitivement
inaccessibles s'il n'y a pas de backup) :

{% highlight bash %}
cryptsetup luksErase /dev/XXX
{% endhighlight %}


## Conclusion

Une fois configuré la première fois, et après les quelques modifications
pénibles pour choisir les noms pour le déchiffrement et le montage,
l'utilisation au quotidien est vraiment très simple : il suffit de rentrer la
passphrase directement à partir du navigateur de fichiers.
