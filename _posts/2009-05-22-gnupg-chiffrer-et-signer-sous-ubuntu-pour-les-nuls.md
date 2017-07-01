---
layout: post
title: 'GnuPG : chiffrer et signer sous Ubuntu pour les nuls'
date: 2009-05-22 10:26:22+02:00
tags:
- planet-libre
- puf
---

Ce billet présente l'utilisation de [GnuPG][] sous Ubuntu pour chiffrer ses
fichiers, ses mails ou sa messagerie instantanée. Tout ceci **sans jamais passer
par la ligne de commande**.

[gnupg]: https://fr.wikipedia.org/wiki/GNU_Privacy_Guard


## Principe

Je ne vais pas expliquer comment fonctionne le chiffrement, c'est déjà bien
expliqué sur [wikipedia][]. **Il est important de comprendre le principe.**

[wikipedia]: http://fr.wikipedia.org/wiki/Cryptographie_asym%C3%A9trique

Pour résumer, si A possède une clé publique _Apub_ et une clé privée _Apriv_, si
B possède une clé publique _Bpub_ et une clé privée _Bpriv_. et si A envoie un
message à B :

  * A peut chiffrer son message pour B en utilisant _Bpub_ ;
  * A peut signer son message en utilisant _Apriv_ ;
  * B peut déchiffrer le message reçu de A en utilisant _Bpriv_ ;
  * B peut vérifier la signature du message reçu de A en utilisant _Apub_.


## Créer sa paire de clés

{: .center}
![menu_chiffrement]({{ site.assets }}/gnupg/menu_chiffrement.png)

Pour créer sa paire de clés (une clé publique et une clé privée) :

  * ouvrir Applications → Accessoires → Mots de passe et clés de chiffrement ;
  * dans la fenêtre qui s'ouvre, cliquer sur Fichier → Nouveau… (Ctrl+N) ;
  * choisir « _Clé PGP (utilisée pour chiffrer les courriels et les fichiers)_ »
    et cliquer sur _Continuer_.

Quelques informations sont demandées :

{: .center}
![gpg_new_key]({{ site.assets }}/gnupg/gpg_new_key.png)

Personnellement, je préfère décocher « _N'expire jamais_ », et faire expirer la
clé au bout de deux ans, au cas où la clé serait perdue…

Il ne reste plus qu'à cliquer sur _Créer_, une _phrase de passe_ (un long mot de
passe) est demandée, et les clés sont générées. Une nouvelle ligne apparaît
alors dans « _Mes clés personnelles_ » :

{: .center}
![seahorse]({{ site.assets }}/gnupg/seahorse.png)

_Si vous avez plusieurs e-mails et/ou adresses Jabber, vous pouvez les rajouter
en cliquant droit sur votre clé, Propriétés, Noms et signatures_.

Une fois créée, je vous conseille de garder une copie du répertoire `~/.gnupg`,
qui contient votre clé privée, sur un support externe (une clé USB).


## Déverrouiller la clé privée durablement

Il est possible de laisser la clé déverrouillée pendant un certain temps après
avoir tapé la phrase de passe. Cela évite de la retaper à chaque fois.

Pour changer ce comportement, il faut aller dans Système → Préférences →
Chiffrement et trousseaux, dans l'onglet « _Phrases de passe PGP_ » :

{: .center}
![gpg_unlock]({{ site.assets }}/gnupg/gpg_unlock.png)


## Exporter sa clé publique

Maintenant que nous avons créé notre paire de clés, il faut que notre clé
publique soit accessible à ceux avec qui nous souhaitons communiquer.

Il suffit pour cela de sélectionner la clé et de cliquer sur le bouton
_Exporter…_ : la clé publique sera alors exportée dans un fichier portant
l'extension `.asc`. Il ne reste plus qu'à donner ce fichier à notre contact, qui
n'aura qu'à double-cliquer dessus (à partir du navigateur de fichiers) ou
l'importer dans Fichier → Importer… (Ctrl+I).

Il est également possible de l'exporter dans le presse-papier, pour pouvoir la
coller n'importe où avec Ctrl+V, sans passer par un fichier : il suffit pour
cela de cliquer-droit sur la clé, puis de cliquer sur _Copier_.

Pour une diffusion plus globale, il existe des **serveurs de clés** : ils
répertorient les clés publiques de tout le monde. Par exemple, il est possible
de publier sa clé sur [pgp.mit.edu][], en y copiant le contenu du fichier `.asc`
exporté.

[pgp.mit.edu]: https://pgp.mit.edu

**Attention : une clé publiée ne sera jamais supprimée du serveur, elle pourra
simplement être révoquée, en créant un certificat de révocation, indiquant à
tous que votre clé est invalide. Ne publiez donc que votre clé "définitive".**

