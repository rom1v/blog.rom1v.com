---
layout: post
title: Scrcpy 2.0, with audio
date: 2023-03-12 02:30:00+01:00
lang: en
---

I am thrilled to announce the release of [scrcpy 2.0][scrcpy]. Now, you can
mirror (and record) your Android 11+ devices in real-time with audio forwarded
to your computer!

This new version also includes an option to select the video and audio codecs.
The device screen can now be encoded in H.265, or even AV1 if your device
supports AV1 encoding (though this is unlikely).

The application is free and open source. Follow the [instructions][get-the-app]
to install it and run it on your computer.

_If you like scrcpy, you can [support my open source work][donate]._


{: .center}
[![scrcpy][logo]][scrcpy]

[logo]: {{ site.assets }}/scrcpy2/icon.png
[scrcpy]: https://github.com/Genymobile/scrcpy
[codec]: https://github.com/Genymobile/scrcpy/blob/master/doc/video.md#codec
[get-the-app]: https://github.com/Genymobile/scrcpy/blob/master/README.md#get-the-app
[donate]: {{ site.baseurl }}/about/#support-my-open-source-work


## Audio usage

Audio forwarding is supported for devices with Android 11 or higher, and it is
enabled by default:

 - For **Android 12 or newer**, it works out-of-the-box.
 - For **Android 11**, you'll need to ensure that the device screen is unlocked
   when starting scrcpy. A fake popup will briefly appear to make the system
   think that the shell app is in the foreground. Without this, audio capture
   will fail.
 - For **Android 10 or earlier**, audio cannot be captured and is automatically
   disabled.

You can disable audio with:

```
scrcpy --no-audio
```

If audio is enabled, it is also recorded:

```
scrcpy --record=file.mkv
```

Unlike video, audio requires some buffering even in real-time. The buffer size
needs to be small enough to maintain acceptable latency, but large enough to
minimize buffer underrun, which causes audio glitches. The default buffer
size is set to 50ms, but it can be adjusted:

```
scrcpy --audio-buffer=30
```

To improve playback smoothness, you may deliberately increase the latency:

```
scrcpy --audio-buffer=200
```

This is useful, for example, to project your personal videos on a bigger screen:

```
scrcpy --video-codec=h265 --display-buffer=200 --audio-buffer=200
```

You can also select the audio codec and bit rate (default is [Opus] at 128Kbps).
As a side note, I'm particularly impressed by the Opus codec at very low
bit rate:

```bash
scrcpy --audio-codec=opus --audio-bit-rate=16k
scrcpy --audio-codec=aac --audio-bit-rate=16k
```

[Opus]: https://en.wikipedia.org/wiki/Opus_(audio_format)

See the [audio documentation][doc-audio] page for more details.

[doc-audio]: https://github.com/Genymobile/scrcpy/blob/master/doc/audio.md


## History

