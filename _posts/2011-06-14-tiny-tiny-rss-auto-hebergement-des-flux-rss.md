---
layout: post
title: 'Tiny Tiny RSS : auto-hébergement des flux RSS'
date: 2011-06-14 12:11:41+02:00
---

Je vais expliquer dans ce billet pourquoi et comment installer [Tiny Tiny
RSS][tt-rss], un gestionnaire de [flux RSS][]) sur son serveur.

[tt-rss]: http://tt-rss.org/
[flux rss]: http://fr.wikipedia.org/wiki/Flux_RSS


## Motivations


### Pourquoi un serveur ?


Il existe de nombreux clients d'agrégateurs de flux, tels que [Liferea][] sous
_Gnome_ ou [NewsFox][] dans _Firefox_.

[liferea]: http://liferea.sourceforge.net/
[newsfox]: {% post_url 2008-12-13-newsfox-plug-in-firefox-agregateur-de-flux-rss %}

Cependant, un tel client pose principalement **deux problèmes**.

  * Le premier, c'est **le temps d'attente de mise à jour des flux**. Lors du
    démarrage, les flux ne sont pas disponibles immédiatement : il faut
    patienter le temps qu'il mette à jour chacun des flux auxquels nous sommes
    abonnés, ce qui peux prendre plusieurs minutes.
  * Le second, c'est **la synchronisation** : nous ne pouvons pas lire nos flux
    à plusieurs endroits (maison, travail, mobile…) en gardant la
    synchronisation (les flux que nous avons lu sont marqués comme lus sur un
    ordinateur, mais pas sur un autre).

Un gestionnaire de flux doit donc, d'après moi, forcément être **hébergé sur un
serveur**.


### Pourquoi son serveur ?

De nombreux services en ligne proposent la gestion de  flux RSS (_Google
Reader_, _NetVibes_, etc.).

**Pourquoi donc héberger un tel service sur son propre serveur ?**

  * **Par principe.** Comme pour le [blog][] ou les [mails][], autant
    auto-héberger son propre contenu, qui n'a rien à faire ailleurs.
  * **Filtrage en entreprise.** Les agrégateurs de flux connus sont souvent
    bloqués par les proxies d'entreprise. Un service perso sur son propre
    serveur aura beaucoup moins de risque d'être filtré.
  * **Données personnelles.** Les flux auxquels chacun est abonné et les
    articles lus sont une information importante pour quiconque souhaite
    renseigner un profil d'utilisateur. Par exemple, pour [modifier les
    résultats d'un moteur de recherche][individualisation] (ou bien d'autres
    choses).
  * **Censure.** Les flux RSS peuvent être une source d'information essentielle
    (c'est ma source d'information principale). Si nous en laissons la gestion à
    un hébergeur, comment nous assurer qu'il ne supprimera pas des flux les
    informations qui le dérangent ? Par exemple, s'il décide malencontreusement
    de supprimer tous les articles qui parlent de _Wikileaks_ ? Je sais que ce
    n'est pas imaginable dans un pays démocratique. Mais regardons quand même
    (au hasard) l'exemple de _Google_, qui [continue d'étendre l'auto-censure de
    son moteur de recherche][auto-censure], dans un pays exerçant des [pressions
    politiques et commerciales][pressions] pour retirer des contenus dérangeants
    hors de toute décision judiciaire (parfois en [supprimant des entrées
    DNS][wikileaks-dns], même pour [un contenu légal dans le pays
    concerné][legal]), pressions auxquelles [peu d'entreprises
    résistent][blocage], y compris lorsqu'il s'agit d'[obtenir des informations
    politiques][politique]… et ils ne [comptent pas s'arrêter en si bon
    chemin][pipa], pour lutter contre ce qu'ils appellent "piratage". Mais non,
    censurer certains flux pour des raisons politiques est inimaginable dans un
    pays démocratique.

[blog]: {% post_url 2009-01-31-nouveau-blog-100-libre %}
[mails]: {% post_url 2009-08-16-hebergez-vos-mails-sur-ubuntu-server-et-liberez-vous %}
[individualisation]: http://www.internetactu.net/2011/06/13/le-risque-de-lindividualisation-de-linternet/

