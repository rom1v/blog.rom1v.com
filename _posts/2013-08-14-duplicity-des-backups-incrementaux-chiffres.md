---
layout: post
title: 'Duplicity : des backups incrémentaux chiffrés'
date: 2013-08-14 14:59:42+02:00
---

Quiconque s'[auto-héberge][auto-hébergement] doit maintenir un système de
sauvegarde de son serveur, permettant de tout remettre en place dans le cas d'un
crash de disque dur, d'un piratage ou d'un cambriolage.

[auto-hébergement]: http://www.auto-hebergement.fr/


## Objectifs

Il est nécessaire de sauvegarder à la fois des **fichiers** (les mails, les
services hébergés, les fichiers de config…) et le contenu de **bases de
données** (associées aux services hébergés).

Le système de sauvegarde doit **conserver les archives** durant un certain temps
(par exemple 2 mois). En effet, un piratage ou une erreur de manipulation
peuvent n'être détectés que quelques jours plus tard : il est important de
pouvoir restaurer un état antérieur.

La sauvegarde doit être **régulière** (par exemple quotidienne).

Seule une infime partie des données étant modifiées d'un jour à l'autre, la
sauvegarde a tout intérêt à être [**incrémentale**][incrémentale].

[incrémentale]: http://fr.wikipedia.org/wiki/Sauvegarde_%28informatique%29#Sauvegarde_incr.C3.A9mentielle_ou_incr.C3.A9mentale

Pour résister aux cambriolages, une sauvegarde doit être réalisée sur (au moins)
une machine distante. Il est donc préférable que ces données soient
[**chiffrées**][chiffrement].

[chiffrement]: http://fr.wikipedia.org/wiki/Chiffrement


## Duplicity

Vous l'aurez compris, [_duplicity_][duplicity] répond à tous ces besoins.

[duplicity]: http://duplicity.nongnu.org/

Je ne vais pas expliquer tout ce qu'il sait faire, mais plutôt comment je
l'utilise et pourquoi.


## Mes choix d'utilisation


### Sauvegarde locale

Personnellement, je n'effectue qu'une sauvegarde **locale** dans une tâche
[_cron_][cron], c'est-à-dire que les fichiers de backups sont stockés sur le
serveur lui-même.

[cron]: http://fr.wikipedia.org/wiki/Cron

En effet, une sauvegarde automatique vers un serveur distant, par _SSH_ par
exemple, nécessiterait une clé privée en clair sur le serveur. Cette
configuration ne résisterait pas à certains piratages : une intrusion sur le
serveur donnerait également accès aux sauvegardes, permettant à un pirate
d'effacer à la fois les données et les backups.

C'est donc une autre machine, à l'initiative de la connexion, qui rapatrie les
backups. Évidemment, elle ne doit pas synchroniser localement les backups
supprimés du serveur (elle serait vulnérable à la suppression des backups par un
pirate), mais doit plutôt supprimer les anciennes sauvegardes de sa propre
initiative.


### Chiffrement

_Duplicity_ utilise [GPG][] pour le chiffrement, permettant :

[gpg]: https://fr.wikipedia.org/wiki/GNU_Privacy_Guard

  * soit un [chiffrement asymétrique][] (une paire de clés publique/privée, la
    clé privée pouvant elle-même être chiffrée par une [passphrase][]) ;
  * soit un [chiffrement symétrique][] (une simple passphrase).

[chiffrement asymétrique]: http://fr.wikipedia.org/wiki/Cryptographie_asym%C3%A9trique
[passphrase]: http://fr.wikipedia.org/wiki/Phrase_secr%C3%A8te
[chiffrement symétrique]: http://fr.wikipedia.org/wiki/Cryptographie_sym%C3%A9trique

Le premier choix nécessite à la fois quelque chose que **je possède** (la clé,
de forte entropie) et quelque chose que **je connais** (la passphrase, de plus
faible [entropie][]). Le second ne nécessite que la passphrase à retenir.

[entropie]: http://en.wikipedia.org/wiki/Password_strength#Entropy_as_a_measure_of_password_strength

L'utilisation d'une clé privée autorise donc une meilleure sécurité, notamment
si vous souhaitez envoyer vos backups sur un [serveur américain][prism].

[prism]: http://fr.wikipedia.org/wiki/PRISM_%28programme_de_surveillance%29

