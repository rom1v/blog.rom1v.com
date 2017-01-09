---
layout: post
title: 'LaTeX : hyperref + numérotation = warning'
date: 2008-09-14 10:11:00+01:00
tags:
- planet-libre
- puf
---

Lorsque l'on veut utiliser [LaTeX][] pour générer un document *pdf*, le package
`hyperref` peut s'avérer bien pratique, car il permet de rendre les liens du
document cliquables. Il modifie également la gestion de la numérotation des
pages, ce qui peut poser problème.

[LaTeX]: http://fr.wikipedia.org/wiki/LaTeX

Tout d'abord, prenons un exemple sans utiliser le package `hyperref`.

{% highlight latex %}    
\documentclass[a4paper]{report}

\usepackage[francais]{babel}
\usepackage[utf8]{inputenc}

\title{Exemple}
\author{®om}

\begin{document}

\maketitle

\begin{abstract}
Un résumé
\end{abstract}

\tableofcontents

\chapter{Un chapitre}
Le premier chapitre.

\end{document}
{% endhighlight %}

C'est un document de 4 pages, qui compile sans problème. Lorsque l'on ouvre le
*pdf* ainsi généré avec *evince* (le lecteur *pdf* par défaut de *Gnome*), les
pages sont numérotées de 1 à 4 dans la partie réservée aux vignettes :

{: .center}
![latex1]({{ site.assets }}/latex_hyperref/latex1.png)

La page de titre et la page du résumé n'étant pas numérotées sur le document,
les numéros imprimées sur les pages ne sont pas les mêmes :

  * la page de la table des matières (numéro 3 dans les vignettes) est en
    réalité la page 1
  * la page du chapitre 1 (numéro 4 dans les vignettes) est en réalité la page
    2.


Cela peut poser des problèmes de compréhension lors du visionnage d'un *pdf* par
l'utilisateur : pour trouver la page 14 (où le numéro 14 est imprimé), il doit
donc chercher la page 16 dans la liste des vignettes.

De plus, sur le document généré, les références ne sont pas cliquables (par
exemple, il est impossible de cliquer sur une ligne du sommaire pour être
aussitôt redirigé vers la page correspondante).

Utilisons alors le package `hyperref`, en rajoutant :

{% highlight latex %}
\usepackage{hyperref}
{% endhighlight %}

Maintenant, les liens sont cliquables :

{: .center}
![latex-tableofcontents]({{ site.assets }}/latex_hyperref/latex-tableofcontents.png)

Le problème de décalage de numéros est également résolu :

{: .center}
![latex2]({{ site.assets }}/latex_hyperref/latex2.png)

Cependant, il y a maintenant plusieurs pages numérotées `1`, ce qui, en plus
d'être assez perturbant et disgrâcieux, provoque un warning à la compilation :

~~~
destination with the same identifier (name{page.1}) hasbeen already used,
duplicate ignored
~~~

Pour éviter ce problème, une astuce consiste à utiliser une numérotation
différente pour la page de titre et pour la vraie numérotation, par exemple une
numérotation par lettres (de toute façon elles ne seront pas affichées). Pour
cela, on peut utiliser `\pagenumbering{alph}` et `\pagenumbering{arabic}` (les
options disponibles sont présentées [ici][latex-numbering].

[latex-numbering]: http://www.image.ufl.edu/help/latex/intext.shtml

{% highlight latex %}
\documentclass[a4paper]{report}

\usepackage[francais]{babel}
\usepackage[utf8]{inputenc}
\usepackage{hyperref}

\title{Exemple}
\author{®om}

\begin{document}

\pagenumbering{alph}
\maketitle

\begin{abstract}
Un résumé
\end{abstract}

\tableofcontents

\pagenumbering{arabic}
\chapter{Un chapitre}
Le premier chapitre.

\end{document}
{% endhighlight %}


Maintenant, le warning a disparu, et nous avons bien le résultat attendu :

{: .center}
![latex3]({{ site.assets }}/latex_hyperref/latex3.png)

Au passage, les liens par défaut ne sont pas très design (un rectangle rouge
autour des liens). Il est possible de passer des paramètres à `hyperref` pour
changer ce comportement :

{% highlight latex %}
\usepackage[colorlinks,linkcolor=blue]{hyperref}
{% endhighlight %}

Le paramètre `colorlinks` indique de colorer directement le texte d'un lien,
plutôt que de l'encadrer, et `linkcolor` permet de changer la couleur.

Le résultat :

{: .center}
![latex-tableofcontents2]({{ site.assets }}/latex_hyperref/latex-tableofcontents2.png)

Merci à la section 4.4.2 de [ce document][pdfdoc] :)

[pdfdoc]: http://theoval.sys.uea.ac.uk/~nlct/latex/pdfdoc/pdfdoc-a4.pdf
