---
layout: post
title: Hébergez vos mails sur Ubuntu Server (et libérez-vous)
date: 2009-08-16 21:52:28+02:00
tags:
- planet-libre
- puf
---

Après avoir acheté un petit serveur pour y héberger ce dont j'avais besoin, mon
objectif est d'héberger TOUT ce qui n'a rien à faire ailleurs. Et comme
l'explique Benjamin Bayart dans sa désormais célèbre conférence [Internet libre
ou Minitel 2.0][minitel2], toutes nos données personnelles entrent dans cette
catégorie.

[minitel2]: http://www.fdn.fr/Internet-libre-ou-Minitel-2.html

[Mon blog][blog] est un bon exemple d'un contenu qui ne doit pas être hébergé
ailleurs. La liste des flux RSS que je consulte aussi (c'est pourquoi j'ai
installé [tt-rss][]). Mon album photos à partager avec la
famille également (j'ai installé [gallery][]). Mais il restait le plus
important : les **mails**.

[blog]: {% post_url 2009-01-31-nouveau-blog-100-libre %}
[tt-rss]: http://tt-rss.org/
[gallery]: http://gallery.menalto.com/

Et c'est bien moins compliqué à installer que je ne l'imaginais !

Je vais donc présenter comment installer son propre serveur de mails sur
**Ubuntu Server** (si vous utilisez autre chose, ça ne devrait pas être bien dur
à adapter). Ainsi, vous pourrez avoir une jolie adresse `login@mondomaine`.


## Prérequis

Je supposerai que vous avez déjà un nom de domaine et que vous savez ajouter des
enregistrements `A` et `MX` (généralement dans l'interface fournie par votre
registrar).


## Présentation

Pour mettre en place un serveur mail complet, nous avons besoin de deux choses :
un serveur SMTP (qui gère le transport du courrier) et un serveur IMAP ou POP3
(permettant de se connecter à sa boîte aux lettres).

J'ai choisi respectivement **postfix** et **dovecot** (ceux par défaut dans
_Ubuntu Server_).

Il est possible de faire tout un tas de choses compliquées, ici je vais aller au
plus simple. Au final on obtiendra donc un compte mail par utilisateur système
(avec son mot de passe système), un serveur SMTP et un accès IMAP, le tout
sécurisé sur TLS. Le serveur SMTP ne pourra relayer les mails envoyés qu'à
partir du réseau local.


## Configuration DNS

Rajoutez deux records de type `A` à votre fichier de zones DNS (`smtp` et `imap`
– histoire de faire comme tout le monde, mais vous mettez ce que vous voulez –),
et ajoutez un record de type `MX` qui pointe vers votre enregistrement `smtp`
(`smtp.rom1v.com.` – n'oubliez pas le `.` à la fin –).

Il faut bien sûr ouvrir les ports sur le routeur et dans le pare-feu…
Pour rappel :

  * SMTP = 25
  * POP = 110
  * POP3S = 995
  * IMAP = 143
  * IMAPS = 993


## Serveur

### postfix



Si vous n'avez pas déjà les paquets installés :

    sudo apt-get install postfix dovecot-imapd


Si vous aviez déjà `postfix` :

    sudo dpkg-reconfigure postfix


Vous obtenez un écran de configuration _debconf_ qui va vous prendre par la main
pour la configuration :

{: .center}
![postfix-debconf]({{ site.assets }}/hebergement_mails/postfix-debconf.png)

  1. **Configuration type du serveur de messagerie :** Site Internet
  2. **Nom de courrier :** votre nom de domaine (`rom1v.com` pour moi)
  3. **Destinataire des courriels de « root » et de « postmaster » :** votre
login sur le serveur (`rom` pour moi)
  4. **Autres destinations pour lesquelles le courrier sera accepté :** rajoutez
votre nom de domaine dans la liste (`rom1v.com`)
  5. **Forcer des mises à jour synchronisées de la file d'attente des
courriels :** non (laisser par défaut)
  6. **Réseaux internes :** `127.0.0.0/8, 192.168.0.0/24` si votre réseau local
est `192.168.0.x`
  7. **Utiliser procmail pour la distribution locale :** non (allons au plus
simple)
  8. **Taille maximale des boîtes aux lettres (en octets) :** à vous de choisir,
moi j'ai mis 5Gio (5368709120)
  9. **Caractère d'extension pour les adresse locales :** `+` (laisser par
défaut)
  10. **Protocoles internet à utiliser :** `all`

Vous avez alors un fichier `/etc/postfix/main.cf` qui ressemble à ceci (sauf la
dernière ligne) :

