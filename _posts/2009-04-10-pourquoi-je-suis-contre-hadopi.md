---
layout: post
title: Pourquoi je suis contre Hadopi
date: 2009-04-10 15:36:10+02:00
tags:
- planet-libre
---

J'ai résisté depuis le début à l'idée de faire un billet à propos de la loi
Hadopi, étant donné le nombre inimaginable d'infos à ce sujet sur internet.

Je fais celui-ci simplement pour indiquer pourquoi je suis contre, notamment du
point de vue technique. Il s'agit en fait grosso modo du contenu des courriers
(électroniques et postaux) que j'ai envoyé à certains députés avant les débats à
l'Assemblée Nationale.

Je soutiens évidemment par ailleurs les arguments avancés par [La Quadrature du
Net](http://www.laquadrature.net/fr).

## Une preuve qui ne prouve rien

L'unique preuve utilisée pour accuser les internautes est une adresse IP (une
suite de 4 nombres), apparaissant dans une liste fournie par les ayant-droits
eux-mêmes. Comme cela a été montré à de nombreuses reprises, **une adresse IP
n'est pas une preuve suffisante pour accuser un internaute** :

  * dans le rapport de l'[UFC­Que­Choisir][ufc], page 18, « Un dispositif
    techniquement irréaliste », une démonstration sous contrôle d'huissier, dont
    le procès verbal est disponible pages 26 à 31 ;
  * le tribunal de Guingamp [a jugé][guingamp] que l'adresse IP ne suffisait pas
    pour établir la culpabilité d'un internaute ;
  * une imprimante [est accusée][imprimante] d'avoir téléchargé illégalement…

[ufc]: http://www.quechoisir.org/document/loi-creation-et-internet.pdf
[guingamp]: http://www.pcinpact.com/actu/news/49526-adresse-ip-livebox-orange-piratage.htm
[imprimante]: http://www.ecrans.fr/Surveillance-du-p2p,4312.html

Et effectivement, baser l'accusation des internautes sur une simple adresse IP
est extrêmement aléatoire :

  * les bornes d'accès WiFi peuvent être utilisées par des voisins ;
  * certains trackers _bittorrent_ rajoutent volontairement des adresses IP
    aléatoires dans la liste des connectés ;
  * de nombreux ordinateurs de particuliers sont des machines zombies : ils
    peuvent donc être utilisés à l'insu de leur propriétaire pour télécharger
    des œuvres protégées ;
  * la liste des adresses IP fournies par les plaignants eux-mêmes fait fois à
    elle seule, dans ce projet de loi…

**Que propose alors ce projet de loi pour éviter ces problèmes ?**

> Une adresse IP n'est pas une preuve suffisante pour accuser un internaute.

L'une des mesures principales du projet est de court-circuiter l'autorité
judiciaire : elle préconise de ne plus passer par un juge pour attaquer les
internautes. Pas de solution ? Supprimons le problème…

> Les bornes WiFi peuvent être utilisées par des voisins.

L'astuce est dans ce projet de ne pas poursuivre les internautes qui
téléchargent des œuvres illégalement, mais le propriétaire qui est responsable
de l'accès internet.

_Les autres problèmes sont tout simplement ignorés._

Il y aura une part énorme des accusés qui le seront probablement à tort !


## Présomption d'innocence, vous avez dit ?

Dans la déclaration universelle des droits de l'Homme de 1948, on trouve cet
article :

> **Article 11.** Toute personne accusée d'un acte délictueux est présumée
> innocente jusqu'à ce que sa culpabilité ait été légalement établie au cours
> d'un procès public où toutes les garanties nécessaires à sa défense lui auront
> été assurées.

Dans ce projet de loi, l'Hadopi accuse l'internaute sans preuves (si ce n'est la
réception de la part des plaignants d'une adresse IP qui aurait été repérée par
leurs outils de détection privés) ; s'il est innocent, c'est à lui de le
démontrer.


## Démontrer son innocence

Nous avons vu qu'il pourrait y avoir de nombreuses accusations à tort, et que ce
sera à l'accusé de prouver son innocence. Étudions alors quand et comment il
pourra se défendre.


### Quand ?

Le projet de loi dit que l'accusé à tort ne pourra pas du tout contester lors
des deux premières étapes de la _riposte graduée_ (réception d'un e-mail
d'avertissement et réception d'une lettre recommandée avec accusé de réception).
Il pourra cependant éventuellement « en faire part » à la *Haute Autorité*,
juste dans le but de les informer.

