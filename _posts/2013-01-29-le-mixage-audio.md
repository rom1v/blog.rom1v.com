---
layout: post
title: Le mixage audio
date: 2013-01-29 14:15:45+01:00
tags:
- planet-libre
---

Que se passe-t-il lorsque nous percevons le son provenant de plusieurs sources
audio simultanément, par exemple lorsque plusieurs personnes parlent en même
temps ?

Dans la réalité, ce que nous entendons est la **somme** de chacun des signaux.

Mais si nous voulons mélanger plusieurs pistes audio numériques, nous
rencontrons un problème : chaque [échantillon][] d'un signal audio est compris
entre une valeur _min_ et une valeur _max_, disons entre `-1` et `1`. Pour les
[mixer][], nous ne pouvons donc pas sommer plusieurs signaux comme dans la
réalité : le signal résultant doit aussi être compris entre `-1` et `1`. Comment
faire alors ?

[échantillon]: http://fr.wikipedia.org/wiki/%C3%89chantillonnage_%28signal%29
[mixer]: http://fr.wikipedia.org/wiki/Mixage_audio


## En théorie

_Les graphes présentés dans les sections suivantes ont été créés avec
[gnuplot][], et les définitions de fonctions sont écrites dans la syntaxe
correspondante. Les sources (`.gnu`) sont disponibles pour chacun des graphes,
vous permettant de les manipuler en 3D._

[gnuplot]: http://www.gnuplot.info/


### Somme tronquée

La première idée est de sommer les signaux en tronquant le résultat dans
l'intervalle `[-1; 1]`. Pour le mixage de **deux** sources audio `x` et `y` :

{% highlight gnuplot %}
mix_sum(x, y) = min(1, max(-1, x + y))
{% endhighlight %}

Le résultat sera _parfait_ lorsque `|x + y| <= 1`. Par contre, dans le reste des
cas, nous obtenons du [clipping][], désagréable à l'oreille.

[clipping]: http://fr.wikipedia.org/wiki/Clipping_%28audio%29

Visualisons cette fonction :

{: .center}
![mix_sum]({{ site.assets }}/mixage_audio/mix_sum.png)

Les axes horizontaux correspondent à un échantillon de chacune des deux sources
audio ; l'axe vertical représente la valeur résultant de la combinaison des deux
en utilisant la _somme tronquée_.

Le _clipping_ correspond aux deux paliers horizontaux du haut et du bas.


### Moyenne

Pour éviter tout _clipping_, il suffirait de moyenner les deux sources audio :

{% highlight gnuplot %}
mix_mean(x, y) = (x + y) / 2;
{% endhighlight %}

{: .center}
![mix_mean]({{ site.assets }}/mixage_audio/mix_mean.png)

Effectivement, ça fonctionne bien. Mais ce n'est pas forcément le meilleur
choix.

Le son résultant va toujours être plus faible que le plus fort des deux sources,
et souvent de manière significative. En particulier, si nous mélangeons une
source audio quelconque avec un silence, l'amplitude va être divisée par deux.

De plus, la définition va également être divisée par deux : si l'amplitude est
codée sur 8 bits, elle peut prendre 256 valeurs. En divisant les signaux par
deux, chaque signal aura une définition de 7 bits (128 valeurs).

Ces inconvénients s'agravent lorsqu'il y a plus de deux sources à mélanger.


### k × somme

Nous pouvons alors chercher un **compromis** entre conserver l'amplitude et éviter le _clipping_. En fait, les fonctions de _somme tronquée_ et de _moyenne_ ne sont que deux cas particuliers de cette fonction :


{% highlight gnuplot %}
mix_ksum(k, x, y) = min(1, max(-1, k * (x + y)))
{% endhighlight %}

En effet :

{% highlight gnuplot %}
mix_sum(x, y) = mix_ksum(1, x, y)
mix_mean(x, y) = mix_ksum(0.5, x, y)
{% endhighlight %}

