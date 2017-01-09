---
layout: post
title: 'Vidéo OGG Theora sur HTTPS (dans Firefox) : configurer Apache'
date: 2010-03-27 22:43:52+01:00
tags:
- planet-libre
- puf
---

Tout le monde a entendu parler de la balise `<video/>`, la nouveauté la plus
médiatisée d'HTML5. Le format vidéo à utiliser sur le web fait polémique
([Theora ou H264][theora-h264]) à cause de brevets logiciels, toujours bien
présents dès il s'agit de freiner l'innovation. Une situation qu'à mon avis
[seul Google peut résoudre][google-fsf]. Mais ce n'est pas l'objet de ce billet,
pour l'instant, le format, c'est _OGG Theora_. Il suffit de placer un fichier
`ogv` quelque part sur un serveur, et _Firefox_ sait la lire.

[theora-h264]: http://standblog.org/blog/post/2010/01/26/Video-Theora-ou-H264
[google-fsf]: http://www.fsf.org/blogs/community/google-free-on2-vp8-for-youtube

Un problème survient cependant dès qu'on veut y accéder sur HTTPS plutôt
qu'HTTP : on ne peut pas _seeker_ dans la vidéo (c'est-à-dire qu'on ne peut pas
déplacer le curseur pour se positionner à n'importe quel endroit), et on ne
connaît pas sa durée totale.

Quelle différence entre l'accès en HTTP et HTTPS ?

En HTTP, on reçoit la taille du fichier vidéo :

    $ curl --compressed -I http://.../video.ogv
    HTTP/1.1 200 OK
    Server: Apache
    …
    Content-Length: 26959501
    Content-Type: video/ogg

En HTTPS, on ne la reçoit pas, car le flux est compressé en _gzip_.

    $ curl --compressed -k -I https://.../video.ogv
    HTTP/1.1 200 OK
    Server: Apache
    …
    Content-Encoding: gzip
    Content-Type: video/ogg

_(`-k` permet d'autoriser l'utilisation d'un certificat SSL non reconnu)_

C'est la source du problème. Pourquoi ce comportement différent par défaut entre
HTTP et HTTPS? Je n'en sais rien (si quelqu'un peut m'éclairer…).

Par contre, il est très facile de désactiver la compression pour certains types
de fichiers, comme les images ou les vidéos (compression qui n'a de toute façon
aucun intérêt, ces fichiers sont déjà compressés).

Pour cela, il suffit de rajouter une ligne dans
`/etc/apache2/mods-available/deflate.conf` :

{% highlight apache %}
    SetEnvIfNoCase Request_URI \.(?:gif|jpe?g|png|ogg|oga|ogv)$ no-gzip dont-vary
{% endhighlight %}

et de recharger _Apache_ :

    sudo service apache reload

Et maintenant, ça fonctionne correctement sur HTTPS :

    $ curl --compressed -k -I https://.../video.ogv
    HTTP/1.1 200 OK
    Server: Apache
    …
    Content-Length: 26959501
    Content-Type: video/ogg
