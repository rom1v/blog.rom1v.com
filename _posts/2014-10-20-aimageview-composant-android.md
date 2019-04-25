---
layout: post
title: AImageView (composant Android)
date: 2014-10-20 18:48:34+02:00
---

Pour afficher une image sur _Android_, le SDK contient un composant
[`ImageView`][ImageView].

[ImageView]: http://developer.android.com/reference/android/widget/ImageView.html

Cependant, son mécanisme de configuration du redimensionnement de l'image
([`ScaleType`][ScaleType]) me semble déficient :

[ScaleType]: http://developer.android.com/reference/android/widget/ImageView.ScaleType.html

  * il ne gère pas tous les cas courants ;
  * le choix de la bonne constante (si elle existe) n'est pas toujours très
    intuitif.

J'ai donc écrit un composant `AImageView` (qui hérite d'`ImageView`) avec un
mécanisme alternatif au _scale type_.


## Principes

`AImageView` propose 4 paramètres :

  * `xWeight` et `yWeight` (des `float`s entre `0` et `1`) indiquent à quel
    endroit lier l'image au conteneur ;
  * `fit` indique si l'image doit s'adapter à l'_intérieur_ du composant (en
    ajoutant des marges), à l'_extérieur_ du composant (en croppant), toujours
    _horizontalement_ ou toujours _verticalement_.
  * `scale` indique si l'on accepte de _downscaler_ (réduire) et/ou d'_upscaler_
    (agrandir) l'image ;

Actuellement, il préserve toujours le [format d'image][] (aspect ratio).

[format d'image]: https://fr.wikipedia.org/wiki/Format_d%27image


## Exemple d'utilisation

{% highlight xml %}
<com.rom1v.aimageview.AImageView
    android:layout_width="match_parent"
    android:layout_height="match_parent"
    android:src="@drawable/myimage"
    app:xWeight="0.5"
    app:yWeight="0.5"
    app:fit="inside"
    app:scale="downscale|upscale" />
{% endhighlight %}

Ici, l'image va s'adapter à l'intérieur (`inside`) du composant (des marges
seront ajoutées si nécessaires), exactement (l'image peut être réduite
– `downscale` – ou agrandie – `upscale` – pour s'adapter) et sera centrée
(`0.5`, `0.5`).


## Équivalences des _scale types_

Les constantes de [`ScaleType`][ScaleType] du composant standard `ImageView`
correspondent en fait à des valeurs particulières de ces paramètres. Comme vous
pourrez le constater, elles ne couvrent pas toutes les combinaisons, et ne sont
pas toujours explicites…


### `ScaleType.CENTER`

{% highlight xml %}
<com.rom1v.aimageview.AImageView
    app:xWeight="0.5"
    app:yWeight="0.5"
    app:scale="disabled" />
<!-- app:fit ne fait rien quand scale vaut "disabled" -->
{% endhighlight %}


### `ScaleType.CENTER_CROP`

{% highlight xml %}
<com.rom1v.aimageview.AImageView
    app:xWeight="0.5"
    app:yWeight="0.5"
    app:fit="outside"
    app:scale="downscale|upscale" />
{% endhighlight %}


### `ScaleType.CENTER_INSIDE`

{% highlight xml %}
<com.rom1v.aimageview.AImageView
    app:xWeight="0.5"
    app:yWeight="0.5"
    app:fit="inside"
    app:scale="downscale" />
{% endhighlight %}


### `ScaleType.FIT_CENTER`

{% highlight xml %}
<com.rom1v.aimageview.AImageView
    app:xWeight="0.5"
    app:yWeight="0.5"
    app:fit="inside"
    app:scale="downscale|upscale" />
{% endhighlight %}


### `ScaleType.FIT_END`

{% highlight xml %}
<com.rom1v.aimageview.AImageView
    app:xWeight="1"
    app:yWeight="1"
    app:fit="inside"
    app:scale="downscale|upscale" />
{% endhighlight %}


### `ScaleType.FIT_START`

{% highlight xml %}
<com.rom1v.aimageview.AImageView
    app:xWeight="0"
    app:yWeight="0"
    app:fit="inside"
    app:scale="downscale|upscale" />
{% endhighlight %}


### `ScaleType.FIT_XY`

Cette configuration ne peut pas être reproduite en utilisant les paramètres
d'`AImageView`, car ce composant préserve toujours l'aspect ratio.


### `ScaleType.MATRIX`

`AImageView` hérite d'`ImageView` et force le `scaleType` à `ScaleType.MATRIX`
(pour redimensionner et déplacer l'image). Par conséquent, il n'y a pas
d'équivalent, `AImageView` est basé dessus.


## Composant

Le composant est disponible sous la forme d'un _project library_ (sous licence
<del>[GNU/LGPLv3][lgpl]</del> ([plus maintenant][license-commit]) [MIT][]):
[AImageView].

[lgpl]: https://fr.wikipedia.org/wiki/Licence_publique_g%C3%A9n%C3%A9rale_limit%C3%A9e_GNU
[license-commit]: https://github.com/rom1v/AImageView/commit/436d3085c0219495899616089918b1ddf2063307
[mit]: https://fr.wikipedia.org/wiki/Licence_MIT
[AImageView]: https://github.com/rom1v/AImageView

Vous pouvez le [compiler][] en fichier `.aar` grâce à la commande :

[compiler]: https://github.com/rom1v/AImageView#build

{% highlight bash %}
cd AImageView
./gradlew assembleRelease
{% endhighlight %}

Il sera généré dans `library/build/outputs/aar/aimageview.aar`.

J'ai aussi écrit une application de démo l'utilisant (avec tous les fichiers
[Gradle][] qui-vont-bien) : [AImageViewSample].

[gradle]: https://en.wikipedia.org/wiki/Gradle

{% highlight bash %}
git clone --recursive https://github.com/rom1v/AImageViewSample
{% endhighlight %}

[AImageViewSample]: https://github.com/rom1v/AImageViewSample

{: .center}
![AImageViewSample]({{ site.assets }}/aimageview/AImageViewSample.jpg)

Pour compiler un `apk` de debug (par exemple) :

{% highlight bash %}
cd AImageViewSample
./gradlew assembleDebug
{% endhighlight %}

Pour ceux que le code intéresse, la classe principale est
[`AImageView`][AImageView.java]. Pour l'utiliser, la partie importante est dans
[`activity_main.xml`][activity_main.xml].

[AImageView.java]: https://github.com/rom1v/AImageView/blob/master/library/src/main/java/com/rom1v/aimageview/AImageView.java
[activity_main.xml]: https://github.com/rom1v/AImageViewSample/blob/master/app/src/main/res/layout/activity_main.xml

N'hésitez pas à critiquer ou à remonter des bugs.
