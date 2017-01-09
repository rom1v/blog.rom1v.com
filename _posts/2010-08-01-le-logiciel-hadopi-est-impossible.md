---
layout: post
title: Le logiciel HADOPI est impossible
date: 2010-08-01 01:03:46+02:00
tags:
- planet-libre
---

Ça y'est, nous en savons plus sur les moyens de sécurisation HADOPI, après la
[diffusion par Numerama][doc-numerama] de leurs spécifications fonctionnelles
([publiques mais secrètes][pubsec]), établies par M. Riguidel ([soupçonné de
conflits d'intérêts][conflits]).

[doc-numerama]: http://www.numerama.com/magazine/16363-exclusif-le-document-secret-de-l-hadopi-sur-les-moyens-de-securisation.html
[pubsec]: http://www.numerama.com/magazine/16351-hadopi-une-consultation-publique-dont-les-questions-sont-secretes.html
[conflits]: http://www.pcinpact.com/actu/news/56771-hadopi-brevet-securisation-filtrage-dpi.htm

**EDIT :** Le document final est publié [ici][doc].

[doc]: http://hadopi.fr/download/sites/default/files/page/pdf/Consultation_sur_les%20specifications_fonctionnelles_des_moyens_de_securisation.pdf



## Sémantique

Ce [document][doc-cache] nous confirme que le logiciel de sécurisation est en
fait un **mouchard**, un **logiciel de surveillance et de contrôle des
utilisateurs**.

En effet, la sécurité permet de se prémunir d'attaques extérieures et
d'espionnage. Au contraire, les spécifications présentées définissent un
logiciel espion qui journalise les faits et gestes (du moins ceux qui
l'intéressent) des internautes. Dans l'esprit des architectes de la loi, la
surveillance des utilisateurs est un moyen de sécurisation de la forme actuelle
de la propriété intellectuelle, de la même manière que la censure est un moyen
de sécurisation de l'opinion publique. Mais **il ne s'agit absolument pas de
sécurisation informatique**.

Il est d'ailleurs amusant d'avoir précisé que logiciel devait espionner mais
sans trop se faire remarquer (page 13) :

> les moyens ne sont pas reconnus comme un « malware » par les antivirus du
> marché

[doc-cache]: http://dl.rom1v.com/hadopi/logiciel-impossible/hadopi-sfh.pdf



## Principe de fonctionnement

Le principe de ce mouchard est d'enregistrer les actions de l'utilisateur dans
deux fichiers (une version en clair et une version "sécurisée"). La version
"sécurisée" doit être conservée un an, et servira au besoin à prouver que
l'utilisateur a ou n'a pas fait telle ou telle action. C'est exactement comme
proposer à la population l'installation d'une caméra de surveillance sur la tête
qui conserverait les enregistrements pendant un an, dans le but de prouver son
innocence lors d'une éventuelle accusation.

**Ces spécifications sont inquiétantes par leur logique de surveillance et de
contrôle.** Les objectifs sont assez clairs. De plus en plus de politiques
prenant conscience des enjeux de la neutralité du net ([le Chili a même voté une
loi pour la garantir][chili]), il paraît difficile d'imposer un filtrage (par
ailleurs inefficace) pour combattre le partage de fichiers (quoique l'idée
pourrait encore resurgir). Il serait également délirant d'imposer à chacun
l'installation d'un tel mouchard sur toutes ses machines ([même la Chine a
reculé][chine]). La solution est donc de créer **une insécurité juridique avec
des accusations aléatoires** (sans aucune preuve) et de proposer un outil de
surveillance qui permettra de _prouver sa bonne foi_. S'ils ne peuvent pas
prouver leur innocence, les utilisateurs risquent une amende de 1500€ et/ou une
coupure d'accès Internet pendant un mois pour délit de _négligence
caractérisée_. **Une subtile manipulation de la présomption d'innocence.**

[chili]: http://www.numerama.com/magazine/16252-le-chili-vote-une-loi-sur-la-neutralite-du-net-une-premiere-mondiale.html
[chine]: http://www.numerama.com/magazine/13351-logiciel-anti-porno-la-chine-n-a-fait-que-decaler-son-deploiement-maj.html



## L'arnaque technique

Attention, on rentre dans la technique, c'est là que ça devient rigolo.


### Un journal… sécurisé

Je disais que le logiciel devait journaliser les actions des utilisateurs dans
deux fichiers, une version en clair et une version "sécurisée". C'est la partie
cruciale du fonctionnement du logiciel de sécurisation. Tout est détaillé pages
28 et 32 :

> Il existe deux sortes de journaux qui sont produits en temps réel dans deux
> bases de données distinctes :
>
> Un journal en clair que les utilisateurs et l'administrateur peuvent
> consulter.
>
> Un journal sécurisé. Ce journal est confidentiel, authentique et
> infalsifiable. Toute tentative de falsification éventuelle est détectable.
> Pour des raisons de sécurité, cette seconde version du journal est en mode
> binaire, compressée, signée électroniquement, chiffrée, et archivée pendant
> une période d'au moins une année. Ce journal sera accessible en clair à la
> demande du titulaire de l'abonnement. Il permettra de vérifier, après
> déchiffrement avec la clé privée correspondant au logiciel, laquelle est
> détenue par le tiers de confiance, la mise en œuvre du logiciel de
> sécurisation à une date et heure donnée, et l'activité informatique de
> l'internaute concerné. Ce journal permet de refléter, sans interférence
> possible du titulaire de l'abonnement, les événements de l'accès Internet
> considéré.

> Le chiffrement des journaux s'opère avec de la cryptographie asymétrique, en
> utilisant la clé publique fournie, avec le logiciel, par un tiers de
> confiance.



On a donc un journal en clair, et une copie en
_binaire-compressé-signé-chiffré-archivé-infalsifiable-incopiable_. Comment est
chiffrée cette copie ? Par une clé publique (fournie avec le logiciel). Comment
est signée cette copie ? Ce n'est pas dit, mais c'est forcément par une clé
(privée), fournie elle-aussi avec le logiciel.

Le poste de l'utilisateur possède donc la clé de chiffrement et la clé de
signature, mais attention, _abracadabra_, il ne doit pas pouvoir créer un "faux"
journal chiffré et signé ! Et s'il tente de créer un faux journal, le logiciel
doit le détecter !



### Un logiciel impossible


Le but du journal "sécurisé" est évidemment de s'assurer qu'il a bien été généré
par le mouchard et qu'il n'a pas été modifié. On se demande alors l'intérêt de
le chiffrer par une clé publique (vu que de toute façon le journal est
accessible en clair). Ce qu'il faut, c'est le signer. Et pour signer, il faut
une clé privée. Et une clé privée, on ne peut pas l'intégrer au logiciel, car
alors elle serait rendue publique, et n'importe qui pourrait signer n'importe
quoi. **La clé privée du "tiers de confiance" ne peut donc pas être diffusée
pour signer les journaux.**

Envoyer les journaux chez le "tiers de confiance" (ce qui est clairement exclu
de toute façon) ne fonctionnerait pas mieux, car rien n'empêcherait
l'utilisateur d'envoyer de faux journaux.

**Il n'est donc pas possible de réaliser un tel logiciel.**

_Mesdames et messieurs de l'Hadopi, si une société vous propose un logiciel qui
répond aux spécifications, méfiez-vous, il n'y répond pas. Mesdames et messieurs
les commerciaux des sociétés informatiques, réfléchissez bien avant d'accepter
un tel contrat, vos équipes projets ne pourront pas le réaliser._



### La cryptographie asymétrique


Pourtant, la cryptographie, ça fonctionne bien et c'est [accessible à tous très
simplement][gnupg]. Pourquoi donc un tel logiciel ne peut pas fonctionner ?

[gnupg]: {% post_url 2009-05-22-gnupg-chiffrer-et-signer-sous-ubuntu-pour-les-nuls %}

La cryptographie asymétrique, ça permet à A d'écrire un message à B de telle
manière que B soit sûr que le message provienne de A et que A soit sûr que seul
B puisse le lire. A et B se font confiance.

Ici, par définition, le tiers de confiance vis-à-vis de l'Hadopi (B) ne fait pas
confiance à l'internaute (A). Donc B veut écrire et signer le message (ici le
journal) qui se trouve chez A. Pour cela, une partie de B (le mouchard) doit se
trouver chez A : cette partie peut donc être contrôlée par A. On en déduit que
**A peut signer les messages qu'il veut en se faisant passer pour B**.

