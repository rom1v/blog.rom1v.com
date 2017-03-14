---
layout: post
title: Serveur-client
date: 2017-03-12 23:17:12+01:00
tags:
- planet-libre
---

L'objectif de ce billet est de parvenir à nous connecter à un serveur a priori
inaccessible derrière un [NAT][].

[NAT]: https://fr.wikipedia.org/wiki/Network_address_translation


## Client-serveur

De nos jours, [TCP][] est toujours utilisé en mode [client-serveur][] :

 - le **serveur** écoute _passivement_ sur un [port][] donné, en attente de la
   connexion d'un client ;
 - le **client** initie _activement_ une connexion vers un serveur.

[TCP]: https://fr.wikipedia.org/wiki/Transmission_Control_Protocol
[client-serveur]: https://fr.wikipedia.org/wiki/Client-serveur
[port]: https://fr.wikipedia.org/wiki/Port_%28logiciel%29

Une fois la connexion établie, cependant, le client et le serveur jouent
exactement le même rôle au niveau de la communication. Par contre, très souvent,
leur rôle applicatif dépend directement de celui qui a initié la connexion :

 - c'est le **client** [HTTP][] qui va envoyer une requête au **serveur** HTTP,
   pas l'inverse ;
 - c'est le **client** [SSH][] qui va ouvrir une session sur le **serveur** SSH…

[HTTP]: https://fr.wikipedia.org/wiki/Hypertext_Transfer_Protocol
[SSH]: https://fr.wikipedia.org/wiki/Secure_Shell

{: .center}
![ssh]({{ site.assets }}/serveur_client/ssh.png)

Ce fonctionnement paraît tellement naturel que "**client**" désigne bien souvent
à la fois celui qui initie la connexion et celui qui effectue des requêtes (au
serveur), alors que "**serveur**" désigne aussi bien la partie en écoute que
celle qui répondra aux requêtes (des clients).


## Puis vint le NAT…

Avec la [pénurie d'adresses IPv4][pénurie], le NAT s'est généralisé. Bien
souvent, un accès internet ne fournit qu'une seule adresse [IPv4][]. Les
différents ordinateurs partageant la même connexion ne sont alors pas
accessibles directement depuis l'extérieur (il est nécessaire d'[ouvrir des
ports][]).

[pénurie]: https://fr.wikipedia.org/wiki/%C3%89puisement_des_adresses_IPv4
[IPv4]: https://fr.wikipedia.org/wiki/IPv4
[ouvrir des ports]: https://fr.wikipedia.org/wiki/Redirection_de_port

Ainsi, derrière un NAT sans ports ouverts, un **serveur** ne sera pas accessible
publiquement. Par contre, un **client** pourra continuer à se connecter à
n'importe quel serveur public.

{: .center}
![ssh-nat]({{ site.assets }}/serveur_client/ssh-nat.png)


## Inversion des rôles

Il existe des situations pour lesquelles nous souhaitons qu'un logiciel joue le
rôle de **serveur** au niveau applicatif, afin de répondre aux requêtes des
clients, mais **client** au niveau de la communication, afin de passer les NATs
sans difficultés.

Par exemple, nous pouvons vouloir accéder, grâce à [VNC][] ou SSH, à un
ordinateur se trouvant derrière un NAT sur lequel, par hypothèse, nous n'avons
pas la main. Dans ce cas, seul le **serveur** (au sens applicatif) aura la
capacité d'ouvrir une connexion vers le **client**.

[vnc]: https://fr.wikipedia.org/wiki/Virtual_Network_Computing


### Logiciel dédié

Il est possible d'utiliser un logiciel spécialement conçu pour gérer cette
inversion des rôles. C'est le cas par exemple de [gitso][], qui _inverse_ le
protocole VNC afin de simplifier l'aide de novices à distance.

[gitso]: https://doc.ubuntu-fr.org/gitso

Cette solution a cependant l'inconvénient d'être très spécifique, nécessitant un
développement supplémentaire pour chaque protocole.


### Redirection de port distant via SSH

SSH permet d'ouvrir un tunnel pour rediriger un port d'une machine distance vers
une adresse quelconque.

Par exemple, après avoir démarré la redirection :

    ssh un_serveur_public -NR2222:localhost:22

toutes les connexions arrivant sur `un_serveur_public:2222` seront redirigées de
manière transparente vers `localhost:22` (sur la machine ayant initié le tunnel,
donc).

_(Cela nécessite d'activer `GatewayPorts yes` dans `/etc/ssh/sshd_config` sur
`un_serveur_public`.)_

