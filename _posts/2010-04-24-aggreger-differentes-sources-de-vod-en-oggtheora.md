---
layout: post
title: Agréger différentes sources de VOD en OGG/Theora
date: 2010-04-24 18:58:08+02:00
tags:
- planet-libre
---

Pour mes flux RSS, j'utilise l'outil [tt-rss][] installé sur
mon serveur, qui récupère régulièrement tous les flux auxquels je suis abonné.

[tt-rss]: http://tt-rss.org

Le but de ce billet est de mettre en place un mécanisme similaire qui s'applique
aux sources de vidéo à la demande (pas forcément prévues pour être agrégées), et
qui les convertit dans le format ouvert OGG/Theora (dans un répertoire rendu
accessible par un serveur web tel qu'_Apache_), tout en parallélisant au maximum
les différentes actions afin que le temps total de récupération soit minimal.

En particulier, il faut éviter de télécharger la première vidéo, puis de
l'encoder, d'attendre que l'encodage soit terminé pour télécharger la seconde
vidéo… Et si plusieurs CPU sont disponibles sur la machine, il faut donner un
encodage à chaque processeur (l'encodeur _theora_ ne sachant pas paralléliser
l'encodage d'une seule vidéo).


## Architecture

Pour cela, il y a donc 2 parties bien distinctes :

  * un **serveur d'encodage**, qui s'occupe du démarrage et de la
    parallélisation des encodages ;
  * des **programmes de récupération** _brute_ pour chaque source de flux, qui
    demandent au serveur d'encodage de s'occuper de la conversion des fichiers
    récupérés.


## Serveur d'encodage

### Principe

Le serveur d'encodage gère plusieurs processus _ouvriers_ (dans l'idéal, il faut
configurer pour avoir autant de processus que de CPU sur la machine). Il attend
de nouvelles tâches, et les transmet aux ouvriers disponibles, qui s'occupent de
l'encodage. Si aucun ouvrier n'est disponible, il attend qu'un se libère.

### Implémentation