The first version of scrcpy was released [5 years ago][blog-scrcpy]. Since then,
audio forwarding has been one of the most requested features (see [issue #14]).

I made a first experimentation and developed [USBaudio][blog-usbaudio] as a
solution, but it worked poorly and the feature it relied on was deprecated in
Android 8.

With the introduction of a new API to capture audio from an Android app in
Android 10, I made a prototype called [sndcpy][blog-sndcpy]. However, there were
several issues. Firstly, it required to be invoked from an Android app (the
scrcpy server is not an Android app, but [a Java executable run with shell
permissions][java-shell]). Most importantly, this API [lets apps
decide][app-restrictions] whether they can be captured or not, meaning many apps
simply could not be captured, causing confusion for users.

By the end of January, [**@yume-chan**][@yume-chan] (a _scrcpy_ user), [provided
a proof-of-concept][pr-poc] to capture the device audio with _shell_ permissions
and also proposed a working workaround for Android 11.

[@yume-chan]: https://github.com/yume-chan

Since then, I [have been working][pr-audio] on a proper integration into scrcpy
_(my evenings and weekends have been quite busy_ 🙂_)_. I added encoding,
recording, buffering and playback with clock drift compensation to prevent audio
delay from drifting.

Below are more technical details.


[blog-scrcpy]: {% post_url 2018-03-08-introducing-scrcpy %}
[java-shell]: {% post_url 2018-03-08-introducing-scrcpy %}#run-a-java-main-on-android
[app-restrictions]: https://developer.android.com/guide/topics/media/av-capture#capture_policy
[blog-usbaudio]: {% post_url 2019-06-20-introducing-usbaudio %}
[blog-sndcpy]: {% post_url 2020-06-09-audio-forwarding-on-android-10 %}
[issue #14]: https://github.com/Genymobile/scrcpy/issues/14
[pr-poc]: https://github.com/Genymobile/scrcpy/pull/3703
[pr-audio]: https://github.com/Genymobile/scrcpy/pull/3757


## Audio capture

On the device, audio is captured by an [`AudioRecord`] with [`REMOTE_SUBMIX`] as
the audio source.

[`AudioRecord`]: https://developer.android.com/reference/android/media/AudioRecord
[`REMOTE_SUBMIX`]: https://developer.android.com/reference/android/media/MediaRecorder.AudioSource#REMOTE_SUBMIX

The API is straightforward to use, but not very low-latency friendly. It is
possible to [read][AudioRecord.read] a number of requested bytes in one of two
modes:
 - [`READ_BLOCKING`][]: the read will block until **all** the requested data is
   read (it should be called `READ_ALL_BLOCKING`).
 - [`READ_NON_BLOCKING`][]: the read will return immediately after reading as
   much audio data as possible without blocking.

[`read()`]: https://man7.org/linux/man-pages/man2/read.2.html
[AudioRecord.read]: https://developer.android.com/reference/android/media/AudioRecord#read(java.nio.ByteBuffer,%20int,%20int)
[`READ_BLOCKING`]: https://developer.android.com/reference/android/media/AudioRecord#READ_BLOCKING
[`READ_NON_BLOCKING`]: https://developer.android.com/reference/android/media/AudioRecord#READ_NON_BLOCKING

However, the most useful mode, which is a blocking read that may return less
data than requested (like the [`read()`] system call), is missing.

Since the amount of data available is unknown beforehand, in `READ_BLOCKING`
mode, scrcpy might wait for too long. Conversely, in `READ_NON_BLOCKING` mode,
scrcpy would read in a live-loop, burning CPU while the function returns 0 most
of the time.

I decided to use `READ_BLOCKING` with a size of 5ms (960 bytes).

Anyway, in practice, on the devices I tested on, audio blocks are produced only
every 20ms, introducing a latency of 20ms. This is not a limiting factor though,
since default OPUS and AAC encoders implementations on Android produce frame
sizes of 960 samples (20ms) and 1024 samples (21.33ms) respectively (and they
are not configurable).

In these conditions, _scrcpy_ reads successively 4 blocks of 5 ms every 20ms.
Although the number of requested bytes could be increased to 20ms (3840 bytes),
in theory some devices might capture audio faster.

With the missing blocking mode (`READ_BLOCKING_THE_REAL_ONE`), it would be
possible to request a read with a larger buffer (e.g. 500ms) in one call, and
the `AudioRecord` would return as much data as possible whenever it is
available.


## Audio encoding

The captured audio samples are then encoded by [`MediaCodec`], which offers both
[synchronous][mediacodec-sync] and [asynchronous][mediacodec-async] APIs.

For our purpose, we need to execute two actions in parallel:
 - sending input audio buffers (read by our `AudioRecord`);
 - receiving output audio buffers (the encoded packets).

Therefore, the asynchronous API is more suitable than the synchronous one.

Here is how it is [documented][mediacodec-async]:

```java
MediaCodec codec = MediaCodec.createByCodecName(name);
codec.setCallback(new MediaCodec.Callback() {
    @Override
    void onInputBufferAvailable(MediaCodec mc, int inputBufferId) {
        ByteBuffer inputBuffer = codec.getInputBuffer(inputBufferId);
        // fill inputBuffer with valid data
        …
        codec.queueInputBuffer(inputBufferId, …);
    }

    @Override
    void onOutputBufferAvailable(MediaCodec mc, int outputBufferId, …) {
        ByteBuffer outputBuffer = codec.getOutputBuffer(outputBufferId);
        // outputBuffer is ready to be processed or rendered.
        …
        codec.releaseOutputBuffer(outputBufferId, …);
    }

    …
}
```

[`MediaCodec`]: https://developer.android.com/reference/android/media/MediaCodec
[mediacodec-sync]: https://developer.android.com/reference/android/media/MediaCodec#synchronous-processing-using-buffers
[mediacodec-async]: https://developer.android.com/reference/android/media/MediaCodec#asynchronous-processing-using-buffers

However, there is a catch: the callbacks (`onInputBufferAvailable()` and
`onOutputBufferAvailable()`) are called from the same thread and cannot run in
parallel.

Filling an input buffer requires a blocking call to read from the `AudioRecord`,
while processing the output buffers involves a blocking call to send the data to
the client over a socket.

If we were to process the buffers directly from the callbacks, the processing
of an output buffer would be delayed until the blocking call to
`AudioRecord.read()` completes (which may be up to 20ms as described in the
previous section). This would result in additional latency.

To address this issue, the callback only submits tasks to input and output
queues, which are processed by dedicated threads:

```java
// simplified
codec.setCallback(new MediaCodec.Callback() {
    @Override
    void onInputBufferAvailable(MediaCodec mc, int inputBufferId) {
        inputTasks.put(new InputTask(index));
    }

    @Override
    void onOutputBufferAvailable(MediaCodec mc, int outputBufferId,
                                 MediaCodec.BufferInfo bufferInfo) {
        outputTasks.put(new OutputTask(index, bufferInfo);
    }

    …
}
```


## Client architecture

Here is an overview of the client architecture for the video and audio streams:

```
                                                 V4L2 sink
                                               /
                                       decoder
                                     /         \
        VIDEO -------------> demuxer             display
                                     \
                                       recorder
                                     /
        AUDIO -------------> demuxer
                                     \
                                       decoder --- audio player
```

The video and audio are captured and encoded on the device, and the resulting
packets are sent via separate sockets over an adb tunnel using a custom
protocol. This protocol transmits the raw encoded packets with packet headers
that provide early information about packet boundaries (useful to [reduce video
latency][#646]) and [PTS] (used for recording).

[#646]: https://github.com/Genymobile/scrcpy/pull/646
[PTS]: https://en.wikipedia.org/wiki/Presentation_timestamp

Video and audio streams are then _demuxed_ into [packets][AVPacket] by a
`demuxer`.

[AVPacket]: https://ffmpeg.org/doxygen/6.0/structAVPacket.html
[AVFrame]: https://ffmpeg.org/doxygen/6.0/structAVFrame.html

If [recording] is enabled, the `recorder` asynchronously _muxes_ the elementary
streams into MP4 or MKV. Thus, the packets are encoded on the device side, but
muxed on the client side (it's the division of labour!).

[recording]: https://github.com/Genymobile/scrcpy/blob/master/doc/recording.md

If a [display] or [V4L2] is enabled, then the video _packets_ must be decoded by
a `decoder` into video [frames][AVFrame] to be displayed or sent to V4L2.

[display]: https://github.com/Genymobile/scrcpy/blob/master/doc/video.md#no-display
[V4L2]: https://github.com/Genymobile/scrcpy/blob/master/doc/v4l2.md

If [audio] playback is enabled (currently when a display is enabled), the
audio packets are decoded into audio frames (blocks of samples) and played by
the audio player.

[audio]: https://github.com/Genymobile/scrcpy/blob/master/doc/audio.md


## Audio player

This is the last component I implemented (I wrote recording before playback),
because it is the trickiest, especially to compensate for the following:
 - **clock offset**: the audio output might not start precisely when necessary to
   play the samples at the right time;
 - **clock drift**: the device clock and the client clock may not advance
   at precisely the same rate;
 - **buffer underrun**: when the player has no samples available when requested
   by the audio output.

While scrcpy displays the latest received video frame without buffering, this
isn't possible for audio. Playing the latest received audio sample would be
meaningless.

As input, the player regularly receives [`AVFrame`][AVFrame]s of decoded audio
samples. As output, a callback regularly requests audio samples to be played. In
between, an audio buffer stores produced samples that have yet to be consumed.

The player aims to feed the audio output with as little latency as possible
while avoiding buffer underrun. To achieve this, it attempts to maintain the
average buffering (the number of samples present in the buffer) around a target
value.  If this target buffering is too low, then buffer underrun will occur
frequently. If it is too high, then latency becomes unacceptable. This target
value is configured using the scrcpy option
[`--audio-buffer`][doc-audio-buffer].


The playback relies only on buffer filling, the [PTS] are not used at all by the
audio player (just as they are not used for video mirroring, unless [video
buffering][#2464] is enabled). PTS are only used for recording.

[#2464]: https://github.com/Genymobile/scrcpy/pull/2464

[doc-audio-buffer]: https://github.com/Genymobile/scrcpy/blob/master/doc/audio.md#buffering

The player cannot adjust the sample input rate (it receives samples produced
in real-time) or the sample output rate (it must provide samples as requested
by the audio output callback). Therefore, it may only apply compensation by
resampling (converting _m_ input samples to _n_ output samples).

The compensation itself is applied by [swresample] (FFmpeg). It is configured
using [`swr_set_compensation()`]. An important work for the player is to
estimate the compensation value regularly and apply it.

The estimated buffering level is the result of averaging the "natural" buffering
(samples are produced and consumed by blocks, so it must be smoothed), and
making instant adjustments resulting of its own actions (explicit compensation
and silence insertion on underflow), which are not smoothed.

Buffer underflow events can occur when packets arrive too late. In that case,
the player inserts silence. Once the packets finally arrive (late), one strategy
could be to drop the samples that were replaced by silence, in order to keep a
minimal latency. However, dropping samples in case of buffer underflow is
inadvisable, as it would temporarily increase the underflow even more and cause
very noticeable audio glitches.

Therefore, the player doesn't drop any sample on underflow. The compensation
mechanism will absorb the delay introduced by the inserted silence.

[swresample]: https://ffmpeg.org/doxygen/6.0/group__lswr.html#details
[`swr_set_compensation()`]: https://ffmpeg.org/doxygen/6.0/group__lswr.html#gab7f21690522b85d7757e13fa9853d4d8



## Conclusion

I'm delighted that scrcpy now supports audio forwarding after much effort.

While I expect that the audio player will require some fine-tuning in the future
to better handle edge cases, it currently performs quite well.

I would like to extend a huge thank you to **@yume-chan** for his initial
proof-of-concept, which made this feature possible.

Happy mirroring!
