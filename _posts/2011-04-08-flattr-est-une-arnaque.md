---
layout: post
title: Flattr est une arnaque
date: 2011-04-08 12:09:53+02:00
tags:
- planet-libre
---

{: .center}
![flattr]({{ site.assets }}/flattr/flattr.png)


## Principe

[Flattr](http://flattr.com/) est un système de micro-paiement permettant de
rémunérer les auteurs des contenus sur Internet. Le principe est simple : chaque
mois, l'utilisateur choisit la somme qu'il va donner (avec un minimum de 2€).
Lorsqu'il tombe sur un site qui supporte _Flattr_, il a la possibilité de
cliquer sur un bouton indiquant qu'il apprécie son contenu. À la fin du mois, la
somme qu'il a versée est répartie entre les auteurs des différents contenus
qu'il a appréciés (moins la commission que prend _Flattr_, 10% des versements
effectués).

Ce principe est très séduisant, car avant cela il n'était pas possible
facilement de donner de petites sommes à plein d'auteurs.

Mais malheureusement, il y a une arnaque dans le système.


## 10% de commission

_Flattr_ prélève 10% des sommes versées. Dit comme ça, on a l'impression que ce
n'est pas grand chose : si je mets 2€, 0,20€ leur seront destinés, et 1,80€
seront pour les auteurs, rien de très choquant à première vue. Sauf que
globalement, cela leur permet juste de récupérer quasiment tout l'argent
injecté.

Je vais tenter d'expliquer pourquoi.


## De l'argent disparaît chaque mois

Afin de bien discerner comment se comporte l'argent injecté (vers où il va
surtout), décidons pour les besoins de la démonstration qu'aucun argent neuf
n'est rajouté chaque mois. Le premier mois, chacun verse une somme quelconque
(pour un total qu'on appellera _M_). Les mois suivants, ils versent une somme de
manière à ce qu'il y ait autant d'argent qui rentre que d'argent qui est sorti
du mois précédent. Par exemple, chacun remet ce qu'il a gagné (si j'ai gagné 7€
je remets les 7€, si j'ai gagné 12€ je remets les 12€). Comme _Flattr_ prend 10%
sur les versements, nous pouvons déterminer à coup sûr le montant total d'un
mois donné : 0,9×(montant du mois précédent).

Sur une période de _m_ mois, la part destinée à _Flattr_ est donc de _(1 - 0,9m)
× M_.

Regardons donc quelle part de ce montant _Flattr_ s'accapare sur une période plus longue.

Sur 1 an, _(1 - 0,912) × M = 0,7176 × M_, soit 71,76% de la somme totale !

Sur 5 ans, _(1 - 0,960) × M = 0,9982 × M_, soit 99,82%. Autrement dit, tout est
pour _Flattr_.

S'il y a 20000 inscrits qui versent chacun 10€, _M = 20000 × 10 = 200000€_. Sur
1 an, 143514,09€ seront destinés à _Flattr_. Sur 5 ans, pas moins de 199640,60€,
le reste circulant entre les membres.

Imaginez que votre compte en banque soit prélevé chaque mois 10% de son montant
pour frais de gestion. Vous avez 5000€, vous êtes prélevés de 500€. Le mois
suivant, plus que 450€… Au bout d'un an, il ne va pas rester grand chose pour
échanger avec les autres…

**Nous avons simplifié le problème en n'injectant pas d'argent neuf.** Ou plutôt
nous avons supprimé ce qui cachait cette incroyable distribution. Maintenant
plaçons-nous dans le cas "réel" et injectons cet argent neuf chaque mois, de
manière à ce que le montant total reste égal à _M = 200000€_ (par exemple, les
20000 membres versent 10€ chaque mois). Par rapport à l'exemple précédent, les
membres injectent donc 20000€ d'argent neuf dans le système (en plus des 180000€
restants du mois précédent). Mais cet "argent neuf", il va évidemment subir le
même traitement que dans la situation précédente, avec un mois de décalage, et
être principalement redistribué vers _Flattr_ au bout de quelques mois. C'est
donc encore plus d'argent pour _Flattr_.


## 10%… voire 100%

