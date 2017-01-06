---
layout: post
title: Modifier la luminosité d'une vidéo dans avconv (ffmpeg)
date: 2012-08-14 12:26:38+02:00
---

Pour partager des vidéos capturées par mon appareil photo, je les convertissais
jusqu'alors en [Ogg/Theora][] grâce à [ffmpeg2theora][]. Ce format
(contrairement au [H264][]) est libre et lisible nativement par _Firefox_, y
compris par la version mobile.

[ogg/theora]: http://fr.wikipedia.org/wiki/Theora
[ffmpeg2theora]: http://v2v.cc/~j/ffmpeg2theora/
[h264]: http://fr.wikipedia.org/wiki/H.264

Mais j'envisage depuis longtemps de passer à [WebM][] (le format [libéré par
_Google_][linuxfr] il y a un peu plus de deux ans), plus performant, lui aussi
lu nativement par _Firefox_ (et [par d'autres][webm-impl]). Pour cela, je vais
utiliser [avconv][].

[webm]: http://fr.wikipedia.org/wiki/WebM
[linuxfr]: http://linuxfr.org/news/webm-un-format-libre-et-ouvert-pour-html5
[webm-impl]: http://fr.wikipedia.org/wiki/WebM#Mises_en_.C5.93uvre
[avconv]: http://libav.org/avconv.html


## avconv

Qu'est-ce qu'_avconv_ ? Le meilleur moyen de le savoir est d'exécuter `ffmpeg`
sans arguments :

    $ ffmpeg
    …
    *** THIS PROGRAM IS DEPRECATED ***
    This program is only provided for compatibility and will be removed in a future release. Please use avconv instead.

**EDIT :** En fait, c'est plus [compliqué][ffmpeg-libav] que ça.

[ffmpeg-libav]: http://blog.pkh.me/p/13-the-ffmpeg-libav-situation.html

J'ai précisé [ffmpeg][] dans le titre du billet car je pense que c'est encore
sous ce nom que l'outil est le plus connu. Le contenu de ce billet s'applique
aussi bien à _ffmpeg_ qu'à _avconv_.

[ffmpeg]: http://ffmpeg.org/


## Filtres

Il m'arrive d'avoir besoin d'appliquer des filtres très simples : typiquement,
augmenter la luminosité d'une vidéo. `ffmpeg2theora` permet de le faire
directement grâce à l'option
[`-B`][man ffmpeg2theora].

Mais on a beau chercher dans [`man avconv`][man avconv], on ne trouve rien.

[man ffmpeg2theora]: https://manpages.debian.org/cgi-bin/man.cgi?query=ffmpeg2theora
[man avconv]: https://manpages.debian.org/cgi-bin/man.cgi?query=avconv


## frei0r

C'est là qu'intervient le projet [frei0r][]. Il s'agit d'une [API][] permettant
d'appliquer des filtres vidéo que chaque application pourra utiliser. Et ça
tombe bien, l'application `avconv` peut l'utiliser si elle a été compilée avec
l'option `--enable-frei0r`.

[frei0r]: http://en.wikipedia.org/wiki/Frei0r
[api]: http://fr.wikipedia.org/wiki/Interface_de_programmation

La bonne nouvelle, c'est que la version distribuée par [_Debian wheezy_][wheezy]
est compilée avec cette option. La mauvaise, c'est que celle fournie dans
[_Ubuntu 12.04_][ubuntu] ne l'est pas.

[wheezy]: http://www.debian.org/releases/wheezy/
[ubuntu]: http://doc.ubuntu-fr.org/precise

_frei0r_ a besoin de _plugins_ permettant d'appliquer les filtres. Il est
nécessaire pour cela d'installer `frei0r-plugins` :

    sudo apt-get install frei0r-plugins


## avconv + frei0r

La [syntaxe][] pour utiliser _frei0r_ dans _avconv_ est la suivante :

    -vf frei0r=<filter_name>[{:|=}<param1>:<param2>:...:<paramN>]

[syntaxe]: http://ffmpeg.org/ffmpeg.html#frei0r-1

Mais comment connaître les `filter_name`s disponibles et leurs paramètres ? Je
n'ai trouvé aucune documentation à ce sujet. J'ai donc consulté les
[sources][].

[sources]: http://code.dyne.org/frei0r/tree/src/filter

À partir de là, on comprend facilement que la luminosité est modifiée par le
filtre [brightness][brightness.c] comportant un paramètre de [type][] `double`
(voir notamment la fonction `f0r_get_param_info`) compris entre `0` (sombre) et
`1` (clair) (`0.5` étant la luminosité de la vidéo d'origine).

[brightness.c]: http://code.dyne.org/frei0r/tree/src/filter/brightness/brightness.c
[type]: http://fr.wikipedia.org/wiki/C_%28langage%29#Types

En respectant la syntaxe, cela donne par exemple :

    avconv -i video.mts -s 640x360 -ac 1 -q:a 2 -b:v 600k \
        -vf frei0r=brightness:0.6 output.webm

Il existe plein d'autres filtres. Par exemple pour le contraste, c'est
[contrast0r][contrast0r.c] :

    -vf frei0r=contrast0r:0.4

[contrast0r.c]: http://code.dyne.org/frei0r/tree/src/filter/contrast0r/contrast0r.c

Pour les combiner, il suffit de concaténer plusieurs blocs `-vf` :

    -vf frei0r=brightness:0.6,frei0r=contrast0r:0.4


## Conclusion

Malgré les apparences, _avconv_ permet, pour peu qu'il soit compilé avec
_l'option-qui-va-bien_, d'encoder des vidéos en modifiant la luminosité, le
contraste, et d'appliquer bien d'autres filtres… ce qui est très pratique.