Les demandes d'encodage se font grâce à un _named pipe_ (aussi appelé FIFO), un
fichier un peu spécial créé avec `mkfifo`. Chaque ligne représente une tâche.
Concrètement, une tâche est décrite par les paramètres à passer à
`ffmpeg2theora` (l'encodeur _theora_), séparés par un séparateur (j'ai choisi
`|`, qui a peu de chance d'être utilisé dans un nom de fichier). _Pour les
puristes, je vous mets au défi d'utiliser comme séparateur `\0`, tout en
conservant le mécanisme de file d'attente dans un fichier._

Un démon récupère les nouvelles lignes ajoutées au fichier, et les transmet une
à une aux ouvriers. Chaque ouvrier recrée le tableau des arguments en
redécoupant la ligne suivant le séparateur choisi, et le passe en paramètre de
`ffmpeg2theora` (en y ajoutant toujours `--nice 19` pour n'utiliser que le CPU
disponible, sans ralentir d'autres programmes en cours d'exécution).

#### Démon

Voici le programme démon (adapter le nombre de CPU)
(`/usr/sbin/ffmpeg2theora-laterd`) :

{% highlight bash %}
#!/bin/bash
CPU=2
TASKS=/tmp/ffmpeg2theora-tasks
[ -p "$TASKS" ] || mkfifo "$TASKS" -m 666
tail -f "$TASKS" | xargs -I{} -P "$CPU" ffmpeg2theora-later-job {}
{% endhighlight %}

Ce script fait donc exécuter par les ouvriers le programme
`ffmpeg2theora-later-job` pour chacune des tâches, dont voici le code
(`/usr/sbin/ffmpeg2theora-later-job`) :

{% highlight bash %}
#!/bin/bash
IFS='|' read -a args <<< "$1"
echo "executing: ffmpeg2theora ${args[@]} --nice 19"
ffmpeg2theora "${args[@]}" --nice 19
{% endhighlight %}

_Je vous conseille de prendre la dernière version de [ffmpeg2theora][],
actuellement celle des dépôts est assez ancienne._

[ffmpeg2theora]: http://v2v.cc/~j/ffmpeg2theora/

Le démon est à lancer une fois (et une seule !), au démarrage du système par
exemple (une solution est de l'ajouter dans `/etc/rc.local`).

#### Client

Les clients (les programmes qui veulent demander un encodage) doivent appeler
`ffmpeg2theora-later`, qui s'occupe d'écrire les paramètres séparés par `|` dans
le FIFO (`/usr/bin/ffmpeg2theora-later`) :

{% highlight bash %}
#!/bin/bash
printf '|%s' "$@" | cut -c2- > /tmp/ffmpeg2theora-tasks
{% endhighlight %}

Son utilisation est extrêmement proche de `ffmpeg2theora` (évidemment, puisqu'il
se contente de lui transmettre ses paramètres), à ceci près que les chemins
doivent être absolus (puisque le démon ne sait pas à partir de quel répertoire
la demande d'encodage a été effectuée).

Ainsi, là où on aurait utilisé, à partir de `/tmp` :

    ffmpeg2theora file.avi -o file.ogv -x 400 -y 300 -v 8 -a 3

on peut appeler :

    ffmpeg2theora-later /tmp/file.avi -o /tmp/file.ogv -x 400 -y 300 -v 8 -a 3


## Programmes de récupération

### Principe

Les programmes de récupération font ce qui est nécessaire pour récupérer les
vidéo à télécharger. Plusieurs outils sont bien utiles pour cela :

  * `wget` si le fichier est disponible en _HTTP_ (mais c'est rare) ;
  * `flvstreamer` pour récupérer les vidéos diffusées en _Flash_ avec des liens
    en `rtmp://` (anciennement `rtmpdump`, je vous recommande le [message][rmtp]
    adressé à _Adobe_ de la part du développeur originel) ;
  * `mimms` pour récupérer les vidéos diffusées en _WMV_ avec des liens en
    `mms://`.

[rmtp]: http://lkcl.net/rtmp/

Pensez bien à ouvrir les ports nécessaires pour récupérer les vidéos (1935 par
défaut pour les liens RTMP, 1755 pour MMS…).

### Implémentation

Afin de rendre un peu indépendants les répertoires manipulés, j'ai décidé de
créer un script `/usr/bin/vodget` qui appelle les programmes de récupération
avec 2 paramètres :

  1. le répertoire de téléchargement ;
  2. le répertoire destination.


{% highlight bash %}
#!/bin/bash
scripts_dir=/var/lib/vodget
script="$scripts_dir/$1"
download_dir=/tmp/vodget
target_dir=/var/www/vod
$script "$download_dir" "$target_dir"
{% endhighlight %}

Les programmes de récupération sont stockés dans `/var/lib/vodget`.

#### Exemple


Voici un exemple qui récupère les guignols de l'info (Canal+)
(`/var/lib/vodget/guignols`) :

{% highlight bash %}
#!/bin/bash
category=guignols
download_dir="$1/$category"
target_dir="$2/$category"
mkdir -p "$download_dir"
mkdir -p "$target_dir"
wget -O- http://www.canalplus.fr/rest/bootstrap.php?/bigplayer/search/guignols | grep -o 'rtmp://[^<]\+.mp4' | while read url
do
    filename="$(echo "$url" | sed 's/.*\([0-9]\{2\}\)\([0-9]\{2\}\)\([0-9]\{2\}\).*/20\1-\2-\3/')"
    if [ ! -f "$target_dir/$filename.ogv" ]
    then
        flvstreamer -r "$url" -o "$download_dir/$filename.mp4"
        touch "$target_dir/$filename.ogv"
        ffmpeg2theora-later "$download_dir/$filename.mp4" -o "$target_dir/$filename.ogv" -v8 -a3
    fi
done
{% endhighlight %}


Cet exemple est une implémentation qui a l'avantage d'être très courte, vous
pouvez aussi adapter [des versions plus évoluées][ubuntu-fr] pour qu'elles
utilisent `ffmpeg2theora-later`.

[ubuntu-fr]: http://forum.ubuntu-fr.org/viewtopic.php?id=346586

Un simple appel à :

    vodget guignols

récupèrera les nouveaux épisodes et les encodera en OGG/Theora.

Il ne restera plus qu'à se rendre sur la page HTTP pointant sur le répertoire
des vidéos avec [un navigateur][firefox] qui supporte le HTML5 et le codec
OGG/Theora, pour pouvoir regarder les vidéos ainsi récupérées :

[firefox]: http://mozilla-europe.org/fr/firefox/

{: .center}
![vod-guignols]({{ site.assets }}/vod_oggtheora/vod-guignols.png)

_Bien sûr, les vidéos récupérées qui ne sont pas sous licence libre sont à usage
personnel. Cela permet de regarder en VOD les épisodes dans un format ouvert,
qui ne nécessite pas de programme propriétaire, ces vidéos ne doivent pas être
placées sur un serveur public._


### Démarrage programmé

Pour automatiser tout cela, il est possible de programmer périodiquement la
récupération des nouvelles vidéos grâce à [cron][]. Pour cela :

[cron]: http://doc.ubuntu-fr.org/cron

    crontab -e

et ajouter la ligne _qui-va-bien_. Par exemple, pour récupérer les nouveaux
épisodes des guignols tous les jours à 23 heures :

    00 23 * * * vodget guignols


### Améliorations

Techniquement, il faudrait gérer le démon par un script _init.d_, mais ça n'est
pas si simple (si on arrête le service alors qu'une vidéo est en cours
d'encodage et qu'on le redémarre, le nombre de CPU à utiliser ne sera plus
respecté…).

Si vous êtes motivés, il est également possible de faire un beau site qui
permette de regarder les vidéos en VOD, plutôt qu'une page qui liste simplement
les fichiers récupérés.


## Conclusion

Les différentes vidéos que je suis susceptible de regarder en VOD (que je ne
regardais pas avant) sont maintenant disponibles sur mon serveur, lisible
directement par mon navigateur.

On peut imaginer de nombreuses sources à aggréger :

  * les sites de VOD des chaînes de télévision (Canal+, France5, M6…) ;
  * des bandes-annonces cinéma ;
  * des chaînes enregistrées en direct avec la TV sur ADSL ;
  * le flux de l'Assemblée Nationale ou du Sénat ;
  * …

Bien sûr, on aimerait mieux que les différentes sources fournissent des flux RSS
pointant vers leurs vidéos, qu'ils diffuseraient eux-même en OGG/Theora. Mais on
peut toujours attendre…
