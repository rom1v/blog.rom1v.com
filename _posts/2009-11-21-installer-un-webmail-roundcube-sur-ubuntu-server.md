---
layout: post
title: Installer un webmail (Roundcube) sur Ubuntu Server
date: 2009-11-21 18:38:55+01:00
tags:
- planet-libre
- puf
---

Un de mes précédents billets présentait l'[installation d'un serveur de mails
sur Ubuntu Server][mails]. Une fois installé, il était possible d'accéder à son
courrier grâce à un client de messagerie.

[mails]: {% post_url 2009-08-16-hebergez-vos-mails-sur-ubuntu-server-et-liberez-vous %}

Il peut-être pratique, en plus de cela, d'accéder à ses mails par un _webmail_
de n'importe où (notamment au travail, où il ne sera pas filtré comme celui de
_gmail_ ou de _yahoo_, puisque c'est un webmail perso).

Je profite de la réinstallation à neuf de mon serveur pour présenter
l'installation de [Roundcube][], un webmail assez moderne, à installer sur
[LAMP][] (avec accès HTTPS) sur Ubuntu Server.

[roundcube]: http://roundcube.net/
[lamp]: http://doc.ubuntu-fr.org/lamp

Voici ce que ça donne une fois installé :

{: .center}
![roundcube]({{ site.assets }}/roundcube/roundcube.png)

_Je partirai du principe que le webmail est sur la même machine que le serveur
SMTP et que le répertoire `~/Maildir` (contenant les mails), et qu'il sera le
seul site hébergé en HTTPS sur le serveur._


## Téléchargement

Tout d'abord, il faut télécharger l'archive sur le site de
[Roundcube][roundcube-dl] (la version complète), et l'extraire dans un
répertoire :

    tar xzf roundcubemail-0.3.1.tar.gz

[roundcube-dl]: https://roundcube.net/download

Il est plus pratique de le renommer :

    mv roundcubemail-0.3.1 mail

Ensuite, il faut donner les droits au serveur d'écrire dans les répertoires
`temp` et `logs` :

    sudo chmod -R 777 mail/temp mail/logs

Enfin, faire un lien du répertoire d'installation de Roundcube (par exemple
`~/mail`) dans `/var/www`.

    sudo ln -s ~/mail/ /var/www


## Préparation de la base de données

Il faut créer une base de données qui sera utilisée par Roundcube, avec son
propre utilisateur. Pour cela, démarrer _mysql_ en tant qu'administrateur (le
login et le mot de passe choisi lors de la configuration de _mysql_) :

    $ mysql -uroot -p
    Enter password:

Ensuite, créer une base de données :

    mysql> CREATE DATABASE mail;
    Query OK, 1 row affected (0,00 sec)

Puis un utilisateur _mysql_ (par exemple `mailuser`/`unmotdepasse`) :

    mysql> GRANT ALL PRIVILEGES ON mail.* TO mailuser@localhost
        -> IDENTIFIED BY 'unmotdepasse';
    Query OK, 0 rows affected (0,00 sec)

Voilà, la base de données est prête à accueillir Roundcube.


## Configuration d'Apache


Tout d'abord, il faut activer le mod `ssl` d'apache :

    sudo a2enmod ssl


Et renseigner le champ `date.timezone` de `php.ini` :

    sudo vi /etc/php5/apache2/php.ini

Remplacer la ligne :

    ;date.timezone =

par :

    date.timezone = 'Europe/Paris'

Ensuite, il faut créer un _VirtualHost_ qui décrit à Apache le site qu'il doit
héberger (le nom du site sur lequel il répond en http, quels répertoires
doivent être accessibles, etc.).

Voici un modèle (qui correspond à mon _VirtualHost_), qui permet :

  * d'afficher le site contenu dans `/var/www/mail` lorsqu'une requête arrive
    sur le port 443 (`https`), c'est-à-dire quand je tape
    `https://mail.rom1v.com` dans un navigateur ;
  * d'interdire l'accès aux répertoires `config`, `temp` et `logs` ;
  * de rediriger automatiquement `http://mail.rom1v.com` vers
    `https://mail.rom1v.com`.

{% highlight apache %}
NameVirtualHost *:443

<VirtualHost *:80>
  ServerName  mail.rom1v.com
  Redirect  / https://mail.rom1v.com/
</VirtualHost>

