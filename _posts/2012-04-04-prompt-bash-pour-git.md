---
layout: post
title: Prompt Bash pour GIT
date: 2012-04-04 23:22:55+02:00
---

J'utilise [git][] depuis quelques mois, et je trouve ça vraiment génial. Si vous
ne connaissez pas, ou peu, vous ne pouvez pas ne pas lire le livre [Pro Git][]
(sous licence [cc-by-nc-sa][]). Les explications très claires permettent en
quelques heures de maîtriser toutes les fonctions de base, et d'être à l'aise
avec la gestion des branches (et bien plus encore).

[git]: http://fr.wikipedia.org/wiki/Git
[Pro Git]: https://git-scm.com/book/en/v2
[cc-by-nc-sa]: http://creativecommons.org/licenses/by-nc-sa/3.0/fr/


## Branches visibles

Le but de ce billet est de répondre à un problème particulier : par manque
d'attention, il m'est arrivé plusieurs fois de commiter des changements sur une
mauvaise branche (j'étais persuadé d'être sur une branche, en fait j'étais sur
une autre). Ce n'est pas très grave (on peut s'en sortir), mais c'est pénible.

Je souhaiterais donc avoir le nom de la branche dans le [prompt][] [bash][].

[prompt]: http://tldp.org/HOWTO/Bash-Prompt-HOWTO/
[bash]: http://fr.wikipedia.org/wiki/Bourne-Again_shell

Des solutions existent déjà : le paquet `git` embarque même [un script][another]
qui répond au besoin. Certains utilisent aussi [des scripts
personnalisés][config-prompt]. Mais aucun de ceux que j'ai trouvés ne me
convenait. J'ai donc écrit mon propre script.

[another]: https://gist.github.com/473838
[config-prompt]: http://blog.nicolargo.com/2012/02/configurer-votre-prompt-bash.html


## Mes prompts


### Version simple

J'ai commencé par une version simple, qui ajoute en couleur `@nomdelabranche` à
la fin du prompt. Un exemple vaut mieux qu'un long discours :

<pre>
<code>rom@rom-laptop:~/dev$ cd myproject/
rom@rom-laptop:~/dev/myproject<span style="font-family:monospace;color:#3264a3">@master</span>$ git checkout testing
Switched to branch 'testing'
rom@rom-laptop:~/dev/myproject<span style="font-family:monospace;color:#3264a3">@testing</span>$ cd img
rom@rom-laptop:~/dev/myproject/img<span style="font-family:monospace;color:#3264a3">@testing</span>$ </code>
</pre>


Dans une arborescence ayant plusieurs projets _GIT_ imbriqués (dans le cas de
l'utilisation de [sous-modules][]), la branche des projets parents n'est pas
affichée :

[sous-modules]: https://git-scm.com/book/en/v2/Git-Tools-Submodules

<pre>
<code>rom@rom-laptop:~/dev$ cd mybigproject/
rom@rom-laptop:~/dev/mybigproject<span style="font-family:monospace;color:#3264a3">@master</span>$ cd submodule/
rom@rom-laptop:~/dev/mybigproject/submodule<span style="font-family:monospace;color:#3264a3">@master</span>$ git checkout exp
Switched to branch 'exp'
rom@rom-laptop:~/dev/mybigproject/submodule<span style="font-family:monospace;color:#3264a3">@exp</span>$ cd ..
rom@rom-laptop:~/dev/mybigproject<span
style="font-family:monospace;color:#3264a3">@master</span>$ </code>
</pre>


### Version améliorée

Dans cette version simple, le nom de la branche est toujours affiché à la fin.
Cela ne me convient pas, je le voudrais toujours à la racine du projet en
question. C'est ce que permet la version améliorée.

Voici le résultat avec les mêmes commandes :

<pre>
<code>rom@rom-laptop:~/dev$ cd myproject/
rom@rom-laptop:~/dev/myproject<span style="font-family:monospace;color:#3264a3">@master</span>$ git checkout testing
Switched to branch 'testing'
rom@rom-laptop:~/dev/myproject<span style="font-family:monospace;color:#3264a3">@testing</span>$ cd img
rom@rom-laptop:~/dev/myproject<span
style="font-family:monospace;color:#3264a3">@testing</span>/img$ </code>
</pre>

Et avec des sous-modules, la branche des projets parents est affichée :

<pre>
<code>rom@rom-laptop:~/dev$ cd mybigproject/
rom@rom-laptop:~/dev/mybigproject<span style="font-family:monospace;color:#3264a3">@master</span>$ cd submodule/
rom@rom-laptop:~/dev/mybigproject<span style="font-family:monospace;color:#3264a3">@master</span>/submodule<span style="font-family:monospace;color:#3264a3">@master</span>$ git checkout exp
Switched to branch 'exp'
rom@rom-laptop:~/dev/mybigproject<span style="font-family:monospace;color:#3264a3">@master</span>/submodule<span style="font-family:monospace;color:#3264a3">@exp</span>$ cd ..
rom@rom-laptop:~/dev/mybigproject<span
style="font-family:monospace;color:#3264a3">@master</span>$ </code>
</pre>

En image :

{: .center}
![gitbashprompt]({{ site.assets }}/gitbashprompt/gitbashprompt.png)


## Script

Le script, sous licence [WTFPL][], est disponible sur un dépôt _git_ :

[wtfpl]: http://www.wtfpl.net/

    git clone http://git.rom1v.com/gitbashprompt.git

(ou sur [github](https://github.com/rom1v/gitbashprompt))

Une fois cloné, éditez le fichier `~/.bashrc` pour remplacer l'initialisation de
la variable `PS1` :

{% highlight bash %}
        PS1='${debian_chroot:+($debian_chroot)}\u@\h:\w\$ '
{% endhighlight %}

par :

{% highlight bash %}
        . full/path/to/your/gitbashprompt
{% endhighlight %}

_Pour tester, ouvrir un nouveau terminal._



## Conclusion



Tout d'abord, je suis content d'avoir exactement le comportement que je
souhaitais pour mon _git_.

Ensuite, j'ai découvert le fonctionnement du prompt, avec notamment les
subtilités d'[échappement de caractères][escape] de la variable `PS1` et la
prise en compte des caractères de contrôle `\[` et `\]`.

[escape]: http://fr.wikipedia.org/wiki/Caract%C3%A8re_d%27%C3%A9chappement

Enfin, je me suis enfin décidé à étudier la gestion des couleurs de _Bash_ (qui,
à première vue, est assez repoussante, il faut bien l'avouer). Mes scripts
seront donc plus jolis à l'avenir ;-)
