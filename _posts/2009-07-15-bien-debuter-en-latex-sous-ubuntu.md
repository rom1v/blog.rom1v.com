---
layout: post
title: Bien débuter en LaTeX sous Ubuntu
date: 2009-07-15 22:35:38+02:00
---

Je ne vais pas présenter **LaTeX**. Si vous ne connaissez pas, je vous renvoie
sur [la page LaTeX de Wikipedia][wikipedia].

[wikipedia]: http://fr.wikipedia.org/wiki/LaTeX

{: .center}
![LaTeX]({{ site.assets }}/latex/latex.png)

Je vais plutôt présenter succintement l'installation de LaTeX et d'un éditeur
pour Ubuntu, puis je vais lister les quelques points basiques qui peuvent poser
problème et qui empoisonnent la vie quand on n'a pas de solutions :

  * les polices pixellisées ;
  * les problèmes d'encodage ;
  * les problèmes de caractères spéciaux dans les méta-données ;
  * d'autres problèmes avec des caractères accentués ;
  * la modification des marges du document…


## Installation de base

Il faut tout d'abord installer le minimum : `texlive`.

Pour pouvoir gérer correctement le français, `texlive-lang-french` est
nécessaire.

Il est conseillé d'installer également `texlive-latex-extra`, qui contient pas
mal de greffons bien utiles.

Enfin, pour avoir des polices vectorielles (et non bitmap, qui sont pixellisées
lors d'un zoom), il faut le paquet `lmodern`.

    sudo apt-get install texlive texlive-lang-french texlive-latex-extra lmodern


## Greffon pour gedit

Depuis _Jaunty_, le greffon LaTeX pour **gedit** est packagé dans les dépôts par
défaut : `gedit-latex-plugin`.

Une fois installé, **gedit** s'enrichit d'une barre d'outil (lorsqu'un document
`.tex` est ouvert) et d'un panneau inférieur _(Ctrl+F9 pour l'activer et le
désactiver)_ :

{: .center}
![gedit-latex]({{ site.assets }}/latex/gedit-latex.png)

Pour compiler le document, rien de plus simple, tout est dans le menu _Outils_ :

{: .center}
![gedit-latex-menu]({{ site.assets }}/latex/gedit-latex-menu.png)



## Configuration de l'en-tête

### Encodage des caractères

Par défaut, si aucune raison particulière ne préconise le contraire, tout texte
devrait être encodé en [UTF-8][] : ça tombe bien, c'est l'encodage par défaut
d'Ubuntu.

[UTF-8]: http://fr.wikipedia.org/wiki/UTF-8

_Si un jour vous rencontrez un problème d'encodage dans n'importe quel domaine,
et que sur un forum quelqu'un vous indique que pour le résoudre, il faut changer
l'encodage en latin1 (iso-8859-1), ne suivez pas son conseil, ça n'est pas une
bonne solution (à part pour des problèmes de compatibilité avec un existant très
vétuste)._

Il faut indiquer au compilateur LaTeX que le document source est encodé en
**UTF-8**. Pour cela, il suffit de rajouter dans l'en-tête la ligne suivante :

{% highlight latex %}
\usepackage[utf8]{inputenc}
{% endhighlight %}


### Accents, bidouille et coupure de mots

Maintenant que l'encodage est correctement reconnu, il reste un petit problème
avec les caractères accentués. Lorsqu'on écrit le caractère `é` par exemple,
le compilateur sait le reconnaître (codage UTF-8), mais l'encodage de la police
par défaut ne permet pas de le dessiner directement : elle ne contient pas ce
caractère. Pour contourner le problème, le compilateur écrit un `e` avec un `'`
au-dessus (`\'e`).

À première vue, ça n'est pas gênant, le rendu est nickel. Sauf que cela pose
deux problèmes :

  * la recherche d'un mot qui contient un caractère accentué ne fonctionne pas
    dans n'importe quelle visionneuse PDF (normal, le caractère accentué n'est
    pas réellement écrit dans le fichier) ;
  * l'algorithme de coupure de mots s'emmêle les pinceaux sur les mots qui
    contiennent des caractères accentués.

Pour éviter le problème, il faut rajouter dans l'en-tête :

