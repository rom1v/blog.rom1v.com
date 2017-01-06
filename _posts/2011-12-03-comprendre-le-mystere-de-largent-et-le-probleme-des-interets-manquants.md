---
layout: post
title: Comprendre le mystère de l'argent et le problème des intérêts manquants
date: 2011-12-03 14:23:01+01:00
---

Peu avant 1940, [Louis Even][] a écrit une célèbre [robinsonade][] pour
_comprendre le mystère de l'argent_ : [L'île des naufragés][].

[Louis Even]: http://fr.wikipedia.org/wiki/Louis_Even
[robinsonade]: http://fr.wikipedia.org/wiki/Robinsonade
[L'île des naufragés]: http://www.michaeljournal.org/ilenauf.htm

_Si vous ne la connaissez pas encore, je vous conseille de la lire avant de
poursuivre. À cette époque, la monnaie était basée sur l'or, mais ça ne change
pas fondamentalement le problème. Ses écrits sont parfois très imprégnés de
religion, aussi faut-il faire preuve de discernement._

Cette fable met en évidence l'injustice du système monétaire, dans lequel
l'argent est créé par le crédit ([j'en ai déjà parlé][injustice]).

[injustice]: {% post_url 2011-05-10-linjustice-monetaire %}


## Création monétaire par le crédit

Commençons par un petit rappel, grâce à [un résumé du fonctionnement de la
création monétaire][résumé] :

[résumé]: http://www.internetactu.net/2010/11/30/linnovation-monetaire-25-comment-se-cree-la-monnaie/

> **3. La création de monnaie en échange d'une promesse**
>
> Comment se créent alors les plus de 90 % restants de monnaie qui circulent sur
> la planète ?
>
> Cette part de monnaie est créée par un mécanisme peu connu et étonnant : par
> le simple fait que vous signiez une demande de prêt à la banque, vous
> reconnaissez que vous rembourserez cette somme (ou qu'à défaut vous serez
> saisis sur vos biens pour un montant équivalent à cette valeur). **Les banques
> créent alors purement et simplement cette somme** par une simple opération
> d'écriture, et elles le déposent sur votre compte. Cet argent est ensuite
> détruit au fur et à mesure du remboursement de la dette. L'argent créé est
> qualifié de monnaie scripturale : de l'argent créé par un jeu d'écriture…
>
> Plus de 90 % de l'argent disponible sur la planète est ainsi constitué des
> dettes en cours auprès des banques. Les banques maîtrisent donc plus de 90 %
> des moyens de paiement qui permettent les échanges entre les
> hommes.

Pour un peu plus de détails : [La création monétaire pour les nuls
(pdf)][pour les nuls].

[pour les nuls]: http://www.societal.org/monnaie/creationmonnaiepourlesnuls.pdf


## Intérêts manquants

Revenons à notre _île des naufragés_.

Cet extrait d'un [texte de James Crate Larkin][larkin] résume bien un élément
important de la thèse de l'auteur :

[larkin]: http://www.michaeljournal.org/Larkin_fr.pdf

> La dette ne peut jamais s'éteindre sous un tel système, parce que tout argent
> mis en circulation l'est par des prêts bancaires et que l'emprunteur doit
> rembourser plus que le montant reçu. Il doit rembourser le principal, créé par
> le banquier, plus l'intérêt créé par personne ! … Le procédé est cumulatif —
> la dette grossit toujours, parce que, pour payer l'intérêt, il faut
> nécessairement quelque part une nouvelle alimentation de monnaie, et cette
> nouvelle émission est elle-même porteuse d'intérêt. Comment la dette
> serait-elle remboursable ?

C'est le problème des _intérêts manquants_ : si seul l'argent correspondant au
principal est créé, comment rembourser les intérêts ? Cet argument, s'il est
valide, ne dénonce pas seulement une _injustice_, il met en évidence une
parfaite _impossibilité_ : il n'existe **aucun moyen** pour la population de
rembourser toutes ses dettes envers la banque, puisqu'il n'y a pas assez
d'argent en circulation.


## Réfutation ?

Cependant, certains critiquent la fable et prétendent en fournir une
[réfutation][], dont l'argument central peut se formuler ainsi : _il existe au
moins un moyen pour la population de rembourser toutes ses dettes envers la
banque, il faut considérer que le banquier va dépenser les intérêts dans
l'économie_.

