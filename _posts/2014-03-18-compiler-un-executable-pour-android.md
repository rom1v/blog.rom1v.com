---
layout: post
title: Compiler un exécutable pour Android
date: 2014-03-18 23:39:52+01:00
---

Je vais présenter dans ce billet comment compiler un exécutable [ARM][] pour
_Android_, l'intégrer à un [APK][] et l'utiliser dans une application.

[arm]: https://fr.wikipedia.org/wiki/Architecture_ARM
[apk]: https://en.wikipedia.org/wiki/APK_%28file_format%29

À titre d'exemple, nous allons intégrer un programme natif, _udpxy_, dans une
application minimale de lecture vidéo.


## Contexte

Le framework multimédia d'_Android_ [ne supporte pas][media-formats] nativement
la lecture de flux [UDP multicast][] ([1][so1], [2][so2]).

[media-formats]: https://developer.android.com/guide/appendix/media-formats.html
[UDP multicast]: https://fr.wikipedia.org/wiki/Multicast
[so1]: http://stackoverflow.com/questions/8313995/udp-video-streaming-on-android
[so2]: http://stackoverflow.com/questions/8656199/open-udp-multicast-video-stream-on-android

Il est possible, pour y parvenir, d'utiliser des lecteurs alternatifs, par
exemple basés sur [ffmpeg][]/[libav][] (l'un est un [fork][ffmpeg-libav] de
l'autre) ou [libvlc][].

[ffmpeg]: https://fr.wikipedia.org/wiki/FFmpeg
[libav]: https://en.wikipedia.org/wiki/Libav
[ffmpeg-libav]: http://blog.pkh.me/p/13-the-ffmpeg-libav-situation.html
[libvlc]: https://bitbucket.org/edwardcw/libvlc-android-sample

Il existe par ailleurs un outil natif, sous licence [GPLv3][], relayant du
trafic UDP multicast vers du HTTP : [udpxy][]. N'importe quel client supportant
HTTP (comme le lecteur natif d'_Android_) peut alors s'y connecter. C'est cet
outil que nous allons utiliser ici.

[gplv3]: http://www.gnu.org/licenses/quick-guide-gplv3.fr.html
[udpxy]: http://www.udpxy.com/index-en.html


## udpxy


### Compilation classique

Avant de l'intégrer, comprenons son utilisation en le faisant tourner sur un
ordinateur classique (_Debian Wheezy 64 bits_ pour moi).

Il faut d'abord le [télécharger les sources][udpxy-sources], les extraire et
compiler :

[udpxy-sources]: http://www.udpxy.com/download-en.html

{% highlight bash %}
wget http://www.udpxy.com/download/1_23/udpxy.1.0.23-9-prod.tar.gz
tar xf udpxy.1.0.23-9-prod.tar.gz
cd udpxy-1.0.23-9/
make
{% endhighlight %}

Si tout se passe bien, nous obtenons (entre autres) un binaire `udpxy`.


### Test de diffusion

Pour tester, nous avons besoin d'une source UDP multicast. Ça tombe bien, VLC
peut la fournir. Pour obtenir le résultat attendu par _udpxy_, nous devons
diffuser vers une [adresse multicast][] (ici `239.0.0.1`). Par exemple, à partir
d'un fichier [MKV][] :

[adresse multicast]: https://fr.wikipedia.org/wiki/Multicast#Adresses_multicast_IPv4_r.C3.A9serv.C3.A9es
[mkv]: https://fr.wikipedia.org/wiki/Matroska

    
{% highlight bash %}
cvlc video.mkv ':sout=#udp{dst=239.0.0.1:1234}'
{% endhighlight %}

En parallèle, démarrons une instance d'`udpxy`, que nous venons de compiler :

{% highlight bash %}
./udpxy -p 8379
{% endhighlight %}

Cette commande va démarrer un proxy relayant de l'UDP multicast vers de l'HTTP,
écoutant sur le port 8379.

Dans un autre terminal, nous pouvons faire pointer VLC sur le flux ainsi
_proxifié_ :

{% highlight bash %}
vlc http://localhost:8379/udp/239.0.0.1:1234/
{% endhighlight %}

Normalement, le flux doit être lu correctement.

Remarquez qu'_udpxy_ pourrait très bien être démarré sur une autre machine (il
suffirait alors de remplacer `localhost` par son IP). Mais pour la suite, nous
souhaiterons justement exécuter _udpxy_ localement sur _Android_.

Bien sûr, avec VLC, nous n'aurions pas besoin d'_udpxy_. Le flux est lisible
directement avec la commande :

{% highlight bash %}
vlc udp://@239.0.0.1:1234/
{% endhighlight %}


## Android

_Notez que certains devices Android [ne supportent pas le
multicast][no-multicast], la réception de flux multicast ne fonctionnera donc
pas._

[no-multicast]: http://code.google.com/p/android/issues/detail?id=51195

Maintenant que nous avons vu comment fonctionne _udpxy_, portons-le sur
_Android_.

Notre but est de le contrôler à partir d'une application et le faire utiliser
par le lecteur vidéo natif.

Pour cela, plusieurs étapes sont nécessaires :

  1. obtenir un binaire ARM exécutable pour Android ;
  2. le packager avec une application ;
  3. l'extraire ;
  4. l'exécuter.


### Exécutable ARM


#### Pré-compilé

Pour obtenir un binaire ARM exécutable, le plus simple, c'est évidemment de le
récupérer déjà compilé, s'il est disponible ([c'est le cas][binaire] pour
_udpxy_). Dans ce cas, il n'y a rien à faire.