{% highlight latex %}
\usepackage[T1]{fontenc}
{% endhighlight %}


### Méta-données

Le package `hyperref` est quasiment incontournable pour générer des PDF, il
permet de personnaliser pas mal de choses, et surtout de faire des liens
cliquables (à l'intérieur du document ou vers une url externe)… Pour l'utiliser,
il suffit de rajouter le package dans l'en-tête, auquel on peut spécifier des
options :

{% highlight latex %}
\usepackage[bookmarks=false,colorlinks,linkcolor=blue]{hyperref}
{% endhighlight %}


_Ici, par exemple, j'ai précisé que je ne voulais pas générer l'index du
document (qui s'affiche par défaut dans certaines visionneuses de PDF, notamment
le logiciel privateur Adobe Reader), que je voulais colorer les liens plutôt que
de les encadrer (ce que je trouve particulièrement moche) et que je les voulais
en bleu._

Ce package permet également de renseigner les propriétés du documents (les
méta-données), ce qui est bien utile pour le référencement.

On trouve souvent la méthode qui consiste à ajouter les propriétés du document
directement en option d'`hyperref` :

{% highlight latex %}
\usepackage[pdfauthor={Romain Vimont},pdftitle={Démo LaTeX}]{hyperref}
{% endhighlight %}

Mais elle ne supporte pas tous les caractères, par exemple :

{% highlight latex %}
\usepackage[pdfauthor={Romain Vimont (®om)},pdftitle={Démo LaTeX}]{hyperref}
{% endhighlight %}

Une bonne pratique est donc de les écrire séparément (et là ça fonctionne) :

{% highlight latex %}
\hypersetup{
  pdftitle={Démo LaTeX},
  pdfsubject={Modèle de document LaTeX},
  pdfkeywords={LaTeX, modèle},
  pdfauthor={Romain Vimont (®om)}
}
{% endhighlight %}

La liste complète des propriétés est disponible [ici][hyperlinks].

[hyperlinks]: http://en.wikibooks.org/wiki/LaTeX/Hyperlinks#Customization

{: .center}
![latex-properties]({{ site.assets }}/latex/latex-properties.png)


### Marges

Les marges par défaut des documents générés sont énormes. Les étudiants en sont
très contents quand ils doivent écrire un rapport de stage de 40 pages dont ils
viennent difficilement à bout, mais dans beaucoup d'autres cas, c'est une perte
de place. Même s'[il y a une raison à cela][marges], on peut vouloir les
diminuer.

[marges]: http://fr.wikibooks.org/wiki/Programmation_LaTeX/Mise_en_page#Modification_des_marges

Le package `geometry` rend cette opération très simple :

{% highlight latex %}
\usepackage[top=1.5cm,bottom=1.5cm,left=1.5cm,right=1.5cm]{geometry}
{% endhighlight %}


### Quelques réglages PDF

Il est possible de définir la version de PDF à utiliser _(j'en ai eu besoin par
exemple pour intégrer correctement des images png transparentes, qui ne
fonctionnait pas avec PDF inférieur à 1.6)_ et le niveau de compression,
permettant de gagner quelques kilo-octets sur le fichier final.

{% highlight latex %}
\pdfminorversion 7
\pdfobjcompresslevel 3
{% endhighlight %}


## Conclusion

Voici donc un modèle de document prêt à être compilé :

{% highlight latex %}
\pdfminorversion 7
\pdfobjcompresslevel 3

\documentclass[a4paper]{article}

\usepackage[utf8]{inputenc}
\usepackage[T1]{fontenc}
\usepackage[francais]{babel}
\usepackage[bookmarks=false,colorlinks,linkcolor=blue]{hyperref}
\usepackage[top=1.5cm,bottom=1.5cm,left=1.5cm,right=1.5cm]{geometry}

\hypersetup{
  pdftitle={Démo LaTeX},
  pdfsubject={Modèle de document LaTeX},
  pdfkeywords={LaTeX, modèle},
  pdfauthor={Romain Vimont (®om)}
}

\begin{document}

\section{Première section}

\subsection{Une sous-section}

Du texte\dots

\subsection{Une autre sous-section}

\section{Une autre section}

\end{document}
{% endhighlight %}
