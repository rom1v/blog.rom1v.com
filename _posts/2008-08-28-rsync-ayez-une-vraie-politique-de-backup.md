---
layout: post
title: 'rsync : ayez une vraie politique de backup !'
date: 2008-08-28 20:07:00+01:00
tags:
- planet-libre
- puf
---

Ça vous est déjà arrivé de perdre un disque dur ?

 - « eh zut, mes photos depuis 5 ans sont perdues »
 - « arf, mon projet à rendre la semaine prochaine »

Eh bien moi non, pas encore (trop chanceux!). Et pour éviter que ça se produise,
j'ai mis en place une petite politique de backup, très simple.

Alors évidemment, j'avais déjà fait des "backups" : quand un répertoire est
important, je le copie autre part. Et quand il est modifié, je supprime l'ancien
backup et je le recopie de nouveau (ou j'écrase l'ancien par le nouveau).  Mais
par exemple pour le répertoire de photos ou autre, quand ça prend plus de 5Gio,
c'est beaucoup trop long.

Voici donc comment faire un backup "incrémental" : seuls les fichiers modifiés,
ajoutés ou supprimés seront modifiés côté "backup".  Et en plus, ça marche aussi
à distance (on peut _backuper_ un répertoire dans un autre sur la même machine,
mais également sur une machine différente, par **ssh**).

Voici par exemple le script que j'utilise pour faire un backup de mon portable
dans un répertoire de mon fixe :

{% highlight bash %}
#!/bin/sh
cmd='rsync -rpltv --del'

backup_dir='rom-desktop:/media/gnu/backup/rom-laptop'
$cmd \
/home/rom/work \
/home/rom/java \
/home/rom/docs \
/home/rom/.thunderbird \
/home/rom/.mozilla \
/home/rom/sh \
$backup_dir
{% endhighlight %}

_(`.mozilla` et `.thunderbird`, c'est la configuration de **firefox** et
**thunderbird**, ainsi que tous les favoris, mails, comptes…)_

Et du fixe vers le fixe (d'un disque dur vers un autre) :

{% highlight bash %}
#!/bin/sh
cmd='rsync -rpltv --del'

backup_dir='/media/gnu/backup/rom-desktop'
$cmd \
/media/tux/photos \
$backup_dir
{% endhighlight %}

Plus d'infos sur les paramètres de rsync :

  * `-r` parcours le dossier indiqué et tous ses sous-dossiers (récursivement)
  * `-p` préserve les droits
  * `-l` copie les liens symboliques comme liens symboliques
  * `-t` préserve les dates (important pour les photos)
  * `-v` plus verbeux
  * `--del` permet de supprimer les fichiers sur _destination_ qui n'existent
    plus sur _source_

`$cmd` c'est la commande `rsync` avec ses paramètres.

`$backup_dir` c'est la destination :

  * `machine:/répertoire` si distant
  * `/répertoire` si local

Ensuite, vous pouvez lancer le script de temps en temps, manuellement (ce que je
fais) ou programmé (la nuit ?).
