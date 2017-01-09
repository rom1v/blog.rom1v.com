---
layout: post
title: apt-get install hadopi
date: 2009-09-21 23:29:53+02:00
tags:
- planet-libre
---

{: .center}
![terminal]({{ site.assets }}/apt-get_install_hadopi/terminal.png)

~~~
albanel@majors$ sudo apt-get install hadopi
Lecture des listes de paquets... Fait
Construction de l'arbre des dépendances
Lecture des informations d'état... Fait
Les paquets supplémentaires suivants seront installés :
  lib-bypass-constitution-francaise lib-propagande
  lib-autorite-administrative-avec-tous-les-droits
E Les paquets suivants ont des dépendances non satisfaites : hadopi dépend de lo
bby-vivendi, mais lobby-vivendi est en conflit avec lib-information-impartiale.
Les actions suivantes permettront de résoudre ces dépendances :
  s-asseoir-sur-les-libertés-fondamentales
Accepter cette solution [O/n] ? O

Les paquets suivants seront enlevés :
  lib-presomption-d-innocence lib-droits-de-la-defense
  lib-separation-des-pouvoirs lib-information-impartiale
Les NOUVEAUX paquets suivants seront installés :
  hadopi lib-bypass-constitution-francaise lib-propagande
  lib-autorite-administrative-avec-tous-les-droits lobby-vivendi
0 mis à jour, 5 nouvellement installés, 4 à enlever et 0 non mis à jour.
Il est nécessaire de prendre 70 heures de débats à l'Assemblée Nationale et un n
ombre indéterminé d'heures de propagande dans les médias.
Après cette opération, 500000€ d'argent public supplémentaires seront utilisés.
Souhaitez-vous continuer [O/n] ? O

Suppression de lib-presomption-d-innocence...
Suppression de lib-droits-de-la-defense...
Suppression de lib-separation-des-pouvoirs...
Suppression de lib-information-impartiale...

Réception de : 1 http://www.vivendi.fr lobby/control lib-bypass-constitution-fra
ncaise réceptionnés en 1s
Réception de : 2 http://www.vivendi.fr lobby/control lobby-vivendi réceptionnés
en 1s
Réception de : 3 http://www.vivendi.fr lobby/control lib-propagande réceptionnés
 en 1s
Réception de : 4 http://www.sarkozy.fr util/rights lib-autorite-administrative-a
vec-tous-les-droits réceptionnés en 1s

Dépaquetage de lib-bypass-constitution-francaise...
Dépaquetage de lobby-vivendi...
I lobby-vivendi était déjà présent et très bien intégré au système.
Dépaquetage de lib-propagande...
Dépaquetage de lib-autorite-administrative-avec-tous-les-droits...

Génération de hadopi...
Refus de discussions...
Rejet des amendements proposés (utilisation du module anéfé-rejeté)...
Compilation de oofirewall...
Compilation de arguments-fallacieux...
  Compilation de les-accords-de-l-elysee...
  Compilation de les-ventes-de-cd-chutent-de-50%-et-celles-des-cassettes-audio-d
e-90%-c-est-inadmissible...
  Compilation de la-creation-est-en-train-de-mourir-a-cause-de-gens-qui-attaquen
t-les-bateaux...

W Le module 5-gus-dans-un-garage semble poser des problèmes de compilation de ar
guments-fallacieux.

E Problème lors de la génération de hadopi : des composants cachés derrière des
rideaux sont apparus de manière inattendue. Réessayer [O/n] ? O

Pour éviter que le problème ne se reproduise, il est nécessaire de mettre à jour
le module deputes-godillots. Souhaitez-vous mettre à jour [O/n] ? O

Compilation de cope-rabat-les-troupes...
Compilation de laver-l-affront...
Mise à jour de deputes-godillots...

W De nombreux paquets de type amendement ralentissent l'installation d'hadopi.

L'installation de hadopi semble avoir réussi.

Vérification de la compatibilité avec la constitution française...
E Le module lib-bypass-constitution-francaise a été détecté par le conseil const
itutionnel.
E Le module répression a dû être désactivé pour protéger la constitution.

Installation obligatoire de internet-liberte-fondamentale.
Réception de : 1 http://www.constitution.fr rights internet-liberte-fondamentale
 réceptionnés en 1s
Dépaquetage de internet-liberte-fondamentale...

albanel@majors$ for m in media; do echo 'Je prends acte de la décision du consei
l constitutionnel. 95% du texte a été validé.'; done

E lobby-vivendi panic détecté.
Prise en charge de l'erreur par sarkozy et lobby-vivendi.

exit