Néanmoins, les backups sont surtout utiles lors de la perte de données,
notamment dans le cas d'un cambriolage, où la clé GPG a potentiellement
également disparu. Et les sauvegardes distantes ne seront d'aucune utilité sans
la clé…

Il peut donc être moins risqué d'opter, comme je l'ai fait, pour une simple
passphrase.

À vous de placer le curseur entre la protection de vos données et le risque de
ne plus pouvoir les récupérer.


## Installation

Sur une _Debian_ :

    sudo apt-get install duplicity


## Fonctionnement

_Duplicity_ effectue des sauvegardes **complètes** et **incrémentales**. Les
sauvegardes incrémentales nécessitent toutes les sauvegardes depuis la dernière
complète pour être restaurées.

Personnellement, j'effectue une sauvegarde complète tous les mois, et une
incrémentale tous les jours.

Pour choisir le mode :

  * `duplicity full …` force une sauvegarde **complète** ;
  * `duplicity incr …` force une sauvegarde **incrémentale** (échoue si aucune
    **complète** n'est trouvée) ;
  * `duplicity …` effectue une sauvegarde **incrémentale** si possible,
    **complète** sinon.

Exemple (à exécuter en `root` pour avoir accès à tous les fichiers) :

{% highlight bash %}
duplicity / file:///var/backups/duplicity/ \
    --include-globbing-filelist filelist.txt \
    --exclude '**'
{% endhighlight %}

_Duplicity_ va sauvegarder à partir de la racine (`/`) tous les fichiers selon
les règles d'inclusion et d'exclusion définies dans `filelist.txt`. Ce fichier
contient simplement la liste des fichiers et répertoires à sauvegarder, ainsi
que ceux à exclure. Par exemple :

    /usr/local/bin/
    /home/rom/Maildir/
    /home/rom/.procmailrc
    - /var/www/blog/wp-content/cache/
    /var/www/blog/

_Attention :_ les fichiers et répertoires à exclure doivent apparaître **avant**
l'inclusion d'un répertoire parent. En effet, _duplicity_ s'arrête à la première
règle qui _matche_ un chemin donné pour déterminer s'il doit l'inclure ou
l'exclure.

Pour **restaurer** :

    duplicity restore file:///var/backups/duplicity/ /any/directory/

_(utiliser l'option `-t` pour restaurer à une date particulière)_

Pour **supprimer** les anciennes sauvegardes (ici de plus de 2 mois) :

    duplicity remove-older-than 2M file:///var/backups/duplicity/ --force


## Bases de données

Tout comme pour les fichiers, il est préférable de sauvegarder incrémentalement
les bases de données (seule une toute petite partie des données change d'un jour
à l'autre).

Une première solution serait d'utiliser la
[fonctionnalité-qui-va-bien][mysql-incremental] de votre [SGBD][].

[mysql-incremental]: http://stackoverflow.com/questions/4411057/how-to-do-a-incremental-backup-in-mysql
[sgbd]: http://fr.wikipedia.org/wiki/Syst%C3%A8me_de_gestion_de_base_de_donn%C3%A9es

Mais si le contenu de vos bases de données ne dépasse pas quelques _Go_ (ce qui
est très probable pour de l'auto-hébergement), _duplicity_ permet de faire
beaucoup plus simple.

Il suffit en effet de générer un _dump_ complet des bases de données vers des
fichiers `.sql` et d'inclure leur chemin dans la liste des fichiers à
sauvegarder. Et là, c'est magique, _duplicity_ va ne sauvegarder que les parties
de ces (gros) fichiers qui ont changées, grâce à [rsync][] et à son
[algorithme][rsync-algo] qui utilise des [rolling checksums][].

[rsync]: http://fr.wikipedia.org/wiki/Rsync
[rsync-algo]: http://en.wikipedia.org/wiki/Rsync#Algorithm
[rolling checksums]: http://en.wikipedia.org/wiki/Rolling_hash

Bien sûr, il ne faut pas compresser ces fichiers avant de les donner à manger à
_duplicity_ (sinon l'intégralité du fichier risque de changer) ; c'est lui qui
va s'en charger. De même, il vaut mieux éviter d'inclure dans les fichies _SQL_
des informations liées au _dump_, comme sa date de génération.

Pour exporter une base de données _MySQL_ par exemple :

    mysql -uroot -ppassword --skip-comments -ql my_database > my_database.sql


## Script

Il reste donc à écrire un script qui exporte les bases de données et qui appelle
`duplicity` avec la liste de ce qu'il y a à sauvegarder.

Voici un prototype, à sauvegarder dans `/usr/local/bin/backup` :

{% highlight bash %}
#!/bin/bash
BACKUP_HOME=/var/backups
TMP_DBDIR="$BACKUP_HOME/dbdump"
BACKUP_DIR="$BACKUP_HOME/duplicity"
MYSQLPW=mon_password_mysql
PASSPHRASE=ma_passphrase_de_chiffrement_des_backups
DATABASES='blog autre_base'
FILELIST="/usr/local/bin/
/home/rom/Maildir/
/home/rom/.procmailrc
- /var/www/blog/wp-content/cache/
/var/www/blog/
$TMP_DBDIR/"

# databases
mkdir -p "$TMP_DBDIR"
for dbname in $DATABASES
do
  printf "## Dump database $dbname...\n"
  mysqldump -uroot -p"$MYSQLPW" --skip-comments -ql "$dbname" \
    > "$TMP_DBDIR/$dbname.sql"
done

# duplicity
printf '## Backup using duplicity...\n'
unset mode
[ "$1" = full ] && mode=full && printf '(force full backup)\n'
mkdir -p "$BACKUP_DIR"
export PASSPHRASE
duplicity $mode / file://"$BACKUP_DIR"/ \
  --include-globbing-filelist <(echo "$FILELIST") --exclude '**'

printf '## Delete old backups\n'
duplicity remove-older-than 2M file://"$BACKUP_DIR"/ --force

# backups are encrypted, we can make them accessible
chmod +r "$BACKUP_DIR"/*.gpg

# remove temp files
rm "$TMP_DBDIR"/*.sql
{% endhighlight %}

Une fois configuré, ne pas oublier de tester : exécuter le script et restaurer
les données dans un répertoire de test, puis vérifier que tout est OK. Cette
vérification doit être effectuée de temps en temps : il serait dommage de
s'apercevoir, lorsqu'on en a besoin, que les backups sont inutilisables ou qu'un
répertoire important a été oublié.


## Cron



Pour démarrer automatiquement une sauvegarde **complète** le premier jour du mois et une **incrémentale** tous les autres jours, _cron_ est notre ami :

    sudo crontab -e

Ajouter les lignes :

    0 1 1    * * /usr/local/bin/backup full
    0 1 2-31 * * /usr/local/bin/backup

La première colonne correspond aux minutes, la deuxième aux heures : le script
sera donc exécuté à 1h du matin. La 3e correspond au numéro du jour dans le
mois. Les deux suivantes sont le numéro du mois dans l'année et le jour de la
semaine.

Il peut être préférable d'exécuter le script en priorité basse :

    0 1 1    * * nice -15 ionice -c2 /usr/local/bin/backup full
    0 1 2-31 * * nice -15 ionice -c2 /usr/local/bin/backup


## Copies

Il ne reste plus qu'à effectuer des copies des fichiers de backups ailleurs.

À partir d'une autre machine, le plus simple est d'utiliser `rsync` (sans
l'option `--delete` !) :

{% highlight bash %}
rsync -rvP --partial-dir=/my/local/tmpbackup --ignore-existing --stats \
    -h server:/var/backups/duplicity/ /my/local/backup/
{% endhighlight %}

`--ignore-existing` évite de récupérer des modifications malicieuses des backups
sur le serveur (ils ne sont pas censés être modifiés). Du coup, il faut aussi
faire attention à sauvegarder les transferts partiels ailleurs
(`--partial-dir`), sans quoi ils ne se termineront jamais.

Pour supprimer les anciens backups sur cette machine, c'est la même commande que
sur le serveur :

    duplicity remove-older-than 2M file:///my/local/backup/ --force


## Conclusion

La génération de sauvegardes à la fois incrémentales et chiffrées, y compris
pour les bases de données, font de _duplicity_ **une solution de backup idéale
pour l'auto-hébergement**.

Je l'utilise depuis plusieurs mois, et j'en suis très satisfait (même si je n'ai
pas encore eu besoin de restaurer les backups en situation réelle).

À vos backups !
