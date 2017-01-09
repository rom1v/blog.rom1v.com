---
layout: post
title: SSHFS inversé (rsshfs)
date: 2014-06-15 13:30:27+02:00
tags:
- planet-libre
- puf
---

[SSHFS][] permet de monter un répertoire d'une machine distance dans
l'arborescence locale en utilisant [SSH][] :

[sshfs]: https://fr.wikipedia.org/wiki/SSHFS
[ssh]: https://fr.wikipedia.org/wiki/Secure_Shell

{% highlight bash %}
sshfs serveur:/répertoire/distant /répertoire/local
{% endhighlight %}

Mais **comment monter un répertoire local sur une machine distante ?**

Une solution simple serait de se connecter en _SSH_ sur la machine distante et
d'exécuter la commande `sshfs` classique.

Mais d'abord, ce n'est pas toujours directement **possible** : la machine locale
peut ne pas être accessible (non adressable) depuis la machine distante. _Ça se
contourne en créant un tunnel SSH utilisant la redirection de port distante
(option `-R`)._

Et surtout, ce n'est pas toujours **souhaitable** : cela nécessite que la clé
privée autorisée sur la machine locale soit connue de la machine distante. Or,
dans certains cas, nous ne voulons pas qu'une machine _esclave_ puisse se
connecter à notre machine _maître_.


## Reverse SSHFS

En me basant sur [la commande donnée en exemple][commande], j'ai donc écrit un
petit script _Bash_ (`rsshfs`, licence [GPLv3][]) qui permet le _reverse
SSHFS_ :

[commande]: https://sourceforge.net/p/fuse/mailman/message/27034864/
[gplv3]: http://www.gnu.org/licenses/quick-guide-gplv3.fr.html

(disponible également sur [github](https://github.com/rom1v/rsshfs))

{% highlight bash %}
git clone http://git.rom1v.com/rsshfs.git
cd rsshfs
sudo install rsshfs /usr/local/bin
{% endhighlight %}

_Les paquets `sshfs` et `fuse` doivent être installés sur la machine distante
(et l'utilisateur doit appartenir au groupe `fuse`). Le paquet
`openssh-sftp-server` doit être installé sur la machine locale._

Son utilisation se veut similaire à celle de `sshfs` :

{% highlight bash %}
rsshfs /répertoire/local serveur:/répertoire/distant
{% endhighlight %}

Comme avec `sshfs`, `/répertoire/distant` doit exister sur `serveur` et doit
être vide.

Il est également possible de monter le répertoire en _lecture seule_ :

{% highlight bash %}
rsshfs /répertoire/local serveur:/répertoire/distant -o ro
{% endhighlight %}

Contrairement à `sshfs`, étant donné que `rsshfs` agit comme un serveur, cette
commande ne retourne pas tant que le répertoire distant n'est pas démonté.

Pour démonter, dans un autre terminal :

{% highlight bash %}
rsshfs -u serveur:/répertoire/distant
{% endhighlight %}

Ou plus simplement en pressant `Ctrl+C` dans le terminal de la commande de
montage.


## Amélioration

J'ai choisi la facilité en écrivant un script indépendant qui appelle la
commande qui-va-bien.

L'idéal serait d'ajouter cette fonctionnalité à [sshfs][sshfs-sources]
directement.

[sshfs-sources]: https://github.com/libfuse/sshfs