[auto-censure]: http://www.numerama.com/magazine/18993-google-etend-l-auto-censure-de-son-moteur-de-recherche.html
[pressions]: http://www.numerama.com/magazine/17507-amazon-n-heberge-plus-wikileaks-apres-des-pressions-politiques-et-commerciales.html
[wikileaks-dns]: http://www.numerama.com/magazine/17519-wikileaks-inaccessible-apres-la-perte-de-son-dns.html
[legal]: http://www.numerama.com/magazine/17946-rojadirecta-juge-legal-en-espagne-saisi-par-les-usa-maj.html
[blocage]: http://www.pcinpact.com/actu/news/60765-twitter-paypal-blocage-julian-assange.htm
[politique]: http://www.pcinpact.com/actu/news/61265-wikileaks-twitter-injonction-cablegate-notification.htm
[pipa]: http://www.pcinpact.com/actu/news/64023-pipa-protect-ip-act-us.htm


## Installation

Je vais expliquer l'installation de _Tiny Tiny RSS_ pour ma configuration, à
savoir _Ubuntu Server 11.04_, avec _Apache_ et _MySQL_.  Je vais l'installer
dans `~/flux` (le répertoire `flux` de mon home), avec un lien symbolique
`/var/www/flux`. L'application sera accessible à partir de `flux.rom1v.com`.
Adaptez ces valeurs selon vos besoins.


### Dépendances

_Tiny Tiny RSS_ a besoin de `php5-curl` :

    sudo apt-get install php5-curl


### Téléchargement

Télécharger la dernière version en bas de [la page officielle][tt-rss] (actuellement la 1.5.4).

Extraire l'archive dans `~/` :

    tar xzf tt-rss-1.5.4.tar.gz

Et renommer le répertoire :

    mv tt-rss-1.5.4 flux


### Base de données

Il faut ensuite initialiser la base de données, grâce aux scripts fournis. Pour
cela, aller dans le répertoire des scripts :

    cd flux/schema

Puis se connecter à _MySQL_ :

    $ mysql -uroot -p
    Enter password:

Une fois connecté, créer la base de données `flux` :

    mysql> CREATE DATABASE flux;
    Query OK, 1 row affected (0,00 sec)

Puis créer un utilisateur `flux` avec les droits sur cette base (on pourra
générer son mot de passe grâce à [pwgen][]) :

[pwgen]: {% post_url 2009-11-21-generer-des-mots-de-passe-aleatoires %}

    mysql> GRANT ALL PRIVILEGES ON flux.* TO flux@localhost IDENTIFIED BY 'unmotdepasse';
    Query OK, 0 rows affected (0.04 sec)

Initialiser la base de données :

    mysql> USE flux
    Database changed

    mysql> \. ttrss_schema_mysql.sql

La base de données est prête.


### Configuration

Retourner dans le répertoire `~/flux` :

    cd ..

Copier le modèle du fichier de configuration :

    cp config.php-dist config.php

Puis l'éditer, par exemple :

    vim config.php

Modifier les informations de connexion à la base de données :

            define('DB_TYPE', "mysql");
            define('DB_HOST', "localhost");
            define('DB_USER', "flux");
            define('DB_NAME', "flux");
            define('DB_PASS', "unmotdepasse");

Modifier l'URL d'accès à l'application, pour moi :

            define('SELF_URL_PATH', 'http://flux.rom1v.com');

Désactiver le mode _utilisateur unique_ (sans quoi l'accès à l'application sera public sans authentification) :

            define('SINGLE_USER_MODE', false);

Si _Tiny Tiny RSS_ est installé à la racine du site (c'est mon cas :
`flux.rom1v.com/`), il faut modifier le répertoire d'icônes, car `/icons` est
réservé par _Apache_ :

            define('ICONS_DIR', "tt-icons");
            define('ICONS_URL', "tt-icons");

Je conseille de désactiver la vérification des nouvelles versions, car lorsque
le site de _Tiny Tiny RSS_ ne répond plus, l'application rencontre des
difficultés :

            define('CHECK_FOR_NEW_VERSION', false);

Pour les performances, activer la compression :

            define('ENABLE_GZIP_OUTPUT', true);

Enfin, une fois que la configuration est terminée, modifier la ligne :

            define('ISCONFIGURED', true);

Les modifications du fichier de configuration sont terminées.

Maintenant, renommer le répetoire `icons` (comme dans le fichier de
configuration) :

    mv icons tt-icons


### Serveur web

Il faut maintenant héberger l'application sur _Apache_.

Tout d'abord, donner les droits à `www-data` sur les répertoires où il a besoin
d'écrire :

    sudo chown -R www-data: cache tt-icons lock

Puis faire un lien symbolique vers le répertoire `/var/www/` :

    sudo ln -s ~/flux /var/www/

Créer (au besoin) un nouveau _VirtualHost_ pour le site, dans le répertoire
`/etc/apache2/sites-available` (pour moi dans un fichier nommé
`flux.rom1v.com`) :

