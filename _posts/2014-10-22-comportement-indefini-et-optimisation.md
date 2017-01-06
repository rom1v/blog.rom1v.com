---
layout: post
title: Comportement indéfini et optimisation
date: 2014-10-22 00:41:59+02:00
---

Dans certains langages (typiquement [C][] et [C++][]), la sémantique de
certaines opérations est [_indéfinie_][undefined behavior]. Cela permet au
compilateur de ne s'intéresser qu'aux cas qui sont définis (et donc de les
optimiser) sans s'occuper des effets produits sur les cas indéfinis.

[c]: https://fr.wikipedia.org/wiki/C_%28langage%29
[c++]: https://fr.wikipedia.org/wiki/C%2B%2B
[undefined behavior]: https://en.wikipedia.org/wiki/Undefined_behavior

C'est un concept très précieux pour améliorer sensiblement les performances.
Mais cela peut avoir des effets surprenants. Si le résultat de votre programme
dépend d'un _comportement indéfini_ (undefined behavior) particulier, alors
votre programme complet n'a pas de sens, et le compilateur a le droit de faire
ce qu'il veut. Et il ne s'en prive pas !


## Programme indéfini

Par exemple, [déréférencer un pointeur NULL][undefined-deref]) est un
_comportement indéfini_. En effet, contrairement à ce que beaucoup pensent,
l'exécution du programme ne va pas forcément provoquer une [erreur de
segmentation][].

[undefined-deref]: https://en.wikipedia.org/wiki/Pointer_%28computer_programming%29#cite_ref-7
[erreur de segmentation]: https://fr.wikipedia.org/wiki/Erreur_de_segmentation

J'ai écrit un petit programme tout simple (`undefined.c`) :

{% highlight c linenos=table %}
#include <stdio.h>
#include <malloc.h>

int main(int argc, char *argv[]) {
    int *i = argc == 1 ? NULL : malloc(sizeof(int));
    *i = 42;
    if (!i)
        return 1;
    printf("pwnd %d\n", *i);
    return 0;
}
{% endhighlight %}

Si `argc` vaut `1` (c'est-à-dire si nous appelons l'exécutable sans passer
d'arguments de la ligne de commande), alors le pointeur `i` est `NULL` (ligne
5).

Cette ligne peut paraître étrange, mais elle permet de faire dépendre `i` d'une
valeur connue uniquement à l'exécution (`argc`), ce qui évite au compilateur de
savoir à l'avance que `i` est `NULL`.

La ligne 6 (`*i = 42`) est donc incorrecte : nous n'avons pas le droit de
déréférencer un pointeur `NULL`. Nous nous attendons donc souvent à une erreur
de segmentation.

Mais suite à ce que je viens de vous dire, admettons que ce ne soit pas le cas,
et que nous arrivions quand même sur la ligne suivante (7). Ici, si `i` est
`NULL`, la fonction se termine (en retournant `1`, ligne 8).

Donc il n'y a donc aucun moyen d'afficher le contenu du `printf` ligne 9.

Et bien… en fait, si !


## Exécution

Essayons (j'utilise `gcc 4.7.2` packagé dans _Debian Wheezy_ en _amd64_, les
résultats peuvent différer avec un autre compilateur ou une autre version de
`gcc`) :

    $ gcc -Wall undefined.c
    $ ./a.out          # argc == 1
    Erreur de segmentation
    $ ./a.out hello    # argc == 2
    pwnd 42

Jusqu'ici, tout va bien. Maintenant, activons des optimisations de compilation :

    $ gcc -Wall -O2 undefined.c
    $ ./a.out          # argc == 1
    pwnd 42

Voilà, nous avons réussi à exécuter le `printf` alors que `argc == 1`.

Que s'est-il passé ?


## Assembleur

Pour le comprendre, il faut regarder le code généré en assembleur, sans et avec
optimisations.


### Sans optimisation

Pour générer le résultat de la compilation sans assembler (et donc obtenir un fichier source assembleur `undefined.s`) :

{% highlight bash %}
gcc -S undefined.c
{% endhighlight %}

J'ai commenté les parties importantes :

