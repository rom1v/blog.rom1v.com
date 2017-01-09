---
layout: post
title: Programmes auto-reproducteurs (quines)
date: 2011-11-14 21:14:17+01:00
tags:
- planet-libre
---

{: .center}
![quine]({{ site.assets }}/quines/quine.jpg)

Vous êtes-vous déjà demandé comment écrire **un programme qui génère son propre
code source** ? Si vous n'avez jamais essayé, je vous conseille de prendre un
peu de temps pour y réfléchir avant de lire la suite. Ce n'est pas évident, car
chaque caractère ajouté dans le code source doit également apparaître sur la
sortie…

Un tel programme s'appelle un [quine][]. Et il est [prouvé][théorème] qu'avec
tout langage de programmation il existe un _quine_ (une infinité ?). [Cette
page][théorie] détaille un peu plus la théorie.

[quine]: http://fr.wikipedia.org/wiki/Quine_%28informatique%29
[théorème]: http://fr.wikipedia.org/wiki/Théorème_de_récursion_de_Kleene
[théorie]: http://www.madore.org/~david/computers/quine.html

Des exemples sont déjà écrits [pour plein de langages][quines].

[quines]: http://www.nyx.net/~gthompso/quine.htm


## Quine simple

Voici un programme auto-reproducteur simple en _C_ :

{% highlight c %}
#include<stdio.h>
main(){char*s="#include<stdio.h>%cmain(){char*s=%c%s%c;printf(s,10,34,s,34,10);}%c";printf(s,10,34,s,34,10);}
{% endhighlight %}

Nous pouvons tester, ce programme se génère bien lui-même :

    $ gcc quine.c && ./a.out | tee quine.c
    #include<stdio.h>
    main(){char*s="#include<stdio.h>%cmain(){char*s=%c%s%c;printf(s,10,34,s,34,10);}%c";printf(s,10,34,s,34,10);}
    $ gcc quine.c && ./a.out | tee quine.c
    #include<stdio.h>
    main(){char*s="#include<stdio.h>%cmain(){char*s=%c%s%c;printf(s,10,34,s,34,10);}%c";printf(s,10,34,s,34,10);}
    $ gcc quine.c && ./a.out | tee quine.c
    #include<stdio.h>
    main(){char*s="#include<stdio.h>%cmain(){char*s=%c%s%c;printf(s,10,34,s,34,10);}%c";printf(s,10,34,s,34,10);}

