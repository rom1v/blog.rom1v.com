---
layout: post
title: Chiffrer son dossier personnel (/home) sous Ubuntu
date: 2010-05-16 15:12:41+02:00
tags:
- planet-libre
- puf
---

Ubuntu permet d'activer le chiffrement du dossier personnel lors de
l'installation, grâce à _eCryptfs_.


## Pourquoi chiffrer son dossier personnel ?

Parce que les documents personnels sont… personnels.

La demande du mot de passe à la connexion ou lors de la sortie de mise en veille
ne protège absolument pas les données : il suffit de booter sur un _LiveCD_ pour
récupérer les données en clair très simplement.

Ces données peuvent être de toutes sortes :

  * des photos de vacances ;
  * l'historique de comptes en banque ;
  * les scans de documents administratifs ;
  * des mails ;
  * le contenu de discussions en messagerie instantanée ;
  * l'historique de navigation ;
  * les mots de passe enregistrés ;
  * bien d'autres choses…

Quand on sait que certains se font [voler leur identité][usurpation] pour [bien
moins que ça][victimes-usurpation]… Vous me direz, certains n'ont pas besoin de
se faire voler leurs données, ils donnent volontairement tous leurs mails privés
à _Google_ et plein d'autres infos à _Facebook_, alors… `</troll>`

[usurpation]: http://fr.wikipedia.org/wiki/Usurpation_d%27identit%C3%A9
[victimes-usurpation]: http://www.lepoint.fr/actualites-societe/2009-10-12/usurpation-d-identite-quand-les-victimes-deviennent-des-coupables/1597/0/384858


### Stockage des mots de passe

Je souhaiterais faire une parenthèse sur le stockage des mots de passe (sur un
disque dur non chiffré).

Sur un système _GNU/Linux_, _a priori_ il y a un _trousseau de clés_. Les
logiciels peuvent l'utiliser pour enregistrer les mots de passe de manière sûre,
en les chiffrant par une passphrase (par défaut le mot de passe du compte).
C'est le cas par exemple d'_Evolution_, d'_Empathy_, de _Gwibber_… Pour voir les
mots de passe enregistrés, il suffit d'ouvrir _Applications > Accessoires > Mots
de passe et clés de chiffrement_.