[binaire]: http://www.udpxy.com/download-en.html

Pour le tester, transférons-le sur le téléphone et exécutons-le :

{% highlight bash %}
adb push udpxy /data/local/tmp
adb shell /data/local/tmp/udpxy -p 8379
{% endhighlight %}

Si tout se passe bien, cette commande ne produit en apparence rien : elle attend
qu'un client se connecte. Pour valider le fonctionnement, si le téléphone est
sur le même réseau que votre ordinateur, vous pouvez utiliser cette instance
(ARM) d'_udpxy_ comme proxy entre la source multicast et un lecteur VLC local :

{% highlight bash %}
vlc http://xx.xx.xx.xx:8379/udp/239.0.0.1:1234/
{% endhighlight %}

Replacer `xx.xx.xx.xx` par l'ip du device, qu'il est possible d'obtenir ainsi :

{% highlight bash %}
adb shell netcfg | grep UP
{% endhighlight %}


#### Compilation ponctuelle

S'il n'est pas disponible, il va falloir le compiler soi-même à partir des
sources, ce qui nécessite le [NDK Android][ndk], fournissant des [chaînes de
compilation][] pré-compilées.

[ndk]: https://developer.android.com/ndk/index.html
[chaînes de compilation]: https://fr.wikipedia.org/wiki/Cha%C3%AEne_de_compilation

Il suffit alors d'[initialiser][standalone-toolchain] la variable
d'environnement `CC` pour pointer sur la bonne chaîne de compilation (adaptez
les chemins et l'architecture selon votre configuration) :

[standalone-toolchain]: https://developer.android.com/ndk/guides/standalone_toolchain.html

{% highlight bash %}
export NDK=~/android/ndk
export SYSROOT="$NDK/platforms/android-19/arch-arm"
export CC="$NDK/toolchains/arm-linux-androideabi-4.8/prebuilt/linux-x86_64/bin/arm-linux-androideabi-gcc --sysroot=$SYSROOT"
make
{% endhighlight %}

Bravo, vous venez de générer un binaire `udpxy` pour l'architecture ARM.


#### Compilation intégrée

La compilation telle que réalisée ci-dessus est bien adaptée à la génération
d'un exécutable une fois de temps en temps, mais s'intègre mal dans un système
de [_build_ automatisé][build auto]. En particulier, un utilisateur avec une
architecture différente devra adapter les commandes à exécuter.

[build auto]: https://fr.wikipedia.org/wiki/Moteur_de_production

Heureusement, le NDK permet une compilation plus générique.

Pour [cela][ndk-get-started], il faut créer un répertoire `jni` dans un projet
_Android_ (ou n'importe où d'ailleurs, mais en pratique c'est là qu'il est censé
être), y mettre les sources et écrire des _Makefiles_.

