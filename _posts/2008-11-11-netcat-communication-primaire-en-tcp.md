---
layout: post
title: 'netcat : communication primaire en TCP'
date: 2008-11-11 11:06:00+01:00
tags:
- planet-libre
- puf
---

Comment envoyer un bout de texte d'un pc à l'autre? Ou même un fichier?

Il y a plein de méthodes, mais parfois la plus rudimentaire fonctionne très
bien : écrire directement en TCP !

Pour cela, sur un pc (`192.168.0.1` par exemple), faites :

{% highlight bash %}
nc -l 1234
{% endhighlight %}

`-l` veut dire `listen` (ça veut dire qu'on lance un serveur)
`-p 1234` précise le port, choisissez ce que vous voulez

Sur un autre pc :

{% highlight bash %}
nc 192.168.0.1 1234
{% endhighlight %}

Et ça y'est, vous avez un tuyau de communication bidirectionnel, pratique pour
faire des copiers-collers d'un ordinateur à l'autre. Si vous ouvrez le port
correspondant sur votre routeur, ça marche aussi sur internet, bien évidemment.

L'avantage c'est que `nc` (ou `netcat`) est installé par défaut.

On peut aussi transférer des fichiers :

{% highlight bash %}
nc -l -p 1234 > monfichier
{% endhighlight %}

{% highlight bash %}
nc 192.168.0.1 1234 < unfichier
{% endhighlight %}

(terminer par `Ctrl+C`)