Il est dit qu'il pourra, par contre, faire appel à un juge une fois la section
prononcée (et l'accès à Internet coupé).


### Comment ?

Un internaute innocent fera donc appel à un juge une fois son accès Internet
coupé. Comment pourra­t­il prouver son innocence ? C'est très simple :

  * soit il invoque un cas de force majeur ;
  * soit il prouve que son accès a été piraté par un tiers ;
  * soit il avait installé sur son ordinateur un _logiciel de sécurisation_
    certifié par l'Hadopi qui l'exonérera de sa condamnation.

Mettons de côté le cas de force majeur, et intéressons-nous aux deux autres cas.


#### Il prouve que son accès internet a été piraté par un tiers

C'est tout simplement impossible. Imaginez-vous un utilisateur lambda démontrer
devant un juge que son accès WiFi a été piraté ? Même si l'accusé est ingénieur
réseau, il aura bien du mal.


#### Il avait installé sur son ordinateur un _logiciel de sécurisation_

Ce type de logiciel n'existant pas encore, Mme Albanel précise que ce sera un
logiciel « de type pare-feu ». M. Riester (rapporteur du projet de loi) indique
de plus que [ces logiciels seront payants et non-interopérables][riester] :

> L'interopérabilité, je n'y suis pas favorable ; il faut laisser au
> consommateur sa totale liberté de choix en fonction de son système
> d'exploitation.

[riester]: http://www.pcinpact.com/actu/news/49218-hadopi-interoperabilite-logiciel-libre-payant.htm

Les [spécialistes de la question][april] bondiront de leur chaise en lisant
cette phrase, qui dit une chose et son contraire : l'interopérabilité, c'est
justement le fait de pouvoir laisser l'utilisateur choisir son logiciel et son
système d'exploitation. Mme Albanel a également précisé lors des deux premières
journées de débat à l'Assemblée Nationale qu'il existait des pare-feux gratuits,
comme par exemple celui livré avec *Microsoft Office* (qui coûte, rappelons­le,
129€) ou dans *OpenOffice* ; ceci est totalement absurde, il n'y a évidemment
aucun pare-feu dans une suite bureautique.

[april]: http://www.april.org/fr/riposte-graduee-le-rapporteur-soppose-a-linteroperabilite-lapril-appelle-a-la-mobilisation

Il existe en effet des pare-feux gratuits (`iptables` par exemple sous
_GNU/Linux_), mais un pare-feu protège _l'intérieur contre les attaques
extérieures_, tandis qu'un logiciel tel que propose ce projet de loi voudrait
protéger _l'extérieur contre les attaques intérieures_ (empêcher un utilisateur
intérieur au réseau d'effectuer un téléchargement à partir d'une source
extérieure). Comme si on voulait vous enfermer dans une pièce avec un verrou
dans la pièce : vous n'êtes pas enfermé, puisqu'il vous suffit d'ouvrir le
verrou (ici de désactiver ou de contourner le logiciel).

Pour éviter ce problème, Olivier Henrard, juriste spécialiste du texte au
ministère de la Culture, ne s'est pas démonté, lors de [son débat avec Jérémie
Zimmermann sur 01net][01net] en précisant que ce logiciel enverra régulièrement
des informations à la *Haute Autorité* concernant l'activité de l'ordinateur. Ce
sera donc un mouchard.

[01net]: http://www.pcinpact.com/actu/news/49428-olivier-henrard-hadopi-jeremie-zimmermann.htm
 
Rappelons tout de même le contexte : chaque particulier devra acheter et
installer un tel logiciel (et ses mises à jour) afin qu'il puisse, dans le cas
où il serait accusé à tort par des aléas techniques du projet de loi, prouver sa
bonne foi. Par analogie, c'est comme si chaque citoyen devait porter un bracelet
électronique, qui lui permettrait alors de se défendre (et ça serait la seule
façon) lorsqu'un délit a lieu près de chez lui (il pourrait prouver qu'il
n'était pas là à ce moment-là).

De plus, ce type de logiciel bloquerait certains protocoles (P2P notamment),
alors même que le P2P n'est pas illégal : il permet entre autres de télécharger
de la musique libre (sur [Jamendo][] par exemple) ou de nombreuses distribution
GNU/Linux. Certes, il peut être utilisé à des fins illégales, tout comme le HTTP
(protocole du Web) peut être utilisé pour télécharger illégalement des fichiers 
protégés par le droit d'auteur. Est-ce pour autant qu'il faut bloquer le Web ?

[jamendo]: http://www.jamendo.com/fr/

Enfin, ce logiciel serait totalement inefficace : il suffirait de l'installer
sur un ordinateur (éventuellement une [machine virtuelle][vm]), et de
télécharger illégalement avec un autre. Et lorsque l'ordinateur où se trouve ce
logiciel est éteint, l'utilisateur ne pourra plus prouver que le soi-disant
_logiciel de sécurisation_ était allumé (puisqu'il était éteint, en même temps
que l'ordinateur).

[vm]: http://fr.wikipedia.org/wiki/Machine_virtuelle

Sans compter son incompatibilité totale avec le [logiciel libre][].

[logiciel libre]: http://fr.wikipedia.org/wiki/Logiciel_libre
 
Olivier Henrard et Christine Albanel proposent une seconde idée : l'internaute
pourra envoyer son disque dur en guise de bonne foi. C'est ridicule (les
personnes accusées à tort sans preuve ne vont pas démonter leur ordinateur pour
envoyer leur disque dur et s'en priver pour une durée indéterminée), et ça ne
prouverait rien (il suffirait d'envoyer un disque dur n'ayant pas servi à un
téléchargement illégal).


## Conclusion

De gros problèmes techniques rendent les accusations envers les internautes
totalement aléatoires. Il reviendra aux accusés sans preuves de prouver leur
innocence. Ils n'auront aucun moyen de se défendre dans les deux premières
étapes de la _riposte graduée_. Lors de la sanction finale, ils auront bien
du mal à prouver leur bonne foi, à moins d'avoir effectué, avant l'accusation,
l'achat et l'installation d'un mouchard sur leur ordinateur (qui bloquerait par
ailleurs beaucoup de contenus légaux), et d'avoir la chance que la date et
l'heure d'accusation coïncide avec celle de fonctionnement du logiciel en
question (en particulier il faut que l'ordinateur soit allumé).

_**EDIT :** À lire également :_

  * [Piratage ou usage commun ?]({% post_url 2010-08-06-piratage-ou-usage-commun %})
  * [Le logiciel HADOPI est impossible]({% post_url 2010-08-01-le-logiciel-hadopi-est-impossible %})
  * [L'abondance contre l'économie]({% post_url 2011-06-08-labondance-contre-leconomie %})
