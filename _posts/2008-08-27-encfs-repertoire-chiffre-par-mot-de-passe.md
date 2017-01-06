---
layout: post
title: 'encfs : répertoire chiffré par mot de passe'
date: 2008-08-27 17:57:00+02:00
---

Ce billet présente comment avoir un (ou plusieurs) répertoire(s) chiffré(s).

Tout d'abord, il faut installer `encfs`.

Si l'on souhaite que le répertoire chiffré soit `~/.encrypted` et que celui
déchiffré soit `~/decrypted` :

    encfs ~/.encrypted ~/decrypted

Voici ce que ça donne :

~~~
$ encfs ~/.encrypted ~/decrypted
Le répertoire "/home/rom/.encrypted/" n'existe pas. Faut-il le créer ? (y/n) y
Le répertoire "/home/rom/decrypted" n'existe pas. Faut-il le créer ? (y/n) y
Création du nouveau volume encrypté.
Veuillez choisir l'une des options suivantes :
entrez "x" pour le mode de configuration expert,
entrez "p" pour le mode paranoïaque préconfiguré,
toute autre entrée ou une ligne vide sélectionnera le mode normal.
?> p

Configuration paranoïaque sélectionnée.

Configuration terminée. Le système de fichier à créer a les propriétés
suivantes :
Chiffrement de système de fichiers "ssl/aes", version 2:1:1
Encodage de fichier "nameio/block", version 3:0:1
Taille de clé : 256 bits
Taille de bloc : 512 octets, y compris 8 octets d'en-tête MAC.
Chaque fichier contient un en-tête de 8 octets avec des données IV uniques.
Noms de fichier encodés à l'aide du mode de chaînage IV.
Les données IV du fichier sont chaînées à celles du nom de fichier

-------------------------- AVERTISSEMENT --------------------------
The external initialization-vector chaining option has been
enabled.  This option disables the use of hard links on the
filesystem. Without hard links, some programs may not work.
The programs 'mutt' and 'procmail' are known to fail.  For
more information, please see the encfs mailing list.
If you would like to choose another configuration setting,
please press CTRL-C now to abort and start over.

Vous devez entrer un mot de passe pour votre système de fichiers.
Vous devez vous en souvenir, car il n'existe aucun mécanisme de récupération.
Toutefois, le mot de passe peut être changé plus tard à l'aide d'encfsctl.

Nouveau mot de passe :
Vérifier le mot de passe :
~~~

On rentre le mot de passe de chiffrement.

Ensuite, pour monter (même commande que pour créer) :

    encfs ~/.encrypted ~/decrypted

Tout ce qui sera écrit dans `~/decrypted` sera écrit en chiffré dans
`~/.encrypted`.

~~~
$ echo bonjour > decrypted/unfichier
$ ls -l decrypted
total 4
-rw-r--r-- 1 rom rom 11 2008-08-22 13:58 unfichier
$ ls -l .encrypted/
total 4
-rw-r--r-- 1 rom rom 27 2008-08-22 13:58 InNjH,-h8EnvvfC28k5oUji1
~~~

Pour démonter :

    fusermount -u ~/decrypted

Après le démontage, les données chiffrées ne sont plus accessibles en clair :)

Et pour éviter d'avoir à taper des commandes, voici un petit script qui permet
de demander le mot de passe, de monter le répertoire décrypté, et d'ouvrir une
fenêtre permettant, lorsqu'on la ferme, de démonter le répertoire :

{% highlight bash %}
#!/bin/sh

encrypted_folder=/home/rom/.encrypted
decrypted_folder=/home/rom/decrypted

gksudo -p -m "Mot de passe de déchiffrement" |
encfs -S "$encrypted_folder" "$decrypted_folder" &&
zenity --info --title='encfs' 
  --text='Cliquez sur valider pour démonter le dossier déchiffré' &&
fusermount -u "$decrypted_folder"
{% endhighlight %}

`zenity` doit être installé.

Il suffit alors de mettre un lanceur sur le bureau/dans la barre pour monter
facilement le répertoire chiffré.

Il y a également d'autres outils plus complets (avec icône dans le systray…).

Plus d'infos sur [la doc d'ubuntu-fr](http://doc.ubuntu-fr.org/encfs).

La prochaine version d'ubuntu (**Ubuntu 8.10 Intrepid Ibex**, prévue le 30
octobre 2008) [devrait proposer][ibex] dès l'installation l'utilisation d'un
répertoire chiffré.

[ibex]: http://www.generation-nt.com/ubuntu-intrepid-ibex-alpha-4-linux-actualite-138721.htm

_**EDIT (astuce) :** Pour utiliser `rsync` avec un répertoire chiffré, utilisez
l'option `-c`._
