---
layout: post
title: Ajouter l'authentification SMTP sur un serveur mail
date: 2010-01-02 20:23:05+01:00
---

Ce billet vient compléter mon premier billet concernant l'[installation d'un
serveur mail sur Ubuntu Server][installation].

[installation]: {% post_url 2009-08-16-hebergez-vos-mails-sur-ubuntu-server-et-liberez-vous %}


## Objectif

La configuration de **postfix** présentée dans mon premier billet limitait
(dans un but de sécurité) l'envoi d'un mail à une personne distante qu'à partir
du réseau local (ou une liste de réseaux prédéfinis). Cela est parfait
lorsqu'on envoie toujours les mails de chez soi, avec au besoin la possibilité
d'envoyer un mail de n'importe où grâce au [webmail][].

[webmail]: {% post_url 2009-11-21-installer-un-webmail-roundcube-sur-ubuntu-server %}

Mais l'utilisation du SMTP à distance devient utile lorsqu'on veut envoyer un
mail à partir de chez un ami avec son client mail (plus pratique pour les
pièces jointes par exemple), et cela devient carrément indispensable lorsqu'on
veut écrire des mails à partir de son téléphone de n'importe où (sans IP fixe).

Ne plus restreindre l'utilisation du SMTP au réseau local implique évidemment
de rajouter une couche d'authentification…

Je vais donc décrire comment mettre en place une authentification SMTP-AUTH "en
clair" (bien sûr encapsulée dans une connexion chiffrée TLS, déjà configurée si
vous avez suivi le premier tuto) qui correspond au login et mot de passe de
l'utilisateur système. Il a été écrit pour une installation sur **Ubuntu Server
9.10**, il faudra donc peut-être l'adapter légèrement si vous utilisez autre
chose.


## Configuration de SASL

Il faut installer le paquet `sasl2-bin` :

    sudo apt-get install sasl2-bin

et ajouter l'utilisateur `postfix` au groupe `sasl` :

    sudo adduser postfix sasl


Ouvrez ensuite `/etc/default/saslauthd`, remplacez :

    START=no

par :

    START=yes

et remplacez la dernière ligne par :

    OPTIONS="-c -m /var/spool/postfix/var/run/saslauthd"

Démarrez le service :

    sudo /etc/init.d/saslauthd start


## Configuration de postfix

À la fin de `/etc/postfix/main.cf`, rajoutez :

    # SASL
    smtpd_sasl_auth_enable = yes
    smtpd_recipient_restrictions = permit_sasl_authenticated,permit_mynetworks,reject_unauth_destination

_Dans ce même fichier, vous pouvez également supprimer le réseau
`192.168.0.0/24` de la variable `mynetworks` (si vous l'aviez rajouté)._

Créez le fichier `/etc/postfix/sasl/smtpd.conf` contenant :

    pwcheck_method: saslauthd
    mech_list: plain login

Rechargez la configuration de postfix :

    sudo /etc/init.d/postfix reload

Voilà, tout est prêt.


## Configuration du client mail

Dans votre client mail, indiquez que le serveur SMTP requiert une
authentification, de type CLAIR (ou PLAIN), et précisez votre compte
utilisateur à utiliser.
