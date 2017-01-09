---
layout: post
title: Android en ligne de commande
date: 2012-03-31 20:41:56+02:00
tags:
- planet-libre
---

Ce billet décrit comment développer et compiler des applications pour _Android_
en ligne de commande (en plus ou à la place d'[Eclipse][] avec [ADT][]).

[eclipse]: http://fr.wikipedia.org/wiki/Eclipse_%28logiciel%29
[adt]: http://developer.android.com/sdk/eclipse-adt.html

**EDIT :** Il a été écrit à l'époque où Android n'utilisait pas [Gradle][]. Il
est maintenant obsolète.

[gradle]: https://fr.wikipedia.org/wiki/Gradle

Je trouve que c'est utile dans certains cas ; par exemple, il vaut mieux
utiliser un script de _build_ automatique, que chacun pourra réutiliser, plutôt
qu'un [wizard][] sur un [IDE][] particulier.

[wizard]: http://fr.wikipedia.org/wiki/Assistant_%28logiciel%29
[ide]: http://fr.wikipedia.org/wiki/Environnement_de_d%C3%A9veloppement_int%C3%A9gr%C3%A9


## Installation

Avant tout, nous avons besoin du [SDK Android][sdk] :

[sdk]: http://developer.android.com/sdk/index.html

    wget http://dl.google.com/android/android-sdk_r17-linux.tgz
    tar xf android-sdk_r17-linux.tgz
    sudo mv /android-sdk-linux /opt

Sur un système 64 bits, `ia32-libs` est nécessaire (le SDK n'est disponible
qu'en 32 bits) :

    sudo apt-get install ia32-libs

[Java][] et [Ant][] doivent également être installés :

[java]: http://fr.wikipedia.org/wiki/OpenJDK
[ant]: http://fr.wikipedia.org/wiki/Apache_Ant

    sudo apt-get install openjdk-6-jdk ant

Pour accéder facilement aux outils du _SDK Android_, il est préférable de
rajouter leurs répertoires dans le `PATH`, en ajoutant la ligne suivante à la
fin de `~/.bashrc` :

    PATH="$PATH:/opt/android-sdk-linux/tools:/opt/android-sdk-linux/platform-tools"


## Configuration et téléchargement des packages

Ouvrir un nouveau terminal, et exécuter :

    android

Une fenêtre s'ouvre, permettant d'installer de nouveaux packages.

Sélectionner _"Available packages"_ et installer les _"Tools"_ ainsi que les
_"SDK Platforms"_ pour les versions souhaitées, puis cliquer sur _Install
packages_ :

{: .center}
![android-sdk-manager]({{ site.assets }}/android_cli/android-sdk-manager.png)

Il est aussi possible de créer un _AVD_ (_Android Virtual Device_) à partir du menu _Tools → Manage AVDs…_. Dans la fenêtre qui s'ouvre, cliquer sur _New…_, puis configurer le téléphone et lui donner un nom :

{: .center}
![android-avd]({{ site.assets }}/android_cli/android-avd.png)

Les _AVD_ sont indifféremment configurables ici ou à partir d'_Eclipse_ (au
final, ils seront stockés dans `~/.android/avd`).


## Projet en ligne de commande



Pour **créer un nouveau projet** :

    android create project \
        -p path> \
        -t target \
        -n name \
        -k package \
        -a activity

Par exemple :

    android create project -p HelloWorld -t 1 -n HelloWorld \
         -k com.rom1v.helloworld -a HelloWorld

Le projet généré est un _hello world_ fonctionnel.

Pour connaître la liste des _targets_ disponibles avec leurs _id_s :

    $ android list targets
    Available Android targets:
    ----------
    id: 1 or "android-7"
         Name: Android 2.1
         Type: Platform
         API level: 7
         Revision: 3
         Skins: WVGA854, WQVGA432, QVGA, HVGA, WQVGA400, WVGA800 (default)
         ABIs : armeabi
    ----------
    id: 2 or "android-13"
         Name: Android 3.2
         Type: Platform
         API level: 13
         Revision: 1
         Skins: WXGA (default)
         ABIs : armeabi

Pour **modifier un projet existant** :

    android update project \
        -p path \
        -t target \
        -n name

Par exemple, pour en changer la _target_ (ici celle qui a pour _id_ `2`) :

    android update project -p HelloWorld -t 2

Pour le compiler ou l'installer sur le téléphone, toutes les tâches _Ant_ ont
été générées (dans un fichier `build.xml` à la racine du projet). Voici les
principales :

    Android Ant Build. Available targets:
       help:      Displays this help.
       clean:     Removes output files created by other targets.
       compile:   Compiles project's .java files into .class files.
       debug:     Builds the application and signs it with a debug key.
       release:   Builds the application. The generated apk file must be
                  signed before it is published.
       install:   Installs/reinstalls the debug package onto a running
                  emulator or device.
                  If the application was previously installed, the
                  signatures must match.
       uninstall: Uninstalls the application from a running emulator or
                  device.

Par exemple, pour générer un APK signé avec une clé de debug :

    ant debug

Le fichier sera créé à l'emplacement `bin/HelloWorld-debug.apk`.


## Eclipse ET la ligne de commande

Dans la majorité des cas, nous voulons que le projet soit utilisable à la fois
dans _Eclipse_ (pour développer) et en ligne de commande (pour automatiser la
compilation, le déploiement…).


### Manipuler un projet Eclipse en ligne de commande

_Eclipse_ ne génère pas le script _Ant_ lors de la création d'un projet
_Android_. Heureusement, il est très simple de le générer manuellement :

    android update project -t target -n nom_du_projet \
         -p répertoire_du_projet


### Importer dans Eclipse un projet créé en ligne de commande

Si vous avez créé un projet entièrement en ligne de commande et que vous décidez
de l'importer par la suite dans _Eclipse_ (parce qu'un IDE, c'est quand même
bien pratique), c'est possible également.

