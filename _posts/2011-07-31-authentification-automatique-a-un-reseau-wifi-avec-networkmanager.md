---
layout: post
title: Authentification automatique à un réseau WiFi avec NetworkManager
date: 2011-07-31 11:43:34+02:00
tags:
- planet-libre
- puf
---

Certains réseaux WiFi sont ouverts (sans clé de sécurité) mais nécessitent une
authentification. C'est souvent le cas des points d'accès dans les gares, les
hôtels, les campings… Cela concerne également les réseaux ouverts tels que
_FreeWifi_.

Une fois connecté à un tel réseau, lorsqu'avec votre navigateur vous tentez
d'accéder à n'importe quel site, vous êtes redirigé vers une page
d'authentification demandant votre identifiant et votre mot de passe (parfois il
ne s'agit que d'accepter des conditions d'utilisation). Après avoir renseigné
ces informations, vous êtes authentifié et pouvez accéder à Internet
normalement.

Mais il faut avouer que s'authentifier manuellement à chaque connexion est
pénible. D'autant plus que la redirection HTTP vers la page d'authentification
ne fonctionne… que pour HTTP. Ainsi, alors que vous êtes connecté au réseau
Wifi, votre client mail ne parviendra à récupérer les mails, votre client XMPP
n'arrivera pas à se connecter au serveur… mais sans message indiquant la cause
du problème.

Le but de ce billet est de mettre en place une authentification automatique lors
de la connexion au réseau.


## Authentification en ligne de commande

La première étape est de pouvoir réaliser cette authentification en ligne de
commande, à partir de l'identifiant et du mot de passe. C'est très simple, il
suffit d'imiter ce que fait le navigateur lors du clic sur le bouton _Valider_.

Pour cela, deux choses sont nécessaires : l'URL de la page de validation
d'authentification et les champs de formulaire qu'elle utilise.

Pour les connaître, il faut regarder le code source de la page sur laquelle vous
êtes redirigés, en particulier la balise `form`. Voici un exemple de ce que vous
pouvez obtenir _(le HTML n'est pas toujours super propre sur ce genre de
pages)_ :

{% highlight html %}
<form method="post" action="http://10.9.0.1:8000/">
Login <input name="auth_user" type="text">
Password <input name="auth_pass" type="password">
<input type="checkbox" name="regagree" value="valeur"
onClick="ChangeStatut(this.form)"> J'accepte le règlement
<input name="redirurl" type="hidden"
value="http://www.google.com/search?ie=UTF-8">
<input type="submit" name="accept" value="Continuer" disabled>
</form>
{% endhighlight %}

Tout y est. La valeur de l'attribut `action` est l'URL de validation, et le nom
des champs utilisés est dans l'attribut `name` de chaque balise `input`.

Dans cet exemple, seuls `auth_user` et `auth_pass` semblent utiles, mais parfois
le serveur effectue des vérifications (étranges) supplémentaires. Ici, il
vérifie qu'il y a bien un attribut `accept` qui vaut `Continuer` (allez savoir
pourquoi).

À partir de ces champs, nous allons construire la chaîne des paramètres sous la
forme :

    champ1=valeur1&champ2=valeur2&champ3=valeur3

et l'envoyer au serveur en [`POST`][post], par exemple grâce à la commande
`POST` _(en majuscules, ça surprend un peu pour une commande shell)_ :

[post]: http://fr.wikipedia.org/wiki/HTTP#M.C3.A9thodes

{% highlight bash %}
POST http://10.9.0.1:8000/ <<<
'auth_user=IDENTIFIANT&auth_pass=MOT_DE_PASSE&accept=Continuer'
{% endhighlight %}



Si la page d'authentification est en HTTPS, il faudra installer le paquet
`libcrypt-ssleay-perl`, ou alors utiliser `wget` :

{% highlight bash %}
wget -qO- https://10.9.0.1:8000/
--post-data='auth_user=IDENTIFIANT&auth_pass=MOT_DE_PASSE&accept=Continuer'
{% endhighlight %}


Voilà, nous avons reproduit en ligne de commande le comportement du navigateur
pour l'authentification. Nous devons maintenant faire en sorte que cette
commande soit exécutée dès la connexion au réseau WiFi.


## Exécuter un script lors de la connexion