{% highlight apache %}
<VirtualHost *:80>
  DocumentRoot  /var/www/flux
  ServerName  flux.rom1v.com
  
  <Directory /var/www/flux/>
    Options FollowSymLinks MultiViews
    AllowOverride All
    Order allow,deny
    allow from all
  </Directory>
  
  ErrorLog  /var/log/apache2/flux_error.log
  CustomLog /var/log/apache2/flux_access.log combined

</VirtualHost>
{% endhighlight %}

Activer le site :

    sudo a2ensite flux.rom1v.com

Redémarrer _Apache_ (un simple `reload` aurait suffit si nous n'avions pas
installé `php5-curl` tout à l'heure) :

    sudo service apache2 restart


## Configuration utilisateur


### Compte utilisateur

L'application doit maintenant fonctionner. S'y connecter, avec l'utilisateur
`admin` et le mot de passe `password` (l'utilisateur par défaut), puis aller
dans la configuration et changer le mot de passe.


### Importation et exportation

_Tiny Tiny RSS_ permet l'importation et l'exportation d'un fichier [OPML][]. Il
est ainsi possible de migrer facilement d'un gestionnaire de flux à un autre.

[opml]: http://fr.wikipedia.org/wiki/OPML


### Intégration à Firefox

Il est possible d'associer son instance de _Tiny Tiny RSS_ à _Firefox_ :
toujours dans la configuration, dans l'onglet _Flux_, _Intégration à Firefox_,
cliquer sur le bouton.

Pour tester, se rendre sur un site, et afficher la liste des flux disponibles.
Pour cela, cliquer sur le petit icône à gauche de l'adresse, puis sur _Plus
d'informations…_, sur l'onglet _Flux_ (s'il y en a un), et enfin sur le flux
désiré. Par exemple, pour [ce blog][] :

{: .center}
![blog-rss]({{ site.assets }}/tt-rss/blog-rss.png)

[ce blog]: /feed

En cliquant sur _S'abonner maintenant_, _Firefox_ devrait proposer d'utiliser
_Tiny Tiny RSS_.


## Programmation de la mise à jour des flux

Il reste encore une étape importante : le serveur doit régulièrement mettre à
jour le contenu de chacun des flux auxquels nous sommes abonnés.

Plusieurs méthodes sont décrites sur [cette page][updating]. Certaines chargent
les flux séquentiellement (par _cron_ notamment), ce qui peut poser problème :
supposons que nous soyons abonnés à 300 flux, avec une mise à jour toutes les 30
minutes, ça donne une moyenne de 6 secondes par flux. Si certains sites sont
long à répondre, la mise à jour risque de dépasser le temps imparti, et _cron_
va lancer une nouvelle tâche avant que la précédente soit terminée (heureusement
_Tiny Tiny RSS_ pose un verrou, donc il ne fera rien la seconde fois, mais du
coup nous perdons une mise à jour). Ceci est d'autant plus dommage que
l'essentiel de la durée nécessaire est le temps de connexion à chacun des
sites : mieux vaut donc paralléliser le chargement.

[updating]: https://tt-rss.org/gitlab/fox/tt-rss/wikis/UpdatingFeeds

C'est la raison pour laquelle je préfère la dernière méthode : **lancer un
_démon_ multi-processus au démarrage du serveur**. Par contre, étant donné le
fonctionnement du _démon_ proposé, il ne semble pas possible d'en faire un
script _init.d_ propre. Le plus simple est donc de rajouter dans
`/etc/rc.local` :

    start-stop-daemon -c www-data -Sbx /var/www/flux/update_daemon2.php

_Vous pouvez exécuter cette commande maintenant pour charger les flux la
première fois._

Ce _démon_ utilise plusieurs processus (par défaut 2), qui mettent à jour les
flux par blocs (par défaut, de 100). Pour changer ces variables (par exemple
pour avoir 5 processus qui chargent des blocs de 50), dans `config.php` :

            define('DAEMON_FEED_LIMIT', 50);

et dans `update_daemon2.php` :

            define('MAX_JOBS', 5);


## Autres interfaces

Une interface mobile en HTML est intégrée. Pour y accéder, il suffit d'ajouter à
l'URL `/mobile`.

Pour _Android_, il existe également une application :
[ttrss-reader-fork][] (à tester, mais je la trouve assez buggée). Pour lui
permettre l'accès, il est nécessaire de sélectionner _"Activer les API
externes"_ dans la page de configuration de _Tiny Tiny RSS_.

[ttrss-reader-fork]: https://github.com/nilsbraden/ttrss-reader-fork


## Conclusion

Vous n'avez plus de raison de laisser traîner vos flux RSS n'importe où ;-)
