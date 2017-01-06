---
layout: post
title: Créer un serveur HTTP en 10 secondes sur Ubuntu grâce à Python
date: 2009-12-06 15:22:39+01:00
---

Il suffit d'aller dans le répertoire à partager et d'exécuter :

    $ python -m SimpleHTTPServer
    Serving HTTP on 0.0.0.0 port 8000 ...

Le répertoire sera chrooté et accessible sur `http://localhost:8000`.

Par défaut, le port 8000 est utilisé, mais on peut le changer :

    $ python -m SimpleHTTPServer 1234
    Serving HTTP on 0.0.0.0 port 1234 ...

Pour les ports inférieurs à 1024, il faut être _root_ :

    $ sudo python -m SimpleHTTPServer 80
    Serving HTTP on 0.0.0.0 port 80 ...

Si le port correspondant est ouvert sur le routeur, il sera également
accessible de l'extérieur. Pratique pour partager rapidement du contenu…
