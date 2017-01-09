---
layout: post
title: 'GIT : squasher des merges'
date: 2013-05-30 15:40:50+02:00
tags:
- planet-libre
- puf
---

{: .center}
![gitmerge]({{ site.assets }}/squash_merges/gitmerge.jpg)

Supposons que je souhaite ajouter une fonctionnalité à un projet sur [GIT][].

[git]: http://fr.wikipedia.org/wiki/Git

Je prends la version actuelle de la branche `master` (`A`), puis ajoute sur ma
branche `topic` les commits `X` et `Y`.

        X---Y  topic
       /
    --A  master

Je propose la fonctionnalité _upstream_ (par un `git request-pull` ou une [pull
request][]), qui met un peu de temps à être revue.

[pull request]: https://help.github.com/articles/using-pull-requests

Pendant ce temps, la branche `master` a avancé, et malheureusement les
modifications effectuées entrent en conflit avec mon travail sur `topic`.

        X---Y  topic
       /
    --A---B---C  master

Une fois mon code revu et accepté, les mainteneurs vont alors me demander de
résoudre les conflits avec la branche `master` avant de merger ma branche
`topic`.

Si j'avais eu à prendre en compte les mises à jour de `master` **avant** d'avoir
rendu public mon `topic`, j'aurais simplement [rebasé][rebase] mon travail
par-dessus `master`. Mais là, [impossible][clean-history].

[rebase]: http://git-scm.com/book/en/Git-Branching-Rebasing
[clean-history]: http://www.mail-archive.com/dri-devel@lists.sourceforge.net/msg39091.html

Je dois donc merger. Très bien. Je merge et je résous les conflits.

        X---Y---M  topic
       /       /
    --A---B---C  master

Mais, alors que je n'ai pas encore rendu `M` public, je m'aperçois qu'il y a un
nouveau commit `D` sur `master`, que je veux intégrer dans `topic`.

        X---Y---M  topic
       /       /
    --A---B---C---D  master

La solution la plus évidente est de merger à nouveau.

        X---Y---M---N  topic
       /       /   /
    --A---B---C---D  master

Mais je voudrais éviter un commit de merge inutile. Pour un seul, ce n'est pas
très gênant, mais si on maintient une branche suffisamment longtemps avant
qu'elle ne soit mergée, ces commits inutiles vont se multiplier.

Une solution serait de revenir à `Y` et de le merger avec `D` :

    git checkout topic
    git reset --hard Y
    git merge master

Ce qui donne :

        X---Y---M'  topic
       /         \
    --A---B---C---D  master

Mais dans ce cas, pour créer `M'`, je vais devoir résoudre à nouveau les
conflits que j'avais déjà résolu en créant `M`.

Comment éviter ce problème ?


## rerere

Une solution est d'avoir activé _rerere_ **avant** d'avoir résolu les conflits
de `M` :

    git config rerere.enabled true

Ainsi, lorsque je tenterai de merger à nouveau `Y` et `D`, les conflits entre
`Y` et `C` seront automatiquement résolus de la même manière que précédemment.

Cependant, cette méthode a ses inconvénients.

Tout d'abord, il ne s'agit que d'un cache local de résolutions des conflits,
stocké pendant une durée déterminée (par défaut à 60 jours pour les conflits
résolus), ce qui est peu pratique si on clone son dépôt sur plusieurs machines
(les conflits ne seront résolus automatiquement que sur certaines).

Ensuite, elle est inutilisable lorsqu'on souhaite [squasher][squash] un merge
conflictuel alors que _rerere_ était désactivé lors de sa création.

[squash]: http://git-scm.com/book/en/Git-Tools-Rewriting-History#Squashing-Commits

Enfin, cette fonctionnalité est encore récente, et la fonction `git rerere
forget` (pour permettre de résoudre autrement des conflits déjà résolus), a la
fâcheuse tendance à [segfaulter][segfault] (un [patch][] a été proposé).

[segfault]: http://fr.wikipedia.org/wiki/Erreur_de_segmentation
[patch]: http://permalink.gmane.org/gmane.comp.version-control.git/220059


## Rebranchement

La solution que j'utilise est donc la suivante.

        X---Y---M---N  topic
       /       /   /
    --A---B---C---D  master

Une fois obtenus les deux merges `M` et `N`, le principe est de remplacer le
parent de `N`, qui était `M`, par `Y`, sans rien changer d'autre au contenu.

              -----
             /     \
        X---Y---M   N' topic
       /       /   /
    --A---B---C---D  master

Ainsi, `M` devient inatteignable, et c'est exactement le résultat souhaité :

        X---Y-------N' topic
       /           /
    --A---B---C---D  master

Pour faire cela, il faut déplacer le HEAD (pointant vers `topic`) sur `Y`, faire
croire à GIT qu'on est en phase de merge avec `D` en modifiant la référence
`MERGE_HEAD`, puis commiter :

{% highlight bash %}
git checkout N
git reset --soft Y
git update-ref MERGE_HEAD D
git commit -eF <(git log ..HEAD@{1} ^master --pretty='# %H%n%s%n%n%b')
{% endhighlight %}

Il n'y a plus qu'à éditer le message de commit de merge.

La fin de la ligne du `git commit` permet de concaténer l'historique des commits
intermédiaires (a priori uniquement des _merges_) comme lors d'un _squash_ avec
`git rebase` (pour pouvoir conserver les messages de merges intermédiaires,
contenant nontamment les conflits).

En utilisant les références plutôt que les numéros de commit, cela donne :

{% highlight bash %}
git checkout feature
git reset --soft HEAD~2
git update-ref MERGE_HEAD master
git commit -eF <(git log ..HEAD@{1} ^master --pretty='# %H%n%s%n%n%b')
{% endhighlight %}

Si vous avez plus simple, je suis preneur…

_Merci aux membres de [stackoverflow][]._

[stackoverflow]: http://stackoverflow.com/questions/1725708/git-rebase-interactive-squash-merge-commits-togethergi
