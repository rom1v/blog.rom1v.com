---
layout: post
title: Optimiser la taille des fichiers png
date: 2008-08-30 10:02:00+01:00
---

L'outil `optipng` permet d'optimiser les fichiers *png* : les compresser
davantage sans aucune perte de qualité.

Pour l'utiliser, c'est très simple :

    optipng image.png


Très pratique pour les captures d'écran que l'on veut poster sur Internet.

Voici un exemple de résultat :

    $ optipng screenshot.png
    OptiPNG 0.5.5: Advanced PNG optimizer.
    Copyright (C) 2001-2007 Cosmin Truta.
    
    ** Processing: screenshot.png
    1196x688 8-bit RGB-alpha non-interlaced
    The image is losslessly reduced to 8-bit RGB
    Input IDAT size = 117762 bytes
    Input file size = 118005 bytes
    Trying...
      zc = 9  zm = 8  zs = 0  f = 0  IDAT size = 81686
      zc = 9  zm = 8  zs = 1  f = 0  IDAT too big
      zc = 1  zm = 8  zs = 2  f = 0  IDAT too big
      zc = 9  zm = 8  zs = 3  f = 0  IDAT too big
      zc = 9  zm = 8  zs = 0  f = 5  IDAT too big
      zc = 9  zm = 8  zs = 1  f = 5  IDAT too big
      zc = 1  zm = 8  zs = 2  f = 5  IDAT too big
      zc = 9  zm = 8  zs = 3  f = 5  IDAT too big
    
    Selecting parameters:
      zc = 9  zm = 8  zs = 0  f = 0  IDAT size = 81686
    
    Output IDAT size = 81686 bytes (36076 bytes decrease)
    Output file size = 81761 bytes (36244 bytes = 30.71% decrease)


Un peu plus de 30% de gain, ça n'est pas négligeable.

Et pour en faire un [script nautilus], mettez ce script dans
`~/.gnome2/nautilus-scripts/optipng` que vous rendez exécutable :

[script nautilus]: http://doc.ubuntu-fr.org/nautilus_scripts

{% highlight bash %}
#!/bin/sh
# Optimizes PNG files.
#
# 30th august 2008 - Romain Vimont (®om)
#

# Use only n as field separator
IFS='
'

# Calls optipng with all args
optipng $NAUTILUS_SCRIPT_SELECTED_FILE_PATHS
{% endhighlight %}

Combiné avec [imagup][], c'est très pratique pour poster des captures d'écran
sur un forum.

[imagup]: {% post_url 2008-08-29-imagup-uploader-une-image-en-2-clics %}
