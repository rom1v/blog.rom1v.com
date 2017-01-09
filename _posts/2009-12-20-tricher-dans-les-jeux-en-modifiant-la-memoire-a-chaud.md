---
layout: post
title: Tricher dans les jeux en modifiant la mémoire à chaud
date: 2009-12-20 19:52:58+01:00
tags:
- planet-libre
- puf
---

Il y a longtemps, sur la première _PlayStation_, j'avais acheté un [Action
Replay][] qui permettait de modifier la mémoire _à chaud_ pour "tricher" ou
changer le comportement d'un jeu.

[Action Replay]: http://fr.wikipedia.org/wiki/Action_Replay

Il est possible de faire la même chose sous GNU/Linux grâce à `scanmem`, qu'il
faut installer :

    sudo apt-get install scanmem

Nous allons le tester sur **Gnometris** (le Tetris-like intégré à _Gnome_) pour
exploser le record.

{: .center}
![gnometris]({{ site.assets }}/scanmem/gnometris.png)

Lançons le jeu, et récupérons son _pid_ :

    $ gnometris &
    [1] 30814

Démarrons **scanmem** avec comme paramètre le _pid_ de **Gnometris** :

    sudo scanmem 30814

_(oui, il faut être root pour lire et écrire la mémoire des autres programmes
lancés, c'est plutôt rassurant)_

On obtient un joli prompt :

    0> 

Il va falloir tout d'abord trouver où se trouve en mémoire la variable à
modifier (celle qui contient le score courant). Pour cela, c'est très simple,
vu que le score est affiché à l'écran, il suffit d'indiquer à `scanmem` sa
valeur. Pour l'instant, mon score est de 0, je rentre donc 0 :

    0> 0
    info: 01/126 searching   0x621000 -   0x623000...........ok
    info: 02/126 searching  0x1f9d000 -  0x2f4e000...........ok
    …
    info: 125/126 searching 0xe83f9000 - 0xe83fa000.ok
    info: 126/126 searching 0xdab4b000 - 0xdab67000.ok
    info: we currently have 12352024 matches.
    12352024> 

Il y a donc 12352024 variables dans la mémoire utilisée par Gnometris qui sont
à 0 (pas étonnant).

Je joue un peu, histoire de faire évoluer le score… _tac tac tac tac…_ Voilà,
j'ai 100 points (j'ai fait 2 lignes), je tape donc 100 :

    12352024> 100
    info: we currently have 36 matches.
    36> 

Il y a 36 variables qui étaient à 0 tout à l'heure et qui sont à 100
maintenant. Je rejoue, je fais 1 ligne, j'ai 140 points, je tape donc 140 :

    36> 140
    info: we currently have 1 matches.
    info: match identified, use "set" to modify value.
    info: enter "help" for other commands.

Voilà, j'ai trouvé la variable qui contient le score, maintenant je peux la
modifier :

    1> set 12345678
    info: setting *0x22e38f0 to 0xbc614e...

Rien ne se passe dans le jeu, c'est normal : pour **Gnometris**, le score n'a
pas pu changer, le label de l'interface graphique contenant le score n'a donc
pas été rafraîchi. Il suffit de gagner quelques points pour s'apercevoir que la
modification a bien été prise en compte :

{: .center}
![gnometris-cheat]({{ site.assets }}/scanmem/gnometris-cheat.png)

Ça fonctionne bien évidemment sur tous les programmes, mais c'est plus
intéressant pour les jeux :-)
