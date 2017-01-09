---
layout: post
title: Installer une application .apk sur Android à partir d'un PC
date: 2010-01-10 11:12:34+01:00
tags:
- planet-libre
- puf
---

J'expliquais, lors de [mes premières impressions d'Android 2 sur le Motorola
Milestone][milestone], qu'il était impossible d'installer un fichier `.apk` sans
accepter les conditions d'utilisation du _market_ ni configurer un compte
_gmail_.

[milestone]: {% post_url 2010-01-03-motorola-milestone-avec-android-2-mes-premieres-impressions %}

C'est en fait possible, grâce à l'outil `adb` du SDK Android, à partir la
connexion USB de l'ordinateur.


## Configurer le téléphone

Pour que l'outil d'installation puisse fonctionner, il faut activer l'option
_Paramètres > Applications > Développement > Débogage USB_ sur le téléphone.


## Configurer l'ordinateur


Il faut télécharger [Android SDK][], malheureusement [non libre][terms].

[Android SDK]: http://developer.android.com/sdk/index.html
[terms]: http://developer.android.com/sdk/terms.html

Sous GNU/Linux (plus précisément _Ubuntu 9.10_, adaptez selon votre
distribution), voici comment l'installer et permettre la reconnaissance du
Motorola Milestone ([plus d'infos ici][device]) :

[device]: http://developer.android.com/guide/developing/device.html

    sudo tar xzf android-sdk_r07-linux_x86.tgz -C /opt
    sudo ln -s /opt/android-sdk-linux_x86/tools/adb /usr/local/bin
    echo 'SUBSYSTEM=="usb", SYSFS{idVendor}=="22b8", MODE="0666"' |
        sudo tee /etc/udev/rules.d/51-android.rules
    sudo service udev reload

Si vous utilisez un système 64 bits, vous aurez besoin également besoin de
`ia32-libs` :

    sudo apt-get install ia32-libs

Vous pouvez maintenant brancher votre téléphone sur le PC en USB. Pour vérifier
que tout fonctionne :

    $ adb devices
    List of devices attached 
    040140621600C00D	device


## Installer une application

### En ligne de commande


Pour installer une application à partir de l'ordinateur, rien de plus simple :

    $ adb install -r ConnectBot-svn-r466-all.apk  
    2343 KB/s (642578 bytes in 0.267s)
    	pkg: /data/local/tmp/ConnectBot-svn-r466-all.apk
    Success

_(`-r` permet d'écraser si l'application est déjà installée)_


### À partir d'un gestionnaire de fichiers

Vous pouvez ensuite ajouter la possibilité d'installer les `.apk` graphiquement
à partir de votre gestionnaire de fichiers. Si vous utilisez **nautilus**, vous
pouvez jouer avec [nautilus-actions][]:

[nautilus-actions]: http://doc.ubuntu-fr.org/nautilus-actions

{: .center}
![install-apk]({{ site.assets }}/install-apk/install-apk.png)

Voici la commande de mon action nautilus (j'ouvre un xterm pour avoir le
résultat de l'installation, si vous avez mieux, n'hésitez pas) :

    xterm -T adb -e 'cd "%d" && /usr/local/bin/adb install -r "%f"; sleep 5'


## Conclusion

J'ai réinitialisé mon téléphone, il n'a plus de compte _gmail_ associé et je
n'ai pas accepté les conditions du _market_, ce qui ne m'empêche donc plus
d'installer les applications dont j'ai besoin.

Même pour ceux qui veulent garder leur compte ou utiliser le _market_, c'est
quand même plus rapide d'installer un `.apk` grâce à un clic-droit,
_"installer"_ à partir du gestionnaire de fichiers plutôt que de copier le
`.apk` sur la carte SD, débrancher le câble USB, aller dans une appli qui va
chercher le fichier et cliquer sur _"installer"_.
