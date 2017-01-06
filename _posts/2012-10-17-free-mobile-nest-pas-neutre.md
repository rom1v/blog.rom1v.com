---
layout: post
title: Free Mobile n'est pas neutre
date: 2012-10-17 10:49:53+02:00
---

Intrigué par le rapport d'un utilisateur sur [RespectMyNet][] (qu'il présente
[en détail][aduf]), j'ai voulu vérifier par moi-même.

[respectmynet]: http://respectmynet.eu/504
[aduf]: http://www.aduf.org/viewtopic.php?t=259262

Sur un serveur hébergé chez moi sur une ligne Free ADSL (maximum ~120Ko/s en
upload), je crée un fichier totalement aléatoire dans un répertoire accessible
en HTTP, avec plusieurs extensions :

{% highlight bash %}
# crée un fichier de 2Mio
dd if=/dev/urandom of=random count=4000
# crée plusieurs liens avec des extensions différentes
for ext in avi ogg oga webm pdf mp4 mov; do ln -s random{,.$ext}; done
{% endhighlight %}

Sur mon téléphone avec Android 4.11, connecté au réseau _Free Mobile_ (sur une
antenne d'_Orange_), je partage la connexion avec mon PC (Paramètres, Connexion,
Sans fil et réseaux, Plus..., Partage de connexion, Via USB). Avec mon PC (sous
_Debian testing_), je m'y connecte.

Je télécharge alors chacun des fichiers :

{% highlight bash %}
for ext in avi ogg oga webm pdf mp4 mov; do wget monserveur/random.$ext; done
{% endhighlight %}

Le résultat est sans appel.

Pour les fichiers portant l'extension `.avi`, `.ogg`, `.mp4`, `.mov` (et sans
doute d'autres), le débit ne dépasse pas **5Ko/s** et est même souvent en
dessous d'**1Ko/s** :

    requête HTTP transmise, en attente de la réponse...200 OK
    Longueur: 2048000 (2,0M) [video/x-msvideo]
    Sauvegarde en : «random.avi»
    
     1% [                                       ] 34 486       757B/s  eta 43m 6s 

Par contre, pour les fichiers portant l'extension `.oga`, `.webm`, et `.pdf`, ça
fonctionne parfaitement :

    requête HTTP transmise, en attente de la réponse...200 OK
    Longueur: 2048000 (2,0M) [audio/ogg]
    Sauvegarde en : «random.oga»
    
    27% [=========>                             ] 570 300      104K/s  eta 14s    


Cette limite ne vient pas de ma ligne Free ADSL (qui fonctionne parfaitement par
ailleurs).  **_Free Mobile_ filtre donc les fichiers sur HTTP en fonction de
leur extension.**

Il reste à vérifier si cela se produit aussi sur une antenne _Free_ (mais je
n'en capte jamais).

**EDIT :** Le problème [ne se produit que](#comment-18) sur une antenne
_Orange_ ; sur une antenne _Free_, ça fonctionne normalement.
