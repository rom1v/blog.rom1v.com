---
layout: post
title: Configurer le thème des applications GTK sous KDE
date: 2012-05-15 20:08:19+02:00
tags:
- planet-libre
- puf
---

Après être passé de [KDE][] à [Gnome][] il y a un peu plus de 4 ans, j'ai décidé
de revenir à _KDE_. Mais de la même manière que [les applications prévues pour
KDE ne s'intègrent pas correctement à Gnome][kde-sous-gnome], les applications
prévues pour _Gnome_ sont horribles sur _KDE_ : elles n'ont pas de thème du tout
(sauf si vous appelez "thème" l'apparence de [Windows 95][]).

[kde]: http://fr.wikipedia.org/wiki/KDE
[gnome]: http://fr.wikipedia.org/wiki/GNOME
[kde-sous-gnome]: {% post_url 2009-06-19-configurer-les-applis-kde-sous-gnome %}
[windows 95]: http://fr.wikipedia.org/wiki/Windows_95

Voici par exemple à quoi ressemble [GIMP][] :

[gimp]: http://fr.wikipedia.org/wiki/GIMP

{: .center}
[![gimp-moche]({{ site.assets }}/gnome_theme/gimp-moche.thumb.jpg)][gimp-moche]

[gimp-moche]: {{ site.assets }}/gnome_theme/gimp-moche.png

Le problème doit être résolu deux fois : une première pour les applications
utilisant [GTK2][] et une seconde pour celles utilisant [GTK3][].

[gtk2]: http://fr.wikipedia.org/wiki/GTK%2B#GTK.2B_2
[gtk3]: http://fr.wikipedia.org/wiki/GTK%2B#GTK.2B_3


## GTK2

Pour _GTK2_, c'est facile. Sous [Debian][] :

    sudo apt-get install gtk2-engines-oxygen gtk-chtheme

[debian]: http://fr.wikipedia.org/wiki/Debian

_(le nom des paquets peut varier selon votre distribution)_

Il ne reste alors plus qu'à exécuter :

    gtk-chtheme

et choisir le thème `oxygen-gtk` :

{: .center}
![gtk-chtheme]({{ site.assets }}/gnome_theme/gtk-chtheme.png)

Le thème des applications telles que [Firefox][]/[Iceweasel][], [GIMP][],
[Ario][] ou [Eclipse][] sera alors totalement cohérent avec celui des
applications prévues pour _KDE_ :

{: .center}
[![gimp-oxygen]({{ site.assets }}/gnome_theme/gimp-oxygen.thumb.jpg)][gimp-oxygen]

[gimp-oxygen]: {{ site.assets }}/gnome_theme/gimp-oxygen.png

[firefox]: http://fr.wikipedia.org/wiki/Mozilla_Firefox
[iceweasel]: http://fr.wikipedia.org/wiki/Renommage_des_applications_de_Mozilla_par_Debian#Iceweasel
[ario]: http://fr.wikipedia.org/wiki/Ario
[eclipse]: http://fr.wikipedia.org/wiki/Eclipse_%28logiciel%29

Pour pousser plus loin l'intégration de _Firefox/Iceweasel_, il y a même un
[module complémentaire][add-on].

[add-on]: https://addons.mozilla.org/fr/firefox/addon/oxygen-kde/


## GTK3

Pour _GTK3_, ce devrait être presque pareil… sauf que le paquet
`gtk3-engines-oxygen` (ou `oxygen-gtk3`) n'est pas encore dans les dépôts
_Debian_ (il est par contre dans d'autres distributions, comme [Ubuntu][] ou
[Arch Linux][]).

[ubuntu]: http://fr.wikipedia.org/wiki/Ubuntu
[arch linux]: http://fr.wikipedia.org/wiki/Arch_Linux

Il est bien sûr possible de [télécharger les sources][sources] pour l'installer
manuellement.

[sources]: https://projects.kde.org/projects/playground/artwork/oxygen-gtk

Mais nous pouvons nous contenter du thème natif de _Gnome_. Pour le configurer,
il suffit d'installer `gnome-themes-standard` :

    sudo apt-get install gnome-themes-standard

et de créer un fichier `~/.config/gtk-3.0/settings.ini` contenant :

    [Settings]
    gtk-theme-name=Adwaita
    gtk-fallback-icon-theme=gnome

(voir [GtkSettings](http://developer.gnome.org/gtk3/3.4/GtkSettings.html))

[Gedit][] avant :

[gedit]: http://fr.wikipedia.org/wiki/Gedit

{: .center}
![gedit-moche]({{ site.assets }}/gnome_theme/gedit-moche.png)

_Gedit_ après :

{: .center}
![gedit-adwaita]({{ site.assets }}/gnome_theme/gedit-adwaita.png)

En attendant qu'_Oxygen GTK3_ soit disponible dans les dépôts, c'est mieux que
rien…

**EDIT :** `oxygen-gtk3` est maintenant disponible dans les dépôts.