Sur une longue période, nous avons vu que _Flattr_ rafflait une part essentielle
de tous les versements. Mais regardons ce qui se passe sur un seul mois, en
pourcentage. Nous avons l'impression que la commission est de 10%, mais en fait
elle est beaucoup plus importante, car les échanges entre les membres se
compensent, au moins en partie.

Pour le comprendre, prenons un exemple concret, avec 3 membres, qui versent
chacun 10€ :

  * A donne 6 à B et 3 à C (et 1 à Flattr) ;
  * B donne 4 à A et 5 à C (et 1 à Flattr) ;
  * C donne 4 à A et 5 à B (et 1 à Flattr).

{: .center}
![graphe-flattr]({{ site.assets }}/flattr/graphe-flattr.png)

Combien d'argent a été échangé en tout entre les membres ?

  * 2€ entre A et B (6€ de A vers B et 4€ de B vers A) ;
  * 1€ entre A et C (3€ de A vers C et 4€ de C vers A) ;
  * 0€ entre B et C (5€ de B vers C et 5€ de C vers B).

3€ ont donc été échangés. Et pour ces 3€ d'échange, 3€ ont été donnés à
_Flattr_, soit 50% des échanges totaux !

C'est même pire que cela, car il faut prendre en compte la transitivité. Ici, C
transfert 1€ vers A, et A transfert 2€ vers B, on en a conclu qu'il y avait 3€
échangés. Mais l'euro qui transite de C vers A est "contenu" dans les 2€ qui
transitent de A vers B. Globalement, tout se passe comme si A et C transféraient
chacun 1€ vers B. Soit un total de 2€ échangés. La part de Flattr dans la somme
des échangés est donc de 60% dans ce cas-là. Mais il ne faut pas s'arrêter là,
il faut aussi intégrer la transitivité des transferts d'argent vers Flattr : A
et C transfèrent chacun 1€ vers B (et 1€ vers Flattr), et B transfert 1€ vers
Flattr. La situation est donc la même que si A et C transféraient chacun 0,50€
vers B et 1,50€ vers Flattr. **Soit 75% des transferts pour Flattr** (cette part
correspond à 100% moins le ratio du montant gagné par les membres ayant un gain
par rapport au montant perdu par les membres ayant une perte).

Le résultat après le premier mois est donc de :

  * A : -2€ ;
  * B : +1€ ;
  * C : -2€ ;
  * Flattr : +3€.

**Donc exactement comme si A et C avaient chacun donné 0,50€ à B et 1,50€ à
_Flattr_.**

Dans le cas limite, si tous les dons sont parfaitement répartis (chacun donne
autant qu'il reçoit, moins les 10%), alors tout se passe comme s'il n'y avait
aucun échange entre les membres. Chacun a mis 10€, et reçoit 9€. Résultat des
courses : le seul échange d'argent qui s'opère est le transfert d'1€ de chaque
membre vers _Flattr_, soit 100% des échanges.

La somme récupérée en valeur absolue par _Flattr_ chaque mois est connue à
l'avance (10% de _M_, ce qui est énorme, comme nous l'avons vu en quelques mois
ils récupèrent quasiment tout). En valeur relative par rapport aux échanges
effectués, en fonction de l'équilibre des dons, ce transfert d'argent des
membres vers _Flattr_ représente entre 10% et 100% des échanges. Ce système
coûte donc extrêmement cher par rapport aux échanges qu'il permet.


## Conclusion

Le concept de base est intéressant et séduisant, mais certaines arnaques sont
bien dissimulées. Il vaut mieux en avoir conscience avant de s'inscrire à un
système injuste.  Pour corriger ces problèmes, _Flattr_ pourrait être rémunéré
de la même manière que ses membres : par les micro-dons.

Il y a également [d'autres][flattr-senlise] [problèmes][flattr-numerama] que je
n'ai pas évoqués ici.

[flattr-senlise]: http://www.creationmonetaire.info/2010/05/flattr-le-modele-senlise.html
[flattr-numerama]: http://www.creationmonetaire.info/2010/12/numerama-installe-le-pire-flattr-un.html

**EDIT :** Je réponds aux deux principales critiques à ce billet [en
commentaire][comment].

[comment]: {{ page.url }}#comment-24