Il est également possible de configurer le gestionnaire de clés pour qu'il les
publie et synchronise directement, en activant dans Édition → Préférences →
Serveurs de clés → _Publier les clés sur…_.

Grâce à ces serveurs de clés, il est facile de trouver la clé publique d'une
personne directement dans le gestionnaire de clés. Il faut cliquer sur le bouton
« _Chercher des clés distantes…_ » et taper le nom de la personne, son mail ou
l'identifiant de sa clé (la suite de 8 caractères hexadécimale qui apparaît dans
la liste des clés) :

{: .center}
![gpg_search]({{ site.assets }}/gnupg/gpg_search.png)

Et le résultat :

{: .center}
![gpg_results]({{ site.assets }}/gnupg/gpg_results.png)

_(la première est barrée car c'était mon ancienne clé, que j'ai révoquée lorsque
mon ordinateur a été volé)_

Il ne reste plus qu'à cliquer sur _Importer_.


## Signer les clés obtenues de confiance

Une fois la clé d'un contact récupérée, il est possible de la signer pour
indiquer qu'on a confiance en cette clé. Certains logiciels n'acceptent
d'ailleurs que les clés de confiance. Pour cela, dans l'onglet « _Autres clés
obtenues_ » du gestionnaire de clés, il faut cliquer-droit sur une clé, puis
« _Signer la clé…_ » :

{: .center}
![gpg_sign_key]({{ site.assets }}/gnupg/gpg_sign_key.png)

**Avant de signer une clé, il est très important de comparer l'empreinte
affichée dans les détails de la clé que vous avez récupérée avec celle de
votre contact.** Cela permet de détecter la récupération d'une fausse clé.

Voilà, maintenant tout est en place, nous pouvons commencer à chiffrer et à
signer.


### Chiffrer et signer des fichiers

C'est très simple : il suffit de cliquer-droit sur un fichier, et de choisir
_Chiffrer_ ou _Signer_ :

{: .center}
![gpg_contextual_menu]({{ site.assets }}/gnupg/gpg_contextual_menu.png)

Ces fonctions nécessitent le paquet `seahorse-plugins`, qui n'est plus installé
par défaut dans Ubuntu 9.10).


### Chiffrer

L'outil de chiffrement demande les destinataires qui pourront déchiffrer le
fichier (avec leur clé privée). Tous ceux n'étant pas dans la liste des
destinataire n'auront aucun moyen de déchiffrer le fichier. En particulier, il
peut être utile de s'ajouter en destinataire.

Il est également possible de signer le fichier en même temps (pour que celui qui
le déchiffre soit sûr de l'identité de celui qui l'a chiffré).

{: .center}
![gpg_encrypt]({{ site.assets }}/gnupg/gpg_encrypt.png)

Il ne reste plus qu'à cliquer sur _Valider_. Lorsque l'on choisit de signer le
fichier en même temps, la phrase de passe de la clé privée est demandée.
Ensuite, le fichier est chiffré dans un nouveau fichier portant l'extension
`.pgp` (alors qu'en ligne de commande, cela crée un fichier `.gpg`, mais peu
importe).

Pour le déchiffrer, il suffit de double-cliquer dessus.


### Signer uniquement

L'outil de signature demande avec quelle clé nous souhaitons signer (utile si
plusieurs utilisateurs utilisent chacun une clé), demande ensuite la phrase de
passe (pour déverrouiller la clé), et crée la signature dans un fichier portant
l'extension `.sig`.

Pour vérifier la signature, il suffit de double-cliquer sur ce `.sig`, une
notification indiquera si la signature est valide :

{: .center}
![gpg_verif_sign]({{ site.assets }}/gnupg/gpg_verif_sign.png)

_Si le fichier est assez volumineux, cela peut prendre un moment (20 ou 30
secondes), et parfois aucune fenêtre ne s'ouvre indiquant que la vérification
est en cours, ce qui est assez perturbant ; mais le processeur, lui, tourne bien
à plein régime pour vérifier la signature._


## Chiffrer et signer des e-mails (avec Evolution)

Dans **Evolution** (le gestionnaire de mails par défaut sous Ubuntu), il faut
associer la clé que nous avons créée avec le compte mail. Pour cela, ouvrir le
menu Édition → Préférences → Comptes de messagerie, sélectionner le compte de
messagerie auquel associer la clé, et cliquer sur _Édition_. Dans l'onglet
« _Sécurité_ », recopier l'identifiant de la clé en question, et valider.

Ensuite, lors de la rédaction d'un message, il est possible d'activer la
signature et le chiffrement :