_(essayez de l'écrire ou de le réécrire tout seul pour bien comprendre comment ça fonctionne)_

L'essentiel de l'astuce ici est de passer la chaîne `s` à la fois en tant que
format et d'argument de `printf`.

Ce n'est pas sans poser de problèmes : dans la déclaration de la chaîne `s`,
nous ne pouvons pas écrire `"` (évidemment), ni `\"`, car alors il faudrait que
le programme génère le `\"`, donc le `"`, ce que précisément nous cherchons à
faire. L'astuce est donc d'utiliser son code [ASCII][] (`34`), inséré grâce aux
`%c`. Le code `10`, quant à lui, correspond au [saut de ligne][].

[ascii]: http://fr.wikipedia.org/wiki/American_Standard_Code_for_Information_Interchange
[saut de ligne]: http://fr.wikipedia.org/wiki/Saut_de_ligne


## Quine alternatif

Nous pouvons imaginer deux programmes qui se génèrent l'un-l'autre : un
programme _A_ génère un code source _B_, tel que le programme _B_ génère le code
source _A_.

À partir de l'exemple précédent, c'est trivial, il suffit de rajouter une
variable (que j'ai appelée « `f` » comme « _flag_ ») dont on alterne la valeur :

{% highlight c %}
#include<stdio.h>
main(){int f=0;char*s="#include<stdio.h>%cmain(){int f=%i;char*s=%c%s%c;printf(s,10,!f,34,s,34,10);}%c";printf(s,10,!f,34,s,34,10);}
{% endhighlight %}

La valeur de `f` alterne entre _0_ et _1_ :

    $ gcc quine.c && ./a.out | tee quine.c
    #include<stdio.h>
    main(){int f=1;char*s="#include<stdio.h>%cmain(){int f=%i;char*s=%c%s%c;printf(s,10,!f,34,s,34,10);}%c";printf(s,10,!f,34,s,34,10);}
    $ gcc quine.c && ./a.out | tee quine.c
    #include<stdio.h>
    main(){int f=0;char*s="#include<stdio.h>%cmain(){int f=%i;char*s=%c%s%c;printf(s,10,!f,34,s,34,10);}%c";printf(s,10,!f,34,s,34,10);}
    $ gcc quine.c && ./a.out | tee quine.c
    #include<stdio.h>
    main(){int f=1;char*s="#include<stdio.h>%cmain(){int f=%i;char*s=%c%s%c;printf(s,10,!f,34,s,34,10);}%c";printf(s,10,!f,34,s,34,10);}


## Quasi-quine

Il est également possible d'écrire un programme qui en génère un autre, qui
lui-même en génère un autre… sans jamais "boucler".  Je me suis amusé à en
écrire deux exemples.


### Fibonacci

Le premier calcule la [suite de Fibonacci][]. Ou plutôt, il contient les deux
premiers éléments de la suite (_F(0)=0_ et _F(1)=1_), génère un programme qui
contiendra _F(1)_ et _F(2)_, qui lui-même générera un programme qui contiendra
_F(2)_ et _F(3)_, etc. :

[suite de Fibonacci]: http://fr.wikipedia.org/wiki/Suite_de_Fibonacci

{% highlight c %}
#include<stdio.h>
main(){int a=0,b=1;char*s="#include<stdio.h>%cmain(){int a=%i,b=%i;char*s=%c%s%c;printf(s,10,b,a+b,34,s,34,10);}%c";printf(s,10,b,a+b,34,s,34,10);}
{% endhighlight %}

Pour obtenir _F(10)_ et _F(11)_ (suivre les valeurs des variables `a` et `b`) :

    $ for i in {1..10}; do gcc quine.c && ./a.out | tee quine.c; done
    #include<stdio.h>
    main(){int a=1,b=1;char*s="#include<stdio.h>%cmain(){int a=%i,b=%i;char*s=%c%s%c;printf(s,10,b,a+b,34,s,34,10);}%c";printf(s,10,b,a+b,34,s,34,10);}
    #include<stdio.h>
    main(){int a=1,b=2;char*s="#include<stdio.h>%cmain(){int a=%i,b=%i;char*s=%c%s%c;printf(s,10,b,a+b,34,s,34,10);}%c";printf(s,10,b,a+b,34,s,34,10);}
    #include<stdio.h>
    main(){int a=2,b=3;char*s="#include<stdio.h>%cmain(){int a=%i,b=%i;char*s=%c%s%c;printf(s,10,b,a+b,34,s,34,10);}%c";printf(s,10,b,a+b,34,s,34,10);}
    #include<stdio.h>
    main(){int a=3,b=5;char*s="#include<stdio.h>%cmain(){int a=%i,b=%i;char*s=%c%s%c;printf(s,10,b,a+b,34,s,34,10);}%c";printf(s,10,b,a+b,34,s,34,10);}
    #include<stdio.h>
    main(){int a=5,b=8;char*s="#include<stdio.h>%cmain(){int a=%i,b=%i;char*s=%c%s%c;printf(s,10,b,a+b,34,s,34,10);}%c";printf(s,10,b,a+b,34,s,34,10);}
    #include<stdio.h>
    main(){int a=8,b=13;char*s="#include<stdio.h>%cmain(){int a=%i,b=%i;char*s=%c%s%c;printf(s,10,b,a+b,34,s,34,10);}%c";printf(s,10,b,a+b,34,s,34,10);}
    #include<stdio.h>
    main(){int a=13,b=21;char*s="#include<stdio.h>%cmain(){int a=%i,b=%i;char*s=%c%s%c;printf(s,10,b,a+b,34,s,34,10);}%c";printf(s,10,b,a+b,34,s,34,10);}
    #include<stdio.h>
    main(){int a=21,b=34;char*s="#include<stdio.h>%cmain(){int a=%i,b=%i;char*s=%c%s%c;printf(s,10,b,a+b,34,s,34,10);}%c";printf(s,10,b,a+b,34,s,34,10);}
    #include<stdio.h>
    main(){int a=34,b=55;char*s="#include<stdio.h>%cmain(){int a=%i,b=%i;char*s=%c%s%c;printf(s,10,b,a+b,34,s,34,10);}%c";printf(s,10,b,a+b,34,s,34,10);}
    #include<stdio.h>
    main(){int a=55,b=89;char*s="#include<stdio.h>%cmain(){int a=%i,b=%i;char*s=%c%s%c;printf(s,10,b,a+b,34,s,34,10);}%c";printf(s,10,b,a+b,34,s,34,10);}

Ce que je trouve fabuleux, c'est que chaque programme, qui connaît deux valeurs
de la suite, sait non seulement générer un nouveau programme qui calculera la
valeur suivante (ça, c'est facile), mais en plus, ce nouveau programme saura
lui-même générer un autre programme qui calculera la prochaine, etc. **Chaque
programme transmet en quelque sorte son _code génétique_ à sa descendance.**


### PGCD

Suivant le même principe, il est possible de calculer le [PGCD][] de deux
nombres. Nous pouvons générer une lignée de programmes qui calculeront chacun
une étape de l'[algorithme d'Euclide][].

[pgcd]: http://fr.wikipedia.org/wiki/Plus_grand_commun_diviseur
[algorithme d'Euclide]: http://fr.wikipedia.org/wiki/Algorithme_d%27Euclide

Cet exemple permet de calculer _PGCD(64,36)_ :

{% highlight c %}
#include<stdio.h>
main(){int a=64,b=36;char*s="#include<stdio.h>%cmain(){int a=%i,b=%i;char*s=%c%s%c;printf(s,10,b==0?a:b,b==0?0:a%%b,34,s,34,10);}%c";printf(s,10,b==0?a:b,b==0?0:a%b,34,s,34,10);}
{% endhighlight %}

Le caractère `%` ayant une signification dans le formatage de `printf`, nous
devons le doubler.

_Nous aurions pu utiliser à la place `a-a/b*b` (ce qui revient à peu près au
même, si `a` et `b` sont positifs avec `a>b`)._

Voici le résultat (suivre les valeurs des variables `a` et `b`) :

    $ for i in {1..5}; do gcc quine.c && ./a.out | tee quine.c; done
    #include<stdio.h>
    main(){int a=36,b=28;char*s="#include<stdio.h>%cmain(){int a=%i,b=%i;char*s=%c%s%c;printf(s,10,b==0?a:b,b==0?0:a%%b,34,s,34,10);}%c";printf(s,10,b==0?a:b,b==0?0:a%b,34,s,34,10);}
    #include<stdio.h>
    main(){int a=28,b=8;char*s="#include<stdio.h>%cmain(){int a=%i,b=%i;char*s=%c%s%c;printf(s,10,b==0?a:b,b==0?0:a%%b,34,s,34,10);}%c";printf(s,10,b==0?a:b,b==0?0:a%b,34,s,34,10);}
    #include<stdio.h>
    main(){int a=8,b=4;char*s="#include<stdio.h>%cmain(){int a=%i,b=%i;char*s=%c%s%c;printf(s,10,b==0?a:b,b==0?0:a%%b,34,s,34,10);}%c";printf(s,10,b==0?a:b,b==0?0:a%b,34,s,34,10);}
    #include<stdio.h>
    main(){int a=4,b=0;char*s="#include<stdio.h>%cmain(){int a=%i,b=%i;char*s=%c%s%c;printf(s,10,b==0?a:b,b==0?0:a%%b,34,s,34,10);}%c";printf(s,10,b==0?a:b,b==0?0:a%b,34,s,34,10);}
    #include<stdio.h>
    main(){int a=4,b=0;char*s="#include<stdio.h>%cmain(){int a=%i,b=%i;char*s=%c%s%c;printf(s,10,b==0?a:b,b==0?0:a%%b,34,s,34,10);}%c";printf(s,10,b==0?a:b,b==0?0:a%b,34,s,34,10);}

Une fois la solution trouvée (lorsque `b` vaut _0_), le programme se comporte
comme un vrai _quine_ (il se regénère à l'identique).


## Compilateur magique

Passons maintenant à l'étape suivante. Jusqu'à maintenant, nous avons généré un
programme qui génère un autre programme… Très bien. Mais ces programmes générés
(en fait, leur code source), nous les compilons avec un [compilateur][] (`gcc`).

[compilateur]: http://fr.wikipedia.org/wiki/Compilateur

Mais un compilateur, c'est un programme, qui en plus est [écrit dans le langage
qu'il doit compiler][inception]. En particulier, le compilateur _C_ est écrit en
_C_.

[inception]: https://fr.wikipedia.org/wiki/Compilateur#Probl.C3.A8me_d.27amor.C3.A7age_.28bootstrap.29

À partir là, il est possible de faire des choses très intéressantes, comme l'a
expliqué en 1984 [Ken Thompson][] dans son célèbre article _[Reflections on
Trusting Trust][]_ (que je vais paraphraser).

[Ken Thompson]: http://fr.wikipedia.org/wiki/Ken_Thompson
[Reflections on Trusting Trust]: http://cm.bell-labs.com/who/ken/trust.html

Le code suivant est une idéalisation du code du compilateur C qui interprète le
[caractère d'échappement][escape] :

[escape]: http://fr.wikipedia.org/wiki/Caract%C3%A8re_d%27%C3%A9chappement

{% highlight c %}
= next();
if (c != '\\')
    return c;
c = next();
if (c == '\\')
    return '\\';
if (c == 'n')
    return '\n';
{% endhighlight %}

C'est "magique" : le programme _sait_, de manière totalement portable, quel
caractère est compilé pour un saut de ligne pour n'importe quel jeu de
caractères. Le fait qu'il le sache lui permet de se recompiler lui-même, en
perpétuant ainsi cette connaissance.

Supposons que nous voulions rajouter le caractère spécial « `\v` » pour écrire
une "tabulation verticale" :

{% highlight c %}
c = next();
if (c != '\\')
    return c;
c = next();
if (c == '\\')
    return '\\';
if (c == 'n')
    return '\n';
if (c == 'v')
    return '\v';
{% endhighlight %}

Évidemment, si le compilateur ne connaît pas `\v`, ce code ne compile pas.
Mais il suffit de lui apprendre : le code [ASCII][ascii] de la tabulation
verticale est `11`. Nous pouvons donc modifier temporairement le compilateur,
pour en générer une nouvelle version :

{% highlight c %}
c = next();
if (c != '\\')
    return c;
c = next();
if (c == '\\')
    return '\\';
if (c == 'n')
    return '\n';
if (c == 'v')
    return 11;
{% endhighlight %}

La nouvelle version du compilateur accepte maintenant « `\v` », et peut donc
compiler son propre code source contenant ce caractère. Le code source contenant
le `11` peut donc être totalement supprimé et oublié.

_C'est un concept profond._ Il suffit de lui dire une fois, l'auto-référencement
fait le reste.


### Cheval de Troie (quasi-)indétectable

Considérons alors la fonction `compile(s)` permettant de compiler une ligne de
code source. Une simple modification va délibérément mal compiler la source
lorsqu'un motif est détecté :

{% highlight c %}
void compile(char *s) {
    if (match(s, "pattern")) {
        compile("bug");
        return;
    }
    // …
}
{% endhighlight %}

Supposons que le motif permette d'identifier la commande `login`, et que le
_bug_ ([cheval de Troie][]) compilé permette d'accepter, en plus du mot de passe
correct, un mot de passe prédéfini quelconque : nous pourrions, en connaissant
ce "faux" mot de passe, nous connecter sur n'importe quelle machine possédant le
programme `login` compilé avec ce compilateur.

[cheval de Troie]: http://fr.wikipedia.org/wiki/Cheval_de_Troie_%28informatique%29

Mais évidemment, un tel bout de code dans le _compilateur C_ ne passerait pas
inaperçu et serait détecté très rapidement… Sauf avec l'ultime étape :

{% highlight c %}
void compile(char * s) {
    if (match(s, "pattern1")) {
        compile("bug1");
        return;
    }
    if (match(s, "pattern2")) {
        compile("bug2");
        return;
    }
    // …
}
{% endhighlight %}

Ici, nous ajoutons un second cheval de Troie. Le premier motif correspond
toujours à la commande `login`, tandis que le second motif correspond au
_compilateur C_. **Le second _bug_ est un programme auto-reproducteur** (tel que
ceux que nous avons vus dans ce billet) qui insère les deux chevaux de Troie. Il
nécessite une phase d'apprentissage comme dans l'exemple avec `\v`. Nous
compilons d'abord la source ainsi modifiée avec le _compilateur C_ normal pour
générer un binaire corrompu, que nous utilisons désormais comme _compilateur C_.
Maintenant, nous pouvons supprimer les _bugs_ de la source, le nouveau
compilateur va les réinsérer à chaque fois qu'il sera compilé.

**Ainsi, en compilant un code source "propre" avec un compilateur dont le code
source est "propre" et que nous avons compilé nous-mêmes, nous générons un
binaire contenant un cheval de Troie !**

_Il est cependant possible de [contrer cette attaque][counter-trusting-trust]._

[counter-trusting-trust]: http://www.dwheeler.com/trusting-trust/