[ndk-get-started]: https://developer.android.com/ndk/guides/index.html

Créons donc un répertoire `jni` contenant les sources. Vu que nous les avons
déjà extraites, copions-les à la racine de `jni/` :

{% highlight bash %}
cp -rp udpxy-1.0.23-9/ jni/
cd jni/
{% endhighlight %}

Créons un _Makefile_ nommé [`Android.mk`][Android.mk] :

[Android.mk]: https://developer.android.com/ndk/guides/android_mk.html

{% highlight makefile %}
LOCAL_PATH := $(call my-dir)

include $(CLEAR_VARS)

LOCAL_MODULE    := udpxy
LOCAL_SRC_FILES := udpxy.c sloop.c rparse.c util.c prbuf.c ifaddr.c ctx.c \
                   mkpg.c rtp.c uopt.c dpkt.c netop.c extrn.c main.c

include $(BUILD_EXECUTABLE)
{% endhighlight %}

Puis compilons :

{% highlight bash %}
ndk-build
{% endhighlight %}

_`ndk-build` se trouve à la racine du NDK._

Le binaire sera généré dans `libs/armeabi/udpxy`.

Afin d'organiser les projets plus proprement, il vaut mieux mettre les sources
d'_udpxy_ et son `Android.mk` dans un sous-répertoire spécifique au projet (dans
`jni/udpxy/`). Dans ce cas, il faut rajouter un fichier `jni/Android.mk`
contenant :

{% highlight makefile %}
include $(call all-subdir-makefiles)
{% endhighlight %}


### Packager avec l'application

_Je suppose ici que vous savez déjà créer une application Android._

Nous devons maintenant intégrer le binaire dans l'[APK][]. Pour cela, il y a
principalement [deux solutions][res-assets] :

[apk]: https://en.wikipedia.org/wiki/APK_%28file_format%29
[res-assets]: http://stackoverflow.com/questions/5583608/difference-between-res-and-assets-directories

  * l'intégrer aux [ressources][res] (dans `res/raw/`) ;
  * l'intégrer aux [assets][] (dans `assets/`).

[res]: http://developer.android.com/guide/topics/resources/providing-resources.html
[assets]: http://developer.android.com/reference/android/content/res/AssetManager.html

Vu que les [projets _library_][library-projects] ne gèrent pas les _assets_,
nous allons utiliser une ressource _raw_.

[library-projects]: https://developer.android.com/studio/projects/index.html#ApplicationModules

Il faut donc copier le binaire dans `res/raw/`, à chaque fois qu'il est généré
(à automatiser donc).


### Extraire l'exécutable

L'exécutable est bien packagé avec l'application, et comme toutes les
_ressources_, nous pouvons facilement obtenir un [`InputStream`][res-stream] (le
fonctionnement est [similaire][asset-stream] pour les _assets_).

[res-stream]: http://developer.android.com/reference/android/content/res/Resources.html#openRawResource%28int%29
[asset-stream]: http://developer.android.com/reference/android/content/res/AssetManager.html#open%28java.lang.String%29

Mais pour l'exécuter en natif, le binaire doit être présent sur le système de
fichiers. Il faut donc le copier et lui donner les droits d'exécution. Sans la
gestion des exceptions, cela donne :

{% highlight java %}
// "/data/data/<package>/files/udpxy"
File target = new File(getFilesDir(), "udpxy")
InputStream in = getResources().openRawResource(R.raw.udpxy);
OutputStream out = new FileOutputStream(target);
// copy from R.raw.udpxy to /data/data/<package>/files/udpxy
FileUtils.copy(in, out);
// make the file executable
FileUtils.chmod(target, 0755);
{% endhighlight %}

Et les parties intéressantes de `FileUtils` :

{% highlight java %}
public static void copy(InputStream in, OutputStream os) throws IOException {
    byte[] buf = new byte[4096];
    int read;
    while ((read = (in.read(buf))) != -1) {
        os.write(buf, 0, read);
    }
}

