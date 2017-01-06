---
layout: post
title: 'pspxconv : script d''encodage de vidéos pour PSP'
date: 2008-10-29 17:37:00+01:00
---

J'ai récemment acheté une PSP, et je voulais pouvoir encoder mes vidéos
facilement, avec les réglages que je voulais. J'ai donc écrit un script.

Il s'utilise comme ceci :

{% highlight bash %}
pspxconv fichier.avi fichier.mp4 500
{% endhighlight %}

si l'on veut convertir un `.avi` en `.mp4` lisible par la PSP avec un bitrate
vidéo de 500Kbps (pour l'instant le bitrate audio est fixé à 96).

Il est également possible de définir une qualité constante plutôt qu'un bitrate
moyen (préférable pour `-hurry` et `-afap`) :

{% highlight bash %}
pspxconv fichier.avi fichier.mp4 q20 -hurry
{% endhighlight %}

Plusieurs _presets_ de qualité sont disponibles :

  * `-afap` : as fast as possible (le plus rapidement possible) ; 1 seule pass
    en **mpeg4**, en 320×240, j'obtiens 190fps ;
  * `-hurry` : 1 seule passe, en **x264**, en 480×272, meilleure qualité,
    j'ai environ 95fps ;
  * `-std` (standard) : 2 pass, qualité normale (réglage à privilégier si on
    n'a pas de contraintes particulières), environ 55fps ;
  * `-hq` : 2 pass, qualité un peu supérieure, environ 38 fps ;
  * `-vhq` : extra haute qualité (inutile), environ 23 fps.

On peut aussi rajouter des options de mencoder :

{% highlight bash %}
pspxconv fichier.avi fichier.mp4 500 -std -ss 10 -endpos 100
{% endhighlight %}

n'encodera que de 10s à 110s.

{% highlight bash %}
pspxconv fichier.avi fichier.mp4 500 -std -audiofile fichier.mp3
{% endhighlight %}

encodera la vidéo `.avi` en utilisant la bande son `.mp3`.

Ce qu'il reste à améliorer (votre aide est la bienvenue) :

  * possibilité d'utiliser plusieurs processeurs à 100% en `-afap` (même si on
    met plusieurs threads, 1 seul est à 100%)
  * possibilité de choisir à partir d'un `.mkv` la piste audio et la piste de
    sous-titres à utiliser et incruster.

Voici le script :

{% highlight bash %}
#!/bin/sh
# pspxconv : video encoding script for PSP
#
# 28th october 2008 - Romain Vimont (®om)
#
# v0.4 (25th june 2009)
#
# Converts input video and audio to mp4 { x264|mpeg4 + faac }, accepted by PSP.
#
# Syntax:
#   pspxconv input_file output_mp4 video_bitrate [quality_preset]
#     [mencoder_options [...]]
#
# video_bitrate can be an integer, or can define a quantifier if it starts
#   with q : q19.5 for example.
#
# quality_preset must be one of :
#   -afap : as fast as possible (poor quality mpeg4, 1 pass, fastest)
#   -hurry : (default) quite fast (poor quality, x264, 1 pass, fast)
#   -std :  standard quality (good quality, normal)
#   -hq  : high quality (very good quality, slow)
#   -vhq : very high quality (best quality, very slow)
#
# mencoder_options are appended to command line of mencoder call.
# For example :
#   -ss 10 -endpos 56 : trim the video from 10s to 66s.
#
# (of course, there are no options -afap, -hurry, -std, -hq nor -vhq)
#

# Syntax error detected.
# Exits the program with return code 1.
syntax_error() {
    printf '%s%s\n' "Syntaxe : $0 input_file output_mp4 video_bitrate " \
      '[quality_preset] [mencoder_options [...]]' >&2
    exit 1
}

# Indicates whether the argument represents an integer.
#
# $1: value to test
# return: the integer if the argument represents an integer, an empty string if
#   it doesn't
is_integer() {
    local value="$1"
    printf %s "$1" | grep -o '^[[:digit:]]\+$'
}

# Returns the quantification value if the bitrate represents such a value.
#
# $1: value to test
# return: the value if the argument represents a quantifier, an empty string if
#   it doesn't
get_q_value() {
    local value="$1"
    printf %s "$1" | grep -o '^q[[:digit:]]\+\(\.[[:digit:]]\+\)\?$' | cut -c2-
}

# Gets a unique id, based on the clock (seconds + nanoseconds).
# 
# return: unique id
uid() {
    date +'%s%N'
}

# Indicates whether the encoder must use x264 codec.
#
# $1: preset
# return: 'yes' if it must use it, nothing otherwise
use_x264() {
    local preset="$1"
    if [ "$preset" != '-afap' ]
    then
        printf 'yes'
    fi
}

# Returns the x264opts command line arguments for the selected preset.
#
# $1: preset
# return: command line arguments
x264_preset() {
    local preset="$1"
    local common='global_header:threads=auto'
#vbv_maxrate=1536:vbv_bufsize=2000:level_idc=30
    local common_q="$common:bframes=1:b_adapt:b_pyramid:weight_b:trellis=2"
    local q_value=$(get_q_value "$q")
    if [ "$q_value" ]
    then
        printf "crf=$q_value:"
    else
        printf "bitrate=$q:"
    fi
    case "$preset" in
    '-hurry') printf "$common:me=dia:subq=1" ;;
    '-std')  printf "$common_q" ;;
    '-hq')   printf "$common_q:me=umh:subq=6" ;;
    '-vhq')  printf "$common_q:me=esa:subq=7" ;;
    esac
}

