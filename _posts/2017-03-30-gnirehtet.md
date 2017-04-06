---
layout: post
title: Gnirehtet
date: 2017-03-30 10:50:00+01:00
tags:
- planet-libre
---

Durant ces dernières semaines chez [Genymobile], j'ai développé un outil de
_reverse tethering_ pour Android, permettant aux téléphones (et aux tablettes)
d'utiliser la connexion internet de l'ordinateur sur lequel ils sont branchés,
sans accès _root_ (ni sur le téléphone, ni sur le PC). Il fonctionne sur
_GNU/Linux_, _Windows_ et _Mac OS_.

[Genymobile]: https://www.genymobile.com/

Nous avons décidé de le publier en open source, sous le nom de
[_gnirehtet_][gnirehtet].

_Oui, c'est un nom bizarre, jusqu'à ce qu'on réalise qu'il s'agit du résultat de
la commande [bash] :_

{% highlight bash %}
rev <<< tethering
{% endhighlight %}

[gnirehtet]: https://github.com/Genymobile/gnirehtet
[bash]: https://fr.wikipedia.org/wiki/Bourne-Again_shell


## Utilisation

Il suffit de télécharger la dernière [release], de l'extraire, et d'exécuter la
commande suivante sur le PC :

    ./gnirehtet rt

[release]: https://github.com/Genymobile/gnirehtet/releases/latest

Une fois activé, un logo en forme de clé apparaît dans la barre de statut du
téléphone :

{: .center}
![key]({{ site.assets }}/gnirehtet/key.png)

Lisez le fichier [README] pour plus de détails.

[README]: https://github.com/Genymobile/gnirehtet/blob/master/README.md


## Fonctionnement

Le projet est composé de deux parties :

 - une application Android (le client) ;
 - une application Java pour le PC (le serveur relais).

Le client s'enregistre en tant que VPN, de manière à intercepter tout le trafic
réseau du téléphone, sous la forme de `byte[]` de [paquets IPv4] bruts, qu'il
transmet alors vers le serveur relais sur une connexion [TCP] (établie
par-dessus [_adb_]).

Le serveur relais analyse les en-têtes des paquets, ouvre des connexions à
partir du PC vers les adresses de destinations demandées, et relaie le contenu
dans les deux sens en suivant les protocoles [UDP] et [TCP]. Il crée et renvoie
des paquets de réponse vers le client Android, qui les écrit sur l'interface
VPN.

D'une certaine manière, le serveur relais se comporte comme un [NAT], en cela
qu'il ouvre des connexions pour le compte d'autres machines qui n'ont pas accès
au réseau.  Cependant, il diffère des NAT standards dans la manière dont il
communique avec les clients, en utilisant un protocole spécifique (très simple)
sur une connexion TCP.

{: .center}
![archi]({{ site.assets }}/gnirehtet/archi.png)

Pour plus de détails, lisez la [page développeurs][DEVELOP].

[paquets IPv4]: https://en.wikipedia.org/wiki/IPv4#Packet_structure
[_adb_]: https://developer.android.com/studio/command-line/adb.html
[udp]: https://fr.wikipedia.org/wiki/User_Datagram_Protocol
[tcp]: https://fr.wikipedia.org/wiki/Transmission_Control_Protocol
[NAT]: https://fr.wikipedia.org/wiki/Network_address_translation
[DEVELOP]: https://github.com/Genymobile/gnirehtet/blob/master/DEVELOP.md


## Conception

Une fois que l'application est capable d'intercepter l'intégralité du traffic
réseau du téléphone, différentes approches sont possibles. Voici celles que j'ai
considérées.

_**TL;DR:** J'ai d'abord étudié l'utilisation d'un "TUN device" sur le
PC, mais ça ne répondait pas à nos besoins. J'ai ensuite voulu utiliser [SOCKS]
pour bénéficier des serveurs existants, mais des contraintes nous empêchaient de
relayer le trafic UDP. Alors j'ai implémenté [gnirehtet][gnirehtet]._


### TUN device

Lors de mes recherches pour savoir comment implémenter le _reverse tethering_,
j'ai d'abord trouvé des projets créant un [TUN device] sur l'ordinateur
([`vpn-reverse-tether`] and [`SimpleRT`]).

Cette conception fonctionne très bien, et a plusieurs avantages :

 - le traitement est effectué directement au niveau réseau, donc il n'y a pas
   besoin de traduction entre le niveau 3 et le niveau 5 du [modèle OSI] ;
 - tous les paquets sont retransmis, indépendamment de leur protocole de
   transport (ils sont donc [tous][protocols] supportés, là où _gnirehtet_
   ne supporte "que" [TCP] et [UDP]).

Cependant :

 - elle nécessite un accès _root_ sur l'ordinateur ;
 - elle ne fonctionne pas sur autre chose que _Linux_.

Il se peut néanmoins que ces applications répondent davantage à vos besoins.

