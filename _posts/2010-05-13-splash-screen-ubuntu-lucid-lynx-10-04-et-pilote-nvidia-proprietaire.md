---
layout: post
title: Splash screen Ubuntu Lucid Lynx (10.04) et pilote NVIDIA propriétaire
date: 2010-05-13 14:26:53+02:00
tags:
- planet-libre
- puf
---

**Ubuntu** utilise maintenant **Plymouth** pour le processus de démarrage
graphique. C'est maintenant le noyau qui s'occupe de la configuration graphique
à la place de _Xorg_ : c'est plus joli, plus rapide…

Le problème, c'est que le logiciel propriétaire ne suit pas le rythme du
logiciel libre. En particulier, le pilote _NVIDIA_ propriétaire ne supporte pas
encore cette fonctionnalité (alors que le pilote libre la gère correctement,
mais ne supporte pas la 3D). Du coup, on se retrouve avec un _splash screen_
très laid en basse résolution au démarrage.

Ce billet décrit comment avoir un logo à la bonne résolution (même si on
n'obtiendra pas la fluidité possible actuellement avec le pilote libre). Une
mise à jour sera peut-être disponible (espérons-le), avec un pilote _NVIDIA_
propriétaire fonctionnant correctement.


## Contourner le problème

**Attention : ces modifications modifient votre configuration graphique, elles
pourraient empêcher votre système de fonctionner correctement.**

_Remplacez dans les étapes suivantes `1680x1050` par la définition de votre
écran._

Tout d'abord, il faut prendre un post-it, un stylo, et écrire _« ne plus acheter
d'ordinateur avec une carte graphique nécessitant des pilotes propriétaires pour
fonctionner »_. Le coller ensuite bien en évidence pour s'en rappeler lors du
prochain achat informatique.

Ensuite, installer le paquet `v86d` :

    sudo apt-get install v86d

Puis éditer le fichier `/etc/default/grub` :

    sudo editor /etc/default/grub

et remplacer la ligne :

    GRUB_CMDLINE_LINUX_DEFAULT="quiet splash"

par :

    GRUB_CMDLINE_LINUX_DEFAULT="quiet splash nomodeset video=uvesafb:mode_option=1680x1050-24,mtrr=3,scroll=ywrap"

et la ligne :

    #GRUB_GFXMODE=640x480

par :

    GRUB_GFXMODE=1680x1050


Puis exécuter les commandes suivantes :

{% highlight bash %}
echo 'uvesafb mode_option=1680x1050-24 mtrr=3 scroll=ywrap' |
    sudo tee -a /etc/initramfs-tools/modules
echo FRAMEBUFFER=y | sudo tee /etc/initramfs-tools/conf.d/splash
sudo update-grub2
sudo update-initramfs -u
{% endhighlight %}


Il ne reste plus qu'à redémarrer le système, le logo est maintenant joli.

Merci à [softpedia][] pour cette astuce.

[softpedia]: http://news.softpedia.com/news/How-to-Fix-the-Big-and-Ugly-Plymouth-Logo-in-Ubuntu-10-04-140810.shtml