# Returns the lavcopts command line arguments for the selected preset.
#
# return: command line arguments
mpeg4_preset() {
    local q_value=$(get_q_value "$q" | sed 's/\..*$//')
    if [ "$q_value" ]
    then
        printf "vqscale=$q_value:"
    else
        printf "vbitrate=$q:"
    fi
    printf 'aglobal=1:vglobal=1'
}

# Indicates whether the preset needs 2 passes.
#
# $1: preset
# return: '2' if the selected preset have 2 passes, nothing otherwise
two_passes_preset() {
    local preset="$1"
    if [ "$preset" != '-afap' -a "$preset" != '-hurry' ]
    then
        printf 2
    fi
}

# Indicates the scale selected for the preset.
#
# $1: preset
# return: width:height scaling
scale_preset() {
    local preset="$1"
    case "$preset" in
    '-afap')  printf 'dsize=320:240:0:16,scale=0:0' ;;
    *)        printf 'dsize=480:272:0:16,scale=0:0'
    esac
}

# error when less than 3 arguments
[ $# -ge 3 ] || syntax_error

# reads the arguments
in="$1"; shift
out="$1"; shift
q="$1"; shift


# audio bitrate
ab=96

# input file must exist
if [ ! -f "$in" ]
then
    printf '%s\n' "Input file doesn't exist : $in" >&2
    exit 2
fi

# bitrate value must be integer or quantifier
if [ ! "$(is_integer "$q")" -a ! "$(get_q_value "$q")" ]
then
    printf '%s\n' "Bitrate value must be integer : $bitrate" >&2
    exit 3
fi

# gets a unique filename, in order to avoid collisions when encoding two video
# at the same time
uid=$(uid)
tmp_log=$(printf '%s' "/tmp/$uid.log")

# chooses the quality preset
quality='-hurry'
if [ "$1" = '-afap' -o "$1" = '-hurry' -o "$1" = '-std' -o "$1" = '-hq' \
  -o "$1" = '-vhq' ]
then
    quality="$1"
    shift
fi

vf=$(scale_preset "$quality")

if [ $(use_x264 "$quality") ]
then
    # gets the x264 options
    opts=$(x264_preset $quality)
    if [ $(two_passes_preset "$quality") ]
    then
        # encodes the first pass
        mencoder "$in" -o /dev/null -of lavf -lavfopts format=mp4 -passlogfile \
        "$tmp_log" -ovc x264 -x264encopts "$opts:pass=1" \
        -vf "$vf" -nosound $@ &&

        # encodes the second pass
        mencoder "$in" -o "$out" -of lavf -lavfopts format=mp4 -passlogfile \
        "$tmp_log" -ovc x264 -x264encopts "$opts:pass=2" \
        -vf "$vf" -oac faac \
        -faacopts br=$ab:raw=yes:object=2 $@ &&

        exit 0
    else
        # encodes the unique pass
        mencoder "$in" -o "$out" -of lavf -lavfopts format=mp4 -passlogfile \
        "$tmp_log" -ovc x264 -x264encopts "$opts" \
        -vf "$vf" -oac faac \
        -faacopts br=$ab:raw=yes:object=2 $@ &&

        exit 0
    fi
else
    # encodes the unique pass
    mencoder "$in" -o "$out" -of lavf -lavfopts format=mp4 -passlogfile \
    "$tmp_log" -ovc lavc -lavcopts $(mpeg4_preset) -vf "$vf" -oac faac \
    -faacopts br=$ab:raw=yes:object=2 $@ &&

    exit 0
fi
{% endhighlight %}
