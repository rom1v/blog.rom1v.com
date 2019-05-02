---
layout: post
title: Introducing scrcpy
date: 2018-03-08 12:00:00+01:00
lang: en
---

I developed an application to display and control Android devices connected on
USB. It does not require any root access. It works on GNU/Linux, Windows and Mac
OS.

{: .center}
[![scrcpy][screenshot]][scrcpy]

[screenshot]: https://raw.githubusercontent.com/Genymobile/scrcpy/master/assets/screenshot-debian-600.jpg

It focuses on:

 - **lightness** (native, displays only the device screen)
 - **performances** (30~60fps)
 - **quality** (1920×1080 or above)
 - **low latency** (70~100ms)
 - **low startup time** (~1 second to display the first image)
 - **non-intrusiveness** (nothing is left installed on the device)

Like my previous project, [_gnirehtet_], [Genymobile] accepted to open source
it: [scrcpy].

[Genymobile]: https://www.genymobile.com/
[_gnirehtet_]: {% post_url 2017-03-30-introducing-gnirehtet %}
[scrcpy]: https://github.com/Genymobile/scrcpy

You can [build, install and run][README] it.

[README]: https://github.com/Genymobile/scrcpy/blob/master/README.md


## How does scrcpy work?

The application executes a server on the device. The client and the server
communicate via a socket over an _adb tunnel_.

The server streams an [H.264] video of the device screen. The client decodes the
video frames and displays them.

The client captures input (keyboard and mouse) events, sends them to the server,
which injects them to the device.

The [documentation][develop] gives more details.

Here, I will detail several technical aspects of the application likely to
interest developers.

[H.264]: https://en.wikipedia.org/wiki/H.264/MPEG-4_AVC
[develop]: https://github.com/Genymobile/scrcpy/blob/master/DEVELOP.md


## Minimize latency

### No buffering

It takes time to encode, transmit and decode the video stream. To minimize
latency, we must avoid any additional delay.

For example, let's stream the screen with `screenrecord` and play it with VLC:

    adb exec-out screenrecord --output-format=h264 - | vlc - --demux h264

Initially, it works, but quickly the latency increases and frames are broken.
The reason is that VLC associates a [PTS] to frames, and buffers the stream to
play frames at some target time.

As a consequence, it sometimes prints such errors on _stderr_:

    ES_OUT_SET_(GROUP_)PCR  is called too late (pts_delay increased to 300 ms)

Just before I started the project, Philippe, a colleague who played with
[WebRTC], advised me to "manually" decode (using _FFmpeg_) and render frames, to
avoid any additional latency. This saved me from wasting time, it was the right
solution.

[Decoding][develop-decoder] the video stream to retrieve individual frames [with
FFmpeg][ffmpeg-decode] is rather [straightforward][decoder].

[PTS]: https://en.wikipedia.org/wiki/Presentation_timestamp
[WebRTC]: https://en.wikipedia.org/wiki/WebRTC
[develop-decoder]: https://github.com/Genymobile/scrcpy/blob/master/DEVELOP.md#decoder
[ffmpeg-decode]: https://www.ffmpeg.org/doxygen/3.4/group__lavc__encdec.html
[decoder]: https://github.com/Genymobile/scrcpy/blob/v1.0/app/src/decoder.c#L94-L110


### Skip frames

If, for any reason, the rendering is delayed, decoded frames are dropped so that
_scrcpy_ always displays the last decoded frame.

Note that this behavior may be changed with a [configuration flag][skip-frames]:

    mesonconf x -Dskip_frames=false

[skip-frames]: https://github.com/Genymobile/scrcpy/blob/v1.0/app/meson.build#L81


## Run a Java main on Android

Capturing the device screen requires some privileges, which are granted to
`shell`.

It is possible to execute Java code as `shell` on Android, by invoking
`app_process` from `adb shell`.


### Hello, world!

Here is a simple Java application:

{% highlight java %}
public class HelloWorld {
    public static void main(String... args) {
        System.out.println("Hello, world!");
    }
}
{% endhighlight %}

Let's compile and _dex_ it:

    javac -source 1.7 -target 1.7 HelloWorld.java
    "$ANDROID_HOME"/build-tools/27.0.2/dx \
        --dex --output classes.dex HelloWorld.class

Then, we push `classes.dex` to an Android device:

    adb push classes.dex /data/local/tmp/

And execute it:

    $ adb shell CLASSPATH=/data/local/tmp/classes.dex app_process / HelloWorld
    Hello, world!


### Access the Android framework

The application can access the Android framework at runtime.

For example, let's use `android.os.SystemClock`:

{% highlight java %}
import android.os.SystemClock;

