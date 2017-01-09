---
layout: post
title: Motorola Milestone avec Android 2, mes premières impressions
date: 2010-01-03 18:10:11+01:00
tags:
- planet-libre
---

Je viens de recevoir mon nouveau téléphone, un Motorola Milestone, avec le
système d'exploitation Android 2, que j'ai pris avec un abonnement SFR
Illymythics 3G+ Full Internet. Ma ligne n'étant pas encore activée, je me suis
connecté en WiFi sur mon routeur.

Voici mes premières impressions de libriste. Comme vous allez le voir, il y a du
positif… et du négatif. Je vais commencer par l'achat et l'accès Internet, pour
ensuite entrer dans le vif du sujet : le matériel et le logiciel.


## L'achat

Exclusivité _rueducommerce_, ce téléphone n'était pas trouvable autre part à sa
sortie : c'est insupportable ces exclusivités, impossible de le voir "en vrai"
avant l'achat. Par contre, il était possible de choisir son opérateur (encore
heureux me direz-vous, mais ça n'est pas toujours le cas).


## L'accès Internet

Comme prévu, un internet mobile (avec un petit _i_) loin d'être neutre, comme on
peut le voir dans les [conditions générales d'abonnement SFR][cga] :

[cga]:
http://www.sfr.fr/mobile/edito/pdf/docs_juridique/181109/conditions_generales_abonnement_SFR.pdf

> 4.1 : L'abonné est informé et accepte que les Offres lui soient proposées sur
> la base de la configuration du terminal compatible opérée par l'opérateur.
> Dès lors, l'abonné qui procèderait à la modification de paramétrage de son
> terminal compatible ne pourra plus bénéficier des Offres et tarifs en l'état.

Les offres et les tarifs dépendent du matériel qu'on utilise pour aller sur
internet ou téléphoner ! Imaginez que votre accès ADSL soit plus cher si vous
achetez un ordinateur Acer plutôt qu'un Asus… ou que vous changiez le système
d'exploitation ou les logiciels pré-installés…

> 4.2 : Le peer to peer, les newsgroups, la Voix sur IP et les usages Modem sont
> interdits, ce que l'abonné reconnaît et accepte, SFR se réservant le droit,
> pour les clients Forfaits Bloqués SFR, de résilier la ligne en cas de
> manquement.

Bon, bah là on est carrément dans le filtrage protocolaire pure et simple. Sans
parler des usages "modem" qui sont interdits, comme si les fournisseurs d'accès
ADSL interdisaient d'installer un routeur perso sur sa connexion…

> 4.3 : Pour permettre à tous les clients SFR d'accéder au réseau SFR dans des
> conditions optimales, le débit maximum de connexion sera réduit au-delà de 1Go
> d'échanges de données par mois jusqu'à la prochaine date de facturation.

Quand une phrase commence comme ça, en général, c'est mauvais signe… Le
soi-disant "Internet" est donc limité à 1Go par mois sans réduction de débit…

Les cas particuliers pour les _iPhones_ sont également assez hallucinants.

Vivement que Free sorte ses offres mobiles…


## Le matériel

{: .center}
![motorola-milestone]({{ site.assets }}/motorola_milestone/motorola-milestone.jpg)

Rien à redire à ce niveau-là, l'écran 3,7' avec une définition de 854×480 est
vraiment très confortable, la navigation sur internet est agréable. L'écran
tactile fonctionne très bien, il a l'air solide et ne se raye pas. Le clavier
physique est très sympa pour écrire tout en gardant l'intégralité de l'écran
visible.

Le téléphone est peut-être un peu lourd, mais on s'y fait.


## Le logiciel


### Mes attentes

Avant de détailler ce que je pense de toute la partie logicielle, je voudrais
détailler ce que j'attends du téléphone.

Tout d'abord, je veux accéder à mes mails, à la messagerie instantanée et aux
salons de discussion IRC. Je veux également pouvoir me connecter en SSH (sur mon
serveur à la maison par exemple) et rediriger des ports (pour faire passer les
connexions dans un tunnel, vers un réseau internet plus neutre, celui que j'ai à
la maison en l'occurrence) ; les logiciels que j'utilise doivent donc supporter
la configuration d'un proxy.

Ensuite, je ne veux pas utiliser tous les services _Google_, en particulier je
ne veux pas de Gmail, de l'agenda, de Google Talk… Plus généralement, je ne veux
pas d'applications qui nécessitent un compte _Google_ (mes données personnelles
n'ont rien à faire chez _Google_ ou chez n'importe qui d'autre).

Enfin, je ne veux pas passer par "Android Market" pour installer des
applications. Je veux installer et désinstaller des applications à ma guise,
même celles qui sont fournies avec le téléphone. D'ailleurs, je ne suis pas
d'accord avec les [conditions d'utilisation][cgu], entre autres :

[cgu]: http://www.google.com/mobile/android/market-tos.html

> Si tel était le cas, Google se réserve le droit de supprimer à distance et à
> sa seule discrétion les Produits concernés de votre Mobile, sans vous en
> informer au préalable.

D'une part, je considère que c'est abusif sur le principe, d'autre part ça
signifie que techniquement le Market est une sorte de trojan à partir duquel une
entité extérieure peut exécuter du code à son bon vouloir. Tout simplement
inacceptable. _Google_ m'a beaucoup déçu sur ce point, en général j'aime bien
leur politique d'ouverture, mais j'avoue avoir été désagréablement surpris par
leurs conditions, qui font malheureusement penser à celles d'Apple (en moins
pire, certes, mais quand même)…