_NetworkManager_ (le gestionnaire de connexion par défaut d'_Ubuntu_) permet
d'exécuter des scripts lors de la connexion ou la déconnexion. Pour cela, il
suffit de placer le script dans `/etc/NetworkManager/dispatcher.d/` et de le
rendre exécutable.

Le script est appelé avec deux paramètres :

  * `$1` : l'interface réseau concernée par la connexion ou la déconnexion
    (`wlan0` par exemple) ;
  * `$2` ayant pour valeur soit `up` (pour la connexion), soit `down` (pour la
    déconnexion).

Nous voulons exécuter la commande `POST` uniquement lors de la connexion de
`wlan0`, et seulement pour le réseau concerné (par exemple celui ayant le nom
`MonLieuDeVacances`).

Il est possible de récupérer le nom du réseau (l'ESSID) auquel nous sommes
connectés grâce à `iwconfig` :

{% highlight bash %}
iwconfig wlan0 | grep -o 'ESSID:".*$' | sed 's/^ESSID:"\(.*\)".*$/\1/'
{% endhighlight %}

Il faut donc créer un script dans `/etc/NetworkManager/dispatcher.d/10auth` :

    sudo vi /etc/NetworkManager/dispatcher.d/10auth

ayant cette structure :

{% highlight bash %}
#!/bin/bash
if [ "$1 $2" = 'wlan0 up' ]
then
    essid=$(iwconfig wlan0 | grep -o 'ESSID:".*$' | sed
's/^ESSID:"\(.*\)".*$/\1/')
    case "$essid" in
        'MonLieuDeVacances')
            POST http://10.9.0.1:8000/ <<< 'auth_user=IDENTIFIANT&auth_pass=MOT_DE_PASSE&accept=Continuer' ;;
        'MaGare')
            POST http://192.168.0.1 <<< 'accept_cgu=1' ;;
    esac
fi
{% endhighlight %}

Et le rendre exécutable :

    sudo chmod +x /etc/NetworkManager/dispatcher.d/10auth


## Script pour FreeWifi

Les pages d'authentification varient d'un réseau à l'autre, il faut donc adapter
les paramètres de connexion selon le service utilisé.

Voici le script à utiliser (en adaptant votre identifiant et votre mot de passe)
pour le réseau _FreeWifi_ (très connu) :

{% highlight bash %}
#!/bin/bash
if [ "$1 $2" = 'wlan0 up' ]
then
    essid=$(iwconfig wlan0 | grep -o 'ESSID:".*$' | sed 's/^ESSID:"\(.*\)".*$/\1/')
    case "$essid" in
        'FreeWifi')
            wget -qO- https://wifi.free.fr/Auth --post-data='login=IDENTIFIANT&password=MOT_DE_PASSE' ;;
    esac
fi
{% endhighlight %}


## Tunnel SSH

Ces réseaux ouverts, gérant éventuellement une authentification HTTP, ne sont
pas chiffrés : n'importe qui écoutant ce qui transite dans les airs pourra
récupérer tout le contenu de votre trafic.  Si vous avez un ordinateur allumé
chez vous (sur un réseau "sûr") accessible en SSH, je vous conseille de faire
passer toutes les connexions dans un tunnel chiffré.

Le principe est simple : dès que vous accédez à un serveur (par exemple en
tapant l'URL dans un navigateur web), l'ordinateur ne va pas s'y connecter
directement, il va transmettre les informations en passant par un tunnel chiffré
à votre serveur SSH, qui lui va s'y connecter, et vous renvoyer la page à
travers le tunnel. Techniquement, le tunnel est un [proxy SOCKS][] écoutant sur
un port local (par exemple `localhost:3128`).

[proxy SOCKS]: http://fr.wikipedia.org/wiki/SOCKS

Pour démarrer le tunnel :

    ssh monserveur -CND3128

Pour configurer le système afin qu'il utilise le tunnel SSH, Système →
Préférences → Serveur mandataire (`gnome-network-properties`), puis configurer
comme sur la capture d'écran :

{: .center}
![proxy]({{ site.assets }}/auth_networkmanager/proxy.png)

Dans l'onglet _Hôtes à ignorer_, rajouter l'adresse de la page
d'authentification.

Ainsi, toutes les connexions des logiciels utilisant les paramètres proxy du
système passeront par le tunnel. Il est également possible de configurer ceci
dans chaque logiciel individuellement (s'ils le proposent).

Pour _Firefox_, il est également recommandé dans [about:config](about:config) de
passer la variable `network.proxy.socks_remote_dns` à `true`, afin que les DNS
soient résolus également de l'autre côté du tunnel (sur le réseau "sûr").

Vous trouverez plus d'infos sur mon [billet concernant SSH][ssh].

[ssh]: {% post_url 2008-08-27-presentation-de-ssh %}


## Conclusion

La connexion à des points d'accès WiFi publics demandant à chaque fois une
authentification ou une acceptation des conditions d'utilisation devient
rapidement insupportable. Il est donc appréciable de l'automatiser.

De plus, ces réseaux ne sont pas "sûrs", n'importe qui peut écouter le trafic.
Il est donc nécessaire de le chiffrer en passant par un réseau de confiance, par
exemple avec un tunnel SSH.