{% highlight nasm linenos=table %}
    .file   "undefined.c"
    .section    .rodata
.LC0:
    .string "pwnd %d\n"
    .text
    .globl  main
    .type   main, @function
main:
.LFB0:
    .cfi_startproc
    pushq   %rbp
    .cfi_def_cfa_offset 16
    .cfi_offset 6, -16
    movq    %rsp, %rbp
    .cfi_def_cfa_register 6
    subq    $32, %rsp
    movl    %edi, -20(%rbp)
    movq    %rsi, -32(%rbp)
    cmpl    $1, -20(%rbp)     ; if (argc == 1)
    je  .L2                   ;     goto .L2
    movl    $4, %edi          ; arg0 = 4  // sizeof(int)
    call    malloc            ; tmp = malloc(4)
    jmp .L3                   ; goto .L3
.L2:
    movl    $0, %eax
.L3:
    movq    %rax, -8(%rbp)    ; i = tmp
    movq    -8(%rbp), %rax
    movl    $42, (%rax)       ; *i = 42
    cmpq    $0, -8(%rbp)      ; if (!i)
    jne .L4                   ;    goto .L4
    movl    $1, %eax          ; ret = 1
    jmp .L5
.L4:
    movq    -8(%rbp), %rax
    movl    (%rax), %eax
    movl    %eax, %esi        ; arg1 = *i
    movl    $.LC0, %edi       ; arg0 points to "pwnd %d\n"
    movl    $0, %eax
    call    printf            ; printf("pwnd %d\n", *i)
    movl    $0, %eax          ; ret = 0
.L5:
    leave
    .cfi_def_cfa 7, 8
    ret                       ; return ret
    .cfi_endproc
.LFE0:
    .size   main, .-main
    .ident  "GCC: (Debian 4.7.2-5) 4.7.2"
    .section    .note.GNU-stack,"",@progbits
{% endhighlight %}

Le code généré est très fidèle au code source C.


### Avec `gcc -O`

Maintenant, activons certaines optimisations :

{% highlight bash %}
gcc -O -S undefined.c
{% endhighlight %}


Ce qui donne :

{% highlight nasm linenos=table %}
    .file   "undefined.c"
    .section    .rodata.str1.1,"aMS",@progbits,1
.LC0:
    .string "pwnd %d\n"
    .text
    .globl  main
    .type   main, @function
main:
.LFB11:
    .cfi_startproc
    cmpl    $1, %edi          ; if (argc == 1)
    je  .L2                   ;    goto .L2
    subq    $8, %rsp
    .cfi_def_cfa_offset 16
    movl    $4, %edi          ; arg0 = 4  // sizeof(int)
    call    malloc            ; tmp = malloc(4)
    movq    %rax, %rdx        ; i = tmp
    movl    $42, (%rax)       ; *i = 42
    movl    $1, %eax          ; ret = 1
    testq   %rdx, %rdx        ; if (!i)
    je  .L5                   ;    goto .L5
    movl    $42, %esi         ; arg1 = 42
    movl    $.LC0, %edi       ; arg0 points to "pwnd %d\n"
    movl    $0, %eax
    call    printf            ; printf("pwnd %d\n", 42)
    movl    $0, %eax          ; ret = 0
    jmp .L5                   ; goto .L5
.L2:
    .cfi_def_cfa_offset 8
    movl    $42, 0            ; segfault (dereference addr 0)
    movl    $1, %eax          ; ret = 1
    ret
.L5:
    .cfi_def_cfa_offset 16
    addq    $8, %rsp
    .cfi_def_cfa_offset 8
    ret                       ; return ret
    .cfi_endproc
.LFE11:
    .size   main, .-main
    .ident  "GCC: (Debian 4.7.2-5) 4.7.2"
    .section    .note.GNU-stack,"",@progbits
{% endhighlight %}

Là, le compilateur a réorganisé le code. Si je devais le retraduire en C,
j'écrirais ceci :

{% highlight c %}
#include <stdio.h>
#include <malloc.h>

int main(int argc, char *argv[]) {
    if (argc == 1)
        *((int *) NULL) = 42;
    int *i = malloc(sizeof(int));
    *i = 42;
    if (!i)
        return 1;
    printf("pwnd %d\n", 42);
    return 0;
}
{% endhighlight %}

