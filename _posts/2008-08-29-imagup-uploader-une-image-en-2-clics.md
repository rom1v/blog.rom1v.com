---
layout: post
title: 'imagup : uploader une image en 2 clics'
date: 2008-08-29 13:27:00+01:00
tags:
- planet-libre
- puf
---

J'ai écrit un petit script pour uploader en ligne de commande une image sur
[imagup][], et récupérer le lien (pratique pour poster sur les forums).

[imagup]: http://www.imagup.com/

Voici comment l'utiliser :

    imagup monimage.jpg

Les extensions `jpg`, `jpeg`, `png` et `gif` sont autorisées.

Voici un exemple de résultat :

    $ imagup rom-avatar.png
    % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                   Dload  Upload   Total   Spent    Left  Speed
    100 28706    0 14129  100 14577   3638   3754  0:00:03  0:00:03 --:--:--  5059
    rom-avatar.png : http://uploads.imagup.com/05/1220023287_rom-avatar.png

Il est possible d'uploader plusieurs images en une seule ligne :

    imagup image1.png image2.jpg

Avec l'option `-open`, une fois l'image uploadée, elle est ouverte dans le
navigateur par défaut.

(`curl` doit être installé.)

Voici le script :

{% highlight bash %}
#!/bin/sh
# IMAGUP script
#
# 29th august 2008 - Romain Vimont (®om)
#
# Uploads images to www.imagup.com, and returns the http:// link.
#
# Syntax:
#   imagup [-open] fichier1 [fichier2 [...]]
#
# If -open is specified, the link is opened in the default associated program
# (the default browser).
#

# Syntax error detected
# Exits the program with return code 1.
syntax_error() {
    printf '%sn' "Syntaxe : $0 [-open] fichier1 [fichier2 [...]]" >&2
    exit 1
}

# Bad extension detected. Prints messages on stderr.
#
# $1: bad extension
bad_extension() {
    local extension="$1"
    printf '%sn' "Extension not supported: $extension" >&2
    printf '%sn' "Must be one of {png, jpg, jpeg, gif}." >&2
}

# Returns the canonical name of the file, from its (full) path.
#
# $1: (full) path of the file
# return: canonical name of the file
#
# example: get_filename 'a/b.c/d.e.f.jpg' returns 'd.e.f.jpg'
get_filename() {
    local path="$1"
    printf %s "$path" | grep -o '[^/]+$'
}

# Returns the radical of a filename (its name without the extension), from its
# (full) path.
#
# $1: (full) path of the file
# return: radical of the file
#
# example: get_radical 'a/b.c/d.e.f.jpg' returns 'd.e.f'
get_radical() {
    local path="$1"
    local filename="$(get_filename "$path")"
    printf %s "$filename" | sed 's/.[^.]*$//'
}

# Returns the extension of a file, from its (full) path.
#
# $1: (full) path of the file
# return: extension of the file
#
# example: get_extension 'a/b.c/d.e.f.jpg' returns 'jpg'
get_extension() {
    local path="$1"
    local filename="$(get_filename "$path")"
    printf %s "$filename" | grep -o '[^.]+$'
}

# Converts a String to lower case.
#
# $1: input text
# return: lower cased text
#
# example: to_lower_case 'AbCdE' returns 'abcde'
to_lower_case() {
    local text="$1"
    printf %s "$text" | tr -s [A-Z] [a-z]
}

# error when no arguments
[ $# -ge 1 ] || syntax_error

if [ "$1" = '-open' ]
then
    # -open is enabled
    open=true
    shift
    # should remain other arguments
    [ $# -ge 1 ] || syntax_error
fi

# for each argument (a file to upload)
for path
do
    extension=$(get_extension "$path")
    ext=$(to_lower_case "$extension")
    # extention must be one of {png, jpg, jpeg, gif}
    if [ "$ext" = png -o "$ext" = jpg -o "$ext" = jpeg -o "$ext" = gif ]
    then
        filename=$(get_filename "$path")
        radical=$(get_radical "$path")
        # uploads and gets the url back
        url=$(curl www.imagup.com -F 
          "fichier=@$path;filename=$radical.$ext;type=image/$ext" |
        grep image-upload |
        grep -o "http://[[:alpha:]]+.imagup.com/[^"]+.(png|jpg|jpeg|gif)")
        if [ "$url" ]
        then
            # if it worked, prints the url
            printf '%sn' "$path : $url"
            # if -open is selected, open-it in the default application
            [ $open ] && xdg-open "$url"
        else
            # it didn't work
            printf '%sn' "Problem while uploading $path" >&2
        fi
    else
        # file extension is bad
        bad_extension "$extension"
    fi
done
{% endhighlight %}

Une fois installé, vous pouvez également l'utiliser comme [script nautilus][].
Mettez le script suivant dans `~/.gnome2/nautilus-script/imagup-wrapper` et
rendez-le exécutable :

[script nautilus]: http://doc.ubuntu-fr.org/nautilus_scripts

{% highlight bash %}
#!/bin/sh
# nautilus IMAGUP script wrapper
#
# 29th august 2008 - Romain Vimont (®om)
#
# Needs "imagup" core script to be installed.
#

# Use only \n as field separator
IFS='
'

# Calls imagup with all args
imagup -open $NAUTILUS_SCRIPT_SELECTED_FILE_PATHS
{% endhighlight %}

Ensuite, dans **nautilus**, il est possible d'envoyer l'image en cliquant sur
Script → imagup-wrapper. Les images ainsi envoyées s'ouvriront dans le
navigateur par défaut.

Cependant, en écrivant ce script, je me suis aperçu de trois problèmes dans
**nautilus** : [un problème de proxy][probproxy], [un problème de vue
liste][probliste] et [un problème avec le lancement de scripts à partir du
bureau][probbureau].

[probproxy]: http://bugzilla.gnome.org/show_bug.cgi?id=549823
[probliste]: http://bugzilla.gnome.org/show_bug.cgi?id=549816
[probbureau]: http://bugzilla.gnome.org/show_bug.cgi?id=549910

Les deux derniers problèmes sont contournés avec le script `imagup-wrapper` (le
dernier script).

Si, au lieu d'appeler une fois `imagup` avec tous les arguments, on voulait
l'appeler _n_ fois avec un seul argument (utile lorsque le programme appelé ne
boucle pas sur les arguments), on aurait pu utiliser :

{% highlight bash %}
printf %s "$NAUTILUS_SCRIPT_SELECTED_FILE_PATHS" |
while read -r arg
do
    imagup -open "$arg"
done
{% endhighlight %}

ou encore :

{% highlight bash %}
IFS='
'
for arg in $NAUTILUS_SCRIPT_SELECTED_FILE_PATHS
do
    imagup -open "$arg"
done
{% endhighlight %}


J'en ai profité pour écrire une section [les pièges à éviter][pièges] sur la doc
ubuntu-fr de `nautilus-scripts`.

[pièges]: http://doc.ubuntu-fr.org/nautilus_scripts#les_pieges_a_eviter