[`SimpleRT`]: https://github.com/vvviperrr/SimpleRT
[`vpn-reverse-tether`]: https://github.com/google/vpn-reverse-tether
[TUN device]: https://en.wikipedia.org/wiki/TUN/TAP
[modèle OSI]: https://fr.wikipedia.org/wiki/Mod%C3%A8le_OSI
[protocols]: https://en.wikipedia.org/wiki/List_of_IP_protocol_numbers


### SOCKS

Afin d'éviter d'avoir à développer un serveur relais spécifique, ma première
idée était d'écrire un client qui parlait le protocole [SOCKS] (suivant le [RFC
1928]). Ainsi, il serait possible d'utiliser n'importe quel serveur SOCKS
existant, par exemple celui fourni par `ssh -D`.

Vous l'avez probablement déjà utilisé pour éviter le filtrage des pare-feux
en entreprise. Pour cela, démarrez le tunnel :

    ssh mon_serveur -ND1080

Puis configurez votre navigateur pour utiliser le proxy SOCKS `localhost:1080`.
N'oubliez pas d'activer la résolution DNS distante pour résoudre les noms de
domaine à partir de `mon_serveur` (dans _Firefox_, activez
`network.proxy.socks_remote_dns` dans `about:config`).

Malheureusement, l'implémentation d'[OpenSSH] ne [supporte pas UDP][sshmail],
même si le protocole [SOCKS5] lui-même le supporte. Et nous avons besoin d'UDP,
au moins pour les requêtes [DNS] (ainsi que pour [NTP]).

Si vous avez lu attentivement les deux paragraphes précédents, vous devriez vous
demander :

> Comment Firefox peut-il résoudre les noms de domaine à distance alors
> que le proxy SOCKS d'OpenSSH ne supporte même pas UDP ?

La réponse se trouve dans la [section 4] du RFC : l'adresse de destination
demandée peut être une IPv4, une IPv6 ou **un nom de domaine**. Par contre,
pour utiliser cette fonctionnalité, le client (par exemple _Firefox_) doit
savoir qu'il passe par un proxy (puisqu'il doit explicitement passer le nom de
domaine au lieu de le résoudre localement), alors que notre _reverse tethering_
doit être **transparent**.

Mais tout n'est pas perdu. Certes, _OpenSSH_ ne supporte pas UDP, mais ce n'est
qu'une implémentation spécifique, nous pourrions en utiliser une autre.
Malheureusement, [SOCKS5 relaie UDP sur UDP][stackoverflow], et les téléphones
et l'ordinateur communiquent sur _adb_ (grâce à `adb reverse`), qui ne supporte
pas non plus la redirection de ports UDP.

Peut-être que nous pourrions au moins relayer les requêtes DNS en les forçant à
[utiliser TCP][DNS over TCP], comme le fait [tsocks] :

> **tsocks** will normally not be able to send DNS queries through a SOCKS
> server since SOCKS V4 works on TCP and DNS normally uses UDP. Version 1.5 and
> up do however provide a method to force DNS lookups to use TCP, which then
> makes them proxyable.

Mais finalement, SOCKS n'est plus une solution aussi attirante pour implémenter
le _reverse tethering_.


[SOCKS]: https://fr.wikipedia.org/wiki/SOCKS
[SOCKS5]: https://fr.wikipedia.org/wiki/SOCKS#SOCKS_v5
[RFC 1928]: https://tools.ietf.org/html/rfc1928
[section 4]: https://tools.ietf.org/html/rfc1928#section-4
[DNS]: https://fr.wikipedia.org/wiki/Domain_Name_System
[OpenSSH]: https://fr.wikipedia.org/wiki/OpenSSH
[sshmail]: http://lists.mindrot.org/pipermail/openssh-unix-dev/2017-January/035662.html
[DNS over TCP]: http://www.bortzmeyer.org/dns-over-tcp.html
[NTP]: https://fr.wikipedia.org/wiki/Network_Time_Protocol
[stackoverflow]: http://stackoverflow.com/questions/41967217/why-does-socks5-require-to-relay-udp-over-udp
[tsocks]: https://linux.die.net/man/8/tsocks


### Gnirehtet

Par conséquent, j'ai développé à la fois le client et le serveur relais
manuellement.

Ce [billet de blog][geekstuff] et différents projets open source ([`SimpleRT`],
[`vpn-reverse-tether`], [`LocalVPN`] et [`ToyVpn`]) m'ont beaucoup aidé à comprendre
comment implémenter cette solution de _reverse tethering_.


## Conclusion

[_Gnirehtet_][gnirehtet] permet aux téléphones et tablettes Android d'utiliser
facilement la connection internet d'un ordinateur par USB, sans accès _root_.
C'est très utile quand vous ne pouvez pas accéder au réseau par un point d'accès
WiFi.

J'espère qu'il pourra être utile à certains d'entre vous.

[geekstuff]: http://www.thegeekstuff.com/2014/06/android-vpn-service/
[`LocalVPN`]: https://github.com/hexene/LocalVPN
[`ToyVpn`]: https://android.googlesource.com/platform/development/+/master/samples/ToyVpn/

_Cet article est également disponible en anglais sur [Medium]._

[Medium]: https://medium.com/@rom1v/gnirehtet-reverse-tethering-android-2afacdbdaec7
