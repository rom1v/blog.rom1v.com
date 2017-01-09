---
layout: post
title: Commentaires statiques avec Jekyll
date: 2017-01-09 18:06:04+01:00
tags:
- planet-libre
---

Pour ce blog, j'ai abandonné
[Wordpress](https://fr.wikipedia.org/wiki/WordPres://fr.wordpress.org/) pour
[Jekyll](https://jekyllrb.com/), un moteur de blog _statique_.

Ainsi, j'écris mes articles en [markdown][] dans mon [éditeur favori][vim], je
les _commite_ dans un [dépôt git][sources], et je génère le blog avec :

    jekyll build

[sources]: https://github.com/rom1v/blog.rom1v.com/
[markdown]: https://fr.wikipedia.org/wiki/Markdown
[vim]: https://fr.wikipedia.org/wiki/Vim

Le contenu hébergé étant statique, les pages ainsi générées à partir des sources
sont renvoyées telles quelles.

Ce fonctionnement a beaucoup d'avantages :

 - le temps de réponse est minimal ;
 - la sécurité est largement accrue ;
 - la maintenance est simplifiée (pas de mises à jour de sécurité régulières) ;
 - le backup est trivial (`git clone`, pas de base de données).


## Sans commentaires

L'inconvénient, c'est qu'un contenu statique est difficilement conciliable avec
le support des commentaires (il faut bien d'une manière ou d'une autre exécuter
du code lors de la réception d'un commentaire).

Il y a plusieurs manières de contourner le problème.

Il est par exemple possible d'en déporter la gestion (sur un service en ligne
comme [Disqus][jekyll-disqus] ou un équivalent libre – [isso][] – à héberger
soi-même). Ainsi, les commentaires peuvent être chargés séparément par le client
en _Javascript_.

[jekyll-disqus]: http://www.perfectlyrandom.org/2014/06/29/adding-disqus-to-your-jekyll-powered-github-pages/
[isso]: https://posativ.org/isso/

Au lieu de cela, j'ai choisi d'intégrer les commentaires aux sources du blog.
Voici comment.

L'objectif est d'une part de pouvoir **stocker** et **afficher** les commentaires
existants, et d'autre part de fournir aux lecteurs la possibilité d'en
**soumettre** de nouveaux, qui me seront **envoyés par e-mail**.

Je me suis principalement inspiré du contenu de [Jekyll::StaticComments][], même
si, comme nous allons le voir, je n'utilise pas le plug-in lui-même.

[Jekyll::StaticComments]: http://theshed.hezmatt.org/jekyll-static-comments/

## Stockage

L'idée est de stocker les commentaires quelque part dans les sources du site au
format [YAML][].

[YAML]: https://fr.wikipedia.org/wiki/YAML

Le plugin _Jekyll::StaticComments_ nécessite de stocker [un fichier par
commentaire][jsc-readme] dans un dossier spécial (`_comments`) parsé par un
script à insérer dans le répertoire `_plugins`.

[jsc-readme]: https://github.com/mpalmer/jekyll-static-comments/blob/master/README.md#technical-details

Personnellement, je préfère avoir tous les commentaires d'un même post regroupés
au sein d'un même fichier. Et pour cela, pas besoin de plug-in : nous pouvons
faire [correspondre][static-comments] à chaque post dans `_posts` une liste de
commentaires dans `_data` (un répertoire géré nativement par _Jekyll_).

[static-comments]: http://stevesspace.com/2014/04/static-jekyll-comments/

Par exemple, ce billet est stocké dans :

    _posts/2017-01-09-commentaires-statiques-avec-jekyll.md

Dans l'idéal, je voudrais que les commentaires associés soient stockés dans :

    _data/comments-2017-01-09-commentaires-statiques-avec-jekyll.yaml

En pratique, pour des raisons techniques ([_Jekyll_ ne donne pas accès au nom du
fichier][nofilename]), le nom du fichier ne contient pas le numéro du jour :

    _data/comments-2017-01-commentaires-statiques-avec-jekyll.yaml

[nofilename]: https://github.com/jekyll/jekyll/issues/633

Il suffit alors de stocker dans ces fichiers les commentaires sous cette forme :

{% highlight yaml %}
- id: 1
  author: this_is_me
  date: 2017-01-02 10:11:12+01:00
  contents: |
    Bonjour,

    Ceci est un commentaire écrit en _markdown_.
- id: 2
  author: dev42
  author-url: https://github.com
  date: 2017-01-02 12:11:10+01:00
  contents: |
    > Ceci est un commentaire écrit en _markdown_.

    Et ça supporte aussi le [Liquid](https://jekyllrb.com/docs/templates/) :

    {% raw %}{% highlight c %}
    int main() {
        return 0;
    }
    {% endhighlight %}{% endraw %}
{% endhighlight %}

Pour des exemples réels, voir les [sources des commentaires][comments] de ce
blog.

[comments]: https://github.com/rom1v/blog.rom1v.com/tree/master/_data
{{ page.filename }}


## Affichage

Maintenant que nous avons les données des commentaires, nous devons les
afficher.

Il faut d'abord trouver la liste des commentaires associée à la page courante.

Comme nous ne pouvons pas récupérer directement le nom du fichier d'une page,
nous devons reconstruire la chaîne à partir de la [variable][] `page.id`, qui
ici vaut :

    {{ page.id }}

Cette ligne de _Liquid_ :

{% highlight liquid %}
{% raw %}comments{{ page.id | replace: '/', '-' }}{% endraw %}
{% endhighlight %}

donne la valeur :

    comments{{ page.id | replace: '/', '-' }}

[variable]: https://jekyllrb.com/docs/variables/#page-variables

Nous avons donc tout ce dont nous avons besoin pour créer le _template_ de
commentaires (à stocker dans [`_include/comments.html`][comments.html]) :

[comments.html]: https://github.com/rom1v/blog.rom1v.com/blob/master/_includes/comments.html

{% highlight html %}
{% raw %}{% capture commentid %}comments{{ page.id | replace: '/', '-' }}{% endcapture %}
{% if site.data[commentid] %}{% endraw %}
<h2 id="comments">Commentaires</h2>
<div class="comments">
    {% raw %}{% for comment in site.data[commentid] %}{% endraw %}
        <div id="comment-{% raw %}{{ comment.id }}{% endraw %}" class="comment" />
            <div class="comment-author">
                {% raw %}{% if (comment.author-url) %}{% endraw %}
                    <a href="{% raw %}{{comment.author-url}}{% endraw %}">
                {% raw %}{% endif %}
                {{ comment.author }}
                {% if (comment.author-url) %}{% endraw %}
                    </a>
                {% raw %}{% endif %}{% endraw %}
            </div>
            <div class="comment-date">
                <a href="#comment-{% raw %}{{ comment.id }}{% endraw %}">
                    {% raw %}{{ comment.date | date: "%-d %B %Y, %H:%M" }}{% endraw %}
                </a>
            </div>
            <div class="comment-contents">
                {% raw %}{{ comment.contents | liquify | markdownify }}{% endraw %}
            </div>
        </div>
    {% raw %}{% endfor %}{% endraw %}
</div>
{% endhighlight %}

Il suffit alors d'inclure cette page à l'endroit où vous souhaitez insérer les
commentaires (typiquement dans [`_layout/post.html`][post.html]) :

[post.html]: https://github.com/rom1v/blog.rom1v.com/blob/master/_layouts/post.html

{% highlight liquid %}
{% raw %}{% include comments.html %}{% endraw %}
{% endhighlight %}


## Formulaire

Pour proposer aux utilisateurs de poster de nouveaux commentaires, il nous faut
un formulaire.

À titre d'exemple, voici le mien (intégré à
[`_include/comments.html`][comments.html]) :

{% highlight html %}
<h3 class="comment-title">Poster un commentaire</h3>
<form method="POST" action="/comments/submit.php">
    <input type="hidden" name="post_id" value="{% raw %}{{ page.id }}{% endraw %}" />
    <input type="hidden" name="return_url" value="{% raw %}{{ page.url }}{% endraw %}" />
    <table class="comment-table">
        <tr>
            <th>Nom :</th>
            <td>
                <input type="text" size="25" name="name" />
                <em>(requis)</em>
            </td>
        </tr>
        <tr>
            <th>E-mail :</th>
            <td>
                <input type="text" size="25" name="email" />
                <em>(requis, non publié)</em>
            </td>
        </tr>
        <tr>
            <th>Site web :</th>
            <td>
                <input type="text" size="25" name="url" />
                <em>(optionnel)</em>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <textarea name="comment" rows="10"></textarea>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input class="comment-submit" type="submit" value="Envoyer" />
            </td>
        </tr>
    </table>
</form>
{% endhighlight %}

Ce formulaire est affiché sous les commentaires existants.


## Traitement

L'`action` du formulaire précédent pointait sur
[`comments/submit.php`][submit.php]. Il nous reste donc à écrire dans ce fichier
le code à exécuter lorsqu'un utilisateur envoie un commentaire au serveur.

[submit.php]: https://github.com/rom1v/blog.rom1v.com/blob/master/comments/submit.php

Ce sera la seule partie "dynamique" du site.

Voici les parties importantes de [`comments/submit.php`][submit.php] (basé sur
[la version de Jekyll::StaticComments][jsc-submit.php]) :

[jsc-submit.php]: https://github.com/mpalmer/jekyll-static-comments/blob/master/commentsubmit.php

{% highlight php %}
<?php
$DATE_FORMAT = "Y-m-d H:i:sP";
$EMAIL_ADDRESS = "your@email";
$SUBJECT = "Nouveau commentaire";
$COMMENT_SENT = "sent.html";

$msg = "post_id: " . $_POST["post_id"] . "\n";
$msg .= "email: " . $_POST["email"] . "\n";
$msg .= "---\n";
$msg .= "- id: ?\n";
$msg .= "  author: " . $_POST["name"] . "\n";
if ($_POST["url"] !== '')
{
    $msg .= "  author-url: " . $_POST["url"] . "\n";
}
$msg .= "  date: " . date($DATE_FORMAT) . "\n";
$msg .= "  contents: |\n" . $_POST["comment"];
if (mail($EMAIL_ADDRESS, $SUBJECT, $msg, "From: $EMAIL_ADDRESS"))
{
    include $COMMENT_SENT;
}
else
{
    echo "Le commentaire n'a pas pu être envoyé.";
}
{% endhighlight %}

Quand un commentaire est envoyé avec succès, la page
[`comments/sent.html`][sent.html] est affichée à l'utilisateur.

[sent.html]: https://github.com/rom1v/blog.rom1v.com/blob/master/comments/sent.html

Ainsi, lorsqu'un commentaire est posté, je reçois un mail :

    post_id: {{ page.id }}
    email: my@email
    ---
    - id: ?
      author: ®om
      author-url: http://blog.rom1v.com
      date: 2017-01-09 19:27:10+01:00
      contents: |
    Ceci est un test.

J'ai d'ailleurs ajouté une règle [procmail][] pour que ces mails arrivent dans
un dossier dédié.

[procmail]: {% post_url 2010-01-06-trier-ses-mails-directement-sur-le-serveur-procmail %}

Je peux alors copier le contenu dans le `.yaml` correspondant, formatter le
commentaire (entre autres l'indenter de 4 espaces, ce qu'on pourrait
automatiser), et le commiter.


## Résumé

Une fois mis en place, vous devriez donc avoir les fichiers suivants :

 * `_data/comments-*.yaml`
 * `_include/comments.html`
 * `comments/submit.php`
 * `comments/sent.html`


## Conclusion

Je souhaitais depuis longtemps migrer vers un moteur de blog statique, qui
correspond davantage à ma façon d'écrire des articles, et offre beaucoup
d'avantages (légèreté, sécurité, maintenance…).

Je suis très content d'y être parvenu sans perdre les commentaires ni la
possibilité d'en poster de nouveaux.

Certes, la validation est très manuelle, mais c'est le prix à payer pour avoir
des commentaires statiques. Pour un blog avec une fréquence de commentaires
assez faible, je pense que ce n'est pas très gênant.
