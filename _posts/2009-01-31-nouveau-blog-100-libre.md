---
layout: post
title: Nouveau blog, 100% libre
date: 2009-01-31 13:25:05+01:00
---

Je viens de migrer mon blog hébergé sur _blogger_ vers un blog _wordpress_ sur
un serveur perso hébergé chez moi.

**Ce nouveau blog est 100% libre :**

  * il ne parle que de **logiciels libres** ;
  * la machine qui l'héberge est une eee-box avec **ubuntu server** ;
  * le serveur est **lamp** (apache, mysql, php) ;
  * le moteur de blog est **wordpress** ;
  * j'accède au serveur par **open-ssh** à partir d'une machine **ubuntu** ;
  * vous le lisez dans **firefox**…


**Les avantages :**

  * accès total (ssh, …) ;
  * pas de restriction d'espace disque ;
  * pas de problèmes de droits comme sur certains hébergeurs ;
  * les hébergeurs de blogs "connus" sont bloqués par les entreprises ;
  * wordpress est plus sympa à utiliser et plus complet que blogger…


**Les inconvénients :**

  * c'est plus long à mettre en place qu'un blog sur blogger, et ça demande un
    peu plus de connaissances ;
  * il faut avoir un serveur (j'ai acheté mon eee-box 229€) allumé 24h/24 ;
  * il faut acheter un nom de domaine (j'ai acheté le mien 15€/an chez gandi) ;
  * il faut gérer les mises à jour et les backups soi-même…


Pour les backups, si ça peut vous servir, voici mon petit script que je lance à
partir de mon pc fixe :

{% highlight bash %}
#!/bin/sh
now=$(date +'%Y%m%d-%H%M%S')
target="/tmp/wordpress_$now.sql.gz"
echo "Connecting to database..."
ssh -t rom-eeebox "mysqldump -u root -p wordpress | gzip > $target"
scp rom-eeebox:"$target" /media/gnu/backup/rom-eeebox
rdiff-backup rom-eeebox::/home/rom/blog /media/gnu/backup/rom-eeebox/blog
{% endhighlight %}


Il met le contenu de la base dans une archive, la transfert sur le pc fixe, puis
fait un backup incrémental de tout le répertoire `/var/www/blog` (tout en
gardant les anciennes versions, c'est l'intérêt de `rdiff-backup` sur `rsync`).
