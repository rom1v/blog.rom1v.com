---
layout: post
title: 'Le format de compression 7zip : impressionnant !'
date: 2008-08-27 08:30:00+02:00
tags:
- planet-libre
- puf
---

J'en avais déjà entendu parler, je l'ai déjà utilisé juste comme ça, mais
vraiment il mérite d'être connu ce format **7z**.

J'ai voulu compresser un dossier de 1.1Go (différents types de fichiers), je
l'ai testé en `.tar.gz`, `.tar.bz2` et en `.7z` :

  * `.tar.gz` : 776Mio
  * `.tar.bz2` : 768Mio
  * `.7z` : 412Mio

La compression `.tar.gz` est la plus rapide, `.7z` est plus lent, mais la
différence de ratio est impressionnante.

Un [journal][] datant de septembre 2006 en parle.

[journal]: http://linuxfr.org/%7ESnarky/22754.html

Il suffit d'installer le paquet `p7zip`(pour le `.7z`) ou `p7zip-full` (pour
gérer d'autres formats en plus, comme `rar`, etc.).

_N'en installez qu'un des deux, sinon vous aurez 2 fois l'entrée `.7z` dans le
gestionnaire d'archives._

Pour compresser :

    7zr a archive.7z fichier1 fichier2 …

Pour décompresser :

    7zr e archive.7z

Si c'est le paquet `p7zip-full` au lieu de `p7zip`, c'est `7z` au lieu de `7zr`.

Une fois installé, le format est géré dans le gestionnaire d'archives.

Espérons que ce paquet soit installé par défaut dans **Ubuntu 8.10** (car sinon
ce n'est pas facile de poster des `.7z`, il faut dire aux gens d'installer un
logiciel en plus).

J'ai fait une demande sur [launchpad][] en ce sens.

[launchpad]: https://bugs.launchpad.net/ubuntu/+bug/261117
