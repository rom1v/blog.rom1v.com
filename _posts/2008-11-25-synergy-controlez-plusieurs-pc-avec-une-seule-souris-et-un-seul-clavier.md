---
layout: post
title: 'Synergy : contrôlez plusieurs PC avec une seule souris et un seul clavier'
date: 2008-11-25 21:10:00+01:00
---

**Synergy** est un outil permettant de contrôler plusieurs ordinateurs avec un
seul clavier et une seule souris. De plus, il permet de partager le
presse-papier : pratique pour copier-coller d'un ordinateur à l'autre ! Mais en
plus, c'est super simple !

Il y a un **serveur** et _n_ **clients**. C'est le serveur qui possède le
clavier et la souris.

{: .center}
![puls_synergy]({{ site.assets }}/synergy/puls_synergy.png)

Tout d'abord, sur chacun des postes, il faut installer le paquet `synergy`

Ensuite, sur le serveur, il faut créer un fichier de configuration
`~/.synergy.conf`, extrêmement simple :

~~~
section: screens
   rom-laptop:
   rom-desktop:
end
section: links
   rom-desktop:
       left = rom-laptop
   rom-laptop:
       right = rom-desktop
end
~~~

Ici, `rom-laptop` est mon portable (le serveur) et `rom-desktop` est mon fixe
(le client). C'est le nom de la machine, que l'on peut connaître avec :

{% highlight bash %}
echo $HOSTNAME
{% endhighlight %}


La section `screen` définit la liste des machines, et la section `links` définit
leur position relative.

Ensuite, côté serveur, on tape :

{% highlight bash %}
synergys
{% endhighlight %}


Et sur chaque client :

{% highlight bash %}
synergyc ip_du_serveur
{% endhighlight %}

Les clients peuvent être lancés avant le serveur, ils vont tenter de se
reconnecter 1 seconde après, puis 3 secondes après, puis 5, puis 15, puis 30 et
enfin toutes les minutes. Ils survivent à la déconnexion du serveur, et tentent
de se reconnecter en suivant la même règle.

Pour arrêter la connexion, sur le serveur :

{% highlight bash %}
killall synergys
{% endhighlight %}

et sur les clients :

{% highlight bash %}
killall synergyc
{% endhighlight %}


## Pour aller plus loin

### Éviter le changement d'écran involontaire

Après quelques minutes d'utilisation, on se rend compte que lorsqu'on est sur le
PC de gauche, et qu'on va à la droite de l'écran (pour déplacer la scrollbar de
_Firefox_ en plein écran par exemple), on se retrouve involontairement sur
l'écran de droite, c'est très embêtant. Mais c'est très facile d'y remédier, il
suffit d'ajouter l'option :

~~~
section: options
    switchDoubleTap = 400
end
~~~

Cela permet de ne changer d'écran qu'en cas de double-contact en moins de 400ms
avec le bord de l'écran.

### Démarrer automatiquement

Sous _Gnome_ (à adapter pour les autres environnements), il suffit de rajouter
au fichier `~/.gnomerc` la commande du serveur ou du client selon le cas.

Pour le serveur :

{% highlight bash %}
echo 'synergys' >> ~/.gnomerc
{% endhighlight %}

Pour le client :

{% highlight bash %}
echo 'synergyc ip_du_serveur' >> ~/.gnomerc
{% endhighlight %}


## Décaler les écrans

Deux écrans côte à côte ne sont pas forcément alignés et ils n'ont pas forcément
la même hauteur. Par exemple l'écran de mon fixe est un 5:4 et il est un peu
surélevé, celui de mon portable est un 16:10 et il est plus bas.

Pourtant, quand je déplace la souris d'un écran à l'autre, je voudrais que la
souris reste à la même hauteur. Aucun problème, on peut passer des arguments
`(start,end)`, exprimés en pourcentage de l'écran, entre 0 et 100 inclus :

~~~
section: screens
    rom-laptop:
    rom-desktop:
end
section: links
    rom-desktop:
        left(35,100) = rom-laptop(0,85)
    rom-laptop:
        right(0,85) = rom-desktop(35,100)
end
section: options
    switchDoubleTap = 400
end
~~~

Ici, la partie supérieure de mon portable ([0%;85%]) est en face de la partie
basse de mon fixe ([35%;100%]).

_Remarque :_ la relation n'a pas besoin d'être symétrique, mais c'est plus
logique qu'elle le soit :)


### Démarrer chacun des clients à distance

Si l'on ne veut pas démarrer **synergy** au démarrage du système, on
souhaiterait pouvoir le faire rapidement sans passer sur chacun des PC pour
exécuter une commande. Avec une connexion SSH correctement configurée (par clés
de préférence), on peut automatiser le lancement de tous les clients :

~~~
synergys
ssh rom-desktop synergyc rom-laptop
ssh un-autre-pc synergyc rom-laptop
~~~

(`rom-laptop` est défini dans `/etc/hosts`)


### Sécuriser la connexion

**Synergy** ne chiffre pas les communications, donc tout passe en clair sur le
réseau (enfin, du moins pour ceux qui connaissent la clé WPA de votre réseau, si
vous êtes en wifi).

Pour chiffrer, il suffit de ne faire écouter le serveur que
sur `localhost` et de faire passer la connexion dans un tunnel SSH.

Pour limiter à localhost :

{% highlight bash %}
synergys -f -a localhost
{% endhighlight %}

Pour cela, ouvrir un tunnel du serveur vers chacun des clients :

{% highlight bash %}
ssh client -CvR24800:localhost:24800 synergyc -f localhost
{% endhighlight %}


Ou, à l'inverse, ouvrir un tunnel de chacun des clients vers le serveur :

{% highlight bash %}
ssh server -CNvL24800:localhost:24800
synergyc -f localhost
{% endhighlight %}

Ce qui est embêtant, c'est qu'il faut déchiffrer la clé privée, ce qui est
problématique pour démarrer **synergy** au démarrage du système.

_Un grand merci à [Génération Linux][] qui m'a fait découvrir cet outil
maintenant indispensable._

[Génération Linux]: http://www.generation-linux.fr/index.php?post/2008/11/19/%5BTest-1er-Billet%5D-Synergy-ou-comment-gagner-de-la-place-sur-votre-bureau
