---
layout: post
title: Fusionner deux dépôts git
date: 2017-07-12 20:30:00+02:00
tags:
- planet-libre
---

Ce billet explique comment fusionner un dépôt _git_ (avec son historique) dans
un sous-répertoire d'un autre dépôt _git_.


## Cas d'usage

Mon projet principal se trouve dans un dépôt `main`. J'ai démarré dans un autre
dépôt un projet `other`, que je souhaite finalement intégrer dans un
sous-répertoire `sub/` du projet principal, en conservant son historique. Après
cette fusion, je ne garderai que le dépôt principal.


## Fusion

Les deux projets se trouvent dans le répertoire courant :

    $ ls
    main  other

Dans le dépôt `main`, _copier_ la branche `master` de `other` dans une nouvelle
branche `tmp` :

{% highlight bash %}
cd main
git fetch ../other master:tmp
{% endhighlight %}

Le dépôt `main` contient alors les historiques disjoints des deux projets.

Nous allons maintenant réécrire l'historique complet de la branche `tmp` pour
déplacer tout le contenu dans un sous-répertoire `sub/`, grâce une commande
donnée en exemple de [`man git filter-branch`][git-scm] :

[git-scm]: https://git-scm.com/docs/git-filter-branch#_examples

{% highlight bash %}
git checkout tmp
git filter-branch --index-filter \
    'git ls-files -s | sed "s-\t\"*-&sub/-" |
        GIT_INDEX_FILE=$GIT_INDEX_FILE.new \
            git update-index --index-info &&
     mv "$GIT_INDEX_FILE.new" "$GIT_INDEX_FILE"'
{% endhighlight %}

À ce stade, nous avons toujours deux historiques indépendants, mais le contenu
lié à la branche `tmp` se trouve dans le sous-répertoire `sub/`.

```
A---B---C---D master

  X---Y---Z tmp
```

La dernière étape consiste à relier les deux historiques, soit grâce à un
_rebase_, soit grâce à un _merge_.

Un _rebase_ réécrit l'historique du sous-projet sur la branche `master` :

{% highlight bash %}
git rebase master

# A---B---C---D---X'--Y'--Z' master
{% endhighlight %}

Un _merge_ relie juste les deux historiques grâce à un commit de _merge_ :

{% highlight bash %}
git merge tmp --allow-unrelated-histories

# A---B---C---D---M  master
#                /
#       X---Y---Z tmp
{% endhighlight %}


## Concrètement

J'ai débuté la réécriture du serveur relais de [gnirehtet] en [Rust] dans un
dépôt séparé. Maintenant qu'il commence à fonctionner, je l'ai fusionné dans un
[sous-répertoire] du [dépôt principal] tout en conservant l'[historique] :

{% highlight bash %}
git fetch ../rustrelay master:tmp
git checkout tmp
git filter-branch --index-filter \
    'git ls-files -s | sed "s-\t\"*-&rustrelay/-" |
        GIT_INDEX_FILE=$GIT_INDEX_FILE.new \
            git update-index --index-info &&
     mv "$GIT_INDEX_FILE.new" "$GIT_INDEX_FILE"'
git rebase master
{% endhighlight %}


[gnirehtet]: {% post_url 2017-03-30-gnirehtet %}
[gnirehtet_rust]: https://github.com/Genymobile/gnirehtet/commits/rust
[sous-répertoire]: https://github.com/Genymobile/gnirehtet/tree/rust/rustrelay
[dépôt principal]: https://github.com/Genymobile/gnirehtet
[historique]: https://github.com/Genymobile/gnirehtet/commits/rust
[Rust]: https://www.rust-lang.org
