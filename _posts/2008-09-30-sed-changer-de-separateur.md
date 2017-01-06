---
layout: post
title: 'sed : changer de séparateur'
date: 2008-09-30 15:03:00+01:00
---

Si vous effectuez quelques traitements simples en ligne de commande, vous
connaissez forcément l'outil `sed`, et plus particulièrement la commande :

{% highlight bash %}
sed 's/ancien/récent/'
{% endhighlight %}

qui permet de remplacer `ancien` par `récent` :

~~~
$ sed 's/ancien/récent/' <<< 'ce système est ancien, voire très ancien'
ce système est récent, voire très ancien
~~~

Pour remplacer toutes les occurrences, on rajoute un `g` :

~~~
$ sed 's/ancien/récent/g' <<< 'ce système est ancien, voire très ancien'
ce système est récent, voire très récent
~~~

Cependant, la syntaxe est assez lourde lorsqu'on veut remplacer des `/`, par
exemple pour remplacer `/home/rom/sh/` par `/usr/bin/` :

~~~
$ sed 's/\/home\/rom\/sh\//\/usr\/bin\//' <<< /home/rom/sh/myscript
/usr/bin/myscript
~~~

Heureusement, il est possible de changer le séparateur, et très facilement :
c'est simplement le caractère après le `s`, et on met ce que l'on veut :

~~~
$ sed 's:/home/rom/sh:/usr/bin:' <<< /home/rom/sh/myscript
/usr/bin/myscript
$ sed 's|/home/rom/sh|/usr/bin|' <<< /home/rom/sh/myscript
/usr/bin/myscript
~~~

*(il suffit de backslasher le caractère qui sert de séparateur dans les tokens)*

Allez, pour s'amuser :

{% highlight bash %}
sed sachagena <<< 'des choux'
sed subucu <<< 'trop bon'
{% endhighlight %}