sarkozy@majors# deluser albanel
sarkozy@majors# ls gauche/corrompus/*
  Trop de résultats ont été trouvés : affichage des premiers résultats.
    jack_lang frederic_mitterrand
sarkozy@majors# adduser lang
 > opération échouée
sarkozy@majors# adduser mitterrand
sarkozy@majors# su mitterrand

mitterrand@majors$ sudo /etc/init.d/pantind start
 * Starting PantinServer                                                 [ OK ]
mitterrand@majors$ pantin -verbose
Attente des ordres...
Exécution à distance de "apt-get install hadopi2"...
L'installation du paquet hadopi2 peut corriger le problème. Tenter [O/n] ? O
Lecture des listes de paquets... Fait
Construction de l'arbre des dépendances
Lecture des informations d'état... Fait
Les paquets supplémentaires suivants seront installés :
  ordonnance-penale negligence-caracterisee
  on-vous-prend-vraiment-pour-des-cons
Les NOUVEAUX paquets suivants seront installés :
  hadopi2 ordonnance-penale negligence-caracterisee
  on-vous-prend-vraiment-pour-des-cons
Les paquets suivants seront mis à jour :
  lib-bypass-constitution-francaise
0 mis à jour, 4 nouvellement installés, 0 à enlever et 1 non mis à jour.
Il est nécessaire de prendre 40 heures de débats à l'Assemblée Nationale.
Après cette opération, 300000€ d'argent public supplémentaires seront utilisés.
Souhaitez-vous continuer [O/n] ? O

Réception de : 1 http://justice-expeditive.fr tools ordonnance-penale réceptionn
és en 1s
Réception de : 2 http://justice-expeditive.fr tools negligence-caracterisee réce
ptionnés en 1s
Réception de : 3 http://habitudes.gouv.fr kernel on-vous-prend-vraiment-pour-des
-cons réceptionnés en 1s
Réception de : 4 http://henrard.fr control lib-bypass-constitution-francaise réc
eptionnés en 5 minutes

Dépaquetage de ordonnance-penale...
Dépaquetage de negligence-caracterisee...
Dépaquetage de on-vous-prend-vraiment-pour-des-cons...
I En fait ce module était déjà présent sur le système depuis un moment.
Dépaquetage de lib-bypass-constitution-francaise...

Paramétrage de on-vous-prend-vraiment-pour-des-cons...
Paramétrage de lib-bypass-constitution-francaise...

Génération de hadopi2...
Refus de TOUTES discussions (ignorer-analyses-pertinentes activé)...
Rejet de TOUS les amendements proposés (utilisation du module anéfé-rejeté-en-ra
fale)...
Le paquet arguments-fallacieux existait déjà, mais il nécessite maintenant les d
épendances arguments-fallacieux-negligence-caracterisee et arguments-fallacieux-
ordonnance-pénale. Installer [O/n] ? O

Réception de : 1 http://henrard.fr control arguments-fallacieux-negligence-carac
terisee réceptionnés en 2 jours
Réception de : 2 http://henrard.fr control arguments-fallacieux-ordonnance-pénal
e réceptionnés en 1 jour

Dépaquetage de arguments-fallacieux-negligence-caracterisee...
Dépaquetage de arguments-fallacieux-ordonnance-pénale...

W hadopi2-senat-1 est moins performant que hadopi-senat-*.
I deputes-godillots utilisé pour compiler le module vote-solennel.
W Problème éventuel de sécurité. Des éléments de compilation de vote-solennel on
t peut-être été contrôlés à distance : des incohérences entre leurs données et l
eurs actions ont été détectées.
La compilation de vote-solennel a réussi.

W L'installation de hadopi2-senat-2 a fonctionné, mais il semble que 90% du code
source du paquet n'ait pas été utilisé.

Compilation de vote-solennel-2...
I deputes-godillots fonctionne à merveille.
La compilation de vote-solennel-2 a réussi.

Vérification de la compatibilité avec la constitution française...
E Le module on-vous-prend-vraiment-pour-des-cons a fortement déplu au conseil co
nstitutionnel.
E Le paquet hadopi2 a été tagué "censuré" par le conseil constitutionnel.

Attente des ordres...
Ordre reçu de lobby-vivendi : créer une taxe sur les fournisseurs d'accès sans c
ontrepartie.
Résolution du problème trouver-des-arguments-bidons-pour-faire-passer-ça-et-refu
ser-la-contribution-créative en cours...
Réception de : 1 http://www.majors.fr help toubon réceptionnés en 1s
Réception de : 2 http://www.majors.fr help zelnik réceptionnés en 1s

Attente des ordres...
~~~
