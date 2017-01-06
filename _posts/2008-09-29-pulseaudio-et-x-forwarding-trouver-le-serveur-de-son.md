---
layout: post
title: 'PulseAudio et X-forwarding : trouver le serveur de son'
date: 2008-09-29 17:55:00+01:00
---

J'avais d'abord présenté comment exécuter un lecteur audio à distance, en ayant
son affichage localement ([présentation de SSH][ssh], chapitre 5).

[ssh]: {% post_url 2008-08-27-presentation-de-ssh %}

J'ai ensuite présenté comment [rediriger le son vers un autre PC][redirect]
(grâce à PulseAudio).

[redirect]: {% post_url 2008-09-14-utilisez-une-sortie-son-dun-autre-pc-avec-ubuntu-804 %}

Le problème, c'est qu'avec **PulseAudio**, l'exécution d'un lecteur audio à
distance avec l'affichage en local ne fonctionne plus : le son ne sort nulle
part, même si à l'écran tout a l'air de fonctionner :

    ssh monserveur -XC rhythmbox

En effet, la variable d'environnement `PULSE_SERVER` n'étant pas affectée lors
d'un `ssh -X`, le lecteur ne trouve pas de serveur audio.

La solution propre serait de rajouter une ligne dans `~/.pulse/client.conf` :

    echo "default-server={$HOSTNAME}unix:/tmp/pulse-$USER/native" >> ~/.pulse/client.conf

Mais un bug de *PulseAudio*, corrigé dans la version 0.9.12, fait que cette
solution ne fonctionne pas. Et malheureusement, cette version ne devrait pas
apparaître dans Ubuntu avant la version 9.04.

Une solution consiste donc à l'initialiser lors du lancement du lecteur audio :

    ssh monserveur -XC 'PULSE_SERVER="{$HOSTNAME}unix:/tmp/pulse-$USER/native" rhythmbox'

Si vraiment l'on veut éviter d'affecter cette variable à chaque fois
manuellement, on peut rajouter à la fin du fichier `/etc/ssh/sshd_config` la
ligne suivante :

    PermitUserEnvironment yes

puis, toujours **sur le serveur**, définir la variable d'environment dans le
fichier `~/.ssh/environment` :

    echo "PULSE_SERVER={$HOSTNAME}unix:/tmp/pulse-$USER/native" >> ~/.ssh/environment

Après avoir redémarré le serveur :

    sudo /etc/init.d/ssh restart

il est possible de lancer sur le client :

    ssh monserveur -XC rhythmbox

Le son ne sera plus perdu dans une faille de l'espace-temps :-)
