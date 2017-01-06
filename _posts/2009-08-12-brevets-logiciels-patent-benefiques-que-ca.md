---
layout: post
title: 'Brevets logiciels : "patent" bénéfiques que ça…'
date: 2009-08-12 23:02:20+02:00
---

## Août, un mois riche en brevets XML

Microsoft, qui a toujours été favorable aux brevets logiciels, et qui le 4 août
dernier a réussi à faire valider une [demande de brevet][brevet-xml] relative à
la gestion des documents XML, se voit maintenant interdit de vendre Microsoft
Word aux États-Unis (et doit verser en plus 290 M$), car [il viole un
brevet][interdit] détenu par la firme canadienne i4i (qui se prononce d'ailleurs
_"eye for eye"_, _"œil pour œil"_).

[brevet-xml]: http://www.clubic.com/actualite-292850-microsoft-brevet-traitements-texte-xml.html
[interdit]: http://www.clubic.com/actualite-293370-microsoft-word-interdit-vente-etats-unis.html

Lorsqu'on lit ce genre d'information, l'ironie de la situation fait sourire,
mais il ne faut pas oublier l'absurdité de cette notion de brevets logiciels…


## Il était une fois le brevet 5787499

Avant de revenir sur les brevets en général et sur les brevets logiciels en
particulier, il est intéressant de voir le contenu de ce brevet « violé » par
Microsoft : il illustre bien (en tout cas pour ceux qui s'y connaissent un peu)
le type d'inventions géniales que protègent un brevet logiciel.

Bizarrement indisponible [ici][freepatentsonline] et [là][patentstorm], le voici
[en pdf][pdf].

[freepatentsonline]: http://www.freepatentsonline.com/5787499.html
[patentstorm]: http://www.patentstorm.us/patents/5787499.html
[pdf]: http://dl.rom1v.com/brevets-logiciels/US5787449A.pdf


### L'algorithme breveté

Après plein de blabla un peu rebutant sur l'intérêt de cette invention
« innovanter », la description détaillée suivie d'exemples montre l'algorithme
que ce brevet protège (pages 14 à 16). Le voici :

>   1. Start at Character Position zero.
>
>   2. Create storage space for the raw content.
>
>   3. Create storage space for a metacode map.
>
>   4. Set the elements in the map to zero.
>
>   5. Read characters until a metacode is encountered based on metacode
>   detection criteria.
>
>   6. Copy the characters up to the start of the code into the mapped content
>   storage or area.
>
>   7. Increase the character position by the number of characters placed into
>   the mapped content area.
>
>   8. Create a new map element and place the code into it.
>
>   9. With the map element store the character position of the beginning of the
>   code.
>
>   10. If there are more characters in the original then go to step 5.
>
>   11. Conversion is complete, store the metacode map and the mapped content.


Et à quoi ça sert? Tout simplement à séparer un document XML (qui contient donc
des balises et du contenu) en deux documents, un qui contient le contenu sans
balises, l'autre qui contient les balises et la position où les insérer dans le
contenu.

Il s'agit donc de transformer ce document :

{% highlight xml %}
<Chapter><Title>The Secret Life of Data</Title><Para>Data is hostile.</Para>The End</Chapter>
{% endhighlight %}

en d'une part :

    The Secret Life of DataData is hostile. The End

et d'autre part :

    1- <Chapter> : position 0
    2- <Title> : position 0
    3- </Title> : position 23
    4- <Para> : position 23
    5- </Para> : position 39
    6- </Chapter> : position 46

Voilà, vous ne révez pas : ce brevet, qui coûte 290M$ à Microsoft et qui lui
vaut l'interdiction de vendre Word aux USA, protège la séparation d'un document
en deux documents…


## Une absurdité technique

Personne n'en doute après les débats sur Hadopi, ceux qui votent les lois ne
sont pas des informaticiens… et ceux qui les écrivent sont en partie des lobbies
influents. Pas étonnant avec ces protagonistes d'arriver à des lois absurdes…

Il faut savoir qu'un programme est composé de données et de traitements. Pour le
réaliser, un développeur a à sa disposition des structures de données, qui
varient un peu selon les API des langages utilisées, mais on retrouve
globalement toujours les mêmes (tableaux, listes chaînées, tableaux
associatifs…). Quand il veut réaliser une tâche complexe, le programmeur écrit
des données dans une structure, en lit dans une autre, les combine… un simple
jeu de déplacements de données d'une structure à une autre pour faire ce qu'il
veut faire… des procédures qui sont bien connues et même détaillées par
exemple dans [The Art of Computer Programming][taocp].

[taocp]: http://fr.wikipedia.org/wiki/The_Art_of_Computer_Programming

Un peu comme en mathématiques, on a à notre disposition des chiffres, des
lettres (pour les variables par exemple), des fonctions (`+`, `-`, `×`, `÷`,
`²`, `√`, `sin`, `cos`, `=`, `≠`, `<`, `>`…), on les combine pour obtenir des
équations :

  * 2 + 3 = 5
  * x² = 4

Breveter le fait spécifique de séparer les données de manière à avoir les
balises d'un côté et le contenu de l'autre, c'est un peu comme si en
mathématiques on brevetait le fait de combiner la somme des carrés de deux
nombres avec la fonction racine carrée `√(a²+b²)`, parce qu'après tout M.
Pythagore il aimerait bien en tirer profit de sa découverte, c'est normal, non?

Donc à partir de maintenant, si vous voulez connaître l'hypothénuse d'un
triangle rectangle à partir de ses deux autres côtés, et bien il va falloir
payer, sinon c'est le procès. Vous avez bien compris, mesdames et messieurs les
architectes ?

Bon, finalement, ce n'est pas si grave, `√(a²+b²)` est breveté, mais
`√((a+b)²-2ab)` ne l'est pas, youhou \o/

Eh oui, parce qu'un algorithme breveté c'est bien gentil, mais on peut arriver
au même résultat par un algorithme différent. Par exemple, dans l'algorithme
décrit précédemment, pour séparer les balises et le contenu, je fais un premier
passage pour trouver toutes les balises, et un second passage pour trouver leur
position, et je n'enfreins plus le brevet. On pourrait me rétorquer qu'un brevet
protège des algorithmes équivalents, auquel cas je demanderais la définition
précise de l'équivalence entre deux algorithmes (avec comme exemple l'algorithme
de compression JPEG)…

