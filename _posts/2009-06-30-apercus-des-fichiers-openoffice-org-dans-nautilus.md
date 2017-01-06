---
layout: post
title: Aperçus des fichiers OpenOffice.org dans nautilus
date: 2009-06-30 20:27:50+02:00
---

Par défaut, Ubuntu ne gère pas les aperçus des fichiers ODF (format utilisé par
**OpenOffice.org**), contrairement aux fichiers images, aux fichiers PDF, etc.
On se retrouve alors avec une simple icône :

{: .center}
![oooicon]({{ site.assets }}/openoffice/oooicon.png)

C'est quand même plus pratique d'obtenir un aperçu du document comme ceci :

{: .center}
![ooothumb]({{ site.assets }}/openoffice/ooothumb.png)

_EDIT: en fait c'est beaucoup plus simple que prévu._

Il suffit d'installer les paquets `libgsf-bin` et `imagemagick` :

    sudo apt-get install libgsf-bin imagemagick

et de redémarrer la session.
