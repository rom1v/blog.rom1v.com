---
layout: post
title: Des slides Beamer en Markdown
date: 2014-02-15 18:29:14+01:00
---

Pour produire des slides propres pour une présentation, j'aime beaucoup
[Beamer][] (basé sur [LaTeX][]). Mais la syntaxe est un peu lourde et la
configuration est parfois inutilement compliquée (fonts, encodage, compilation
multipasse…).

[beamer]: https://fr.wikipedia.org/wiki/Beamer
[latex]: https://fr.wikipedia.org/wiki/LaTeX

Est-il possible d'avoir les avantages de _Beamer_ sans ses inconvénients ? La
réponse est _oui_, grâce à [pandoc][] et son [Markdown étendu][pandoc-markdown].

[pandoc]: http://pandoc.org
[pandoc-markdown]: http://pandoc.org/MANUAL.html#pandocs-markdown


## Beamer

Voici le code d'un exemple très simple de présentation _Beamer_ :

{% highlight latex %}
\documentclass[hyperref={pdfpagelabels=false}]{beamer}
\usepackage[utf8]{inputenc}
\usepackage{lmodern}

\title{Exemple}
\author{Romain Vimont}
\date{15 février 2014}

\begin{document}

\begin{frame}
\titlepage
\end{frame}

\section{Ma section}

\begin{frame}{Ma première frame}
\begin{itemize}
 \item c'est bien
 \item mais c'est verbeux
\end{itemize}
\end{frame}

\end{document}
{% endhighlight %}


Le code source est, il faut bien l'avouer, assez rebutant, et le rapport
signal/bruit assez faible.

Une fois les paquets `pdflatex`, `textlive-latex-base` et `latex-beamer`
installés (sous _Debian_), vous pouvez le compiler avec :

    pdflatex fichier.tex


## Markdown

Voici maintenant l'équivalent en _Pandoc-Markdown_ :

    % Exemple
    % Romain Vimont
    % 15 février 2014

    # Ma section

    ## Ma première frame

     - c'est bien
     - et en plus ce n'est pas verbeux

Indiscutablement, c'est beaucoup plus lisible !

Avec le paquet `pandoc` (en plus des paquets latex déjà installés), vous pouvez
le compiler avec :

    pandoc -st beamer fichier.md -o fichier.pdf

_Notez que le résultat n'est pas strictement identique, la version compilée avec
`pandoc` ajoute une frame de section, mais il ne s'agit que d'une différence de
template par défaut._


## Démo

J'ai créé une présentation d'exemple avec un thème personnalisé.

{: .center}
![beamer]({{ site.assets }}/beamer/beamer.png)

Le résultat est disponible [ici][slides.pdf], mais c'est surtout la
[source][slides.md.html] ([raw][slides.md]) qui est intéressante. Pour récupérer
le projet et générer le pdf :

[slides.pdf]: http://dl.rom1v.com/mdbeamer/slides.pdf
[slides.md.html]: http://dl.rom1v.com/mdbeamer/slides.md.html
[slides.md]: http://dl.rom1v.com/mdbeamer/slides.md

{% highlight bash %}
git clone http://git.rom1v.com/mdbeamer.git
cd mdbeamer
make
{% endhighlight %}

Il est également disponible sur [github](https://github.com/rom1v/mdbeamer).

Ce projet a vocation à être utilisé comme base pour mes futures présentations
(et les vôtres si vous le désirez). Chacune d'entre elles sera sur une
[branche][] _git_ et sur un [remote][] différents.

[branche]: http://gitref.org/branching/
[remote]: http://gitref.org/remotes/


## Injection de version

Pour pouvoir distinguer rapidement différentes versions d'une même présentation,
j'ai également ajouté au _Makefile_ une commande pour injecter un identifiant de
version à côté de la date (donc à la fin de la 3e ligne du code source). Il
s'agit du résultat de `git describe` (contenant le nom du dernier [tag
annoté][]) ou à défaut simplement le numéro de commit actuel.

[tag annoté]: http://git-scm.com/book/ch2-6.html#Annotated-Tags

Pour l'utiliser :

    make withversion


## Un format pivot

J'utilise ici le _Pandoc-Markdown_ pour écrire du _Beamer_ plus simplement.

Mais son intérêt est beaucoup plus général : il s'agit d'un véritable **format
pivot**, compilable vers de nombreux formats.

Pour de la documentation par exemple, il suffit de l'écrire en _Pandoc-Markdown_
et de la compiler, grâce à `pandoc`, en :

  * [html](https://fr.wikipedia.org/wiki/HTML5)
  * [tex](https://fr.wikipedia.org/wiki/LaTeX)
  * [pdf](https://fr.wikipedia.org/wiki/Portable_Document_Format)
  * [mediawiki](https://fr.wikipedia.org/wiki/MediaWiki)
  * [docbook](https://fr.wikipedia.org/wiki/DocBook)
  * [epub](https://fr.wikipedia.org/wiki/EPUB_%28format%29)
  * [odt](https://fr.wikipedia.org/wiki/OpenDocument)
  * [docx](https://fr.wikipedia.org/wiki/Docx)
  * …

C'est d'ailleurs très pratique quand quelqu'un vous demande une documentation
dans un format totalement inadapté (type `docx`), à rédiger de manière
collaborative : il suffit alors d'utiliser _Pandoc-Markdown_, _git_ et un
_Makefile_.

Pour les slides, _pandoc_ supporte, en plus de _Beamer_, la compilation vers des
slides HTML :

  * [slidy](http://www.w3.org/Talks/Tools/)
  * [slideous](http://goessner.net/articles/slideous/)
  * [dzslides](http://paulrouget.com/dzslides/)
  * [revealjs](http://lab.hakim.se/reveal-js/#/)
  * [s5](http://meyerweb.com/eric/tools/s5/)

Cette généricité a bien sûr des limites : l'utilisation de code spécifique à un
format particulier (tel que j'en utilise dans mon exemple) empêche de le
compiler correctement vers d'autres formats.


## Conclusion

Le language _Markdown_ (étendu par _pandoc_) permet de combiner la généricité,
la simplicité et la _git_abilité pour écrire des documents ou des slides, ce qui
en fait un outil absolument indispensable.