Certains me demanderont alors "mais pourquoi donc as-tu choisi un Android ?".
Pour moi, Android a beaucoup d'attraits : le système d'exploitation est sympa,
on peut rajouter des applications sous licence libre sans forcément passer par
le Market, on peut se connecter directement en USB à l'ordinateur, j'aime bien
l'interface, etc. Avoir accès facilement à mes données personnelles offertes
gracieusement à _Google_ est loin d'être ma priorité… Et je rajouterais que
faute de mieux, Android est le moins pire au niveau ouverture…


### Que de déceptions !

Tout d'abord, lorsqu'on allume le téléphone, on se rend compte qu'il y a
quelques applications installées dont on n'a pas besoin ("Agenda", "Agenda
d'entreprise", "Annuaire d'entreprise", "Gmail", "Motonav", "Phone Portal", sans
compter "Market" puisque j'ai dit que je ne comptais pas m'en servir).  Après
tout, ce n'est pas gênant, sur Ubuntu _Empathy_ est pré-installé, moi j'utilise
_Gajim_, il me suffit de désinstaller _Empathy_ et d'installer _Gajim_.

Mais là, non ! Il est tout simplement impossible de désinstaller les
applications pré-installées, certaines ressemblant plus à des crapwares qu'à des
applications utiles (ça me fait penser aux pauvres utilisateurs de Windows qui
achètent un ordinateur avec Norton pré-installé et difficile à retirer)…

En fait, il faut attendre que le téléphone soit rooté pour pouvoir faire ce que
l'on veut sur sa machine. Et là vient encore une nouvelle déception vis-à-vis de
_Google_ (à moins que ça ne soit la faute de _Motorola_ ?) : pourquoi n'est-il
pas proposé par défaut la fonctionnalité de passer root sur la machine ?
Pourquoi est-ce considéré comme du "piratage" de rooter son téléphone, comme
pour le jailbreak de l'_iPhone_ ? Imaginez-vous acheter un ordinateur sur lequel
on vous empêche d'être root ? Pourquoi serait-ce différent pour un téléphone ?

Ça commence mal, mais ce n'est pas très grave, je me dis que je vais ignorer ces
applications, elles prennent juste un peu de place en mémoire et surtout dans le
menu principal… Après tout, je peux installer les logiciels libres que je veux
en les téléchargeant sur le site en `.apk` et en les copiant sur la carte
mémoire, non ? Pas tout-à-fait, car par défaut, le téléphone ne sait pas
installer les `.apk`… Ça aurait été plus utile que les bidules pré-installés,
non ?

Parce que du coup, il faut installer un logiciel qui s'appelle _appsInstaller_
(non libre).  _EDIT: je vous conseille plutôt le gestionnaire de fichiers libre
[OI File Manager][oifm]. Comment? En passant par le Market. Ce qui implique
d'accepter les conditions disant "ce programme est un trojan, voulez-vous
accepter ?" (je caricature à peine). Et qui implique de renseigner un compte
Gmail dans le téléphone, qui sera utilisé par toutes les autres applications._

[oifm]: http://www.openintents.org/en/download