Nous pouvons choisir n'importe quel `k` entre 0.5 et 1 : plus `k` est faible,
moins le _clipping_ sera probable ; plus `k` est élevé, plus l'amplitude sera
conservée.

Voici le graphe pour `k = 0.7` :

{: .center}
![mix_ksum]({{ site.assets }}/mixage_audio/mix_ksum.png)

Cette méthode est très utile si nous connaissons _à l'avance_ les sources audio.
Par exemple, pour mélanger deux fichiers son, nous pouvons effectuer une
première passe pour analyser le max `m` de la somme des deux signaux, et choisir
`k < 1/m` : cela garantit qu'il n'y aura pas de _clipping_, et nous pouvons
conserver l'amplitude dans la mesure du possible, sans distorsion.

Si ces sources audio nous parviennent en direct (_streaming_, conversation
audio…), nous pouvons choisir un nombre arbitrairement (plus ou moins basé sur
l'expérience). Choisir `k > 0.5` se justifie car si deux sources audio sont
indépendantes, les ajouter ne provoque pas des pics deux fois plus importants
(les pics d'un signal vont souvent être compensés par les creux de l'autre).


### Fonction non-linéaire

Mais nous pouvons trouver un meilleur compromis grâce à des fonctions
[non-linéaires][].

[non-linéaires]: http://fr.wikipedia.org/wiki/Non-lin%C3%A9arit%C3%A9

Dans la première partie de son billet [Mixing digital audio][], Viktor T. Toth
présente une stratégie très intéressante. Il part du principe que le mixage de
deux sources audio doit respecter les règles suivantes :

[mixing digital audio]: http://www.vttoth.com/CMS/index.php/technical-notes/68

  * si l'une des sources est silencieuse, alors nous voulons entendre l'autre inaltérée ;
  * si les signaux sont de même signe, l'amplitude du résultat (en valeur absolue) doit être supérieure à celle des sources.

Et si les signaux prennent valeur dans `[0, 1]`, la fonction suivante respecte
ces contraintes :

{% highlight gnuplot %}
vtt(x, y) = x + y - x * y
{% endhighlight %}

{: .center}
![vtt]({{ site.assets }}/mixage_audio/vtt.png)

Cependant, en réalité, les signaux prennent valeur dans `[-1, 1]`, et cette
fonction ne convient pas. L'auteur s'en est rendu compte, mais malheureusement
la solution qu'il propose n'est pas appropriée (par exemple, le mixage ne se
comporte pas symétriquement si nous inversons le signal).

Nous pouvons extrapoler son idée originale pour la faire fonctionner sur `[-1,
1]` :

{% highlight gnuplot %}
mix_vtt(x, y) = \
    x >= 0 && y >= 0 ? x + y - x * y \
  : x <= 0 && y <= 0 ? x + y + x * y \
  : x + y
{% endhighlight %}

Le principe est d'utiliser le symétrique de sa fonction pour la partie négative,
et ajouter deux bouts de plans pour les raccords :

{: .center}
![mix_vttx]({{ site.assets }}/mixage_audio/mix_vttx.png)

Mais quelque chose saute aux yeux : sa représentation n'est pas _lisse_ (la
fonction n'est pas [continûment dérivable][] (_C<sup>1</sup>_)). Cela signifie
que les variations du résultat en fonction des variations des sources changent
brutalement en certains endroits.

[continûment dérivable]: http://fr.wikipedia.org/wiki/D%C3%A9rivation_it%C3%A9r%C3%A9e#Classe_Cn

Ce n'est pas satisfaisant mathématiquement.


### Surface _lisse_

Et en effet, en y réfléchissant, la fonction souffre de quelques défauts.

Par exemple, si l'une des deux sources audio est à `1`, alors si l'autre est
positive, elle n'a aucun impact, si elle est négative, elle a un impact
[linéaire][] important. Ce n'est rien d'autre qu'un _clipping_ de l'une des deux
sources.

[linéaire]: http://fr.wikipedia.org/wiki/Lin%C3%A9arit%C3%A9

Par ailleurs, dans la réalité, le mixage de deux signaux est simplement leur
addition. Le résultat devrait donc être **invariant** si nous ajoutons une
constante à une source et la soustrayons à l'autre :

{% highlight gnuplot %}
mix(x, y) = mix(x + k, y - k) = x + y
{% endhighlight %}

Cette propriété me semble importante : peu importe que le son provienne d'une
source ou d'une autre, cela n'intervient pas dans le mixage.

Or, dans la fonction précédente, elle n'est pas respectée. Par exemple :

{% highlight gnuplot %}
mix_vttx(0.5, 0.5) = 0.75
min_vttx(0, 1) = 1
{% endhighlight %}

Afin de dépasser ce problème, posons cette propriété comme principe : puisque
l'identification de l'apport individuel de chaque signal ne compte pas,
considérons uniquement leur somme (ou leur moyenne). Ainsi, au lieu d'une
fonction à deux variables `x` et `y`, nous pouvons utiliser une fonction à une
seule variable `z = (x + y) / 2` (la moyenne).

Remarquons que nous pouvions déjà exprimer les [fonctions linéaires][] vues
précédemment en fonction d'une seule variable. En effet, en posant `z = (x + y)
/ 2`, nous obtenons :

[fonctions linéaires]: http://fr.wikipedia.org/wiki/Application_lin%C3%A9aire

{% highlight gnuplot %}
sum(z) = max(-1, min(1, 2 * z))
mean(z) = z
ksum(z) = max(-1, min(1, 2 * k * z))
{% endhighlight %}

Dans le fond, nous cherchons une fonction qui s'approche de `sum` pour les
amplitudes faibles (pour conserver l'amplitude au mieux) et de `mean` pour les
amplitudes élevées (pour éviter le _clipping_).

Avec un peu d'imagination, nous pouvons trouver une fonction qui convient
parfaitement (pour 2 pistes audio) :

{% highlight gnuplot %}
g(z) = z * (2 - abs(z))
{% endhighlight %}

Elle se généralise pour _n_ pistes audio :

{% highlight gnuplot %}
g(z) = sgn(z) * (1 - (1 - abs(z)) ** n)
{% endhighlight %}

_`abs(x)` désigne la [valeur absolue][] de `x` (\|x\|), et `**` est la fonction
[puissance][] (`a ** n` signifie a<sup>n</sup>)_

[valeur absolue]: https://fr.wikipedia.org/wiki/Valeur_absolue
[puissance]: http://fr.wikipedia.org/wiki/Puissance_d%27un_nombre

{: .center}
![g]({{ site.assets }}/mixage_audio/g.png)

Cette fonction a plein de propriétés intéressantes :

∀x, \|_g_(x)\| <= 1
: dans le bon intervalle

_g_(-1) = -1, _g_(0) = 0 et _g_(1) = 1
: résultats cohérents

∀x (\|x\| < 1), _g'_(x) > 0
: pas de _clipping_

∀x, \|_g_(x)\| ≤ \|_sum_(x)\|
: l'amplitude ne dépasse jamais la somme de celle des sources

∀x, \|_g_(x)\| ≥ \|_mean_(x)\|
: l'amplitude est toujours supérieure à la moyenne des sources

∀x, _g'_(0) = _sum'_(x) = n
: _g_ se comporte comme _sum_ lorsque l'amplitude est faible (elle
[varie][dérivée] de la même manière)

∀x≠0, x._g''_(x) < 0
: la croissance de _g_ ralentit lorsque l'amplitude augmente (en valeur
absolue), donc les fortes amplitudes sont plus _compressées_ que les faibles

∀x, _g_(-x) = -_g_(x) ([impaire][])
: comportement symétrique sur un signal inversé

_g_ ∈ C1 ([continûment dérivable][])
: parfaitement lisse

[dérivée]: http://fr.wikipedia.org/wiki/D%C3%A9riv%C3%A9e
[impaire]: http://fr.wikipedia.org/wiki/Parit%C3%A9_d%27une_fonction

Passons alors en 3 dimensions, et posons :

{% highlight gnuplot %}
mix_f(x, y) = g((x + y) / 2)
{% endhighlight %}

{: .center}
![mix_f]({{ site.assets }}/mixage_audio/mix_f.png)

Nous nous apercevons que c'est une version _lissée_ de la fonction précédente :

{: .center}
![mix_f_vttx]({{ site.assets }}/mixage_audio/mix_f_vttx.png)

**Cette fonction me semble donc pertinente pour mixer plusieurs flux audio.**


## En pratique

Bon, jusqu'ici nous avons fait de beaux dessins, c'était rigolo. Maintenant,
passons à la pratique, et implémentons les fonctions de mixage en [C][].

[C]: http://fr.wikipedia.org/wiki/C_%28langage%29

Nous manipulerons uniquement des flux [audio brut][] (des [wav][] sans en-tête),
contenant uniquement des [échantillons][] encodés par des [entiers signés][] sur
16 [bits][], en [little endian][]. Ça peut paraître compliqué comme ça, mais
c'est juste le format utilisé pour les [cd audio][].

[audio brut]: http://fr.wikipedia.org/wiki/RAW_%28format_audio%29
[wav]: http://fr.wikipedia.org/wiki/WAVEform_audio_format
[échantillons]: http://fr.wikipedia.org/wiki/%C3%89chantillonnage_%28signal%29
[entiers signés]: http://fr.wikipedia.org/wiki/Format_de_donn%C3%A9es#Nombres_entiers
[bits]: http://fr.wikipedia.org/wiki/Bit
[little endian]: http://fr.wikipedia.org/wiki/Endianness#Little_endian
[cd audio]: http://en.wikipedia.org/wiki/CD-DA#Technical_details

Le programme est indifférent au nombre de canaux ou à la fréquence (il mixe les
échantillons les uns à la suite des autres), mais bien évidemment les
différentes pistes mixées doivent avoir ces paramètres identiques.


### Implémentation

Bien que nous n'ayons vu jusqu'ici que le mixage de **deux** pistes audio
(au-delà c'était compliqué de les visualiser sur des graphes), l'implémentation
permet de mixer _n_ pistes audio.

Le mixage s'effectue sur un échantillon de chaque piste audio à la fois (il est
indépendant des échantillons précédents et suivants). Les fonctions de mixage
ont toutes la même signature :

{% highlight c %}
int mix(int n, int samples[]);
{% endhighlight %}

`n`
: le nombre de pistes audio

`samples`
: le tableau des `n` échantillons à mixer

La valeur du retour ainsi que celles des `samples[i]` _tient_ sur 16 bits (compris entre -32768 et 32767).

À titre d'exemple, voici l'implémentation de la fonction _f_ (celle qui est
_lisse_) :

{% highlight c %}
int mix_f(int n, int samples[]) {
    double z = _dsum(n, samples) / n;
    int sgn = z >= 0 ? 1 : -1;
    double g = sgn * (1 - pow(1 - sgn * z, n));
    return to_int16(g);
}
{% endhighlight %}

avec `_dsum` une fonction qui somme les _n_ samples et `to_int16` une fonction
qui convertit un [flottant][] compris entre -1 et 1 vers un entier compris entre
-32768 et 32767.

[flottant]: http://fr.wikipedia.org/wiki/Virgule_flottante

Une fonction `main` s'occupe d'ouvrir les fichiers dont les noms sont passés en
paramètres et d'appliquer pour chaque échantillon la fonction de _mixage_
désirée.


### Sources

Les sources complètes sont [gittées][git] : [mixpoc].

[git]: http://fr.wikipedia.org/wiki/Git

[mixpoc]: https://github.com/rom1v/mixpoc

Le projet contient :

  * le code du [PoC][] (`mixpoc.c`) ;
  * un [makefile][] minimaliste (`Makefile`) ;
  * les sources des graphes _gnuplot_ (`*.gnu`) ;
  * des scripts utilitaires (voir ci-dessous l'utilisation).

[poc]: http://fr.wikipedia.org/wiki/Preuve_de_concept
[makefile]: http://fr.wikipedia.org/wiki/Make


### Utilisation


#### Fichiers _raw_

Le _PoC_ ne manipule que des fichiers _raw_. Il est peu probable que vous ayez
de tels fichiers sur votre ordinateurs, vous devez donc pouvoir en créer à
partir de vos fichiers audio habituels.

_Vous aurez besoin de `sox` et éventuellement `avconv` ou `ffmpeg`._

    ./toraw file.wav file.raw
    ./toraw file.ogg file.raw
    ./toraw file.flac file.raw

Pour l'opération inverse :

    ./rawtowav file.raw file.wav

Si le format n'est pas supporté par `sox` (comme le _mp3_), convertissez-le en
`wav` d'abord :

    avconv -i file.mp3 file.wav
    ffmpeg -i file.mp3 file.wav


#### Lecture et enregistrement

Il est possible de lire des fichiers _raw_ directement et d'en enregistrer de
nouveaux à partir du microphone (pratique pour essayer de mixer une musique avec
une conversation).

_Le paquet `alsa-utils` doit être installé (vérifiez que le microphone est bien
activé dans `alsamixer`)._

Pour enregistrer :

    ./record file.raw
    ./record > file.raw

Pour lire :

    ./play file.raw
    ./play < file.raw

Pour lire en direct le son provenant du microphone (à tester avec un casque pour
éviter l'[effet Larsen][]) :

    ./record | ./play

[effet Larsen]: http://fr.wikipedia.org/wiki/Effet_Larsen


#### Mixpoc

Pour compiler `mixpoc` (nécessite `make` et un compilateur C comme `gcc`) :

    make

Pour l'utiliser, la syntaxe est la suivante :

    ./mixpoc (sum|mean|ksum|vttx|f) file1 [file2 [...]]

Le résultat sort sur la [sortie standard][stdout]. Ainsi :

[stdout]: http://fr.wikipedia.org/wiki/Flux_standard#Sortie_standard

    ./mixpoc f file1.raw file2.raw filen.raw > result.raw

écrit le fichier `result.raw`.

Pour lire en direct le résultat :

    ./mixpoc f file1.raw file2.raw filen.raw | ./play

ou plus simplement (grâce au script `mix`) :

    ./mix f file1.raw file2.raw filen.raw

Pour ajouter une source silencieuse, vous pouvez utiliser `/dev/zero` :

    ./mix f file.raw /dev/zero

Vous avez maintenant tout ce dont vous avez besoin pour tester.


#### Gnuplot

Pour visualiser les graphes _gnuplot_, je vous conseille le paquet `gnuplot-qt`.

Pour les ouvrir :

    gnuplot -p file.gnu

La souris ou les flèches du clavier permettent de tourner le graphe en 3
dimensions.

Les commandes nécessaires pour générer une image `.png` sont écrites en
commentaire à l'intérieur du fichier. Je les ai fait commencer par `##` pour
pouvoir les décommenter automatiquement (sans décommenter le reste) avec un
script.

Ainsi, pour générer les fichiers `.png` :

    ./gg file.gnu
    ./gg *.gnu


## Conclusion

Pour mixer plusieurs pistes son, la fonction _f_ me semble très bonne, à la fois
en théorie et en pratique. Sur les exemples que j'ai testés, le résultat était
celui attendu.

Cependant, je n'ai ni du matériel audio ni des oreilles de haute qualité, et mes
connaissances en [acoustique][] sont très limitées.

[acoustique]: http://fr.wikipedia.org/wiki/Acoustique

Les critiques sont donc les bienvenues.
