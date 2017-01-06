---
layout: post
title: Recompresser ses photos en masse de manière incrémentale
date: 2008-08-27 18:45:00+02:00
---

Mon appareil photo possède 3 réglages de "qualité" (niveau de compression
JPEG) :

  * fin
  * normal
  * économique

Mais lorsque l'on choisit un réglage, les photos font à peu près toutes la même
taille, qu'elles soient simples ou complexes. Par exemple, en réglage _fin_,
leur taille est quasiment toujours comprise entre 2,8Mio et 2,9Mio.

Or, une photo uniforme devrait prendre beaucoup moins de place qu'une photo très
complexe, avec beaucoup de contours.

D'ailleurs, on s'en rend compte lorsqu'on recompresse ces photos sur un
ordinateur :

{: .center}
![photos-tree]({{ site.assets }}/recompresser_photos/photos-tree.png)

Sur cette capture d'écran, les photos contenues à la racine (les 5 dernières)
sont les photos originales, prises par l'appareil photo.

Les photos contenues
dans les répertoires `convertXX` sont les photos converties avec :

    convert image.jpg -quality XX convertXX/image.jpg

(`imagemagick` doit être installé)

On se rend compte que les photos ont une taille beaucoup plus variable, ce qui
est une bonne chose. Sur l'ensemble de mes albums, j'ai des photos à 500Kio et
d'autres à 1,8Mio : **c'est la qualité finale de la photo qui est prise en
compte**, et non la taille à atteindre.

J'ai donc décidé de prendre les photos en qualité maximale sur l'appareil photo,
et de les recompresser à l'importation.

J'utilise [digiKam][] pour gérer mes photos, qui possède une fonctionnalité pour
recompresser les photos (utilisant exacement le même algorithme que
`imagemagick`).

[digiKam]: https://fr.wikipedia.org/wiki/DigiKam

Le problème, c'est que je veux éviter, par inattention, de recompresser
plusieurs fois les mêmes photos (perte de qualité inutile). Par exemple,
lorsqu'on importe les photos d'une carte mémoire, qu'on les recompresse, puis
qu'on importe les photos d'une seconde carte mémoire, difficile de différencier
les photos déjà recompressées des autres (pour les photos se trouvant dans le
même dossier).

J'ai donc écrit un petit script, qui garde dans un fichier la liste des photos
déjà recompressées, et qui compresse toutes celles qui ne sont pas présentes
dans le fichier.

Ainsi, après une importation de photos, j'exécute le script, seules les
nouvelles seront recompressés.

{% highlight bash %}
#!/bin/sh
#
# Mogrifie tous les fichiers jpeg non encore mogrifiés (recompressés en jpeg)
#

# le fichier "mogrified" doit exister, si ce n'est pas le cas, faire
# touch mogrified" avant de lancer le script
if [ ! -f mogrified ]
then
    echo 'mogrified file not found.'
    exit 1
fi

# liste tous les fichiers .jpg dans les répertoires décrivant une année
# (2005, 2006...)
find -iname '*.jpg' | grep ^./20 | sort > filelist &&

# supprime de cette liste tous les fichiers déjà mogrifiés
comm -23 filelist mogrified |
while read photo
do
    # mogrifie ces fichiers
    echo "$photo"
    mogrify -quality 90 "$photo"
done

# les fichiers mogrifiés sont maintenant les fichiers de la liste complète
mv filelist mogrified
{% endhighlight %}

L'outil `mogrify` fait la même chose que `convert`, sauf qu'il modifie le
fichier sur place (et écrase donc la source).

**Cela permet d'avoir un très bon rapport qualité/taille, sans provoquer des
pertes visibles sur les photos très complexes, ni utiliser de l'espace
inutilement sur les photos simples.**