[réfutation]: http://gidmoz.wordpress.com/2011/05/29/lile-des-naufrages-refutation/

Et sur ce point, ils ont raison. Il est _possible_ de trouver une suite
d'échanges entre les individus permettant à la population de rembourser toutes
ses dettes : la banque _peut_ dépenser les intérêts perçus au fur et à mesure,
permettant ainsi à la population de les _regagner_.

Voici un exemple avec 3 individus :

  * **B** : le banquier
  * **X** : un boulanger
  * **Y** : un producteur de pommes

[bread]: {{ site.assets }}/mystere_argent/bread.png
[apple]: {{ site.assets }}/mystere_argent/apple.png


### État initial

Au départ, tous les comptes sont à zéro.

| individu | compte | dettes | produits                                                                     |
|:--------:|:------:|:------:|-----------------------------------------------------------------------------:|
| B        | 0      |        |                                                                              |
| X        | 0      |        | ![bread][] ![bread][] ![bread][] ![bread][] ![bread][] ![bread][] ![bread][] |
| Y        | 0      |        | ![apple][] ![apple][] ![apple][] ![apple][] ![apple][]                       |


### Étape 1

X souhaite acheter des pommes à Y, pour un montant de 5.

Il emprunte donc 5 à B, qui les crée, pour une période donnée (plusieurs
années). B demandera des intérêts, disons 40%. En tout, X devra donc rembourser
7.

| individu | compte | dettes | produits                                                                     |
|:--------:|:------:|:------:|-----------------------------------------------------------------------------:|
| B        | 0      | <strong>X me doit 5</strong><br/><strong>je dois 5 à X</strong><br/><strong><em>X me doit 2 (intérêts)</em></strong> | |
| X        | 5      | <strong>B me doit 5</strong><br/><strong>je dois 5 à B</strong><br/><strong><em>je dois 2 à B (intérêts)</em></strong> | ![bread][] ![bread][] ![bread][] ![bread][] ![bread][] ![bread][] ![bread][] |
| Y        | 0      |        | ![apple][] ![apple][] ![apple][] ![apple][] ![apple][]                       |


### Étape 2

X peut maintenant acheter des pommes à Y.

| individu | compte | dettes | produits                                                                     |
|:--------:|:------:|:------:|-----------------------------------------------------------------------------:|
| B        | 0      | X me doit 5<br/><strong>je dois 5 à Y</strong><br/><em>X me doit 2 (intérêts)</em> |  |
| X        | 0      | je dois 5 à B<br/><em>je dois 2 à B (intérêts) | ![bread][] ![bread][] ![bread][] ![bread][] ![bread][] ![bread][] ![bread][] <br/> ![apple][] ![apple][] ![apple][] ![apple][] ![apple][] |
| Y        | 5      | <strong>B me doit 5</strong> |                                                        |

_Nous nous rendons bien compte ici que **la monnaie n'est qu'une dette de banque qui circule**._



### Étape 3

Y décide d'acheter des baguettes à X, pour un montant de 5.

| individu | compte | dettes | produits                                                                     |
|:--------:|:------:|:------:|-----------------------------------------------------------------------------:|
| B        | 0      | X me doit 5<br/><strong>je dois 5 à X</strong><br/><em>X me doit 2 (intérêts)</em> |  |
| X        | 5      | <strong>B me doit 5</strong><br />je dois 5 à B<br/><em>je dois 2 à B (intérêts) | ![bread][] ![bread][] ![apple][] ![apple][] ![apple][] ![apple][] ![apple][] |
| Y        | 0      |        | ![bread][] ![bread][] ![bread][] ![bread][] ![bread][]                       |

_Les comptes se retrouvent exactement dans la même situation qu'à l'étape 1._


### Étape 4

X rembourse les intérêts à B, d'un montant de 2.

| individu | compte | dettes | produits                                                                     |
|:--------:|:------:|:------:|-----------------------------------------------------------------------------:|
| B        | 2      | X me doit 5<br/><strong>je dois 3 à X</strong> |  |
| X        | 3      | <strong>B me doit 3</strong><br />je dois 5 à B | ![bread][] ![bread][] ![apple][] ![apple][] ![apple][] ![apple][] ![apple][] |
| Y        | 0      |        | ![bread][] ![bread][] ![bread][] ![bread][] ![bread][]                       |


### Étape 5