J'accepte donc les conditions et crée un compte bidon (_jeneveuxpasdecompte_ at
_gmail.com_). Une fois _appsInstaller_ installé, je tente de supprimer ce compte
de mon téléphone, "Impossible de supprimer ce compte". `sudo supprimer ce
compte`, non ça n'est pas possible ? Décidément, on n'est pas maître de la
machine tant qu'on n'est pas root !

_**EDIT 10/01/2010 :** C'est en fait possible d'installer une application `.apk`
sans jamais configurer un comte gmail ni passer par le market : [Installer une
application .apk sur Android à partir d'un PC][apk]._

[apk]: {% post_url 2010-01-10-installer-une-application-apk-sur-android-a-partir-dun-pc %}


### Du positif quand même

Malgré tout cela, il y a des choses qui fonctionnent bien.

Par exemple la connexion USB qui permet d'accéder directement au contenu la
carte SD, quelque soit le système d'exploitation. Ou la musique Ogg Vorbis qui
se lit très bien avec le lecteur par défaut… La gestion des notifications est
également sympa (un peu à la manière d'`indicator-applet` dans Ubuntu). Le GPS
fonctionne bien en extérieur (par contre en intérieur, il fait n'importe quoi
chez moi).

Voici quelques retours d'expérience sur les programmes "de base" (mails,
messagerie, ssh, jabber). Si vous connaissez d'autres logiciels libres sympa,
n'hésitez pas à partager.


#### Mail

Le client mail par défaut se connecte sans problème à mon serveur perso en
utilisant IMAP/TLS et SMTP/TLS. Il n'offre par contre pas un super affichage
pour les dossiers IMAP (une liste de noms "bruts" comme
"INBOX.forums.ubuntu-fr", "INBOX.mailing-list.april"…). il ne gère pas le _push_
(pour recevoir son mail aussitôt) et a un peu de mal avec les pièces jointes.

J'ai installé [k9mail][] (Apache License 2.0), qui est un peu plus complet, et
qui gère le _push_ et les pièces jointes. Par contre, il n'est qu'en anglais.

[k9mail]: https://github.com/k9mail/k-9


#### Messagerie instantanée

Pour utiliser la messagerie instantanée _Jabber_, j'utilise le client [Beem][]
qui fonctionne très bien :

{: .center}
![beem]({{ site.assets }}/motorola_milestone/beem.png)

[beem]: http://beem-project.com/


#### Identi.ca

Pour tweeter sur _identi.ca_, j'ai installé [mustard][]. Très sympa (sauf qu'il
rafraîchit les flux à chaque fois qu'on le lance, même si le dernier chargement
a eu lieu il y a 15 secondes).

[mustard]: http://macno.org/mustard/


#### SSH

L'application [connectbot][] (GNU/GPLv3), permet de se connecter en SSH à un
serveur. Elle gère les paires de clés publique/privée et la redirection de
ports.

[connectbot]: https://connectbot.org/

En particulier, je l'utilise pour lancer [irssi][] (un client IRC en ligne de
commande) dans un [screen][] sur un serveur. Cela permet de pouvoir déconnecter
et reconnecter le client sans se déconnecter des salons et ni perdre le fil de
discussion…

[irssi]: http://www.irssi.org/ [screen]: http://doc.ubuntu-fr.org/screen

Malheureusement, le navigateur internet par défaut ne permet pas de configurer
de proxy (pour utiliser un tunnel SSH). Si vous en connaissez un bien en
attendant _Fennec_, je suis preneur. D'autant que le navigateur intégré ne
fonctionne pas correctement sur [tt-rss][] (quand je clique sur un flux, il
considère que je clique sur toute la colonne de gauche).

[tt-rss]: http://tt-rss.org/


## Conclusion

Le téléphone et le système sont de jolis jouets technologiques.

Mais je m'attendais, de la part de _Google_, à ce que ça soit quand même plus
ouvert que ça… Là on est obligé d'accepter des conditions inacceptables,
d'utiliser un compte _Google_ alors qu'on n'a rien demandé, on ne peut pas
désinstaller les crapwares… On se sent un peu limité, on n'a pas la maîtrise
totale de la machine tant qu'elle n'aura pas été rootée, je trouve que c'est
vraiment dommage.

Attendons donc qu'elle soit rootée…

_PS: Quelques trolls se sont malencontreusement glissés dans ce billet,
saurez-vous les retrouver ? ;-)_