Et si un brevet protègeait effectivement des « algorithmes équivalents » (si un
juriste lit ce billet…), il s'agirait donc d'**un brevet sur une idée** (ici la
séparation des balises et du contenu d'un XML en deux documents séparés).

Pour prendre une autre analogie plus simple, on est dans le même cas qu'une
partition de musique : un musicien sait jouer des notes, un compositeur peut
agencer les notes dans l'ordre qu'il veut pour faire une mélodie. Mais voilà
qu'un compositeur a trouvé que la suite de note do-mi-sol c'était très bien, il
dépose donc un brevet dessus. Maintenant, tous ceux qui veulent utiliser ces 3
notes à la suite devront payer ou subir un procès… Les brevets logiciels sont un
cas tout aussi stupide.


## Les brevets, à quoi bon?

Mettons de côté l'absurdité technique, car après tout, « _la technique, on s'en
fiche_[^1] ».

[^1]: Remarque brevetée par plusieurs députés UMP qui ont voté pour Hadopi.

Un brevet, ça sert à protéger une **invention**. Pourquoi? **Pour favoriser
l'innovation**.

Effectivement, sans brevets, comment une entreprise pourrait investir des
millions d'euros pour trouver quelque chose (une nouvelle molécule pour
fabriquer un médicament par exemple) si aussitôt découvert, tous les concurrents
pouvaient en faire commerce ? Il est donc important de protéger l'innovation,
pour une durée limitée (généralement 20 ans[^2]).

[^2]: Leurs lobbies sont moins performants que ceux de l'industrie du disque,
qui eux ont réussi à obtenir 70 ans de protection des droits patrimoniaux…

Les brevets ont donc une raison d'être.

Pour être brevetable, une invention doit cependant répondre à trois critères
essentiels (cf [wikipedia][]) :

[wikipedia]: http://fr.wikipedia.org/wiki/Brevet

  1. Elle doit être nouvelle, c'est-à-dire que rien d'identique n'a jamais été
accessible à la connaissance du public, par quelque moyen que ce soit (écrit,
oral, utilisation, …), où que ce soit, quand que ce soit.

  2. Sa conception doit être inventive, c'est-à-dire qu'elle ne peut pas
découler de manière évidente de l'état de la technique, pour une personne
connaissant le domaine technique concerné.

  3. Elle doit être susceptible d'une application industrielle, c'est-à-dire
qu'elle peut être utilisée ou fabriquée de manière industrielle (ce qui exclut
les œuvres d'art ou d'artisanat, par exemple).


C'est d'ailleurs sur ce troisième point que l'Europe refuse les brevets
logiciels[^3], car elle considère que les logiciels sont des « créations de
l'esprit ».

[^3]: Des lobbies rôdent et la [bataille][] n'est pas terminée.
[bataille]: http://www.april.org/groupes/brevets

Pour les deux premiers points, des techniques nouvelles et inventives, lorsqu'il
s'agit de traiter et de stocker des données sur une certaine forme, on peut en
trouver une infinité… Il suffit de les breveter et faire des procès à ceux qui
par inadvertance utilisent la même procédure. Et les brevets, ça rapporte !

Certaines sociétés l'ont bien compris, et se constituent des portefeuilles de
brevets, leur seule raison d'exister étant de permettre de faire des procès.

## NON aux brevets logiciels !

{: .center}
[![brevets_logiciels]({{ site.assets }}/brevets/brevets_logiciels.png)](http://www.april.org)

Le logiciel est protégé par la loi sur les droits d'auteur. La loi sur les
brevets, par contre, a peu à voir avec la protection. Au contraire, le droit des
brevets met grandement en danger les vrais innovateurs parce que leurs créations
indépendantes pourraient être attaquées par des racketteurs et des concurrents
malveillants.

[NON aux brevets logiciels!](http://www.nosoftwarepatents.com/fr)
