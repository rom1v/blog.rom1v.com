---
layout: post
title: Lecture différée de la webcam d'un Raspberry Pi
date: 2014-01-20 10:20:40+01:00
---

L'objectif de ce billet est de parvenir à lire le flux provenant de la
[caméra][] d'un [Raspberry Pi][] avec un décalage de quelques secondes (plutôt
qu'en direct), avec les outils dédiés que sont `raspivid` et `omxplayer`.

[caméra]: http://www.raspberrypi.org/camera
[raspberry pi]: https://fr.wikipedia.org/wiki/Raspberry_Pi

{: .center}
![raspi]({{ site.assets }}/delay/raspi.png)


### Contexte

Là où je travaille, il y a un babyfoot. Nous avons récemment décidé de
l'informatiser un peu pour avoir la détection et le ralenti des buts. Entre
autres, un _Raspberry Pi_ a été installé avec sa caméra au-dessus du terrain de
manière à fournir une vue aérienne.

`raspivid` permet d'afficher en direct ce que la caméra filme. Mais l'intérêt
est faible dans notre cas : le direct, nous l'avons déjà sous les yeux.

Il est bien plus utile d'avoir un "direct" différé de quelques secondes : lors
d'un but ou d'une action litigieuse, il suffit de tourner la tête pour revoir ce
qu'il vient de se passer (à vitesse réelle).

Je me suis intéressé à faire fonctionner ce cas d'usage. Je vais détailler ici
les principes et les problèmes rencontrés.


### Un simple _tube_

La première idée fut de brancher le flux [H.264][] que produit `raspivid` sur
l'entrée de `omxplayer`, qui serait démarré quelques secondes plus tard.

[H.264]: https://fr.wikipedia.org/wiki/H.264

Premier problème, `omxplayer` ne semblait pas savoir lire sur son entrée
standard. Ce n'est pas très gênant, il suffit d'utiliser un _tube nommé_ grâce à
`mkfifo`. En effet :

{% highlight bash %}
printf 'a\nb\nc\n' | grep b
{% endhighlight %}

peut être remplacé par :

{% highlight bash %}
# terminal 1
mkfifo /tmp/fifo
printf 'a\nb\nc\n' > /tmp/fifo

# terminal 2
< /tmp/fifo grep b
{% endhighlight %}

Mais en fait, il y a plus direct : `omxplayer` n'est qu'un script _wrapper_ pour
le vrai `omxplayer.bin`, c'est lui qui empêchait la lecture sur l'entrée
standard. Il suffit juste d'exporter la variable qui-va-bien et d'appeler
`omxplayer.bin` directement :

{% highlight bash %}
export LD_LIBRARY_PATH="/opt/vc/lib:/usr/lib/omxplayer"
omxplayer.bin …
{% endhighlight %}

Cependant, contrairement à ce que proposent beaucoup de commandes shell,
`omxplayer.bin` ne prévoit pas explicitement de lire sur son entrée standard, il
attend obligatoirement un fichier en paramètre. Donnons-lui donc comme fichier
`/dev/stdin` !

{% highlight bash %}
raspivid -t 0 -w 1280 -h 720 -fps 25 -n -o - | omxplayer.bin /dev/stdin
{% endhighlight %}

Vu la durée de démarrage d'`omxplayer.bin`, pas besoin de retarder son
lancement, la vidéo sera bien décalée de quelques secondes.

Le problème, c'est que le buffer lié au _tube_ est très limité (`man 7 pipe`) :
il sera très vite plein, bloquant totalement l'enregistrement et la lecture de
la vidéo.


### Avec un buffer

Nous avons besoin d'un buffer plus important. Pour cela, nous pouvons utiliser
`mbuffer`, ici avec une taille de 10Mo :

{% highlight bash %}
raspivid … | mbuffer -m 10m | omxplayer.bin /dev/stdin
{% endhighlight %}

Et là, cela semble fonctionner.

Pour décaler un peu plus la lecture par `omxplayer.bin`, il est possible
d'utiliser les _commandes groupées_ (`man bash`) pour ajouter un appel à `sleep`
avant le démarrage :

{% highlight bash %}
raspivid … | mbuffer -m 10m | { sleep 3; omxplayer.bin /dev/stdin; }
{% endhighlight %}

`raspivid` est censé enregistrer à 25 fps (`-fps 25`) et `omxplayer` nous
indique dans la console qu'il lit à 25 fps.

Cependant, en réalité, le décalage n'est pas constant : il augmente petit à
petit au fil des minutes, et le buffer se remplit légèrement plus vite qu'il ne
se vide. La lecture consomme moins d'images que n'en produit l'enregistrement,
comme si le débit d'images de l'enregistrement était supérieur à celui de
lecture.

Il y a donc un manque d'_exactitude_ (à ne pas confondre avec un manque de
[précision][]) dans le nombre d'images enregistrées et/ou lues par seconde.

[précision]: http://www.dspguide.com/ch2/7.htm

{: .center}
![accuracy_precision]({{ site.assets}}/delay/accuracy_precision.png)

Si nous tentons d'enregistrer à un débit légèrement inférieur (24 fps), c'est le
contraire : le retard est rattrapé progressivement jusqu'à fournir une lecture
en direct.

