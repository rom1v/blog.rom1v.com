---
layout: post
title: Introducing USBaudio
date: 2019-06-20 09:40:00+02:00
lang: en
---

## Forwarding audio from Android devices

In order to support audio forwarding in [scrcpy], I first implemented an
experimentation on a separate branch (see [issue #14]). But it was too hacky
and fragile to be merged (and it does not work on all platforms).

So I decided to write a separate tool: [USBaudio].

It works on _Linux_ with _PulseAudio_.

[scrcpy]: {% post_url 2018-03-08-introducing-scrcpy %}
[issue #14]: https://github.com/Genymobile/scrcpy/issues/14#issuecomment-375103051
[USBaudio]: https://github.com/rom1v/usbaudio


## How to use USBaudio

First, you need to [build it][build] (follow the instructions).

[build]: https://github.com/rom1v/usbaudio/tree/master/README.md#build

Plug an Android device. If USB debugging is enabled, just execute:

{% highlight bash %}
usbaudio
{% endhighlight %}

If USB debugging is disabled (or if multiple devices are connected), you need to
specify a device, either by their _serial_ or _vendor id_ and _product_id_ (as
printed by `lsusb`):

{% highlight bash %}
usbaudio -s 0123456789abcdef
usbaudio -d 18d1:4ee2
{% endhighlight %}

The audio should be played on the computer.

If it's stuttering, try increasing the _live caching_ value (at the cost of a
higher latency):

{% highlight bash %}
# default is 50ms
usbaudio --live-caching=100
{% endhighlight %}

_Note that it can also be directly captured by [OBS]:_

{: .center}
![obs]({{ site.assets }}/usbaudio/obs.png)

[OBS]: https://obsproject.com/

## How does it work?

USBaudio executes 3 steps successively:

 1. It enables audio accessory on the device (by sending [AOA] requests via
    [libusb]), so that the audio is forwarded over USB. If it works,
    _PulseAudio_ (or _ALSA_) on the computer should detect a new audio input
    source.
 2. It retrieves the _PulseAudio_ input source id associated to the Android
    device (via [libpulse]).
 3. It [exec]s VLC to play audio from this input source.

[AOA]: https://source.android.com/devices/accessories/aoa2
[libusb]: https://libusb.info/
[libpulse]: https://freedesktop.org/software/pulseaudio/doxygen/
[exec]: https://linux.die.net/man/3/exec

_Note that enabling audio accessory changes the [USB device product id][pid],
so it will close any adb connection (and scrcpy). Therefore, you should enable
audio forwarding **before** running scrcpy._

[pid]: https://source.android.com/devices/accessories/aoa2#detecting-android-open-accessory-20-support


## Manually

To only enable audio accessory without playing:

{% highlight bash %}
usbaudio -n
usbaudio --no-play
{% endhighlight %}

The audio input sources can be listed by:

{% highlight bash %}
pactl list short sources
{% endhighlight %}

For example:

```
$ pactl list short sources
...
5   alsa_input.usb-LGE_Nexus_5_05f5e60a0ae518e5-01.analog-stereo     module-alsa-card.c  s16le 2ch 44100Hz   RUNNING
```

Use the number (here `5`) to play it with VLC:

{% highlight bash %}
vlc -Idummy --live-caching=50 pulse://5
{% endhighlight %}

Alternatively, you can use ALSA directly:

{% highlight bash %}
cat /proc/asound/cards
{% endhighlight %}

For example:

```
$ cat /proc/asound/cards
...
 1 [N5             ]: USB-Audio - Nexus 5
                      LGE Nexus 5 at usb-0000:00:14.0-4, high speed
```

Use the device number (here `1`) as follow:

{% highlight bash %}
vlc -Idummy --live-caching=50 alsa://hw:1
{% endhighlight %}

If it works manually but not automatically (without `-n`), then please open an
[issue].

[issue]: https://github.com/rom1v/usbaudio/issues


## Limitations

It does not work on all devices, it seems that audio accessory is not always
well supported. But it's better than nothing.

[Android Q] added a new [playback capture API][androidq-api]. Hopefully,
_scrcpy_ could use it to forward audio in the future (but only for Android Q
devices).

[Android Q]: https://en.wikipedia.org/wiki/Android_Q
[androidq-api]: https://developer.android.com/preview/features/playback-capture
