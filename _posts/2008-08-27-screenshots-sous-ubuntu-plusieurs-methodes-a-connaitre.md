---
layout: post
title: 'Screenshots sous Ubuntu : plusieurs méthodes à connaître'
date: 2008-08-27 11:03:00+02:00
tags:
- planet-libre
- puf
---

## Méthode #1

La méthode la plus simple consiste à appuyer sur **ImprÉcran**.

Cela ouvre une fenêtre dans lequelle on peut choisir la destination :

{: .center}
![imprecran-bureau]({{ site.assets }}/ubuntu_screenshots/imprecran-bureau.png)

En appuyant sur **Alt+ImprÉcran**, il est possible de capturer uniquement la
fenêtre active :

{: .center}
![imprecran-fenetre]({{ site.assets }}/ubuntu_screenshots/imprecran-fenetre.png)

Cette méthode est simple, mais elle ne permet pas de faire une sélection
arbitraire ni de définir un délai (ce qui empêche donc de faire une capture
lorsque le cube est en rotation, ou lorsqu'un menu contextuel est ouvert).


## Méthode #2

Cette méthode est en fait la même que la #1, car c'est le même programme qui est
lancé (`gnome-screenshot`), sauf qu'au lieu d'être lancé par **ImprÉcran**, il
est lancé manuellement :

{: .center}
![capture-menu]({{ site.assets }}/ubuntu_screenshots/capture-menu.png)

L'avantage, c'est que plus d'options sont disponibles, notamment le délai :

{: .center}
![capture-details]({{ site.assets }}/ubuntu_screenshots/capture-details.png)

L'option « inclure la bordure de la fenêtre » ne fonctionne que si on n'utilise
pas de gestionnaire de bureau composite (compiz).


## Méthode #3

**Gimp** propose également la capture d'écran : Fichier → Acquisition → Capture
d'écran.

{: .center}
![gimp-details]({{ site.assets }}/ubuntu_screenshots/gimp-capture.png)

Cette fenêtre offre toutes les options intéressantes. La capture effectuée
s'ouvre ensuite dans Gimp, il suffit de sauver le fichier. Cette méthode est
pratique lorsqu'on veut retourcher la capture (flouter certaines zones…), ou
capturer une région de l'écran avec délai (on attend d'abord le délai, puis on
sélectionne, ça permet de rendre active la fenêtre qu'on veut pour le screenshot
par exemple). Par contre un inconvénient peut être que Gimp pourrait apparaître
à l'écran (dans la barre des tâches), selon la zone capturée.


## Méthode #4

C'est la méthode la plus rapide. Elle nécessite **compiz** et l'activation du
plug-in **screenshots** (dans Extras).

Une fois activé, il suffit de faire **toucheWindows+clicGauche** pour dessiner
un rectangle translucide sur l'écran, et cette zone sera capturée.

L'image sera sauvée dans le répertoire prédéfini, par défaut dans `~/Desktop`.

_Attention, ce répertoire peut ne pas exister, si par exemple votre bureau se
trouve dans `~/Bureau`. Pensez donc à changer le répertoire utilisé pour les
screenshots._

{: .center}
![compiz-screenshot]({{ site.assets }}/ubuntu_screenshots/compiz-screenshot.png)

Voilà, je trouve que c'est utile de connaître toutes ces méthodes, ça permet de
choisir celle adaptée selon le cas.

Voici un petit tableau récapitulatif :

       ╭───────┬─────────┬────────────┬───────┬─────────────┬─────────────╮
       │ plein │ fenêtre │ sélection  │ délai │    choix    │   1 ou 2    │
       │ écran │ active  │ arbitraire │       │ destination │ actions max │
    ╭──┼───────┼─────────┼────────────┼───────┼─────────────┼─────────────┤
    │#1│   ✔   │    ✔    │     ✘      │   ✘   │      ✔      │      ✔      │
    ├──┼───────┼─────────┼────────────┼───────┼─────────────┼─────────────┤
    │#2│   ✔   │    ✔    │     ✘      │   ✔   │      ✔      │      ✘      │
    ├──┼───────┼─────────┼────────────┼───────┼─────────────┼─────────────┤
    │#3│   ✔   │    ✔    │     ✔      │   ✔   │      ✔      │      ✘      │
    ├──┼───────┼─────────┼────────────┼───────┼─────────────┼─────────────┤
    │#4│   ✘   │    ✘    │     ✔      │   ✘   │      ✘      │      ✔      │
    ╰──┴───────┴─────────┴────────────┴───────┴─────────────┴─────────────╯

Vous pouvez maintenant [optimiser les images png][optipng] et les [uploader sur
un hébergeur][imagup] pour les poster sur un forum.

[optipng]: {% post_url 2008-08-30-optimiser-la-taille-des-fichiers-png %}
[imagup]: {% post_url 2008-08-29-imagup-uploader-une-image-en-2-clics %}
