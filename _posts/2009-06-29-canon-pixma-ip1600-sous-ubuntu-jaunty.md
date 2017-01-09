---
layout: post
title: Canon PIXMA iP1600 sous Ubuntu Jaunty
date: 2009-06-29 20:56:46+02:00
tags:
- planet-libre
- puf
---

_Ce billet est un petit aide-mémoire pour moi-même._

_**EDIT 30/05/2010 :** Attention, ceci ne fonctionne pas sous Ubuntu Lucid Lynx
(10.04). Je vous conseille de suivre [ce post][ubuntu-fr] car la procédure
change à chaque version…_

[ubuntu-fr]: http://forum.ubuntu-fr.org/viewtopic.php?id=61554

L'imprimante **Canon PIXMA iP1600** fonctionne avec les pilotes de l'**iP2200**,
disponible sur mon serveur ici: <http://dl.rom1v.com/ip1600/> _(normalement il
faut télécharger les paquets en `.rmp` sur le site de Canon, puis les convertir
en `.deb`, mais je me suis déjà occupé de cette étape)_.

Pour télécharger les `.deb` directement :

    wget http://dl.rom1v.com/ip1600/cnijfilter-{common,ip2200}_2.60-2_i386.deb

Ensuite il faut les installer. Pour un système 32 bits :

    sudo dpkg -i cnijfilter-*.deb

Il faut ensuite rajouter des liens car les pilotes n'utilisent pas les bonnes
versions des dépendances :

    sudo ln -s /usr/lib/libtiff.so.{4,3}
    sudo ln -s /usr/lib/libpng{12.so.0,.so.3}

Pour un système 64 bits, c'est plutôt :

    sudo dpkg -i --force-architecture cnijfilter-*.deb

et :

    sudo ln -s /usr/lib32/libtiff.so.{4,3}
    sudo ln -s /usr/lib32/libpng{12.so.0,.so.3}

Ensuite, il suffit de brancher, allumer et ajouter l'imprimante dans Système →
Administration → Impression en cliquant sur le bouton _Nouveau_.

_« **Canon iP1600** »_ doit apparaître dans la liste des périphériques, la
sélectionner et cliquer sur _Suivant_.

Choisir _« Fournir un fichier PPD »_ et sélectionner le fichier :

    /usr/share/cups/model/canonip2200.ppd

Il ne reste plus qu'à répondre à quelques questions basiques (choisir un nom
pour l'imprimante…), et voilà. Normalement, ça fonctionne !

Si des problèmes de dépendances persistent (l'imprimante n'imprime pas), cette
commande peut être utile :

    ldd /usr/local/bin/cifip2200
