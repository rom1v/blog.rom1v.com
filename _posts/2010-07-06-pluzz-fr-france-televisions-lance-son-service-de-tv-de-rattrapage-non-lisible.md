---
layout: post
title: 'Pluzz.fr : France Télévisions lance son service de TV de rattrapage non lisible'
date: 2010-07-06 14:57:11+02:00
tags:
- planet-libre
- puf
---

{: .center}
![france-televisions]({{ site.assets }}/pluzz/france_televisions.jpg)

Le 5 juillet (hier donc), _France Télévisions_ [a
lancé](http://linuxfr.org/~fabiensk/29918.html) son service de télévision de
rattrapage, **qui ne permet pas de lire les vidéos**. À moins d'accepter
d'installer un système d'exploitation particulier avec un logiciel particulier
(propriétaires évidemment). C'est comme s'ils diffusaient leurs émissions
uniquement pour les utilisateurs équipés d'une TV Sony ou Philips, et pas pour
les autres… _France Télévisions_ a simplement oublié que c'était un avant tout
un **service public**.


## Formats

La lecture des vidéos nécessite soit _Windows Media Player_, soit _Silverlight_.
C'est dommage, il aurait été préférable que leur site soit du web, accessible à
tous.

En plus de cela, les vidéos sont diffusées dans le format fermé WMV. Certaines
contiennent même des DRM. Les DRM, pour rappel, c'est ce qui empêche les
utilisateurs de lire le contenu proposé. Certains prétendent que ça permet
d'empêcher la copier ; ce n'est pas totalement faux : quand on ne peut pas lire
le contenu on ne peut pas le copier. Une autre technique plus efficace serait de
ne pas le publier du tout.

**En numérique, tout ce qui est lisible est copiable. Par contraposée, tout ce
qui n'est pas copiable n'est pas lisible.**


## outil d'accès

comme _france télévisions_ n'a pas fait son boulot d'[interopérabilité][], et
qu'a priori chacun a droit d'accéder à ce service (public !), nous sommes
obligés de nous débrouiller par nous-mêmes.

[interopérabilité]: http://fr.wikipedia.org/wiki/interop%c3%a9rabilit%c3%a9

j'ai donc écrit un petit script _bash_ qui permet d'accéder relativement
simplement à _pluzz_ à partir d'un système libre (où vlc doit être installé,
testé sur _ubuntu 10.04_). pour l'utiliser, rendez-vous sur [pluzz.fr][],
cliquez sur l'émission de votre choix, et copier l'adresse de la page (par
exemple `http://www.pluzz.fr/jt-20h.html`).

[pluzz.fr]: http://www.pluzz.fr

ensuite, pour lire la vidéo, tapez :

    pluzz play http://www.pluzz.fr/jt-20h.html

pour l'enregistrer (bah oui, tout ce qui est lisible est enregistrable) :

    pluzz record http://www.pluzz.fr/jt-20h.html


si vous voulez simplement l'url du flux :

    pluzz url http://www.pluzz.fr/jt-20h.html


Ceci ne fonctionnera que pour les vidéos sans DRM : les vidéos avec DRM ne sont
pas lisibles.

**EDIT :** _Pluzz_ a récemment changé la manière dont les vidéos sont diffusées.
Le script que j'ai proposé ici ne fonctionnera donc plus pour une majorité de
vidéos. _Chaoswizard_ en a créé [un nouveau][other], en _Python_ pour prendre
en compte ces changements.

[other]: http://forum.ubuntu-fr.org/viewtopic.php?pid=7728361#p7728361

Le plus simple maintenant est d'utiliser l'outil [`youtube-dl`][youtube-dl]
(qui, contrairement à ce que son nom pourrait laisser penser, ne télécharge pas
_que_ sur _Youtube_) :

    sudo apt-get install youtube-dl

[youtube-dl]: http://rg3.github.io/youtube-dl/


## Script

Le script est disponible sous licence [wtfpl][] sur ce dépôt _git_ : [pluzz].

[wtfpl]: http://sam.zoy.org/wtfpl/
[pluzz]: https://github.com/rom1v/pluzz

Pour fonctionner, le paquet `flvstreamer` doit être installé :

    sudo apt-get install flvstreamer


Le plus simple pour l'installer est de créer télécharger le script `pluzz` et
d'exécuter :

    sudo install pluzz /usr/local/bin


## Conclusion

Après s'être déjà fait remarqué par leur [exclusivité avec Orange][orange],
j'espère que _France Télévisions_ acceptera un jour de permettre l'accès à tous
à la télévision de rattrapage.

[orange]: http://www.numerama.com/magazine/15230-la-detestable-exclusivite-de-france-televisions-sur-orange-va-prendre-fin.html