<VirtualHost *:443>
  DocumentRoot  /var/www/mail
  ServerName  mail.rom1v.com
  SSLEngine on
  SSLCertificateFile /etc/ssl/certs/ssl-cert-snakeoil.pem
  SSLCertificateKeyFile /etc/ssl/private/ssl-cert-snakeoil.key

  <Directory /var/www/mail/>
    Options FollowSymLinks MultiViews
    AllowOverride All
    Order allow,deny
    allow from all
  </Directory>
  
  <Directory /var/www/mail/config>
    Options -FollowSymLinks
    AllowOverride None
  </Directory>
  
  <Directory /var/www/mail/temp>
    Options -FollowSymLinks
    AllowOverride None
  </Directory>
  
  <Directory /var/www/mail/logs>
    Options -FollowSymLinks
    AllowOverride None
    Order allow,deny
    Deny from all
  </Directory>
  
  ErrorLog  /var/log/apache2/mail_error.log
  CustomLog /var/log/apache2/mail_access.log combined

</VirtualHost>
{% endhighlight %}


Ce fichier, une fois adapté à votre serveur, est à écrire dans
`/etc/apache2/sites-available/` (dans un fichier nommé comme votre site par
exemple, `mail.rom1v.com` pour moi).

Il faut ensuite l'activer :

    sudo a2ensite mail.rom1v.com

Et recharger Apache :

    sudo /etc/init.d/apache2 reload


## Configuration de Roundcube

Il ne reste plus qu'à aller sur `http://votreserveur/installer` avec un
navigateur pour configurer Roundcube.

Bien sûr, lors de la première connexion, Firefox vous alerte car il ne connaît
pas votre certificat SSL (qui est auto-signé), il suffit de lui indiquer que
vous faites confiance à ce site (puisque c'est le vôtre) en ajoutant une
exception.

{: .center}
![firefox-ssl]({{ site.assets }}/roundcube/firefox-ssl.png)

L'**étape 1** _(Check environment)_ doit bien se passer (à part quelques
modules optionnels que vous n'avez pas, ce n'est pas grave).

Pour l'**étape 2** _(Create config)_ :

  * Choisir un `product_name` : le titre des pages souhaité ;
  * Activer `ip_check` ;
  * Désactiver `enable_spellcheck` (sinon tous vos mails seront envoyés à
    Google) ;
  * Renseigner les données pour l'accès à la base de données (que nous avons
    créée tout à l'heure) ;
  * Dans la partie IMAP, renseigner uniquement `default_host` à la valeur
    `localhost` ;
  * Laissez par défaut le reste de la partie IMAP et la partie SMTP (vu que les
    mails sont sur la même machine).

Lors de la validation, deux fichiers sont générés. Il faut les télécharger et
les placer dans le répertoire `config/` _(ce n'est pas très pratique
d'ailleurs, lorsqu'on accède au serveur par ssh, devoir télécharger les
fichiers localement pour les renvoyer au serveur est un peu tordu)_.

L'**étape 3** _(Test config)_ permet de tester que tout est OK. Il faut cliquer
sur _Initialize database_.

Une fois ces étapes effectuées, rendez-vous sur l'adresse du webmail, vérifiez
que ça fonctionne (avec votre login système et votre mot de passe). Si tout est
ok, supprimez le répertoire `installer/` :

    rm -rf /var/www/mail/installer


## Modifier les préférences

Une fois connecté, il est possible de changer les préférences utilisateur. Je
vous laisse découvrir les options par vous-même, mais certaines sont
importantes.

Tout d'abord, dans l'onglet _identités_, renseignez votre adresse mail (par
défaut c'est `login@localhost`, ce qui posera des problèmes à ceux qui voudront
vous répondre).

Ensuite, activez les options qui concernent la suppression d'un message sur un
compte IMAP dans _Préférences du serveur_ de l'onglet _Préférences_ :

  * _Mettre le drapeau de suppression au lieu de supprimer_ (sinon vous ne
    pourrez pas supprimer de messages) ;
  * _Ne pas montrer les messages supprimés_ (sinon vous les verrez encore en
    barré).


## Conclusion

Bravo. Vous avez maintenant un serveur mail perso avec un accès webmail sur
HTTPS en plus de votre accès IMAP/TLS. Vous gagnez 1 point de liberté et 1
point de confort.
