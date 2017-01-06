---
layout: post
title: 'pastebinit : postez vos fichiers en ligne de commande'
date: 2008-10-11 10:18:00+01:00
---

Ceux qui vont souvent sur IRC connaissent [pastebin][], qui
permet de poster plusieurs lignes (un fichier de config par exemple) sans
flooder le chan.

[pastebin]: http://pastebin.com

Je me suis dit que ce serait bien de faire un script qui automatise tout cela,
pour envoyer son fichier en ligne de commande. Avant de le commencer, je regarde
quand même si ça n'existe pas déjà : oui, évidemment, ça existe déjà :)

Il suffit donc d'installer le paquet `pastebinit`.

Ensuite, l'utilisation est très simple :

{% highlight bash %}
pastebinit /etc/X11/xorg.conf
{% endhighlight %}

L'utilisateur par défaut est `$USER` (le nom système de l'utilisateur). Il est
possible de le changer :

{% highlight bash %}
pastebinit -a 'moi' /etc/X11/xorg.conf
{% endhighlight %}

On peut également utiliser la coloration syntaxique :

{% highlight bash %}
pastebinit -f bash /usr/bin/compiz
{% endhighlight %}

Il est possible de poster l'entrée standard :

{% highlight bash %}
pastebinit <<< 'mon premier paste'
{% endhighlight %}

Le programme donne en retour le résultat sur la sortie standard. On peut donc
ouvrir directement la page dans le navigateur :

{% highlight bash %}
xdg-open $(pastebinit /etc/X11/xorg.conf)
{% endhighlight %}
