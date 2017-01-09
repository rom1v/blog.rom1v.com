---
layout: post
title: Améliorer les performances nvidia sous compiz-fusion
date: 2008-10-07 12:22:00+01:00
tags:
- planet-libre
- puf
---

Ce billet s'adresse aux possesseurs de carte graphique **nvidia** qui utilisent
**compiz-fusion**.

Tout d'abord, pour que `compiz` prennent en compte les paramètres de
`nvidia-settings` au démarrage (vblank, anti-aliasing,
anisotropic…), il faut que la commande `nvidia-settings -l` soit exécutée
**avant** le lancement de compiz.

Pour cela, j'ai proposé un [patch][] qui consiste à rajouter un fichier dans
`/etc/X11/Xsession.d`, en espérant qu'il soit intégré à la version finale
d'*Intrepid*.

[patch]: https://bugs.launchpad.net/ubuntu/+bug/215876/comments/6

En attendant, le plus simple est de rajouter la ligne dans `~/.gnomerc` (créer
le fichier s'il n'existe pas) :

    nvidia-settings -l

*NB: l'anti-aliasing provoque [quelques problèmes][] avec compiz.*

[quelques problèmes]: https://bugs.launchpad.net/ubuntu/+bug/279571

Maintenant, que la configuration de `nvidia-settings` est prise en compte,
optimisons les performances.

Pour cela, toujours dans `~/.gnomerc`, il faut rajouter la ligne :

    nvidia-settings -a InitialPixmapPlacement=2 -a GlyphCache=1

Et dans `/etc/X11/xorg.conf`, dans la `Section "Device"` correspondant à la
carte graphique, il faut rajouter :

        Option     "PixmapCacheSize" "1000000"
        Option     "AllowSHMPixmaps" "0"

ce qui donne, chez moi :

    Section "Device"
        Identifier "Configured Video Device"
        Driver     "nvidia"
        Option     "NoLogo" "True"
        Option     "PixmapCacheSize" "1000000"
        Option     "AllowSHMPixmaps" "0"
    EndSection

puis redémarrer le serveur X.

Rassurez-vous, je n'ai pas inventé toutes ces modifications, elles sont
**fortement** recommandées par **nvidia**, en attendant d'avoir les bonnes
valeurs par défaut dans une version future. Plus d'infos [ici][nvnews].

[nvnews]: http://www.nvnews.net/vbulletin/showthread.php?t=118088

Voici une amélioration directe suite à ces modifications : dans `ccsm`, si dans
le plugin "redimensionner la fenêtre" le mode de redimensionnement est sur
l'option "Normal", les redimensionnements de fenêtres sont très très lents.
C'est pour cela que j'utilisais plutôt le mode "Stretch".

Après les modifications, les performances sont beaucoup plus correctes (ça n'est
pas parfait, mais c'est déjà ça).
