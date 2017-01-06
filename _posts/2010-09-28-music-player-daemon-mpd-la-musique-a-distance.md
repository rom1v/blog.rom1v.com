---
layout: post
title: 'Music Player Daemon (MPD) : la musique à distance'
date: 2010-09-28 20:25:46+02:00
---

MPD est un lecteur audio libre un peu particulier : il fonctionne suivant le
modèle client/serveur. Le serveur lit la musique, et les clients font office de
télécommande (évoluée).

Typiquement, le serveur est installé sur une machine reliée aux enceintes du
salon, et les clients sont installés sur chacun des ordinateurs et des
téléphones (ainsi que sur le serveur lui-même s'il est relié à un écran).



## Serveur


### Installation


L'installation est extrêmement simple (testé sur _Ubuntu 10.10_), il suffit
d'installer `mpd` :

    sudo apt-get install mpd


Ensuite, il y a quelques petites lignes à modifier dans le fichier de
configuration `/etc/mpd.conf`.  Tout d'abord, il faut définir le répertoire du
serveur qui contient la musique :

    music_directory                "/home/rom/Musique"


Il vaut mieux commenter la ligne `bind_to_address` (pour éviter pas mal de
problèmes) :

    #bind_to_address               "localhost"


Pour que `MPD` ne monopolise pas le son de tout le système, commenter la ligne :

    #	device		"hw:0,0"	# optional


Enfin, pour pouvoir changer le volume, décommenter la ligne :

    mixer_type                     "software"


### Mise à jour de la base de données

La première fois, et à chaque fois que de nouveaux fichiers sont ajoutés au
répertoire de musique, la base de données doit être mise à jour :

    sudo mpd --create-db


_(le serveur doit être stoppé pour mettre à jour la base de cette manière)_

**EDIT :** L'option `--create-db` n'existe plus côté serveur, la mise à jour de la
base de données doit être demandée par un client. Par exemple :

    mpc -h host update


Des logiciels clients permettent également de mettre à jour la base d'un simple
clic.

Vérifiez bien que les fichiers contenus dans ce répertoire sont bien lisibles
par tous (en particulier par l'utilisateur `mpd`). Si ce n'est pas le cas,
modifiez les droits avec :

    chmod +r -R /home/rom/Musique


### Démarrage et arrêt

Pour démarrer le serveur :

    sudo service mpd start


Pour le stopper :

    sudo service mpd stop


Il démarrera automatiquement à chaque démarrage du système.


## Clients

Les clients permettent de gérer la lecture à distance. Il est possible d'ouvrir
plusieurs clients à la fois (un sur l'ordinateur et un sur le téléphone par
exemple) qui resteront synchronisés avec le serveur. La lecture ne s'arrête pas
lors de la fermeture du client. Chaque client a juste besoin de l'IP du serveur
et du port (par défaut 6600).

Il en existe [de nombreux][] pour toutes les plateformes. Malheureusement,
beaucoup ne sont pas stables et souffrent de problèmes d'ergonomie. Globalement,
ils sont moins agréables à utiliser qu'un vrai lecteur de musique installé
localement (mais les contraintes ne sont pas les mêmes).

[clients]: http://mpd.wikia.com/wiki/Clients

Je vais en présenter deux, un pour _Gnome_ et un pour _Android_.


### Ario

Après avoir testé de nombreux clients pour PC, mon choix s'est porté sur
[Ario][] :

{: .center}
![ario]({{ site.assets }}/mpd/ario.png)

[ario]: http://ario-player.sourceforge.net/

Quelques avantages qui m'ont convaincu :

  * il est très bien intégré à _Gnome_ (y compris avec le système de
    notifications utilisé par _Ubuntu_) ;
  * il ressemble beaucoup à _Rhythmbox_ dans son utilisation ;
  * le double-clic sur un album peut être configuré pour **remplacer** la liste
    de lecture (plutôt que d'ajouter son contenu à la fin, sans le lire
    immédiatement), contrairement à de nombreux clients ;
  * l'interface ne reste pas figée systématiquement lors d'une communication
    avec le serveur ;
  * la base de données du serveur peut être mise à jour en un clic…


### DMix/MPDroid

Du côté d'_Android_, il y a beaucoup moins de clients. J'ai choisi
[DMix/MPDroid][dmix] :

{: .center}
![dmix]({{ site.assets }}/mpd/dmix.png)

[dmix]: https://github.com/abarisain/dmix/downloads

C'est un simple fichier `apk` [à installer][install].

[install]: {% post_url 2010-01-10-installer-une-application-apk-sur-android-a-partir-dun-pc %}

Dans la bibliothèque, lors de la navigation dans la liste des albums, une
pression longue ajoute l'album sélectionné à la liste de lecture alors qu'une
pression courte permet de naviguer vers les titres de l'album (pour les ajouter
un par un). Le fonctionnement est similaire pour la liste des artistes. C'est
bon à savoir ;-)


## Aller plus loin

Je me suis contenté ici de décrire les fonctionnalités de base de MPD qui me
sont utiles. Mais il est également possible de le configurer en serveur de
streaming, pour lire la musique du serveur sur un ordinateur ou un téléphone.
OpenSyd en parle à la fin d'[un récent billet][opensyd].

[opensyd]: http://blog.opensyd.fr/gerer-sa-collection-musicale-avec-mpd-et-y-acceder-en-streaming-avec-mpdroid/


## Conclusion

Ce lecteur est vraiment très pratique pour une gestion centralisée de la musique
chez soi, et permet de toujours lire la musique sur le système son du salon
(plutôt que sur la sortie audio de l'ordinateur).