~~~
myhostname = rom1v.com
alias_maps = hash:/etc/aliases
alias_database = hash:/etc/aliases
mydestination = rom1v.com, rom-eeebox, localhost.localdomain, localhost
relayhost =
mynetworks = 127.0.0.0/8, 192.168.0.0/24
mailbox_size_limit = 5368709120
recipient_delimiter = +
inet_interfaces = all
myorigin = /etc/mailname
inet_protocols = all
home_mailbox = Maildir/
~~~

Ajoutez donc la dernière ligne (c'est très important pour faire fonctionner
IMAP).

Par défaut, les mails sont limités à 10Mio (ce qui paraît normal). Cependant, il
arrive toujours que quelqu'un dans votre entourage vous envoie 10 photos de 3Mio
chacune. Pour cela il faut ajouter dans `/etc/postfix/main.cf` la ligne suivante
(pour 50Mio) :

~~~
message_size_limit = 52428800
~~~

Voilà, le serveur SMTP est prêt :

    sudo /etc/init.d/postfix restart

Il ne sera possible d'envoyer un mail qu'à partir des réseaux définis dans
`mynetworks` (c'est pour cela qu'il n'y a pas d'authentification par défaut).

Pour mettre en place une authentification plutôt que de limiter l'accès à une
liste de réseaux, lisez [ce billet][auth].

[auth]: {% post_url 2010-01-02-ajouter-lauthentification-smtp-sur-un-serveur-mail %}


### dovecot

Il y a deux choses à changer dans le fichier `/etc/dovecot/dovecot.conf`, tout
d'abord pour activer le protocole IMAP :

~~~
protocols = imap
~~~

Ensuite pour choisir le répertoire de stockage des mails pour les utilisateurs
(forcément `~/Maildir`) :

~~~
mail_location = maildir:~/Maildir
~~~

Enfin, il faut préparer ce répertoire en exécutant la commande :

~~~
maildirmake.dovecot ~/Maildir
~~~

Pour forcer SSL/TLS :

~~~
ssl = required
~~~

Voilà, c'est fini :

    sudo /etc/init.d/dovecot restart


## Client

Le serveur est configuré, nous pouvons maintenant l'utiliser.


### mailutils


Commençons par le tester grâce au paquet `mailutils` qui permet d'envoyer des
mails en ligne de commande :

    sudo apt-get install mailutils

Directement sur le serveur :

~~~
$ mail login@mondomaine
Cc:
Subject: Mon premier serveur
Ça y'est, j'ai configuré mon premier serveur !

~~~
_(terminer avec une nouvelle ligne suivi de Ctrl+D)_

Le mail a dû arriver dans `~/Maildir/new`.


### Un vrai client

Comme pour n'importe quelle adresse e-mail, il suffit de configurer le client
(Evolution par exemple) en renseignant les champs demandés.


#### Réception

 * **Type de serveur :** IMAP
 * **Serveur :** `imap.rom1v.com` _(à adapter avec votre nom de domaine)_
 * **Nom d'utilisateur :** _votre login sur le serveur_
 * **Sécurité :** Chiffrement TLS
 * **Type d'authentification :** Mot de passe


#### Envoi

 * **Type de serveur :** SMTP
 * **Serveur :** `smtp.rom1v.com` _(à adapter avec votre nom de domaine)_
 * **Nom d'utilisateur :** _votre login sur le serveur_
 * **Sécurité :** Chiffrement TLS
 * Pas d'authentification.


## Sauvegardes

Bien entendu, maintenant que vous hébergez vos mails, vous êtes responsables de
leur stockage, et donc des backups. Mais j'imagine que cela ne vous posera pas
de problèmes, car vous avez bien évidemment déjà mis en place un système (au
moins `rsync`) qui sauvegarde vos données sur une autre machine : il suffira
donc de rajouter le répertoire `~/Mailbox` à la liste des répertoires
sauvegardés.


## Disponibilité

Si le serveur est déconnecté moins de 5 jours, ce n'est pas catastrophique,
les mails ne seront probablement pas perdus, la majorité des serveurs SMTP
tentent de renvoyer le courrier après un échec. Évidemment, si le serveur est
éteint durant un mois, il n'y a pas de magie : pas de serveur, pas de mails…


## Conclusion

Cette étape est pour moi un pas de plus vers un internet libre…

J'ai présenté ici le minimum vital, mais vous pouvez trouver des fonctionnalités
à mettre en place (un webmail tel que [RoundCube][], un [anti-spam][], le [tri
des mails sur le serveur][tri], une [authentification SMTP][auth]…).

[RoundCube]: {% post_url 2009-11-21-installer-un-webmail-roundcube-sur-ubuntu-server %}
[anti-spam]: {% post_url 2010-03-25-filtrer-les-spams-sur-un-serveur-mail-spamassassin %}
[tri]: {% post_url 2010-01-06-trier-ses-mails-directement-sur-le-serveur-procmail %}

Amusez-vous bien !
