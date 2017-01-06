---
layout: post
title: Utiliser Wireshark sous Debian
date: 2012-06-02 12:48:21+02:00
---

[Wireshark][] est un outil incontournable pour connaître les paquets qui
transitent sur le réseau. Mais on se retrouve vite bloqué à cause d'un problème
de droits.

[wireshark]: http://fr.wikipedia.org/wiki/Wireshark

En effet, en démarrant `wireshark` avec un compte utilisateur _non-root_,
l'interface graphique s'affiche, mais il est impossible de capturer les trames :
aucune interface réseau n'est disponible.

Devant ce problème, que fait l'utilisateur pressé ? Il démarre `wireshark` en
_root_, bien sûr _(c'est ce que je faisais sous Ubuntu)_ ! Eh bien pas de
chance :

    $ sudo wireshark
    No protocol specified
    
    (wireshark:27210): Gtk-WARNING **: cannot open display: :0


Déjà, c'est bien fait pour lui : on n'essaie pas de démarrer une interface
graphique en _root_ !

Mais comment faire alors ? En _non-root_ on ne peut pas capturer, en _root_ on
ne peut pas démarrer…

Alors on lit la doc, qui propose deux solutions :

    less /usr/share/doc/wireshark/README.Debian

**EDIT :** Une troisième solution, donnée [en commentaire][], me semble encore
meilleure :

[en commentaire]: {{ page.url }}#comment-10

{% highlight bash %}
sudo tcpdump -pni eth0 -s0 -U -w - | wireshark -k -i -
{% endhighlight %}


## Utiliser dumpcap pour capturer

Avec cette méthode, il faut d'abord capturer les paquets réseau et les sauver
dans un fichier, grâce à `dumpcat` (en _root_), puis ouvrir ce fichier dans
`wireshark` (_non-root_).

Pour démarrer la capture de l'interface `eth0` dans le fichier
`/tmp/mycapture` :

    sudo dumpcap -i eth0 -w /tmp/mycapture

Pour connaître la liste des interfaces réseau capturables :

    $ sudo dumpcap -D 
    1. eth0
    2. wlan0
    3. nflog (Linux netfilter log (NFLOG) interface)
    4. any (Pseudo-device that captures on all interfaces)
    5. lo

_Ctrl+C_ arrête la capture.

Le fichier généré n'est lisible que par _root_. Avant de l'ouvrir dans
_Wireshark_, il faut donc changer ses droits :

    sudo chmod +r /tmp/mycapture

C'est la méthode configurée par défaut sous _Debian_.


## Autoriser les utilisateurs non-root

Si on souhaite à la fois capturer et analyser à partir de _Wireshark_ (et
permettre les captures "en live"), sans passer par `dumpcap` en ligne de
commande, il faut autoriser les utilisateur _non-root_ à capturer des paquets.

Pour cela :

    sudo dpkg-reconfigure wireshark-common

Ce qui affiche :

     ┌─────────────────────┤ Configuration de wireshark-common ├──────────────────────┐
     │                                                                                │
     │ Dumpcap peut être installé afin d'autoriser les membres du groupe              │
     │ « wireshark » à capturer des paquets. Cette méthode de capture est préférable  │
     │ à l'exécution de Wireshark ou Tshark avec les droits du superutilisateur, car  │
     │ elle permet d'exécuter moins de code avec des droits importants.               │
     │                                                                                │
     │ Pour plus d'informations, veuillez consulter                                   │
     │ /usr/share/doc/wireshark-common/README.Debian.                                 │
     │                                                                                │
     │ Cette fonctionnalité constitue un risque pour la sécurité, c'est pourquoi      │
     │ elle est désactivée par défaut. En cas de doute, il est suggéré de la laisser  │
     │ désactivée.                                                                    │
     │                                                                                │
     │ Autoriser les utilisateurs non privilégiés à capturer des paquets ?            │
     │                                                                                │
     │                      <Oui>                         <Non>                       │
     │                                                                                │
     └────────────────────────────────────────────────────────────────────────────────┘



Après avoir répondu _Oui_, tous les utilisateurs du groupe `wireshark` (aucun,
par défaut) seront autorisés à capturer les paquets.

_**Remarque :** un programme non-root sera donc en théorie capable de savoir
tout ce qui passe sur le réseau (déjà qu'il est capable de [connaître tout ce
qui est tapé au clavier][keylogger])._

[keylogger]: {% post_url 2011-11-01-keylogger-sous-gnulinux-enregistrer-les-touches-tapees-au-clavier %}

Il ne reste donc plus qu'à ajouter son compte utilisateur au groupe
`wireshark` :

    sudo addgroup $USER wireshark

Cette modification ne sera prise en compte qu'après une reconnexion du compte
utilisateur (il faut donc fermer la session et en démarrer une nouvelle).