De cette manière, un serveur SSH inaccessible derrière un NAT est rendu
accessible à travers un tunnel en passant par une machine publique
(`un_serveur_public`). Ainsi, il est possible de s'y connecter avec la
commande :

    ssh un_serveur_public -p2222

{: .center}
![ssh-remote]({{ site.assets }}/serveur_client/ssh-remote.png)

Cette stratégie fonctionne bien, mais elle nécessite que la machine qui souhaite
exposer un serveur grâce à un tunnel possède un accès SSH sur
`un_serveur_public`.

Si l'on souhaite aider quelqu'un grâce à la prise de contrôle de sa machine à
distance, il y a toutes les chances que cette personne n'ait pas d'accès SSH
vers une machine publiquement accessible. Il est alors possible de lui [créer un
compte restreint dédié][tuxicoman] sur un serveur que l'on contrôle, mais c'est
très intrusif, et il faut s'assurer de ne pas réduire la sécurité.

[tuxicoman]: https://tuxicoman.jesuislibre.net/2015/03/aide-a-une-noob-par-reverse-ssh.html

Mais en fait, **cette contrainte est superflue**.


### Redirections SOCAT

La redirection de port distant nécessite des permissions car, outre le fait
qu'elle est implémentée sur SSH, il serait déraisonnable d'autoriser n'importe
qui à ouvrir une [socket][] en écoute sur un port arbitraire d'une machine
distante.

[socket]: https://fr.wikipedia.org/wiki/Berkeley_sockets

Pour éviter ce problème, nous pouvons décomposer la redirection de port distant
fourni par SSH en deux parties :

 1. l'ouverture de la connexion vers `un_serveur_public`, redirigée vers
    l'adresse `localhost:22` dans l'exemple précédent ;
 2. l'ouverture d'une socket en écoute sur un port (`2222`) de la machine
    distante, redirigée vers la première connexion.

L'idée est de mettre en place le premier demi-tunnel sur la machine serveur, et
le second demi-tunnel, nécessitant des permissions, sur la machine publique,
contrôlée par le client.

Pour cela, nous allons utiliser l'outil `socat`, qui permet de relayer les
données entre deux sockets, quelque soit le rôle qu'elles aient joué lors de
l'initialisation.


#### Active-passive

Pour comprendre son utilisation, nous allons ouvrir grâce à _netcat_ (`nc`) une
socket TCP en écoute sur le port `5000` et nous y connecter :

{% highlight bash %}
# terminal 1
nc -l -p 5000
# terminal 2
nc localhost 5000
{% endhighlight %}

Toute entrée validée par un retour à la ligne dans le terminal 1 s'affichera
dans le terminal 2 (et vice-versa).

{: .center}
![nc]({{ site.assets }}/serveur_client/nc.png)


#### Passive-passive

Démarrons maintenant dans deux terminaux différents une socket en écoute sur les
ports `1111` et `2222` :

{% highlight bash %}
# terminal 1
nc -l -p 1111
# terminal 2
nc -l -p 2222
{% endhighlight bash %}

Pour les mettre en communication avec `socat`, dans un 3e terminal :

    socat tcp:localhost:1111 tcp:localhost:2222

{: .center}
![socat-connect]({{ site.assets }}/serveur_client/socat-connect.png)


#### Active-active

Inversement, il est possible de mettre en communication deux sockets _actives_
(sans compter sur leur [synchronisation][syn]). Pour cela, commençons par ouvrir
le serveur relai :

    socat tcp-listen:1111 tcp-listen:2222

[syn]: http://linuxfr.org/users/benoar/journaux/syn-c-est-pour-synchronisation

Puis connectons-y deux sockets :

{% highlight bash %}
# terminal 1
nc localhost 1111
# terminal 2
nc localhost 2222
{% endhighlight bash %}

{: .center}
![socat-connect]({{ site.assets }}/serveur_client/socat-listen.png)


#### Tunnel

Nous sommes maintenant prêts pour créer l'équivalent d'une redirection de port
distant SSH grâce à deux `socat`s, qui vont permettre d'inverser la connexion
uniquement sur la portion qui permet de traverser le NAT :

{% highlight bash %}
# sur un_serveur_public
socat tcp-listen:1234 tcp-listen:5678
# sur le serveur derrière le NAT
socat tcp:un_serveur_public:1234 tcp:localhost:22
# sur le client
ssh un_serveur_public -p5678
{% endhighlight %}

{: .center}
![ssh-socat]({{ site.assets }}/serveur_client/ssh-socat.png)