public static boolean chmod(File file, int mode) throws IOException {
    String sMode = String.format("%03o", mode); // to string octal value
    String path = file.getAbsolutePath();
    String[] argv = { "chmod", sMode, path };
    try {
        return Runtime.getRuntime().exec(argv).waitFor() == 0;
    } catch (InterruptedException e) {
        throw new IOException(e);
    }
}
{% endhighlight %}


### Exécuter le programme natif

Maintenant que le binaire est disponible sur le système de fichiers, il suffit
de l'exécuter :

{% highlight java %}
String[] command = { udpxyBin.getAbsolutePath(), "-p", "8379" };
udpxyProcess = Runtime.getRuntime().exec(command);
{% endhighlight %}

Le lecteur vidéo pourra alors utiliser l'URI _proxifié_ comme source de
données :

{% highlight java %}
String src = UdpxyService.proxify("239.0.0.1:1234");
{% endhighlight %}


## Projets


### andudpxy

Je mets à disposition sous licence [GPLv3][] le projet _library_ `andudpxy`, qui
met en œuvre ce que j'ai expliqué ici :

[gplv3]: http://www.gnu.org/licenses/quick-guide-gplv3.fr.html

    git clone http://git.rom1v.com/andudpxy.git

(ou sur [github](https://github.com/rom1v/andudpxy))

Pour l'utiliser dans votre application, n'oubliez pas de référencer la _library_
et de déclarer le service `UdpxyService` dans votre `AndroidManifest.xml` :

{% highlight xml %}
<service android:name="com.rom1v.andudpxy.UdpxyService" />
{% endhighlight %}

Pour démarrer le [démon][] :

[démon]: https://fr.wikipedia.org/wiki/Daemon_%28informatique%29

{% highlight java %}
UdpxyService.startUdpxy(context);
{% endhighlight %}

et pour l'arrêter :

{% highlight java %}
UdpxyService.stopUdpxy(context);
{% endhighlight %}


### andudpxy-sample

J'ai également écrit une application minimale de lecture vidéo qui utilise cette
_library_ :

    git clone http://git.rom1v.com/andudpxy-sample.git

(ou sur [github](https://github.com/rom1v/andudpxy-sample))

C'est toujours utile d'avoir une application d'exemple censée fonctionner ;-)

L'adresse du flux UDP multicast à lire est écrite en dur dans `MainActivity` (et
le flux doit fonctionner lors du démarrage de l'activité) :

{% highlight java %}
private static final String ADDR = "239.0.0.1:1234";
{% endhighlight %}


### Compilation

Après avoir cloné les 2 projets dans un même répertoire parent, renommez les
`local.properties.sample` en `local.properties`, éditez-les pour indiquer le
chemin du SDK et du NDK.

Ensuite, allez dans le répertoire `andudpxy-sample`, puis exécutez :

{% highlight bash %}
ant clean debug
{% endhighlight %}

Vous devriez obtenir `bin/andudpxy-sample-debug.apk`.

Bien sûr, vous pouvez aussi les importer dans [Eclipse][] (ou un autre [IDE][])
et les compiler selon vos habitudes.

[eclipse]: https://fr.wikipedia.org/wiki/Eclipse_%28projet%29
[ide]: https://fr.wikipedia.org/wiki/Environnement_de_d%C3%A9veloppement


## Conclusion

Nous avons réussi à compiler et exécuter un binaire ARM sur _Android_, packagé
dans une application.

Ceci peut être utile pour exécuter du code déjà implémenté nativement pour
d'autres plates-formes, pour faire tourner un démon natif… Par exemple, le
projet [Serval][] (sur lequel j'ai un peu [travaillé][contrib]) utilise un démon
`servald`, qui tourne également sur d'autres architectures.

[serval]: http://www.servalproject.org/
[contrib]: /contrib/#servalbatphone

Ce n'est cependant pas la seule manière d'exécuter du code natif dans une
application : la plus courante est d'appeler des fonctions natives (et non un
exécutable) directement à partir de _Java_, en utilisant [JNI][]. L'une et
l'autre répondent à des besoins différents.

[jni]: https://fr.wikipedia.org/wiki/Java_Native_Interface