Le projet restera dans le répertoire où il se trouve, donc si vous le voulez
dans votre workspace _Eclipse_, déplacez-le maintenant.

Ensuite, il ne faut pas _importer_, mais _créer_ un nouveau projet : _File → New
→ Android Project_, sélectionner _Create project from existing source_ et
indiquer le chemin du projet dans _Location_.


## Exécution

Un projet _Android_ s'exécute soit sur un téléphone physique, soit sur un
émulateur.



### Émulateur

Pour démarrer un émulateur :

    emulator -avd NomAVD

Par exemple :

    emulator -avd MyPhone


### Téléphone

Pour utiliser le téléphone branché en USB plutôt que l'émulateur, il est
nécessaire d'activer l'option _Paramètres → Applications → Développement →
Débogage USB_.

S'il n'est pas reconnu, c'est peut-être un problème de droits.  Dans ce cas,
trouver le _Vendor ID_ du matériel sur [cette page][devices] puis créer un
fichier `/etc/udev/rules.d/51-android.rules` ([sauf sous _Debian_][debian])
contenant :

    SUBSYSTEM=="usb", ATTR{idVendor}=="XXXX", MODE="0666", GROUP="plugdev"

(remplacez _XXXX_ par le _Vendor ID_)

[devices]: http://developer.android.com/studio/run/device.html
[debian]: {% post_url 2012-05-24-se-connecter-a-un-telephone-android-depuis-debian %}

Alternativement, il est possible de donner les droits à n'importe quel matériel.
Pour cela, il suffit de ne pas filtrer par _Vendor ID_, et d'écrire simplement :

    SUBSYSTEM=="usb", MODE="0666", GROUP="plugdev"


## Installation et désinstallation


### Ant

Il suffit d'utiliser les tâches _Ant_ `install` et `uninstall` :

    ant install

Dans ce cas, un seul périphérique doit être présent dans la liste :

    $ adb devices
    List of devices attached 
    emulator-5554	device


### Adb

`adb` (_Android Debug Bridge_) permet de communiquer avec le téléphone ou
l'émulateur.

Pour installer une application :

    adb install fichier.apk

Si à la fois le téléphone et l'émulateur sont détectés, il faut choisir grâce à
`-d` ou `-e` (respectivement) :

    adb -e install fichier.apk

Pour désinstaller, il faut connaître le nom du package (celui défini dans
_AndroidManifest.xml_).

    adb uninstall le.package.de.lapplication

Pour extraire le nom du package à partir d'un `fichier.apk` :

    aapt d badging fichier.apk | grep ^package


## Signature d'un APK



Pour signer une application, nous avons tout d'abord besoin d'un _keystore_. Pour en créer un :

    keytool -genkey -v -keystore ~/.android/rom.keystore -alias rom -validity 10000

(_Google_ recommande de choisir [une validité de plus de 25 ans][release-mode],
d'où les 10000 jours dans la commande ci-dessus)


Pour permettre à _Ant_ de signer, il suffit de lui indiquer la clé à utiliser
dans `ant.properties` (à la racine du projet) :

    key.store=/home/rom/.android/rom.keystore
    key.alias=rom

Il est également possible de pré-remplir les mots de passe :

    key.store.password=PASSWORD
    key.alias.password=PASSWORD

Ainsi, il signera automatiquement lors de l'exécution de :

    ant release


### Signature différée

Il est également possible de générer un APK non signé (par _Ant_, qui génère un
fichier `monprojet-unsigned.apk`), et de [le signer manuellement plus
tard][app-signing] :

    jarsigner -verbose -keystore ~/.android/rom.keystore monprojet-unsigned.apk rom

_La [clé de debug][debug-mode] générée par le SDK se trouve dans
`~/.android/debug.keystore`, son alias est `androiddebugkey` et son mot de passe
est `android`._

[app-signing]: https://developer.android.com/studio/publish/app-signing.html
[debug-mode]: https://developer.android.com/studio/publish/app-signing.html#debug-mode
[release-mode]: https://developer.android.com/studio/publish/app-signing.html#release-mode

Pour vérifier que la signature a bien fonctionné :

    jarsigner -verbose -verify -certs monprojet-unsigned.apk

(si c'est le cas, le fichier `monprojet-unsigned.apk` peut être renommé en
`monprojet-unaligned.apk`)

Il ne reste plus qu'à [aligner le fichier final][align] :

    zipalign -v 4 monprojet-unaligned.apk monprojet.apk

[align]: https://developer.android.com/studio/publish/app-signing.html#sign-manually


## Sources

Merci aux billets de [Freelan][] et de [DMathieu][] dont je me suis beaucoup
inspiré, en plus de la [documentation officielle][doc] (incontournable).

[freelan]: http://blog.freelan.org/2010/11/22/developper-pour-android-sans-utiliser-eclipse/
[dmathieu]: http://dmathieu.com/articles/development/deploy-an-android-application/
[doc]: https://developer.android.com/develop/index.html