B achète des baguettes à X pour un montant de 2 (_il dépense les intérêts dans l'économie_).

| individu | compte | dettes | produits                                                                     |
|:--------:|:------:|:------:|-----------------------------------------------------------------------------:|
| B        | 0      | X me doit 5<br/><strong>je dois 5 à X</strong> | ![bread][] ![bread][]                |
| X        | 5      | <strong>B me doit 5</strong><br />je dois 5 à B | ![apple][] ![apple][] ![apple][] ![apple][] ![apple][] |
| Y        | 0      |        | ![bread][] ![bread][] ![bread][] ![bread][] ![bread][]                       |


### Étape 6

X rembourse sa dette à B.

| individu | compte | dettes | produits                                                                     |
|:--------:|:------:|:------:|-----------------------------------------------------------------------------:|
| B        | 0      |        | ![bread][] ![bread][]                                                        |
| X        | 0      |        | ![apple][] ![apple][] ![apple][] ![apple][] ![apple][]                       |
| Y        | 0      |        | ![bread][] ![bread][] ![bread][] ![bread][] ![bread][]                       |

Voilà, tout est remboursé. [CQFD][], Louis Even racontait n'importe quoi, sa
fable n'est que pure manipulation. Circulez, y'a rien à voir.

[cqfd]: http://fr.wikipedia.org/wiki/CQFD_%28abr%C3%A9viation%29

Attendez, pas si vite ! Tout au plus, nous pouvons en déduire que le récit est
incomplet, car il n'a traité que le cas où les intérêts n'étaient pas dépensés
dans l'économie. Traitons donc la partie manquante, quand les intérêts sont
dépensés.


## Avec intérêts dépensés

Remarquons avant tout que si seulement une partie des intérêts est dépensée,
nous sommes confrontés exactement au même problème (il manquera juste un peu
moins d'argent pour rembourser, mais il en manquera). Seule l'hypothèse où
**100% des intérêts sont dépensés** pourrait donc permettre, éventuellement, la
pénurie de monnaie. Et si effectivement nous pouvions éviter cette pénurie, le
système serait-il juste ?


### Injustice visible

Dans l'exemple détaillé où tout a été remboursé, qui a produit quoi et qui a
consommé quoi ?

  * X a produit des baguettes pour une valeur de 7, et a consommé des pommes
    pour une valeur de 5 ;
  * Y a produit des pommes pour une valeur de 5 ;
  * B a consommé des baguettes pour une valeur de 2.


**B a gagné des intérêts sur de l'argent qu'il a créé [_ex nihilo_][ex
nihilo]**, sans aucun travail, qu'il a pu ensuite dépenser dans l'économie.

[ex nihilo]: http://fr.wikipedia.org/wiki/Ex_nihilo

James Crate Larkin [écrivait][larkin] :

[larkin]: http://www.michaeljournal.org/Larkin_fr.pdf

> Ce paiement d'intérêt, par la société, au système bancaire, sur de la monnaie
> nouvellement créée et qui ne coûte rien, n'est pas du tout semblable ni
> comparable à l'intérêt qu'un prêteur ordinaire exige sur de l'argent déjà en
> existence, qu'il a gagné, épargné et prêté à l'industrie.

Si je dis uniquement cela, vous allez tout de suite me rétorquer que j'ai oublié
de prendre en compte le service qu'a fourni le banquier : B a rendu service à X
pour une valeur de 2, et X a "consommé" le service de la dette. Le banquier
fournit un service, et comme tout service, il est normal qu'il le fasse payer.

Mais cet argument ne tient pas.

D'abord, le service fourni n'est pas un service comme un autre : **le banquier
demande, en échange de quelque chose, ce même quelque chose en quantité plus
importante, alors que lui seul a le droit de créer ce quelque chose**. Cela
n'implique pas strictement, nous l'avons vu, que le remboursement sera
impossible (en pratique, il le sera, nous le verrons), mais cela en fait à
l'évidence **un service singulier**.

Ensuite, remarquons que la banque perçoit un pourcentage sur toute la monnaie en
circulation. Modifions l'exemple précédent pour qu'il y ait un banquier, 1
million de X et 1 million de Y. Dans ce nouvel exemple, regardons ce qu'il se
passe :

  * chaque X a produit des baguettes pour une valeur de 7, et a consommé des
    pommes pour une valeur de 5 ;
  * chaque Y a produit des pommes pour une valeur de 5 ;
  * B a consommé pour une valeur de **2000000**.

Le déséquilibre est flagrant. Il n'est pas défendable que le banquier puisse (et
doive) récupérer, à son profit, un pourcentage de toutes les richesses créées.
Un tel gain ne peut raisonnablement pas correspondre au service fourni.

Vous me répondrez qu'en réalité, tout n'est pas pour la banque : elle doit par
exemple verser des intérêts à ses clients. Mais d'une part, les intérêts qu'elle
perçoit (sur de l'argent créé !) sont supérieurs aux intérêts que perçoivent les
clients (sur de l'argent gagné) ; l'argument reste donc le même avec simplement
un pourcentage moins important. Et d'autre part, nous pouvons appliquer le
raisonnement sur l'ensemble constitué des banques et des personnes les plus
riches (les [fameux 1%][99%]), qui sont évidemment les principaux bénéficiaires
de ces intérêts.  Vous me ferez peut-être également remarquer que la banque
verse des salaires. Oui. Les autres entreprises aussi.

[99%]: http://en.wikipedia.org/wiki/We_are_the_99%25


### Injustice cachée

En plus de cela, le banquier profite d'une [déflation][].

[déflation]: http://fr.wikipedia.org/wiki/D%C3%A9flation

Pour le comprendre, reprenons l'exemple détaillé ci-dessus, et considérons à
chaque étape non seulement la masse monétaire dans son ensemble, mais également
l'argent qui circule au sein de la population (entre X et Y).

| Étape | Qté en circulation | Qté entre X et Y | Commentaire |
|:-----:|:------------------:|:----------------:|:----------- |
| 0     | 0                  | 0                |             |
| 1     | 5                  | 5                |             |
| 2     | 5                  | 5                |             |
| 3     | 5                  | 5                |             |
| 4     | 5                  | **3**            | Déflation locale, les prix ont tendance à baisser. C'est à ce moment que le banquier achète à X. |
| 5     | 5                  | **5**            | Une fois l'argent réinjecté dans l'économie, les prix ont tendance à réaugmenter. Ce qu'a acheté le banquier retrouve donc sa valeur. |

_Il faut bien avoir à l'esprit que la déflation se produit au fur et à mesure
du remboursement des intérêts au banquier, sur une longue période, et non
brutalement comme l'exemple pourrait le laisser penser._

Le banquier peut racheter les actifs de X et Y à bas prix, puisqu'ils ont
baissé suite à la déflation. Cette réinjection de monnaie fera augmenter le
prix de ces mêmes actifs en proportion mais ils auront changé de main de façon
forcée (vers le banquier).

_Merci à [Galuel](http://www.creationmonetaire.info/) pour cette explication._


### Hypothèse vérifiée ?

Nous avons vu qu'il était _nécessaire_ que _100% des intérêts soient dépensés
dans l'économie_ pour éviter la pénurie d'argent. Remarquons que rien ne montre
que cette hypothèse, fût-elle _injuste_, serait _suffisante_.

Mais est-elle seulement _vérifiée_ dans la réalité ? Car la simple possibilité
théorique de cette situation idéalisée ne peut suffire à prétendre qu'elle
serait vérifiée en pratique.

Nous pouvons même nous convaincre du contraire.

Les prêts que nous avons considérés jusqu'à présent sont ceux effectués par le
système bancaire, qui sont les seuls créateurs de monnaie. Mais l'argent ainsi
créé peut être reprêté par quelqu'un à quelqu'un d'autre, avec intérêts. Dans
ce cas, **cet argent sera grevé de 2 intérêts**.

Supposons que la banque promette de dépenser 100% des intérêts dans l'économie,
et qu'une personne, libre de toutes dettes, possède de l'argent en excès. Elle
va pouvoir le prêter (ou l'investir) _perpétuellement_ pour percevoir
_perpétuellement_ des intérêts. Tout dépenser dans l'économie ne serait pas à
son avantage, car cela représente pour elle une source intarissable de revenus.
Ses emprunteurs, pour lui payer les intérêts, devront nécessairement récupérer
de l'argent dans le stock disponible… qui n'en contient pas assez pour
rembouser le prêt principal **et** le prêt secondaire avec intérêts. Dans ce
cas, il est inévitable de rembourser les anciens prêts avec… de nouveaux prêts
plus importants.

Plus généralement, ceux qui ont de l'argent s'attendent à percevoir un intérêt
dessus. L'investissement peut créer de nouvelles richesses réelles, mais seules
de nouvelles dettes peuvent créer plus d'argent. Donc toute attente de profits,
à partir de l'argent investi ou de prêts, crée de la demande pour plus
d'argent, qui peut être :

  * soit récupéré à partir de quelqu'un d'autre (ce qui déplacera la pénurie
    d'argent) ;
  * soit créé par une nouvelle dette.

_C'est [ce qu'a expliqué Paul Grignon][grignon] en réponse aux critiques contre
sa fameuse vidéo [L'argent dette][argent dette], qui l'ont poussé à publier
[L'argent dette 2 : promesses chimériques][argent dette 2]._

[grignon]: http://paulgrignon.netfirms.com/MoneyasDebt/disputed_information.html#disputed2
[argent dette]: http://www.dailymotion.com/video/x75e0k_l-argent-dette-de-paul-grignon-fr-i_news
[argent dette 2]: http://www.dailymotion.com/video/xbqww7_l-argent-dette-2-promesses-chimeriq_news


## Légitimité

Mais avant de rentrer dans tous ces détails, peut-être aurait-il simplement
fallu questionner, sur le principe, la _légitimité_ d'un tel système.

L'argent est créé par le crédit. Donc la société est _forcément_ endettée
(sinon, il n'y aurait pas d'argent). Ce qui implique qu'**elle devra
perpétuellement payer des intérêts aux banques**, des acteurs privés.

Ceci est d'autant plus étonnant que c'est la population, dans son ensemble, qui
produit les richesses. La banque ne fait que créer l'argent permettant de les
échanger. **Ce sont donc ceux qui produisent les richesses qui sont endettés
envers ceux qui créent l'argent.**

Louis Even [l'expliquait][lecon3] très bien :

[lecon3]: http://www.michaeljournal.org/lecon3.htm

> Soulignons aussi un point frappant: C'est la production qui donne de la
> valeur à l'argent. Une pile d'argent, sans produits pour y répondre, ne fait
> pas vivre. Or, ce sont les cultivateurs, les industriels, les ouvriers, les
> professionnels, le pays organisé, qui font les produits, marchandises ou
> services. Mais ce sont les banquiers qui font l'argent basé sur ces produits.
> **Et cet argent, qui tire sa valeur des produits, les banquiers se
> l'approprient et le prêtent à ceux qui font les produits.** C'est un vol
> légalisé.

[Maurice Allais][], prix Nobel d'économie, [écrivait même][faux-monnayeurs] :

[Maurice Allais]: http://fr.wikipedia.org/wiki/Maurice_Allais
[faux-monnayeurs]: http://fr.wikipedia.org/wiki/Faux-monnayage#Faux-monnayage_l.C3.A9gal

> Dans son essence, **la création monétaire ex nihilo actuelle par le système
> bancaire est identique**, je n'hésite pas à le dire pour bien faire
> comprendre ce qui est réellement en cause, **à la création de monnaie par des
> faux-monnayeurs**, si justement condamnée par la loi. Concrètement elle
> aboutit aux mêmes résultats. La seule différence est que ceux qui en
> profitent sont différents.

_(La Crise mondiale aujourd'hui. Pour de profondes réformes des institutions
financières et monétaires., Maurice Allais, éd. Clément Juglar, 1999, p. 110)_

_Le texte [est accessible ici][crise mondiale]._

[crise mondiale]: http://etienne.chouard.free.fr/Europe/messages_recus/La_crise_mondiale_d_aujourd_hui_Maurice_Allais_1998.htm


## Conséquences

Ne vous paraît-il pas étonnant qu'il faille toujours travailler plus (au point
de [réformer les retraites][retraites]), alors que nous avons considérablement
amélioré notre productivité ces dernières décennies ? Et la crise viendrait
d'un manque de travail, d'un manque de production, vraiment ?

[retraites]: http://www.lepoint.fr/economie/malakoff-mederic-le-frere-de-sarkozy-soupconne-de-tirer-profit-de-la-reforme-des-retraites-28-10-2010-1255629_28.php

La véritable raison, ce n'est pas que nous manquons de richesses réelles. Nous
ne devons pas lutter contre une rareté de produits dont nous avons besoin, mais
contre la rareté de l'argent permettant d'accéder à ces produits. Et la rareté
de cet argent dépend du crédit, qui dépend de la croissance.

En clair : **sans croissance, nous avons (toujours) une abondance de produits,
mais une pénurie de monnaie pour y accéder. Grâce à la croissance, nous
limitons temporairement la pénurie de monnaie**.

C'est la raison pour laquelle nous devons toujours produire (et consommer)
plus. Ce qui, d'ailleurs, [amplifie le problème][lecon3] :

> A mesure que le pays se développe, en production comme en population, il faut
> plus d'argent. Or on ne peut avoir d'argent nouveau qu'en s'endettant d'une
> dette collectivement impayable.
>
> Il reste donc le choix entre arrêter le développement ou s'endetter; entre
> chômer ou contracter des emprunts impayables. C'est entre ces deux choses-là
> qu'on se débat justement dans tous les pays.

Dans [un récent discours][discours klein], [Naomi Klein][] dénonçait cette
pénurie artificielle (citation rapportée par [Zoupic][]) :

[discours klein]: http://www.naomiklein.org/articles/2011/10/occupy-wall-street-most-important-thing-world-now
[Naomi Klein]: http://fr.wikipedia.org/wiki/Naomi_Klein
[zoupic]: http://www.zoupic.com/2011/10/14/naomi-klein-nous-parle-de-rarete-et-dabondance/

> Nous savons tous, ou du moins nous sentons que le monde est à l'envers : nous
> agissons comme s'il n'y avait pas de limites à ce qui, en réalité, n'est pas
> renouvelable – les combustibles fossiles et l'espace atmosphérique pour
> absorber leurs émissions. Et nous agissons comme s'il y avait des limites
> strictes et inflexibles à ce qui, en réalité, est abondant – les ressources
> financières pour construire la société dont nous avons besoin.


### Vivre "au-dessus de ses moyens"

Si nous sommes endettés, ce n'est donc pas parce que _nous vivons au-dessus de
nos moyens_, comme nous pouvons l'entendre parfois, mais bien parce que le
système monétaire entraîne inévitablement l'endettement.

Cela rejoint un point de l'[argumentaire de
MrQuelquesMinutes][MrQuelquesMinutes], à propos de [sa vidéo sur la dette
publique][dette publique] :

[MrQuelquesMinutes]: http://www.mrquelquesminutes.fr/#dettepublique_argumentaire
[dette publique]: https://www.youtube.com/watch?v=ZE8xBzcLYRs

> **B-2) L'État doit emprunter aux marchés financiers pour fonctionner, cela
> veut-il dire que l'État utilise l'argent qu'il n'a pas ? Et que l'État vit
> donc "au dessus de ses moyens" ?**
>
> Si l'on poursuit ce raisonnement, alors toute la société vit au dessus de ses
> moyens puisque toute la monnaie qu'elle utilise provient à l'origine du
> crédit bancaire. Pourtant, cela n'a pas de sens de dire cela, puisque c'est
> la société elle-même qui produit les biens et les services qu'elle utilise.
>
> Qu'une nation ou qu'un État s'endette dans un système monétaire où la monnaie
> est créé uniquement par le crédit, ne veut pas dire qu'il "vit au-dessus des
> ses moyens", mais que ce système monétaire, de part sa nature, provoque
> l'endettement généralisé de cette nation ou de cet État.


## Conclusion

La possibilité très théorique que la société soit capable de rembourser ses
dettes (afin qu'elles n'augmentent pas perpétuellement) ne remet nullement en
cause les critiques fondamentales que porte _Louis Even_ (et bien d'autres) sur
le fonctionnement de la création monétaire, manifestement injuste.

Un système où la pénurie de monnaie est quasiment garantie, et où les richesses
sont redistribuées massivement de la population vers les banques, n'est pas
acceptable. Ne devons donc le corriger. Comment ?

La première étape est de comprendre comment fonctionne le système actuel, pour
envisager plusieurs réponses. L'une d'elles est le [100% monnaie][].

[100% monnaie]: http://postjorion.wordpress.com/2011/11/04/215-resume100monnaie/

Personnellement, je suis convaincu que la meilleure est le [dividende
universel, pour plusieurs raisons][dividende universel] ([et beaucoup
d'autres][tetedequenelle]).

[dividende universel]: {% post_url 2011-02-17-dividende-universel-un-enjeu-majeur-de-societe %}
[tetedequenelle]: http://www.tetedequenelle.fr/2011/11/pourquoi-nous-avons-besoin-dun-revenu-garanti/

À vous de vous forger votre avis…