Mais il y a un grand absent dans la liste des logiciels qui le gèrent :
_Firefox_. _Firefox_ enregistre les mots de passe quasiment en clair ; c'est
dommage, c'est à lui qu'on donne la majorité de nos mots de passe. Du coup, si
j'accède à un disque dur non chiffré, je peux récupérer tous les mots de passe
enregistrés dans _Firefox_. D'ailleurs, c'est très simple : _Édition >
Préférences > Sécurité > Mots de passe enregistrés… > Afficher les mots de
passe_ (ça devrait inciter les gens à verrouiller leur session quand ils
s'absentent plus de 5 secondes). Il y a bien une option _"Utiliser un mot de
passe principal"_, mais comme il n'est pas intégré au système, il faut le
renseigner une fois par session _Firefox_ (en plus du mot de passe système
donc). Cela suffit à dissuader de l'activer.

Je ne sais pas si c'est prévu pour _Firefox 4.0_, mais je pense que la sécurité
des mots de passe aurait été plus utile que des thèmes à la manière de _WinAmp_
il y a 10 ans (pardon on dit des [personas][])…

[personas]: http://www.getpersonas.com

Ceci donne un argument de plus pour chiffrer son dossier personnel… Parenthèse
fermée.


## Mise en place du chiffrement

Pour activer le chiffrement du dossier personnel lors de l'installation, il
suffit de choisir la bonne option :

{: .center}
![ubuntu-install-ecryptfs]({{ site.assets }}/ecryptfs/ubuntu-install-ecryptfs.png)

_(je vous conseille aussi d'utiliser une partition séparée pour `/home`)_

Et voilà, c'est tout.

Enfin, pas tout-à-fait, car quand on chiffre ses données, il est certes
important qu'elles soient protégées, mais il y a quelque chose d'encore plus
important, c'est qu'elles soient récupérables…

Nous allons donc voir comment ça fonctionne, comment récupérer les données, ce à
quoi il faut faire attention, etc.


## Principe

Le système utilise une clé (une passphrase) pour chiffrer toutes les données
avant de les écrire sur le disque. Elle est générée automatiquement, et devra
être notée quelque part (sur un bout de papier à garder précieusement). Cette
clé est elle-même chiffrée par une passphrase, qui est le mot de passe du compte
utilisateur. Ainsi, lors de la connexion de l'utilisateur, la clé pourra être
déchiffrée et utilisée pour lire et écrire des données.

Il faut bien distinguer ces deux _passphrases_ :

  * la première est la **passphrase de montage** : c'est elle qui permet de
    monter et d'utiliser le répertoire chiffré ;
  * la seconde est la **passphrase de login** : c'est elle qui permet de
    déchiffrer la première, lors de la connexion de l'utilisateur.

Tant que vous connaissez la _passphrase de montage_, vous pourrez récupérer vos
données. Si vous connaissez uniquement la _passphrase de login_, vous pourrez
normalement récupérer la _passphrase de montage_ (mais c'est plus sûr de garder
dans un coin la _passphrase de montage_, car on peut effacer involontairement le
fichier permettant de faire le lien). Si vous ne connaissez aucune des deux, vos
données sont définitivement perdues…

Physiquement, les dossiers chiffrés sont stockés dans
`/home/.ecryptfs/USER/.Private`. Les données servant au chiffrement et au
déchiffrement sont dans `/home/.ecryptfs/USER/.ecryptfs`. Le répertoire
`/home/USER`, quant à lui, n'existe pas physiquement : c'est juste une "vue"
déchiffrée du répertoire `.Private`.

_Remarque : les noms de fichiers étant eux-aussi chiffrés, ils ne comportent
physiquement pas le même nombre de caractères que le nom de fichier "en clair"
(d'autant plus qu'ils contiennent un préfixe). Ceci a une conséquence : en EXT4
les noms de fichiers ne doivent pas dépasser 256 caractères, mais un nom de
fichier "en clair" d'environ 140 caractères entraîne un nom de fichier chiffré
de 256 caractères. Les noms de fichiers sont donc limités à environ 140
caractères sur un dossier chiffré…_


## Connaître la passphrase de montage

Une fois le système démarré, il est possible de connaître la _passphrase de
montage_ :

    $ ecryptfs-unwrap-passphrase
    Passphrase: (entrer ici la passphrase de login)
    6ebf259226f1d0859e707eb4349a9476

D'ailleurs, lors du premier démarrage, _Ubuntu_ vous demandera d'exécuter cette
commande et de noter quelque part le résultat.

Pour récupérer cette passphrase sans que le système en question soit démarré
(par exemple en accédant à la partition à partir d'un _LiveCD_), il faut
préciser le fichier qui contient la _passphrase de montage_ chiffréer :

    $ ecryptfs-unwrap-passphrase /media/DISK/.ecryptfs/USER/.ecryptfs/wrapped-passphrase
    Passphrase: (entrer ici la passphrase de login)
    6ebf259226f1d0859e707eb4349a9476


## Changer la passphrase de login

On ne peut pas changer facilement la _passphrase de montage_, car il faudrait
alors rechiffrer toutes les données. Par contre, la _passphrase de login_ peut
être aisément changée (puisque seule la _passphrase de montage_ sera à
rechiffrer).

En pratique, ce changement est fait automatiquement lors d'un changement de mot
de passe du compte utilisateur.

Pour la changer manuellement (attention, il ne sera plus possible de démarrer
correctement si la _passphrase de login_ ne correspond pas au mot de passe de
connexion) :

    $ ecryptfs-rewrap-passphrase /home/.ecryptfs/USER/.ecryptfs/wrapped-passphrase
    Old wrapping passphrase: (entrer ici l'ancienne passphrase de login)
    New wrapping passphrase: (entrer ici la nouvelle passphrase de login)


## Réinstaller le système d'exploitation

Pour réinstaller le système d'exploitation (par exemple pour y mettre une
nouvelle version d'_Ubuntu_) en conservant son dossier personnel chiffré, il
faut bien sûr avoir le `/home` sur une partition séparée, ne pas la formater
lors de la nouvelle installation, mais il faut aussi utiliser le même login et
**le même mot de passe de connexion**. Si vous respectez cette règle, vous
n'avez rien de particulier à faire, tout est transparent.

Si vous avez changé le mot de passe, l'installation se déroule normalement sans
avertissement, mais une fois le système installé, vous ne pourrez pas vous
connecter (car vous n'avez pas de `/home` accessible). Si cela vous arrive, ce
n'est pas bien grave, allez dans un TTY (Ctrl+Alt+F1), connectez-vous et changez
manuellement votre _passphrase de login_ (comme expliqué dans la section
ci-dessus) pour la faire correspondre à votre mot de passe de connexion. Votre
ancienne _passphrase de login_ vous sera demandée.

Si malheureusement vous ne vous souvenez plus de votre ancienne _passphrase de
login_ (vous le faites exprès ou quoi ?), mais que vous possédez votre
_passphrase de montage_, vous pouvez vous en sortir :

    $ ecryptfs-wrap-passphrase /home/.ecryptfs/USER/.ecryptfs/wrapped-passphrase
    Passphrase to wrap: (entrez ici la passphrase de montage)
    Wrapped passphrase: (entrez ici la nouvelle passphrase de login)

Redémarrez le système, et normalement tout fonctionne.


## Chiffrer son dossier personnel après installation


Il est également possible de chiffrer son dossier personnel une fois le système
installé. Cependant, il y a une limitation très contraignante : il faut avoir
comme espace libre 2,5× la taille de l'espace occupé par le dossier personnel,
c'est-à-dire que la partition contenant `/home` ne doit pas être remplie à plus
de 28%.

Avant toute chose, **faire une sauvegarde** sur un disque externe ou sur une
autre machine (un problème pourrait entraîner la perte de toutes les données).

Le paquet `ecryptfs-utils` doit être installé :

    sudo apt-get install ecryptfs-utils

La commande qui permet de _migrer_ son home est `ecyptfs-migrate-home`.
Cependant, aucune ressource de l'utilisateur du dossier personnel à migrer ne
doit être utilisée (pas même un shell). On a donc besoin d'un autre utilisateur,
par exemple `root` (provisoirement).

On réactive donc le compte root et on lui affecte un mot de passe :

    sudo passwd root

Ensuite, il faut redémarrer la machine (déconnecter son compte ne suffit pas).
Lors de l'écran de login, passer en TTY (Ctrl+Alt+F1), se connecter avec `root`,
et exécuter :

    ecryptfs-migrate-home -u USER

(en remplaçant `USER` par le nom de l'utilisateur dont le dossier personnel doit
être migré)

Un peu de patience, il faut attendre un certain temps (qui se compte en heures
selon la quantité de données et la puissance du processeur)…

Une fois terminé, se connecter avec l'utilisateur (repasser en mode graphique
avec Ctrl+Alt+F7). Normalement tout doit fonctionner.

L'ancien dossier personnel (non chiffré) est dans `/home/USER.xxxxxx`.

Si tout s'est bien passé ce dossier doit être supprimé, et le compte `root` peut
être désactivé :

    sudo passwd --lock root


## Récupérer ses données chiffrées

C'est la partie indispensable pour accepter d'utiliser un dossier personnel
chiffré : être sûr de pouvoir récupérer ses données. Je vous conseille de tester
cette procédure une fois le chiffrement mis en place.

Pour accéder aux données, il suffit d'un _LiveCD_ d'une distribution avec un
noyau _Linux_ supérieur ou égal à 2.6.26. J'ai donc utilisé le _LiveCD_ d'Ubuntu
Lucid Lynx (10.04) pour mes tests, en m'inspirant de [cette doc][doc].

[doc]: https://help.ubuntu.com/community/EncryptedPrivateDirectory#Recovering%20Your%20Data%20Manually

_**EDIT :** Désormais, la commande `ecryptfs-recover-private` permet
d'automatiser tout le processus manuel qui suit._

Tout d'abord, il faut monter la partition contenant les dossiers chiffrés (ça se
fait graphiquement en cliquant sur le disque correspondant dans le menu
_Raccourcis_). J'utiliserai l'emplacement `/media/DISK` comme exemple.

Tout ce que nous allons faire à partir de maintenant nécessite d'être `root`,
passons donc `root` :

    sudo -s

La signature de la clé de chiffrement des noms de fichiers sera nécessaire pour
la suite :

    root@ubuntu:/~# ecryptfs-add-passphrase --fnek
    Passphrase: (entrer la passphrase de montage)
    Inserted auth tok with sig [514d1d3af1a232cd] into the user session keyring
    Inserted auth tok with sig [7890544814a5865f] into the user session keyring

C'est le code entre crochets de la dernière ligne qui est important.

On va monter le répertoire chiffré dans un répertoire qu'on va appeler
`decrypted`, créons-le :

    root@ubuntu:/~# mkdir decrypted

Ensuite, on monte et on répond aux questions :

    root@ubuntu:/~# mount -t ecryptfs /media/DISK/.ecryptfs/USER/.Private decrypted
    Selection [aes]:
    Selection [16]:
    Enable plaintext passthrough (y/n) [n]:
    Enable filename encryption (y/n) [n]: y
    Filename Encryption Key (FNEK) Signature [514d1d3af1a232cd]: 7890544814a5865f

(pour la FNEK, il faut bien préciser la signature qu'on a récupéré juste au-dessus)

Si tout s'est bien passé :

    Attempting to mount with the following options:
      ecryptfs_unlink_sigs
      ecryptfs_fnek_sig=7890544814a5865f
      ecryptfs_key_bytes=16
      ecryptfs_cipher=aes
      ecryptfs_sig=514d1d3af1a232d
    Mounted eCryptfs

Les données sont maintenant accessibles :

    root@ubuntu:~# ls decrypted
    Bureau     examples.desktop  Modèles  Public           Vidéos
    Documents  Images            Musique  Téléchargements

Pour démonter :

    root@ubuntu:~# umount decrypted


## updatedb

`updatedb` indexe régulièrement les fichiers pour pouvoir les rechercher
rapidement avec `locate`. Il faut lui indiquer de ne pas indexer les répertoires
`.Private` afin de ne pas perdre du temps, de la place, et surtout éviter
d'obtenir des résultats insignifiants lors d'une recherche avec `locate`.

Pour cela, ouvrir `/etc/updatedb.conf`, et éditer la ligne suivante pour y
ajouter `.Private` _(la décommenter si elle commençait par un `#`)_ :

    PRUNENAMES=".git .bzr .hg .svn .Private"


## Conclusion

Je pense qu'on a fait le tour de l'essentiel à savoir pour chiffrer son dossier
personnel et pouvoir récupérer ses données. J'en ai profité pour chiffrer celui
de mon ordinateur portable, tout fonctionne très bien.

Il faut cependant être conscient de deux choses.

Tout d'abord, les données personnelles ne sont pas présentes uniquement dans le
répertoire `/home`, elles sont copiées dans `/tmp`, dans la RAM, dans le SWAP
_(il est également possible de chiffrer le SWAP, grâce à
`ecryptfs-setup-swap`)_, etc. Le chiffrement est donc une étape essentielle dans
la protection des données, mais il faut comprendre ce que ça protège (voir à ce
sujet le [guide d'autodéfense numérique][guide]).

[guide]: https://guide.boum.org

Ensuite, ce chiffrement est là pour protéger la _vie privée_, pas pour cacher
quelque chose à la justice. D'une part, le code pénal prévoit une peine de 3 ans
et 45000€ d'amende pour refus de fournir la _convention secrète de
déchiffrement_ (autrement dit la clé). D'autre part, pour des sujets graves, nul
doute que les États mettront les moyens pour casser la clé (qui est relativement
faible, car proportionnée à l'objectif à atteindre, à savoir la protection de la
vie privée).

Pour utiliser le chiffrement pour des communications plutôt que pour le stockage
des données, vous pouvez consulter [GnuPG : chiffrer et signer sous Ubuntu pour
les nuls][gnupg].

[gnupg]: {% post_url 2009-05-22-gnupg-chiffrer-et-signer-sous-ubuntu-pour-les-nuls %}

Amusez-vous bien.

## Addendum

À l'utilisation, j'ai remarqué que cette méthode de chiffrement entraînait des
problèmes de performance, notamment pour les dossiers contenant beaucoup de
fichiers.

Je recommande donc plutôt [LUKS+LVM][].

[LUKS+LVM]: https://doc.ubuntu-fr.org/tutoriel/chiffrer_son_disque
