---
layout: post
title: 'screex264 : réencodez vos captures d''écran vidéos (screencasts) sous Ubuntu'
date: 2008-09-12 09:11:00+01:00
---

Vous connaissez sans doute l'outil `gtk-recordmydesktop`, qui permet de faire
une capture vidéo (un screencast) de votre écran.

Pour obtenir une bonne qualité, dans les options vidéos, il faut vérifier que
"compression nulle" est bien sur l'option "Activé" (malheureusement, la
compression à la volée utilisée provoque quand même une légère perte de
qualité).

Mais une telle vidéo prend un peu de place. Je vous propose donc de la
réencoder, dans le format [x264][] (issu du projet VideoLAN, ayant donné
naissance à VLC), multiplexé dans le conteneur [MKV][].

[x264]: http://fr.wikipedia.org/wiki/X264
[mkv]: http://fr.wikipedia.org/wiki/Matroska

Le fichier ainsi généré sera lisible par exemple avec VLC.

Disons-le tout de suite, le **x264** est actuellement LE meilleur codec de
compression vidéo, proposant un rapport qualité/taille impressionnant. Le
**mkv** permet, par exemple, de contenir une piste vidéo, plusieurs pistes audio
dans différentes langues, des sous-titres, des chapitres, des pièces jointes…
Ici, ce qui nous intéresse, c'est qu'il est libre, et qu'à contenu égal, il
prend moins de place que les autres conteneurs (l'overhead est quasi-nul).

Voici comment utiliser le script.

Pour encoder la vidéo `mavideo.ogg` en `mavideo.mkv`, avec un débit de 400Kbps :

    screex264 mavideo.ogg mavideo.mkv 400

Pour encoder la vidéo `mavideo.ogg` en `mavideo.mkv`, avec un débit de 400Kbps
avec une meilleure qualité (plus lent) :

    screex264 mavideo.ogg mavideo.mkv 400 -hq

Pour encoder la vidéo `mavideo.ogg` en `mavideo.mkv`, avec un débit de 400Kbps
avec une qualité maximale (très lent) :

    screex264 mavideo.ogg mavideo.mkv 400 -vhq

Il est également possible d'utiliser n'importe quels paramètres de mencoder.
Ainsi, pour encoder la vidéo `mavideo.ogg` en `mavideo.mkv`, avec un débit de
400Kbps, à partir de la 10e seconde et pour une durée de 20 secondes :

    screex264 mavideo.ogg mavideo.mkv 400 -ss 10 -endpos 20

Ce script peut également permettre à réencoder les petites vidéos enregistrées
grâce à un appareil photo, et diviser leur taille d'un facteur 15.

Passons donc aux choses sérieuses. Tout d'abord, ce script nécessite `x264`,
`mencoder` et `mkvtoolnix` pour fonctionner.

{% highlight bash %}
#!/bin/sh
# screex264 : screencast encoding script
#
# 11th september 2008 - Romain Vimont (®om)
#
# v0.2 (26th june 2009)
#
# Converts input video (ignoring audio) to x264 video, muxed in mkv.
#
# Syntax:
#   screex264 input_file output_mkv bitrate [quality_preset]
#     [mencoder_options [...]]
#
# quality_preset must be one of :
#   -std : (default) standard quality (good quality, normal)
#   -hq  : high quality (very good quality, slow)
#   -vhq : very high quality (best quality, very slow)
#
# mencoder_options are appended to command line of mencoder call.
# For example :
#   -ss 10 -endpos 56 : trim the video from 10s to 66s.
#
# (of course, there are no options -std, -hq nor -vhq)
#

# Syntax error detected.
# Exits the program with return code 1.
syntax_error() {
   printf '%s%sn' "Syntaxe : $0 input_file output_mkv bitrate " 
     '[quality_preset] [mencoder_options [...]]' >&2
   exit 1
}

# Indicates whether the argument represents an integer.
# Returns the integer if the argument represents an integer, an empty string if
#   it doesn't.
#
# $1: value to test
is_integer() {
   local value="$1"
   printf %s "$1" | grep -o '^[[:digit:]]+$'
}

# Gets a unique id, based on the clock (seconds + nanoseconds).
# Returns a unique id.
uid() {
   date +'%s%N'
}

# Returns the command line arguments for the selected preset.
#
# $1: preset
# return: command line arguments
x264_preset() {
   local preset="$1"
   case "$preset" in
   '-std')
       printf '%s%s%s' "bitrate=$bitrate:frameref=8:mixed_refs:" 
         "bframes=3:b_adapt:b_pyramid:weight_b:partitions=all:8x8dct:" 
         "me=hex:subq=5:trellis=2:threads=auto" ;;
   '-hq')
       printf '%s%s%s' "bitrate=$bitrate:frameref=16:mixed_refs:" 
         "bframes=3:b_adapt:b_pyramid:weight_b:partitions=all:8x8dct:" 
         "me=umh:subq=6:trellis=2:threads=auto" ;;
   '-vhq')
       printf '%s%s%s' "bitrate=$bitrate:frameref=16:mixed_refs:" 
         "bframes=3:b_adapt:b_pyramid:weight_b:partitions=all:8x8dct:" 
         "me=esa:subq=7:trellis=2:threads=auto" ;;
   *)
       printf '%s%sn' 'Quality preset must be one of {-std,-hq,-vhq} : ' 
         "$preset" >&2
       exit 4
   esac
}

# error when less than 3 arguments
[ $# -ge 3 ] || syntax_error

# reads the arguments
in=$1; shift
out=$1; shift
bitrate=$1; shift

# input file must exist
if [ ! -f "$in" ]
then
   printf '%sn' "Input file doesn't exist : $in" >&2
   exit 2
fi

# bitrate value must be integer
if [ ! $(is_integer "$bitrate") ]
then
   printf '%sn' "Bitrate value must be integer : $bitrate" >&2
   exit 3
fi

# choose the quality preset
quality='-std'
if [ "$1" = '-std' -o "$1" = '-hq' -o "$1" = '-vhq' ]
then
   quality="$1"
   shift
fi

# gets the x264 options
opts=$(x264_preset $quality)

# gets a unique filename, in order to avoid collisions when encoding two video
# at the same time
uid=$(uid)
tmp_x264=$(printf '%s' "/tmp/$uid.avi")
tmp_log=$(printf '%s' "/tmp/$uid.log")

# encodes the first pass
mencoder "$in" -o /dev/null -passlogfile $tmp_log -ovc x264 -x264encopts 
 "$opts:pass=1" -nosound $@ &&

# encodes the second pass
mencoder "$in" -o $tmp_x264 -passlogfile $tmp_log -ovc x264 -x264encopts 
 "$opts:pass=2" -nosound $@ &&

# muxes the result in a mkv
mkvmerge -o "$out" -d 0 -A -S "$tmp_x264" --track-order 0:0
{% endhighlight %}

Vous pouvez maintenant faire chauffer le processeur !