Ce qui est amusant, c'est qu'il alloue de la mémoire pour stocker `i`, il lui
affecte la valeur `42`… mais ne la lit jamais. En effet, il a décidé de recoder
en dur `42` pour le paramètre du `printf`.

Mais avec ce résultat, impossible d'atteindre le `printf` si `argc == 1`.


### Avec `gcc -O2`

Optimisons davantage :

{% highlight bash %}
gcc -O2 -S undefined.c
{% endhighlight %}

Ou, plus précisément (avec `gcc 4.9.1` par exemple, l'option `-O2` ne suffit
pas) :

{% highlight bash %}
gcc -O -ftree-vrp -fdelete-null-pointer-checks -S undefined.c
{% endhighlight %}

(les options d'optimisation sont décrites dans la [doc][]).

[doc]: https://gcc.gnu.org/onlinedocs/gcc/Optimize-Options.html

{% highlight nasm linenos=table %}
    .file   "undefined.c"
    .section    .rodata.str1.1,"aMS",@progbits,1
.LC0:
    .string "pwnd %d\n"
    .section    .text.startup,"ax",@progbits
    .p2align 4,,15
    .globl  main
    .type   main, @function
main:
.LFB11:
    .cfi_startproc
    subq    $8, %rsp
    .cfi_def_cfa_offset 16
    movl    $42, %esi         ; arg1 = 42
    movl    $.LC0, %edi       ; arg2 points to "pwnd %d\n"
    xorl    %eax, %eax
    call    printf            ; printf("pwnd %d\n", 42)
    xorl    %eax, %eax        ; ret = 0
    addq    $8, %rsp
    .cfi_def_cfa_offset 8
    ret                       ; return ret
    .cfi_endproc
.LFE11:
    .size   main, .-main
    .ident  "GCC: (Debian 4.7.2-5) 4.7.2"
    .section    .note.GNU-stack,"",@progbits
{% endhighlight %}

Là, l'optimisation donne un résultat beaucoup plus direct :

{% highlight c %}
#include <stdio.h>

int main() {
    printf("pwnd %d\n", 42);
    return 0;
}
{% endhighlight %}

Quel raisonnement a-t-il pu suivre pour obtenir ce résultat ? Par exemple le
suivant.

Lorsqu'il rencontre la ligne 6 de `undefined.c`, soit `i` est `NULL`, soit `i`
n'est pas `NULL`. Le compilateur sait que déréférencer un pointeur `NULL` est
_indéfini_. Il n'a donc pas à gérer ce cas. Il considère donc que `i` est
forcément non-`NULL`.

Mais alors, à quoi bon tester si `i` est non-`NULL` ligne 7 ? Le test ne sert à
rien. Donc il le supprime.

Ce raisonnement permet de transformer le code ainsi :

{% highlight c %}
#include <stdio.h>
#include <malloc.h>

int main(int argc, char *argv[]) {
    int *i = argc == 1 ? NULL : malloc(sizeof(int));
    *i = 42;
    printf("pwnd %d\n", *i);
    return 0;
}
{% endhighlight %}

Mais ce n'est pas tout. Le compilateur sait que `i` n'est pas `NULL`, donc il
peut considérer que le `malloc` a lieu. Et allouer un entier en mémoire, écrire
`42` dedans, puis lire la valeur cet entier plus tard, ça se simplifie
beaucoup : juste lire `42`, sans allouer de mémoire.

Ce qu'il simplifie en :

{% highlight bash %}
printf("pwnd %d\n", 42);
{% endhighlight %}

CQFD.


### Avec `clang -02`

Il est intéressant d'observer ce que produit un autre compilateur : [Clang][].

[clang]: https://fr.wikipedia.org/wiki/Clang

{% highlight bash %}
clang -O2 -S undefined.c
{% endhighlight %}

Voici le résultat :

{% highlight nasm linenos=table %}
    .file   "undefined.c"
    .text
    .globl  main
    .align  16, 0x90
    .type   main,@function
main:                                   # @main
.Ltmp2:
    .cfi_startproc
# BB#0:
    pushq   %rbp
.Ltmp3:
    .cfi_def_cfa_offset 16
.Ltmp4:
    .cfi_offset %rbp, -16
    movq    %rsp, %rbp
.Ltmp5:
    .cfi_def_cfa_register %rbp
    cmpl    $1, %edi          ; if (argc == 1)
    je  .LBB0_4               ;     goto .LBB0_4
# BB#1:
    movl    $4, %edi          ; arg0 = 4  //sizeof(int)
    callq   malloc            ; tmp = malloc(4)
    movq    %rax, %rcx        ; i = tmp
    movl    $42, (%rcx)       ; *i = 42
    movl    $1, %eax          ; ret = 1
    testq   %rcx, %rcx        ; if (!i)
    je  .LBB0_3               ;     goto .LBB0_3
# BB#2:
    movl    $.L.str, %edi     ; arg0 points to "pwnd %d\n"
    movl    $42, %esi         ; arg1 = 42
    xorb    %al, %al
    callq   printf            ; printf("pwnd %d\n", *i)
    xorl    %eax, %eax        ; ret = 0
.LBB0_3:
    popq    %rbp
    ret                       ; return ret
.LBB0_4:                                # %.thread
    ud2                       ; undefined instruction
.Ltmp6:
    .size   main, .Ltmp6-main
.Ltmp7:
    .cfi_endproc
.Leh_func_end0:

    .type   .L.str,@object          # @.str
    .section    .rodata.str1.1,"aMS",@progbits,1
.L.str:
    .asciz   "pwnd %d\n"
    .size   .L.str, 9


    .section    ".note.GNU-stack","",@progbits
{% endhighlight %}

Il réalise les mêmes optimisations que `gcc -O`, sauf qu'il génère une erreur
explicite grâce à l'instruction machine
[`ud2`][ud2].

[ud2]: https://en.wikipedia.org/wiki/X86_instruction_listings#Added_with_Pentium_Pro

{% highlight c %}
#include <stdio.h>
#include <malloc.h>

int main(int argc, char *argv[]) {
    if (argc == 1)
        ud2(); /* hardware undefined instruction */
    int *i = malloc(sizeof(int));
    *i = 42;
    if (!i)
        return 1;
    printf("pwnd %d\n", 42);
    return 0;
}
{% endhighlight %}

Étonnamment, _Clang_ ne prend jamais la décision de supprimer le `malloc`.

Par contre, avec une version suffisamment récente (ça marche avec _Clang
3.5.0_), il est possible d'ajouter des [vérifications lors de
l'exécution][runtime] :

[runtime]: http://clang.llvm.org/docs/UsersManual.html#controlling-code-generation

    $ clang -fsanitize=null undefined.c && ./a.out
    undefined.c:6:5: runtime error: store to null pointer of type 'int'
    Erreur de segmentation

Ça peut être pratique pour détecter des problèmes. Et puis des
[`NullPointerException`][npe]s en C, ça fait rêver, non ?

[npe]: http://docs.oracle.com/javase/7/docs/api/java/lang/NullPointerException.html


## À retenir

Si un programme contient un _comportement indéfini_, alors son comportement
**est** _indéfini_. Pas juste la ligne en question. Pas juste les lignes qui
suivent la ligne en question. Le programme. Même s'il fonctionne maintenant sur
votre machine avec votre version de compilateur.

> Somebody once told me that in basketball you can't hold the ball and run. I
> got a basketball and tried it and it worked just fine. He obviously didn't
> understand basketball.

([source](http://www.eskimo.com/~scs/readings/undef.950311.html))

Pour aller plus loin et étudier d'autres exemples, je vous recommande la lecture
des articles suivants (en anglais) :

  * [A Guide to Undefined Behavior in C and C++][guide1] \| [2][guide2] \|
    [3][guide3]
  * [What Every C Programmer Should Know About Undefined Behavior][know1] \|
    [2][know2] \| [3][know3]
  * [Undefined Behavior: What Happened to My Code?][code] (pdf)

[guide1]: http://blog.regehr.org/archives/213
[guide2]: http://blog.regehr.org/archives/226
[guide3]: http://blog.regehr.org/archives/232
[know1]: http://blog.llvm.org/2011/05/what-every-c-programmer-should-know.html
[know2]: http://blog.llvm.org/2011/05/what-every-c-programmer-should-know_14.html
[know3]: http://blog.llvm.org/2011/05/what-every-c-programmer-should-know_21.html
[code]: http://pdos.csail.mit.edu/papers/ub:apsys12.pdf


## Optimisations multi-threadées

Les _comportements indéfinis_ font partie intégrante du _C_ et du _C++_. Mais
même dans des langages de plus haut niveau, il existe des comportements
_indéfinis_ (pas de même nature, je vous l'accorde), notamment lorsque plusieurs
[threads][] s'exécutent en parallèle.

[threads]: https://fr.wikipedia.org/wiki/Thread_%28informatique%29

Pour garantir certains comportements, il faut utiliser des mécanismes de
[synchronisation][]. Dans une vie antérieure, j'avais
[présenté][java-synchronisation] certains de ces mécanismes en [Java][].

[synchronisation]: https://en.wikipedia.org/wiki/Synchronization_%28computer_science%29
[java-synchronisation]: http://rom.developpez.com/java-synchronisation/
[java]: https://fr.wikipedia.org/wiki/Java_%28langage%29

Mais une erreur courante est de penser que la synchronisation ne fait que
garantir l'[atomicité][] avec des [sections critiques][]. En réalité, c'est plus
complexe que cela. D'une part, elle ajoute des [barrières mémoire][] empêchant
certaines réorganisations des instructions (ce qui explique pourquoi le
[double-checked locking][] pour écrire des [singletons][] est [faux][flawed]).
D'autre part, elle permet de synchroniser les caches locaux des threads, sans
quoi l'exemple suivant (inspiré d'[ici][stackoverflow]) est incorrect :

[atomicité]: https://fr.wikipedia.org/wiki/Atomicit%C3%A9_%28informatique%29
[sections critiques]: https://fr.wikipedia.org/wiki/Section_critique
[barrières mémoire]: https://en.wikipedia.org/wiki/Memory_barrier
[double-checked locking]: https://fr.wikipedia.org/wiki/Double-checked_locking
[singletons]: https://fr.wikipedia.org/wiki/Singleton_%28patron_de_conception%29
[flawed]: http://www.javamex.com/tutorials/double_checked_locking.shtml
[stackoverflow]: http://stackoverflow.com/questions/5022100/when-does-java-thread-cache-refresh-happens/5022188#5022188

{% highlight java linenos=table %}
public class VisibilityTest extends Thread {

    boolean keepRunning = true;

    public static void main(String... args) throws Exception {
        VisibilityTest thread = new VisibilityTest();
        thread.start();
        Thread.sleep(1000);
        thread.keepRunning = false;
        System.out.println(System.currentTimeMillis() +
                           ": keepRunning false");
    }

    @Override
    public void run() {
        System.out.println(System.currentTimeMillis() + ": start");
        while (keepRunning);
        System.out.println(System.currentTimeMillis() + ": end");
    }
}
{% endhighlight %}

Pour le compiler et l'exécuter :

{% highlight bash %}
javac VisibilityTest.java && java VisibilityTest
{% endhighlight %}

Sans synchronisation, il est très fort probable que le _thread_ démarré ne se
termine jamais, voyant toujours `keepRunning` à `true`, même si le thread
principal lui a donné la valeur `false`.

Là encore, c'est une optimisation (la mise en cache d'une variable) qui provoque
ce comportement "inattendu" sans [synchronisation][synchronized].

[synchronized]: http://www.javamex.com/tutorials/synchronization_concurrency_synchronized2.shtml

_Déclarer `keepRunning` [`volatile`][volatile] suffit à résoudre le problème._

[volatile]: http://www.javamex.com/tutorials/synchronization_volatile.shtml


## Conclusion

La notion de _comportement indéfini_ est très importante pour améliorer la
performance des programmes. Mais elle est source de bugs parfois difficiles à

`Erreur de segmentation`
