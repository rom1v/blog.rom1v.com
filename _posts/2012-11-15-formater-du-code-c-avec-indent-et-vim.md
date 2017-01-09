---
layout: post
title: Formater du code C avec indent et Vim
date: 2012-11-15 12:23:54+01:00
tags:
- planet-libre
- puf
---

Pour suivre des règles de codage et s'y tenir, rien de tel qu'un outil qui
formate automatiquement le code (c'est plus rapide et sans erreurs). Sous
[Eclipse][] par exemple, la combinaison de touches _Ctrl+Shift+F_ est
indispensable. Mon but est d'obtenir la même fonctionnalité sous [Vim][] pour le
[langage C][].

[eclipse]: http://fr.wikipedia.org/wiki/Eclipse_%28logiciel%29
[vim]: http://fr.wikipedia.org/wiki/Vim
[langage c]: http://fr.wikipedia.org/wiki/C_%28langage%29


## Indent

L'outil [`indent`][indent] permet de formater un source C selon des règles
définies par des paramètres en ligne de commande. Ces options sont [très
nombreuses][options]. Heureusement, il y a quelques styles bien connus
prédéfinis, comme le [style K&R][] (option `-kr`).

[indent]: http://fr.wikipedia.org/wiki/Indent
[options]: http://www.gnu.org/software/indent/manual/
[style k&r]: http://en.wikipedia.org/wiki/1_true_brace_style#K.26R_style

Pour illustrer son fonctionnement, voici un code source écrit n'importe comment
(et qui fait n'importe quoi) :

{% highlight c %}
#include <stdio.h>

void f (  int* x  )
  {
 *x=4;}

void g(int x)
{goto mylabel;
        /* my comment */
    if(x>10)x=10;
       mylabel:
    printf ("%d\n",x *2);
switch(x){case 1:x=4;break;case 2:x=1;}
    while   ( * ( &x ) <10)x++; /* what? */
}

void h(char ( * ( * x ( ) ) [ ] ) ( ) ) {
char ( * ( * y ) [ ] ) ( ) = \
x ( ) ; char ( * z ) ( ) = * \
( * y ) ; char c = z ( ); putchar \
(c);
}

int main(){
int i=  2;  f(&i);
  g(i);

        return    0;
}
{% endhighlight %}

Pour formater :

    indent -st -kr -ts4 file.c

  * `-st` affiche le résultat sur la sortie standard au lieu de modifier le
    fichier ;
  * `-kr` utilise le style K&R ;
  * `-ts4` considère 4 espaces comme une tabulation.

Voici le résultat :

{% highlight bash %}
#include <stdio.h>

void f(int *x)
{
    *x = 4;
}

void g(int x)
{
    goto mylabel;
    /* my comment */
    if (x > 10)
        x = 10;
  mylabel:
    printf("%d\n", x * 2);
    switch (x) {
    case 1:
        x = 4;
        break;
    case 2:
        x = 1;
    }
    while (*(&x) < 10)
        x++;                    /* what? */
}

void h(char (*(*x())[])())
{
    char (*(*y)[]) () = x();
    char (*z) () = *(*y);
    char c = z();
    putchar(c);
}

int main()
{
    int i = 2;
    f(&i);
    g(i);

    return 0;
}
{% endhighlight %}

C'est plus joli, non ?


## vim

Pour pouvoir reformater directement dans _Vim_, il suffit d'ajouter dans
`~/.vimrc` la ligne suivante :

{% highlight vim %}
autocmd BufNewFile,BufRead *.c set formatprg=indent\ -kr\ -ts4
{% endhighlight %}

Ensuite, la commande `gq` formate (`u` annule).

Par exemple, sur le fichier source mal formaté ci-dessus :

  * placer le curseur sur la ligne 7 ;
  * appuyer sur `V` ;
  * descendre avec `j` (ou la flèche du bas) jusqu'à la ligne 15 ;
  * taper `gq`.

Ainsi, seule la fonction `g` est formatée.

À partir de la ligne 7, le même résultat est obtenu en tapant directement `gq8j`
(descendre de 8 lignes) ou `gq15G` (jusqu'à la ligne 15).

Pour reformater un bloc, le plus simple est de se placer sur une accolade `{` ou
`}` et de taper `gq%` (`%` navigue entre les `{}`, `()` et `[]` ouvrant et
fermant).

Pour reformater tout le fichier, il faut taper `gggqG` :

  * `gg` amène le curseur au début du fichier ;
  * `gq` formate jusqu'à... ;
  * `G` va à la fin du fichier.

`:wq`
