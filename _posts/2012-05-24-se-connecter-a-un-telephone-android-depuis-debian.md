---
layout: post
title: Se connecter à un téléphone Android depuis Debian
date: 2012-05-24 22:41:09+02:00
---

Je décrivais récemment la marche à suivre pour [se connecter à un téléphone
Android à partir d'une distribution GNU/Linux][android_cli] (qui correspond à
[ce que dit la documentation officielle][doc]).

[android_cli]: {% post_url 2012-03-31-android-en-ligne-de-commande %}#tlphone
[doc]: https://developer.android.com/studio/run/device.html#setting-up

Pour résumer, il s'agit de créer un fichier `/etc/udev/rules.d/51-android.rules`
contenant :

    SUBSYSTEM=="usb", MODE="0666", GROUP="plugdev"

Mais ceci ne fonctionne pas sur [Debian][] (en tout cas ni sur [testing][] ni
sur [sid][]) :

[debian]: http://fr.wikipedia.org/wiki/Debian
[testing]: http://www.debian.org/releases/testing/
[sid]: http://www.debian.org/releases/sid/

    $ adb devices
    List of devices attached 
    ????????????    no permissions

En effet, contrairement aux autres distributions, _Debian_ possède un fichier
`/lib/udev/rules.d/91-permissions.rules` qui contient, entre autres :

    # usbfs-like devices
    SUBSYSTEM=="usb", ENV{DEVTYPE}=="usb_device", \
                                    MODE="0664"

Comme 91 > 51, ce fichier est parsé **après** notre fichier `51-android.rules`.

La solution est donc très simple : renommer `51-android.rules` en
`92-android.rules` afin que les permissions ne soient pas écrasées :

    sudo mv /etc/udev/rules.d/{51,92}-android.rules

_(ou en utilisant n'importe quel entier entre 92 et 99)_

Après cette modification, `udev` doit être redémarré :

    sudo service udev restart

et le téléphone débranché puis rebranché.

Et là, ça fonctionne :

    $ adb devices
    List of devices attached 
    040140621600C00D        device

_Merci à [unforgiven512][] qui m'a donné la solution (en anglais)._

[unforgiven512]: http://unforgivendevelopment.com/2011/05/20/udev-headaches-on-debian-testing-wheezy/
