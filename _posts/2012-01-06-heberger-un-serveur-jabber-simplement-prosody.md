---
layout: post
title: Héberger un serveur Jabber simplement (prosody)
date: 2012-01-06 22:12:58+01:00
tags:
- planet-libre
- puf
---

{: .center}
![jabber]({{ site.assets }}/prosody/jabber.png)

J'ai enfin décidé d'héberger mon propre serveur [Jabber][], pour plusieurs raisons :

[jabber]: http://www.jabberfr.org/

  * la liste de mes contacts est mieux sur mon serveur que sur un autre ;
  * le serveur que j'utilisais ([jabber.fr][]) rencontre parfois quelques
    difficultés ;
  * mon adresse _Jabber_ sera ainsi la même que [mon adresse mail][mail] (`rom`
    suivi de `@rom1v.com`).

[jabber.fr]: https://jabber.apinc.org/
[mail]: {% post_url 2009-08-16-hebergez-vos-mails-sur-ubuntu-server-et-liberez-vous %}

Et c'est simple !


## Installation et configuration

Tout d'abord, installer le paquet `prosody` :

    apt-get install prosody

Puis ajouter à la fin du fichier `/etc/prosody/prosody.cfg.lua` :

    Host "<em>nom.de.domaine</em>"

Pour moi :

    Host "rom1v.com"

Créer un utilisateur en ligne de commande et choisir un mot de passe :

    prosodyctl adduser utilisateur@nom.de.domaine


## Certificat

Un [certificat][] TLS/SSL est créé par défaut, mais les champs sont renseignés
avec des valeurs non pertinentes (`localhost` au lieu de `nom.de.domaine` par
exemple). Il est donc préférable d'en [générer un nouveau][prosody
certificates].

[certificat]: http://fr.wikipedia.org/wiki/Certificat_%C3%A9lectronique#Certificat
[prosody certificates]: http://prosody.im/doc/certificates

Dans le répertoire `/etc/prosody/certs`, exécuter :

    openssl req -new -x509 -nodes -out nom.de.domaine.cert -keyout \
        nom.de.domaine.key -days 1000

Renseigner les champs demandés _(« `.` » pour laisser un champ vide)_. En
particulier, indiquer dans `Common Name` le nom de domaine pour lequel ce
certificat sera utilisé.

Remplacer le certificat dans le fichier de configuration :

    ssl = {
            key = "/etc/prosody/certs/nom.de.domaine.key";
            certificate = "/etc/prosody/certs/nom.de.domaine.cert";
    }


### Empreinte

Comme c'est un certificat auto-signé, les clients _Jabber_ ne lui feront pas
confiance : ils demanderont une confirmation, en présentant son empreinte. Il
faudra alors vérifier que le certificat présenté est bien le bon, c'est-à-dire
que l'empreinte est la même.

Pour la connaître :

    openssl x509 -fingerprint -noout -in nom.de.domaine.cert

Par exemple :

    $ openssl x509 -fingerprint -noout -in rom1v.com.cert
    SHA1 Fingerprint=C3:6D:9B:65:06:55:C4:84:B4:A5:8D:4B:12:68:2F:08:71:7E:AC:DD


## Ports

Les [ports][] TCP 5222 et 5269 [doivent être ouverts][jabber ports].

[ports]: http://fr.wikipedia.org/wiki/Liste_des_ports_logiciels
[jabber ports]: http://www.accessgrid.org/agdp/guide/ports/1.03/x112.html


## Démarrer

Il ne reste plus qu'à démarrer le service.

    service prosody start


## Clients

Il est maintenant possible de se connecter en utilisant le nom d'utilisateur et
le mot de passe créés :

{: .center}
![empathy]({{ site.assets }}/prosody/empathy.png)


## Backup

Les données du serveur sont stockées dans `/var/lib/prosody`. Il est donc
important de ne pas oublier ce répertoire dans le processus de
[sauvegarde][].

[sauvegarde]: http://fr.wikipedia.org/wiki/Sauvegarde

_Merci à [Cyrille Borne][] et [nicolargo][]._

[Cyrille Borne]: http://www.cyrille-borne.com/index.php?post/2011/01/13/Faire-son-serveur-jabber-personnel-en-moins-de-5-minutes
[nicolargo]: http://blog.nicolargo.com/2011/01/un-serveur-jabber-en-5-minutes-chronos-sous-debianubuntu.html