public class HelloWorld {
    public static void main(String... args) {
        System.out.print("Hello,");
        SystemClock.sleep(1000);
        System.out.println(" world!");
    }
}
{% endhighlight %}

We link our class against `android.jar`:

    javac -source 1.7 -target 1.7 \
        -cp "$ANDROID_HOME"/platforms/android-27/android.jar
        HelloWorld.java

Then run it as before.

_Note that scrcpy also needs to access [hidden methods] from the framework. In
that case, linking against `android.jar` is not sufficient, so it uses
[reflection]._

[hidden methods]: https://github.com/Genymobile/scrcpy/blob/master/DEVELOP.md#hidden-methods
[reflection]: https://en.wikipedia.org/wiki/Reflection_(computer_programming)


### Like an APK

The execution also works if `classes.dex` is embedded in a zip/jar:

    jar cvf hello.jar classes.dex
    adb push hello.jar /data/local/tmp/
    adb shell CLASSPATH=/data/local/tmp/hello.jar app_process / HelloWorld

You know an example of a zip containing `classes.dex`? An [APK]!

Therefore, it works for any installed APK containing a class with a main method:

    $ adb install myapp.apk
    …
    $ adb shell pm path my.app.package
    package:/data/app/my.app.package-1/base.apk
    $ adb shell CLASSPATH=/data/app/my.app.package-1/base.apk \
        app_process / HelloWorld

[apk]: https://en.wikipedia.org/wiki/Android_application_package

### In scrcpy

To simplify the build system, I decided to build the server as an APK using
[gradle], even if it's not a real Android application: _gradle_ provides tasks
for running tests, checking style, etc.

Invoked that way, the server is authorized to capture the device screen.

[gradle]: https://gradle.org/


## Improve startup time

### Quick installation

Nothing is required to be installed on the device by the user: at startup, the
client is responsible for executing the server on the device.

We saw that we can execute the main method of the server from an APK either:
 - installed, or
 - pushed to `/data/local/tmp`.

Which one to choose?

    $ time adb install server.apk
    …
    real    0m0,963s
    …

    $ time adb push server.apk /data/local/tmp/
    …
    real    0m0,022s
    …

So I decided to push.

_Note that  `/data/local/tmp` is readable and writable by `shell`, but not
world-writable, so a malicious application may not replace the server just
before the client executes it._


### Parallelization

If you executed the _Hello, world!_ in the previous section, you may have
noticed that running `app_process` takes some time: `Hello, World!` is not
printed before some delay (between 0.5 and 1 second).

In the client, initializing SDL also takes some time.

Therefore, these initialization steps [have been parallelized][improve-startup].

[improve-startup]: https://github.com/Genymobile/scrcpy/commit/90a46b4c45637d083e877020d85ade52a9a5fa8e


## Clean up the device

After usage, we want to remove the server (`/data/local/tmp/scrcpy-server.jar`)
from the device.

We could remove it on exit, but then, it would be left on device disconnection.

Instead, once the server is opened by `app_process`, _scrcpy_ [unlink]s (`rm`)
it.  Thus, the file is present only for less than 1 second (it is removed even
before the screen is displayed).

The file itself (not its name) is actually removed when the last associated open file
descriptor is closed (at the latest, when `app_process` dies).

[unlink]: http://man7.org/linux/man-pages/man2/unlink.2.html


## Handle text input

Handling input received from a keyboard is more complicated than I thought.


### Events

There are 2 kinds of "keyboard" events:

 - [key][keyboard-event] events,
 - [text input][textinput-event] events.

Key events [provide][keyevents] both the _scancode_ (the physical location of a
key on the keyboard) and the _keycode_ (which depends on the keyboard layout).
Only _keycodes_ are used by _scrcpy_ (it doesn't need the location of physical
keys).

[keyevents]: https://wiki.libsdl.org/CategoryKeyboard

However, key events are not sufficient to handle [text
input][textinput-tutorial]:

> Sometimes it can take multiple key presses to produce a character. Sometimes a
> single key press can produce multiple characters.

Even simple characters may not be handled easily with key events, since they
depend on the layout. For example, on a French keyboard, typing `.` _(dot)_
generates `Shift`+`;`.

Therefore, _scrcpy_ forwards key events to the device only for a [limited set of
keys][keyset]. The remaining are handled by _text input_ events.

[keyboard-event]: https://wiki.libsdl.org/SDL_KeyboardEvent
[textinput-event]: https://wiki.libsdl.org/SDL_TextInputEvent
[textinput-tutorial]: https://wiki.libsdl.org/Tutorials/TextInput
[keyset]: https://github.com/Genymobile/scrcpy/blob/v1.0/app/src/convert.c#L75-L87