{: .center}
![gpg_evolution_mail]({{ site.assets }}/gnupg/gpg_evolution_mail.png)

Pour que le chiffrement fonctionne, il faut évidemment avoir dans le trousseau
de clés les clés publiques de tous les destinataires du mail.

Lorsque nous recevons un message chiffré et/ou signé, Evolution vérifie la
signature et déchiffre le mail. Pour l'illustrer, je me suis envoyé à moi-même
un message chiffré et signé, lorsque je l'ouvre, Evolution me demande la phrase
de passe (pour déchiffrer le message), et ensuite me l'affiche de cette
manière :

{: .center}
![gpg_evolution_received]({{ site.assets }}/gnupg/gpg_evolution_received.png)



## Chiffrer ses communications Jabber (avec Gajim)

_Il est préférable d'utiliser [OTR][] pour la messagerie instantanée, [GPG n'est
pas approprié][cypherpunks]._

[otr]: http://fr.wikipedia.org/wiki/Off-the-Record_Messaging
[cypherpunks]: http://www.cypherpunks.ca/otr/otr-wpes.pdf

Dans [gajim][] (le client *Jabber* de référence), il faut associer la clé avec
le compte Jabber. Pour cela, ouvrir le menu Édition → Comptes, sélectionner le
compte, et dans l'onglet « _Informations personnelles_ », choisir la clé à
associer. Au passage, activer la case « _Utiliser un Agent GPG_ ».

[gajim]: http://doc.ubuntu-fr.org/gajim

Une fois la clé associée au compte, _gajim_ va toujours se connecter en signant
la présence. Et qui dit signature dit déverrouillage de la clé privée, et donc
demande de la phrase de passe à chaque démarrage de _gajim_. Il est possible de
désactiver la signature de la présence : Édition → Préférences, onglet
« _Avancées_ » → « _Éditeur de configuration avancé_ » → Ouvrir… et faire passer
`gpg_sign_presence` à _Désactivé_.

Ensuite, il faut avoir les clés publiques des contacts avec qui nous souhaitons
communiquer de manière chiffrée. **Ces clés doivent être [de confiance][]**.
Pour assigner une clé à un contact Jabber, il faut cliquer-droit sur ce contact,
Gérer le Contact → Assigner une clé OpenPGP… :

{: .center}
![gpg_gajim_assign]({{ site.assets }}/gnupg/gpg_gajim_assign.png)

[de confiance]: #signer-les-cls-obtenues-de-confiance

Ensuite, lors de la conversation, il est possible d'activer le chiffrement :

{: .center}
![gpg_gajim_enable]({{ site.assets }}/gnupg/gpg_gajim_enable.png)

Inutile de vous dire que pour la messagerie instantanée, il vaut mieux avoir
configuré le trousseau pour que la clé soit déverrouillée [durablement][], afin
de ne pas retaper la phrase de passe à chaque message.

[durablement]: #dverrouiller-la-cl-prive-durablement


## Seahorse-applet : l'applet Gnome

Un applet _Gnome_ permet de chiffrer, déchiffrer et signer le presse-papier.
Sachant que le presse-papier contient ce qui est surligné avec la souris (ou ce
qui est copié avec Ctrl+C), c'est parfois bien pratique. Pour l'ajouter, il faut
cliquer droit sur un panel de Gnome (la barre du haut par exemple), puis
« Ajouter au tableau de bord… », et l'ajouter :

{: .center}
![gpg_gnome_applet]({{ site.assets }}/gnupg/gpg_gnome_applet.png)

{: .center}
![gpg_import_key]({{ site.assets }}/gnupg/gpg_import_key.png)

Petit plus, lorsque le presse-papier contient une clé, l'applet permet de
l'importer directement dans le trousseau de clés. Une fois que vous avez ajouté
l'applet, essayez de sélectionner tout le texte de [ma clé publique][].

[ma clé publique]: /keys/rom_gpg.asc

Ensuite, cliquez sur le bouton de l'applet : vous pourrez importer ma clé
directement.

_Merci à cyril pour cette astuce :-)_


## Aller plus loin


Pour plus d'infos sur l'outil **gpg**, rendez-vous sur [le site officiel (en
anglais)][gpg-website] ou sur la [doc ubuntu-fr][gpg-ubuntu].

[gpg-website]: http://www.gnupg.org
[gpg-ubuntu]: http://doc.ubuntu-fr.org/gnupg

Vous pouvez également consulter `man gpg` pour l'utiliser en ligne de commande.

{% if false %}
Pour chiffrer son dossier personnel (`/home`), [c'est ici][home].

[home]: { post_url 2010-05-16-chiffrer-son-dossier-personnel-home-sous-ubuntu %}
{% endif %}
