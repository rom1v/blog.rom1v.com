---
layout: post
title: Introducing gnirehtet
date: 2017-03-30 11:00:00+01:00
lang: en
langref:
- lang: fr
  url: /2017/03/gnirehtet
  internal: true
---

I spent the last few weeks at [Genymobile] developing a tool providing _reverse
tethering_ for Android, so that devices may use the internet connection of the
computer they are plugged in, without requiring any _root_ access (neither on
the device nor on the computer). It works on _GNU/Linux_, _Windows_ and _Mac
OS_.

[Genymobile]: https://www.genymobile.com/

We decided to open source it under the name [_gnirehtet_][gnirehtet].

_Yeah, that's a weird name, until you realize that this is the output of this
[bash] command:_

{% highlight bash %}
rev <<< tethering
{% endhighlight %}

[gnirehtet]: https://github.com/Genymobile/gnirehtet
[bash]: https://en.wikipedia.org/wiki/Bourne-Again_shell


## How to use Gnirehtet

Basically, just download the latest [release], extract it, and execute the
following command on the computer:

    ./gnirehtet rt

[release]: https://github.com/Genymobile/gnirehtet/releases/latest

Once activated, a “key” logo appears in your device status bar:

{: .center}
![key]({{ site.assets }}/gnirehtet/key.png)

Check the [README] file of the project for more details.

[README]: https://github.com/Genymobile/gnirehtet/blob/master/README.md


## How does gnirehtet work?

Gnirehtet is composed of two parts:

 - an Android application (the client);
 - a Java desktop application (the relay server).

_Since then, [I rewrote it in Rust][rust]._

[rust]: {% post_url 2017-09-21-gnirehtet-rewritten-in-rust %}

The client registers itself as a VPN, in order to intercept the whole device
network traffic, as `byte[]` of raw [IPv4 packets], which it transmits to the
relay server over a [TCP] connection (established over [_adb_]).

The relay server parses the packets headers, open connections from the computer
to the requested destinations, and relays the content in both directions
following the [UDP] and [TCP] protocols. It creates and sends response packets
back to the Android client, which writes them to the VPN interface.

In a sense, the relay server behaves like a [NAT], in that it opens connections
on behalf of private peers. However, it differs from standard NATs in the way it
communicates with the clients (the private peers), by using a very specific
(though simple) protocol over a TCP connection.

{: .center}
![archi]({{ site.assets }}/gnirehtet/archi.png)

For more details, you can read the [developers][DEVELOP] page.

[IPv4 packets]: https://en.wikipedia.org/wiki/IPv4#Packet_structure
[_adb_]: https://developer.android.com/studio/command-line/adb.html
[udp]: https://en.wikipedia.org/wiki/User_Datagram_Protocol
[tcp]: https://en.wikipedia.org/wiki/Transmission_Control_Protocol
[NAT]: https://en.wikipedia.org/wiki/Network_address_translation
[DEVELOP]: https://github.com/Genymobile/gnirehtet/blob/master/DEVELOP.md


## Here are the solutions I have considered

Once the application is able to intercept the whole device network traffic,
several alternative designs are possible.

_**TL;DR:** I first considered creating a “TUN device” on the computer, but it
did not suit our needs. Then I wanted to benefit from existing [SOCKS] servers,
but some constraints prevented us to relay UDP traffic. So I implemented
[gnirehtet]._


### TUN device

During my investigations on how to implement _reverse tethering_, I first found
projects creating a [TUN device] on the computer ([`vpn-reverse-tether`] and
[`SimpleRT`]).

This design works very well, and has several advantages:

 - it operates at network level, so there is no need for translation between
   level 3 and level 5 of the [OSI model];
 - all IP packets are tunneled, regardless of their transport protocol (so they
   are [all][protocols] supported, while _gnirehtet_ “only” supports [TCP] and
   [UDP]).

However:

 - it requires _root_ access on the computer;
 - it does not work on platforms other than _Linux_.

_You could still consider using these “TUN device” applications, they may better
suit your needs._

[`SimpleRT`]: https://github.com/vvviperrr/SimpleRT
[`vpn-reverse-tether`]: https://github.com/google/vpn-reverse-tether
[TUN device]: https://en.wikipedia.org/wiki/TUN/TAP
[OSI model]: https://en.wikipedia.org/wiki/OSI_model
[protocols]: https://en.wikipedia.org/wiki/List_of_IP_protocol_numbers