### Inject text

On the Android side, we may not inject text directly (injecting a [`KeyEvent`]
created by [the relevant constructor][keyevent-ctor] does not work).
Instead, we can retrieve a list of `KeyEvent`s to generate for a `char[]`, using
[`getEvents(char[])`][getEvents].

For example:

{% highlight java %}
char[] chars = {'?'};
KeyEvent[] events = charMap.getEvents(chars);
{% endhighlight %}

Here, `events` is initialized with an array of 4 events:

 1. press `KEYCODE_SHIFT_LEFT`
 2. press `KEYCODE_SLASH`
 3. release `KEYCODE_SLASH`
 4. release `KEYCODE_SHIFT_LEFT`

[Injecting][inject] those events correctly generates the char `'?'`.

[`KeyEvent`]: https://developer.android.com/reference/android/view/KeyEvent.html
[keyevent-ctor]: https://developer.android.com/reference/android/view/KeyEvent.html#KeyEvent(long,%20java.lang.String,%20int,%20int)
[getEvents]: https://developer.android.com/reference/android/view/KeyCharacterMap.html#getEvents(char[])
[inject]: https://github.com/Genymobile/scrcpy/blob/v1.0/server/src/main/java/com/genymobile/scrcpy/EventController.java#L103-L107


### Handle accented characters

Unfortunately, the previous method only works for ASCII characters:

{% highlight java %}
char[] chars = {'é'};
KeyEvent[] events = charMap.getEvents(chars);
// events is null!!!
{% endhighlight %}

I first thought there was no way to inject such events from there, until I
discussed with Philippe (yes, the same as earlier), who knew the solution: it
works when we decompose the characters using [combining diacritical dead key
characters][deadkeys].

Concretely, instead of injecting `"é"`, we inject `"\u0301e"`:

{% highlight java %}
char[] chars = {'\u0301', 'e'};
KeyEvent[] events = charMap.getEvents(chars);
// now, there are events
{% endhighlight %}

Therefore, to support accented characters, _scrcpy_ attempts to [decompose] the
characters using [`KeyComposition`].

[deadkeys]: https://source.android.com/devices/input/key-character-map-files#behaviors
[`KeyComposition`]: https://github.com/Genymobile/scrcpy/blob/v1.0/server/src/main/java/com/genymobile/scrcpy/KeyComposition.java
[decompose]: https://github.com/Genymobile/scrcpy/blob/v1.0/server/src/main/java/com/genymobile/scrcpy/EventController.java#L97

_EDIT: Accented characters do not work with the virtual keyboard Gboard (the
default Google keyboard), but work with the default (AOSP) keyboard and
SwiftKey._


## Set a window icon

The application window may have an icon, used in the title bar (for some desktop
environments) and/or in the desktop taskbar.

The window icon must be set from an [`SDL_Surface`] by [`SDL_SetWindowIcon`].
Creating the surface with the icon content is up to the developer. For exemple,
we could decide to load the icon from a PNG file, or directly from its raw
pixels in memory.

Instead, another colleague, [Aurélien], suggested I use the [XPM] image format,
which is also a valid C source code: [`icon.xpm`].

Note that the image is not the content of the variable `icon_xpm` declared in
`icon.xpm`: it's the whole file! Thus, `icon.xpm` may be both directly opened in
[Gimp] and included in C source code:

{% highlight c %}
#include "icon.xpm"
{% endhighlight %}

As a benefit, we directly "recognize" the icon from the source code, and we can
patch it easily: in debug mode, the [icon color][icon-debug] is changed.

[`SDL_Surface`]: https://wiki.libsdl.org/SDL_Surface
[`SDL_SetWindowIcon`]: https://wiki.libsdl.org/SDL_SetWindowIcon
[Aurélien]: http://agateau.com/
[xpm]: https://en.wikipedia.org/wiki/X_PixMap
[`icon.xpm`]: https://github.com/Genymobile/scrcpy/blob/v1.0/app/src/icon.xpm
[gimp]: https://www.gimp.org/
[icon-debug]: https://github.com/Genymobile/scrcpy/blob/v1.0/app/src/tinyxpm.c#L34-L37


## Conclusion

Developing this project was an awesome and motivating experience. I've learned a
lot (I never used _SDL_ or _libav/FFmpeg_ before).

The resulting application works better than I initially expected, and I'm happy
to have been able to open source it.

_Discuss on [reddit] and [Hacker News]._

[reddit]: https://www.reddit.com/r/Android/comments/834zmr/introducing_scrcpy_an_app_to_display_and_control/
[Hacker News]: https://news.ycombinator.com/item?id=16544977
