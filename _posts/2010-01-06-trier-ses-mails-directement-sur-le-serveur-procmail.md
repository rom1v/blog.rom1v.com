---
layout: post
title: Trier ses mails directement sur le serveur (procmail)
date: 2010-01-06 17:43:18+01:00
---


Dans la continuité des articles consacrés à l'[auto-hébergement des
mails][selfhost], je vais présenter quelque chose que je voulais mettre en place
depuis un moment : **le tri du courrier directement sur le serveur**.

[selfhost]: {% post_url 2009-11-21-installer-un-webmail-roundcube-sur-ubuntu-server %}


## Introduction

Lorsqu'on est abonné à des mailing-lists ou qu'on reçoit des notifications de
forums ou de blogs, il est inconcevable de garder tous ses mails dans un seul et
même dossier, et impensable de les déplacer manuellement (à moins de passer 30
minutes par jour à les trier). Un tri doit être mis en place automatiquement, en
se basant sur les en-têtes des mails reçus.

J'utilisais jusqu'à maintenant les filtres de messages de mon client mail,
_Evolution_, mais ça n'était pas forcément approprié :

  * d'une part c'est très long avec un compte IMAP (les dossiers étant gérés
    côté serveur), car le client doit récupérer localement les nouveaux
    messages du serveur et les analyser ; s'il faut en déplacer un, il doit
    demander au serveur de le copier de la boîte de réception vers le dossier
    destination adéquat, puis demander de le supprimer de la boîte de réception,
    et enfin récupérer le messages déplacé… Rien que ça !
  * d'autre part, lorsqu'on accède à ses mails à partir de plusieurs endroits
    (par exemple le client mail, le webmail et le téléphone portable), il
    devient évident que ce ne peut pas être le rôle des clients de trier les
    messages…

C'est donc au serveur de placer les mails dans le bon dossier dès la réception.
C'est ce que **procmail** permet de faire.


## Les dossiers IMAP

Les dossiers IMAP sont des dossiers physiques contenus dans `~/Maildir` (le
répertoire des mails) qui respectent une structure particulière :

  * leur nom commence par `.` (ce sont des dossiers cachés) et les sous-dossiers
    "logiques" sont séparés par des `.` (par exemple, si je veux un dossier `a`
    contenant un sous-dossier `b`, le répertoire physique sera
    `~/Maildir/.a.b`) ;
  * ils contiennent 3 sous-dossiers physiques : `cur`, `new` et `tmp`.

Pour les créer, il suffit d'utiliser `maildirmake` ou `maildirmake.dovecot`, à
partir du répertoire `~/Maildir` :

    maildirmake.dovecot .forums.ubuntu-fr
    maildirmake.dovecot .forums.developpez

pour obtenir l'arborescence suivante :

    |-- .forums.developpez
    |   |-- cur
    |   |-- new
    |   `-- tmp
    `-- .forums.ubuntu-fr
        |-- cur
        |-- new
        `-- tmp

Il est également possible de les créer graphiquement grâce à un client mail.


## Configuration de postfix

Il faut indiquer à **postfix** que **procmail** va s'occuper de trier les mails,
en lui précisant dans `/etc/postfix/main.cf` :

    mailbox_command = /usr/bin/procmail

Il faudra ensuite recharger la configuration :

    sudo /etc/init.d/postfix reload


## Définir les règles de tri

Tout se passe dans le fichier (à créer) `~/.procmailrc`, qui contient deux
parties : la définition des variables et la définition des "recettes" (les
règles de tri).


### Les variables

Pour les variables, copiez simplement ceci (en décommentant les 2 premières
lignes si vous voulez des logs).

    #VERBOSE=yes
    #LOGFILE=.procmail.log
    SHELL=/bin/sh
    PATH=/bin:/usr/bin:/usr/local/bin
    MAILDIR=Maildir/
    DEFAULT=./


### Les recettes

Les recettes sont écrites sous la forme suivante :

    :0 [drapeaux] [ : [verrou_local] ]
    <zéro ou plusieurs conditions (une par ligne)>
    <exactement une ligne d'action>

Les conditions commencent toutes par `*`, suivie d'une expression régulière.
Pour qu'une recette exécute l'action définie, il faut que le mail en question
valide **toutes** les conditions.

Pour faire simple, nous allons simplement créer des règles qui déplacent des
mails dans des dossiers. Pour définir une telle action, il suffit d'écrire le
nom du dossier, en terminant la ligne par `/` (très important, cette convention
indique à **procmail** que le dossier est au format _maildir_).

Un exemple étant plus parlant, voici une règle qui déplace toutes mes
notifications de blog dans un dossier `blog` :

    :0
    * ^From: .*<wordpress@blog\.rom1v\.com>$
    .blog/

Cet autre exemple permet d'envoyer une copie des mails validant les conditions à
des adresses e-mails spécifiées (je m'en sers pour transférer les messages
vocaux de mon répondeur téléphonique sur plusieurs adresses) :

    :0c
    * ^From: telephonie\.freebox@freetelecom\.com$
    ! autre@email.com


### Résultat

Au final, voici un extrait de mon fichier `~/.procmailrc` (je n'ai pas mis
toutes les règles, c'est juste pour donner quelques exemples) :

    #VERBOSE=yes
    #LOGFILE=.procmail.log
    SHELL=/bin/sh
    PATH=/bin:/usr/bin:/usr/local/bin
    MAILDIR=Maildir/
    DEFAULT=./

    :0
    * ^From: .*<wordpress@blog\.rom1v\.com>$
    .blog/

    :0
    * ^Reply-To: .*<[0-9]+@bugs\.launchpad\.net>$
    .bugs.launchpad/

    :0
    * ^From: .*<dev\.null@ubuntu-fr\.org>$
    .forums.ubuntu-fr/

    :0
    * ^List-Id: <april\.april\.org>$
    .ml.april/