**Il n'y a d'ailleurs rien d'étonnant à ce qu'un outil de sécurité et de
protection ne réponde pas au besoin d'un logiciel de surveillance et de
contrôle.**



## Cachez le code


### Logiciels propriétaires


Il reste une petite subtilité à détailler. J'ai dit que si la clé privée était
intégrée au logiciel, alors elle était publique (accessible à tous), et que si
le mouchard se trouvait chez un internaute, il pouvait être contrôlé par
l'internaute.

En fait, certains logiciels ne permettent pas aux utilisateurs d'en étudier
directement le fonctionnement, et donc _a fortiori_ d'en modifier le
comportement : ce sont les logiciels dits _propriétaires_ ou _privateurs_ (du
moins ceux dont les sources ne sont pas fournies). Ces logiciels sont par
définition une privation d'une partie du contrôle de sa propre machine :
l'utilisateur doit faire confiance aveuglément aux actions du logiciel. C'est
l'idéal pour un programme de surveillance.

Mais il ne s'agit en rien d'une sécurité, la clé se trouve quand même dans le
programme, et sera un moment ou à un autre utilisée (pour signer le journal). Ne
pas fournir les sources du programme ne fera que rendre la tâche un petit peu
plus difficile (il faudra sans doute lire de l'assembleur), mais je n'ai aucun
doute sur le fait que 48 heures après le programme diffusé, un outil permettant
d'en extraire la clé sera disponible.

Bien loin d'une protection rendant le journal **infalsifiable** comme exigée.


### Logiciels libres


Les rédacteurs du document ont bien compris que le _logiciel libre_ ne devait
pas être écarté du champ du mouchard, et qu'il fallait que l'expression
"logiciel libre" apparaisse dans les spécifications (page 6) :


> Les moyens peuvent être réalisés à partir de logiciels libres et/ou
> fonctionner sur des systèmes d'exploitation libres.


Ils peuvent être réalisés _"à partir"_ ou _"fonctionner sur"_ des logiciels
libres. Mais fondamentalement, **un mouchard ne peut pas être libre**. Le
logiciel libre permet à l'utilisateur d'avoir le contrôle de sa machine ; le
mouchard lui propose de perdre une partie de ce contrôle pour être surveillé.
C'est forcément incompatible.

Concrètement, c'est très simple à comprendre : il suffit de modifier les sources
du logiciel qui écrit le journal "sécurisé" pour qu'il n'écrive que ce que l'on
décide.



## Droit de contrôle de son ordinateur


On observe de nombreuses tentatives de s'emparer du contrôle d'au moins une
partie des ordinateurs de la population. C'est le cas avec des systèmes de
suppression d'applications ou de contenu à distance sans le consentement de
l'utilisateur (je pense notamment à _Apple_ et _Google_ pour leur systèmes
mobiles). C'est le cas maintenant avec des mouchards que la loi recommande.

Je pense qu'il serait intéressant de faire du _"droit de contrôle de son
ordinateur"_ (ou appelez ça comme vous voulez) un enjeu au même titre que la
**neutralité du net** : si le filtrage est interdit au niveau du réseau, il va
être chez l'utilisateur. Dans ce cas, la seule forme acceptable est qu'il soit
sous son contrôle, et non sous le contrôle d'une entreprise privée ou d'une
quelconque autre entité.

D'ailleurs, le document de spécifications du logiciel HADOPI laisse penser que
l'installation d'applications dans les boitiers ADSL hors du contrôle de
l'utilisateur est prévu (page 9) :

> Pour le moment le parc des boitiers ADSL est très hétérogène, et les boitiers
> sont dimensionnés de telle manière qu'il est difficile de loger des
> applications supplémentaires dans ces boitiers. Pourtant, on peut réfléchir à
> ces solutions pour les futures générations de boitiers, dans le cadre du
> renouvellement général du parc.
