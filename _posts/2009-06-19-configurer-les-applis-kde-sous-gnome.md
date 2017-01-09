---
layout: post
title: Configurer les applis KDE sous Gnome
date: 2009-06-19 22:12:32+02:00
tags:
- planet-libre
- puf
---

Même en utilisant _Gnome_, il peut arriver de vouloir utiliser des applications
_KDE_ (Qt), telles que **amarok**, **digikam**, **kile** ou d'autres… Et là,
c'est le drame :

  * c'est en anglais ;
  * les actions sont effectuées par défaut sur simple clic ;
  * les polices de caractères ne sont pas à la même taille ;
  * le thème ne s'intègre pas du tout dans l'environnement _Gnome_…


## Applis KDE en Français

Pour le premier problème, c'est vite réglé, il suffit d'installer le paquet
`kde-i18n-fr` :

{% highlight bash %}
sudo apt-get install kde-i18n-fr
{% endhighlight %}

_**EDIT:** Sous Ubuntu Lucid Lynx (10.04), le paquet s'appelle maintenant
`kde-l10n-fr` :_

{% highlight bash %}
sudo apt-get install kde-l10n-fr
{% endhighlight %}


## SystemSettings

Pour le reste, si on ne veut pas éditer des fichiers de configuration à la main,
il faut la fenêtre de configuration de _KDE_ : `systemsettings`. Mais si on
n'installe que ce paquet, la fenêtre de configuration est presque vide ; pour
pouvoir tout configurer, il faut également le paquet `kdebase-workspace-bin` (et
ses dépendances)… et ça quand on ne le sait pas, on galère`!

{% highlight bash %}
sudo apt-get install systemsettings kdebase-workspace-bin
{% endhighlight %}

Une fois installé, il suffit de lancer `systemsettings` (Applications → Outils
Système → System Settings).

Si la fenêtre est toujours vide, exécutez la commande `kbuildsycoca4`.

Quelques icônes n'apparaissent pas (elles sont remplacées par l'icône par
défaut), ça n'est pas bien grave.

_**EDIT:** Attention, ceci risque de modifier le rendu des polices de caractères
dans certaines applications (notamment Firefox), à cause de certaines
configurations par défaut de KDE. Pour résoudre ce problème, il faut supprimer
(ou renommer) le fichier `~/.fonts.conf` :_

{% highlight bash %}
mv ~/.fonts.conf{,.old}
{% endhighlight %}


### Apparence

{: .center}
![systemsettings-main]({{ site.assets }}/kde_theme/systemsettings-main.png)

Dans la configuration de l'apparence (première icône), il est possible de
modifier le style (choisir **GTK+** au lieu de **Oxygen** pour une meilleure
intégration dans _Gnome_), le thème d'icônes (par exemple **Human**) et les
polices de caractères (même si personnellement, je n'arrive pas à avoir le même
rendu que les polices de _Gnome_, au moins je peux les mettre à la même taille).

{: .center}
![systemsettings-appearance]({{ site.assets }}/kde_theme/systemsettings-appearance.png)


### Comportement de la souris

Dans le menu principal de **systemsettings**, vers le bas se trouve le bouton
_Clavier & Souris_ : c'est là qu'il est possible de configurer le comportement
de la souris, en particulier effectuer les actions sur double clic plutôt que
sur simple clic :

{: .center}
![systemsettings-mouse]({{ site.assets }}/kde_theme/systemsettings-mouse.png)


## Résultat

Et voilà le résultat pour la fenêtre de **dolphin** (le navigateur de fichiers
de _KDE4_) :

{: .center}
![dolphin-gnome]({{ site.assets }}/kde_theme/dolphin-gnome.png)