Comme le débit d'images est la seule information temporelle disponible et
qu'elle est inexacte, il semble impossible de contrecarrer cette variation de
délai.


### Information temporelle

Mais en réalité, ce n'est pas la seule information temporelle dont nous
disposons : nous savons que le flux est **en direct**.

Comment exploiter cette information ? Pour le comprendre, il suffit
d'enregistrer à un débit d'images très faible (`-fps 5`) et de le lire toujours
à 25 fps.

Si la lecture provient d'un fichier, alors la vidéo passe en accéléré. Par
contre, si la lecture sort de la webcam en direct, alors la vidéo passe à
vitesse normale mais à 5 fps : le lecteur a beau vouloir lire 25 images par
seconde, s'il n'en reçoit que 5 chaque seconde, il n'a pas d'autre choix que de
lire à 5 fps.

Ainsi, sans même connaître sa valeur réelle exacte, nous parvenons à obtenir le
même débit d'images à l'enregistrement qu'à la lecture.

Mais comme nous l'avons vu, avec un débit d'images d'enregistrement inférieur,
le délai introduit se réduira inexorablement (le retard sera rattrapé). Ce que
nous voulons éviter : nous voulons un délai **constant**.


### Delay

Nous avons cependant avancé, car maintenant, si nous disposions d'une commande
qui retarde ce qui sort de `raspivid` pour le donner à `omxplayer` _x_ secondes
plus tard, et que nous enregistrons à un débit d'images légèrement inférieur à
celui de la lecture, alors `omxplayer` rattrapera le retard pour parvenir au
direct… décalé de _x_ secondes. Exactement ce que nous voulons !

J'ai donc demandé sur [stackoverflow][] si une telle commande existait, ce qui
ne semblait pas être le cas.

[stackoverflow]: http://stackoverflow.com/questions/20979694/is-there-a-shell-command-to-delay-a-buffer

Je l'ai donc implémentée (sous licence [GPLv3][]) :

[gplv3]: https://fr.wikipedia.org/wiki/Licence_publique_g%C3%A9n%C3%A9rale_GNU

{% highlight bash %}
git clone http://git.rom1v.com/delay.git
cd delay
make && sudo make install
{% endhighlight %}

(ou sur [github](https://github.com/rom1v/delay))

Elle permet de décaler tout ce qui arrive sur `stdin` d'un délai constant pour
le sortir sur `stdout` :

    delay [-b <dtbufsize>] <delay>

Elle est donc très générique, et n'a aucun lien avec le fait que le flux soit
une vidéo.

Elle fonctionne aussi très bien pour différer la lecture de la webcam dans VLC
sur un pc classique :

{% highlight bash %}
ffmpeg -an -s 320x240 -f video4linux2 -i /dev/video0 -f mpeg2video -b 1M - |
  delay 2s | vlc -
{% endhighlight %}

Nous pourrions penser qu'il suffit de faire la même chose avec `raspivid` et
`omxplayer`, avec un débit d'images légèrement inférieur pour l'enregistrement
(24 fps) :

{% highlight bash %}
raspivid -t 0 -w 1280 -h 720 -fps 24 -n -o - |
  delay -b10m 4s |
  omxplayer.bin /dev/stdin
{% endhighlight %}

Malheureusement, avec `omxplayer`, ce n'est pas si simple.


### Initialisation immédiate

En effet, l'initialisation d'`omxplayer` pour une lecture vidéo est très longue
(plusieurs secondes), et surtout, elle ne débute que lorsque une partie
suffisamment importante de la vidéo à lire est reçue (les headers ne suffisent
pas). Décaler la vidéo de _x_ secondes décale également l'initialisation de _x_
secondes, ajoutant d'autant plus de décalage.

Certes, le retard supplémentaire sera rattrapé progressivement, mais cela
prendra du temps (environ 1 image chaque seconde, soit 1 seconde toutes les 25
secondes). Pour obtenir le délai désiré dès le départ, ce problème doit être
évité.

Une solution de contournement consiste à passer les premiers (méga-)octets
sortis de `raspivid` directement à `omxplayer.bin`, et de ne différer que le
reste avec `delay`. De cette manière, les premières images seront lues
immédiatement, permettant au lecteur de s'initialiser, alors que la suite sera
différée.

Grâce aux _commandes groupées_ de _bash_ (encore elles), c'est très simple :

{% highlight bash %}
raspivid -t 0 -w 1280 -h 720 -fps 24 -n -o - |
  { head -c10M; delay -b10m 4s; } |
  omxplayer.bin /dev/stdin
{% endhighlight %}

La commande `head` va passer immédiatement les 10 premiers méga-octets à
`omxplayer.bin`, puis la commande `delay` prendra le relai. Ainsi,
l'initialisation aura déjà eu lieu quand les premiers octets sortiront de
`delay`.

À part les premières secondes un peu chaotiques, le flux vidéo sera alors bien
diffusé en différé avec un délai constant (testé sur 24 heures).


### Conclusion

Nous avons donc bricolé une solution qui permet un replay différé en continu sur
un _Raspberry Pi_.
