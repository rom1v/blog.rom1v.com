---
layout: post
title: Extraire les recherches Google des logs Apache
date: 2011-06-24 13:10:36+02:00
tags:
- planet-libre
- puf
---

Aujourd'hui, c'est un billet de _distraction pour geeks_.

## Lister les recherches

Si vous utilisez _Apache_, voici une commande qui liste dans l'ordre alphabétique les recherches _Google_ ayant permis aux internautes d'arriver sur vos sites :

{% highlight bash %}
php -r "echo urldecode(\"$(zgrep 'http://www\.google\.\w*/' /var/log/apache2/*|grep -o '[?&]q=[^&"]*'|cut -c4-)\");"|sort|uniq -c
{% endhighlight %}

_**EDIT 25/06/2011 :** cette commande semble échouer lorsque la liste des
recherches est trop longue, celle donnée à la fin du billet est donc à
préférer._

(pour les autres moteurs de recherche, il faudrait s'inspirer de ce qu'ont fait
les développeurs de [Piwik][])

[piwik]: http://piwik.org/faq/general/#faq_39

Voici à quoi ressemble le résultat de la commande :

          1 ubtunu tiny tiny rss
          5 ubuntu
          1 ubuntu 10.04 change startup screen
          1 ubuntu 10.04 configurer compte messagerie hotmail dans couriel
          3 ubuntu 10.04 cryptage
          1 ubuntu 10.04 écran grub invisible au démarrage
          2 ubuntu 10.04 ecran noir nvidia 
          1 ubuntu 10.04 et video nvidia
          1 ubuntu 10.04 grub nvidia
          1 ubuntu 10.04 grub-pc couleur
          4 ubuntu 10.04 installation partition home chiffrée

Le texte correspond aux recherches, le numéro devant indique le nombre de fois
où elles ont été effectuées.


## Analyse


### Billets les plus recherchés

Sans conteste, les deux billets qui amènent le plus d'internautes par _Google_
concernent [pluzz][] et [apk][].

Et parfois ça ne doit pas les aider beaucoup : certains recherchent par exemple
_"pluzz plus belle la vie"_ dans _Google_ à partir d'_Internet Explorer_, je ne
suis pas sûr que mon script shell pour _pluzz_ réponde à leurs attentes.

[pluzz]: {% post_url 2010-07-06-pluzz-fr-france-televisions-lance-son-service-de-tv-de-rattrapage-non-lisible %}
[apk]: {% post_url 2010-01-10-installer-une-application-apk-sur-android-a-partir-dun-pc %}


### Recherches insolites

Dans la liste, il y a forcément des recherches drôles ou étranges. En voici
quelques unes que j'ai trouvées dans mes logs :

  * _"clitoris.apk"_ : il y a vraiment une application pour tout !
  * _"comment afficher 350 sous linux"_ : c'est si différent que sur les autres
    systèmes d'exploitation ?
  * _"comment on invente une machine pour voler à usage individuelle"_ : dérober
    ou s'envoler ?
  * _"du-ble-plein-les-poches es ce une arnaque"_ : sans doute…
  * _"est-il possible de prelever de l'argent sans que ça se voit sur le
    compte"_ : je veux rester discret…
  * _"l'ecran d'un ordinateur portable est de 14,1 pouce avec 1024*768 pixel
    quelle est la taille de l'ecran N*H en cm ?"_ : le moteur de recherche, j'en
    suis sûr, va comprendre ma question, faire le calcul, et me répondre…
  * _"logiciel adopy"_ : comme ça se prononce !
  * _"pour quel raison on doit interdire le zoo"_ : les animaux c'est
    dangereux !

Il y a certains sites qui s'amusent à référencer ce genre de recherches, par
exemple [Comment devenir un ninja gratuitement ?][ninja]

[ninja]: http://devenirunninjagratuitement.tumblr.com/

N'hésitez pas à poster les vôtres…


## Challenge

J'ai essayé d'écrire la commande la plus courte possible. Je n'ai pas réussi à
faire moins de **129 caractères** sans perdre d'information ou prendre plus de
risque (par exemple on pourrait remplacer `apache2` par `a*2`, mais c'est plus
risqué).

Par contre, cette commande ne fonctionne pas correctement si l'on rajoute
`|less` (on ne peut pas se déplacer avec haut et bas), je ne sais pas trop
pourquoi ni comment le résoudre (si certains ont une idée).

Une autre commande, sans `php` (en 141 caractères), ne pose pas ce problème :

{% highlight bash %}
zgrep 'http://www\.google\.\w*/' /var/log/apache2/*|grep -o '[?&]q=[^&"]*'|cut -c4-|echo -e $(sed 's/$/\\n/;s/+/ /g;s/%/\\x/g')|sort|uniq -c
{% endhighlight %}

Si vous avez des astuces pour faire mieux que 129 (ou 141), ne vous gênez pas
;-)


## Scripts

[D'autres][bortzmeyer] ont fait [des scripts plus complets][script], qui
permettent de récupérer des informations supplémentaires, par exemple la page
sur laquelle l'internaute est arrivé en effectuant cette recherche…

[bortzmeyer]: http://www.bortzmeyer.org/je-parle-a-mon-moteur-de-recherche.html
[script]: http://www.bortzmeyer.org/files/SearchEngineQueries.py
