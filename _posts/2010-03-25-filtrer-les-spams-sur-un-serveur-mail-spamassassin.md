---
layout: post
title: Filtrer les spams sur un serveur mail (SpamAssassin)
date: 2010-03-25 22:25:54+01:00
---

Pour continuer ma série d'articles sur l'auto-hébergement de ses mails, je vais
présenter l'installation de _SpamAssassin_.

Pour mon serveur mail (et plus généralement pour les outils que j'utilise),
j'essaie de mettre en place uniquement ce dont j'ai besoin. Et jusqu'ici, je
n'avais pas l'utilité d'un anti-spams, ne recevant aucun courrier indésirable.
Mais depuis peu, j'en reçois un de temps en temps… C'est donc l'occasion de m'y
mettre.


## Installation et configuration

Il existe plusieurs méthodes, j'ai choisi la plus simple : c'est _procmail_ qui
fournit les mails à _SpamAssassin_.

Il faut tout d'abord [installer et configurer procmail][procmail], puis
installer le paquet `spamassassin` :

[procmail]: {% post_url 2010-01-06-trier-ses-mails-directement-sur-le-serveur-procmail %}

    sudo apt-get install spamassassin

Ensuite, rajouter dans `~/.procmailrc` la règle suivante (copiée de [doc][]) :

[doc]: http://spamassassin.apache.org/full/3.0.x/dist/procmailrc.example

    # Pipe the mail through spamassassin (replace 'spamassassin' with 'spamc'
    # if you use the spamc/spamd combination)
    #
    # The condition line ensures that only messages smaller than 250 kB
    # (250 * 1024 = 256000 bytes) are processed by SpamAssassin. Most spam
    # isn't bigger than a few k and working with big messages can bring
    # SpamAssassin to its knees.
    #
    # The lock file ensures that only 1 spamassassin invocation happens
    # at 1 time, to keep the load down.
    #
    :0fw: spamassassin.lock
    * < 256000
    | spamassassin


Enfin, éditer `/etc/spamassassin/local.cf`.

Pour uniquement ajouter les en-têtes de spam (ce qui est suffisant pour
filtrer), il faut changer la valeur de `report_safe` :

    report_safe 0

Pour ajouter un tag dans le sujet d'un mail considéré comme un spam :

    rewrite_header Subject *****SPAM*****

Il est également possible de configurer le score requis pour qu'un mail soit
considéré comme un spam. Plus cette valeur est faible, plus le filtre est
agressif.  La valeur par défaut (5.0) est un peu faible, je vous conseille
d'augmenter un peu si vous voulez limiter les faux-positifs :

    required_score 6.0

Rajouter éventuellement les lignes suivantes :

    # Langues attendues (les autres auront un score plus élevé)
    ok_languages fr

    # Rapports en français
    lang fr

Pour ajouter un expéditeur en liste blanche, rajouter :

    whitelist_from any@mail.com


## Test

Pour tester, le plus simple est de mettre un filtre très sévère, par exemple avec un score négatif :

    required_score -2

En m'envoyant un mail à moi-même qui contient comme sujet _test_, je constate à
la réception que les en-têtes ont été modifiés :

    Subject: *****SPAM***** test
    Date: Thu, 25 Mar 2010 21:19:52 +0100
    Message-Id: <1269548392.9798.9.camel@rom-laptop>
    X-Spam-Flag: YES
    X-Spam-Checker-Version: SpamAssassin 3.2.5 (2008-06-10) on rom-eeebox
    X-Spam-Level: ***
    X-Spam-Status: Yes, score=3.9 required=-2.0 tests=ALL_TRUSTED,

Le mail a bien été détecté comme un spam. Ça fonctionne.


## Filtrage


Maintenant que les spams sont détectés, il faut les traiter (les déplacer dans
un dossier prévu à cet effet).

Il suffit pour cela de créer un dossier sur le serveur :

    maildirmake.dovecot ~/Maildir/.Spams

et d'ajouter la règle suivante dans ~/.procmailrc ([plus d'infos][procmail]) :

    :0
    * ^X-Spam-Status: Yes
    .Spams/


## Conclusion

Les spams auront maintenant un peu plus de mal à se glisser dans ma boîte mail.

La configuration présentée ici est vraiment minimale. Selon son efficacité il
faudra peut-être l'affiner.


## Voir aussi

Mes précédents billets sur l'auto-hébergement des mails :

 * [Hébergez vos mails sur Ubuntu Server (et libérez-vous)]({% post_url 2009-08-16-hebergez-vos-mails-sur-ubuntu-server-et-liberez-vous %})
 * [Installer un webmail (RoundCube) sur Ubuntu Server]({% post_url 2009-11-21-installer-un-webmail-roundcube-sur-ubuntu-server %})
 * [Ajouter l'authentification SMTP sur un serveur mail]({% post_url 2010-01-02-ajouter-lauthentification-smtp-sur-un-serveur-mail %})
 * [Trier ses mails directement sur le serveur (procmail)][procmail]