### SOCKS

In order to avoid to develop a specific relay server, my first idea was to make
the client talk the [SOCKS] protocol (according to [RFC 1928]). That way, it
would be possible to use any existing SOCKS server, for instance the one
provided by `ssh -D`.

You probably already used it to bypass annoying enterprise firewalls. For this
purpose, just start the tunnel:

    ssh my_serveur -ND1080

Then configure your browser to use the SOCKS proxy `localhost:1080`. Also take
care to enable remote DNS resolution if you want to resolve domain names from
`my_server` (in _Firefox_, enable `network.proxy.socks_remote_dns` in
`about:config`).

Unfortunately, the [OpenSSH] implementation [does not support UDP][sshmail],
although the [SOCKS5] protocol itself does. And we do need UDP, at least for
[DNS] requests (and also [NTP]).

If you read carefully the two last paragraphs, you might want to ask yourself:

> How may Firefox resolve domain names remotely through the OpenSSH SOCKS proxy
> if it does not even support UDP?

The answer lies in the [section 4] of the RFC: the requested destination address
may be an IPv4, an IPv6 or **a domain name**. However, using this feature
implies that the client (e.g. _Firefox_) is aware of the proxy (since it must
explicitly pass the domain name instead of resolving it locally), while our
_reverse tethering_ must be **transparent**.

But all is not lost. OK, _OpenSSH_ does not support UDP, but this is just a
specific implementation, we could consider another one. Unfortunately, [SOCKS5
requires to relay UDP over UDP][stackoverflow], but the devices and the computer
communicate over _adb_ (thanks to `adb reverse`), which does not support UDP
port forwarding either.

Maybe we could at least relay DNS requests by forcing them to [use TCP][DNS over
TCP], like [tsocks] does:

> **tsocks** will normally not be able to send DNS queries through a SOCKS
> server since SOCKS V4 works on TCP and DNS normally uses UDP. Version 1.5 and
> up do however provide a method to force DNS lookups to use TCP, which then
> makes them proxyable.

But then, SOCKS was no longer attractive to me for implementing _reverse
tethering_.


[SOCKS]: https://en.wikipedia.org/wiki/SOCKS
[SOCKS5]: https://en.wikipedia.org/wiki/SOCKS#SOCKS5
[RFC 1928]: https://tools.ietf.org/html/rfc1928
[section 4]: https://tools.ietf.org/html/rfc1928#section-4
[DNS]: https://en.wikipedia.org/wiki/Domain_Name_System
[OpenSSH]: https://en.wikipedia.org/wiki/OpenSSH
[sshmail]: http://lists.mindrot.org/pipermail/openssh-unix-dev/2017-January/035662.html
[DNS over TCP]: https://tools.ietf.org/html/rfc7766
[NTP]: https://en.wikipedia.org/wiki/Network_Time_Protocol
[stackoverflow]: http://stackoverflow.com/questions/41967217/why-does-socks5-require-to-relay-udp-over-udp
[tsocks]: https://linux.die.net/man/8/tsocks


### Gnirehtet

Therefore, I developed both the client and the relay server manually.

This [blog post][geekstuff] and several open source projects ([`SimpleRT`],
[`vpn-reverse-tether`], [`LocalVPN`] et [`ToyVpn`]) helped me a lot to
understand how to implement this solution.


## Conclusion

[_Gnirehtet_][gnirehtet] allows Android devices to use the internet connection
from a computer easily, without any _root_ access. It helps when you can't
access the network using a WiFi access point.

I hope it will be useful to some of you.

[geekstuff]: http://www.thegeekstuff.com/2014/06/android-vpn-service/
[`LocalVPN`]: https://github.com/hexene/LocalVPN
[`ToyVpn`]: https://android.googlesource.com/platform/development/+/master/samples/ToyVpn/

_This post was initially published on [medium]._

[medium]: https://medium.com/genymobile/gnirehtet-reverse-tethering-android-2afacdbdaec7

_Discuss on [reddit] and [Hacker News]._

[reddit]: https://www.reddit.com/r/Android/comments/62lc8z/a_reverse_tethering_tool_for_android_no_root/
[Hacker News]: https://news.ycombinator.com/item?id=14011590
