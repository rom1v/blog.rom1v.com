---
layout: post
title: Générer des mots de passe aléatoires
date: 2009-11-21 19:05:29+01:00
tags:
- planet-libre
- puf
---

Il arrive de vouloir changer de mot de passe, et comme d'habitude, d'en vouloir
un aléatoire.

Le programme `pwgen` est bien pratique. Il génère au choix :

  * des mots de passe qui sont facilement mémorisables par des humains, tout en
    restant aussi sécurisés que possible : pratique pour un mot de passe sans
    importance dont on veut se souvenir facilement ;
  * des mots de passe totalement aléatoires (plus sécurisés) : pour le mot de
    passe principal d'un utilisateur ;
  * des mots de passe totalement aléatoires avec des caractères spéciaux (le
    top) : pour des mots de passe jamais écrits à la main (bases de données),
    ou pour le mot de passe principal d'un geek ^^).

Sur ces 3 exemples, on voit tout de suite les niveaux de difficulté de
mémorisation :

~~~
$ pwgen
EithooK4 faey6aeM Dai5aat5 ue0Aen8e lee1Aiyi Niemap6b ahW7ooth Pohc9iez
tien7Pho ahz5aaJ4 nohd0EiR uiS7xoot Cah9joo1 Fiede8to chus3Mee Ohji2dok
ieJah6de Nool6ael lahd6Pau dooc9Ach nai4Ther Iegahzu7 Roop2tha iiViG4pa
Chaeb1qu Ie0ohxis hohz9Aiw UeYae4Ae uthae6Ga veeCho9o Vo0shait aiShue1h
ieX8geen Auyeix9t OBeb0pae Cah0Ooqu aeG7nooV Ohpu5aic UaTh7eem Fakex4hu
vei7oNgu doc0Fi5J xahFel6r AeK5ing0 xaX6ahBu Coo7Quoh Aghu7lah xec9IeLu
ae1phiaR aem5Auy6 ebohP0ta FieGh3ee ki8QuaoN fahvev6R Aezil9Th aeVeex3f
aeh6eeTe ohR6Ahfi IoV4WaCh EeSah9Oo eeg2Wae8 Eeh5phee eiraGeu0 Owe5thah
Oa0eaqui iaV8joiw yoF1ueF9 Nee3Athi ooW3Noet uphai8Ai Eedoi2ch ao2Eich4
Chie3voo vexae9UR ri4en4Ai ig9ohDaZ iej1iRae Iechee6i uiS3geim zi0ee1Qu
Co5ahcoh Ahyai2ga eiw7ahCa Phier3ie Thoocai3 fieY0xeo aeNei2ei xieb2Cha
Le1oep5e oavohn8E ahbeeM5i Aedeif7i Ohth4Fai Uo4gaevu BeFohch3 iePu9aem
choh6zaY facoo0Na Ug5noh7z fet8ahS8 lanohl0H eiW5dahY Miex1Cey iPeew0Ai
Ooxae1Co Fa4ioz9w ooyaip1A tei6Pae7 eefaeX6J tahWoh6l quee0eeZ gooD0uDu
iev9aeS8 Deb7Iez1 tii8LaVi BuJoon3g eePeghe7 Cee8kahn Ru3rosho baiph2Iu
bahg1eiG vi8aeTho eequ7Voe Xi4oocha raij8aeJ Oe7heGhu Jae4iz1U Ineem3ie
koh1oiPh Bo9xi9Qu Ash5Aisu IeVuiph4 ul8fePoo Aaphe1ru eiWeim1v NuN7xira
uwe0Iexa ahBu9aiv Suu0aexi haiz2Eem eiC3phuh eingo1Cu Aibek8Ta Theiha2I
feP8poir id1ib5Ah Aib2Eish Ahk8cheu Woo7cha3 hahX4pai shiu2uYi Oobiev6E
euf7le8I Vaa2ao4w dah1Ieja pi4OaPuu Aeroh9th thue7Gu5 Aivu3Nah loo9le9E
~~~

~~~
$ pwgen -s
ji3f2Nvp chK36N5w yn7toyvP 3997nLrJ nzFqz14R 9dnkBHYL 9zF2zVQr T1psPkYX
nMNvbMU1 wz1xYluN z9D62kI0 ycZGb5a7 rKMj0W7P NNih1iC2 2TFeDTO1 ul0gKW9G
tplaj4Fg 9SAjOAdn b2KpUY4c M3G5lwAr 9mWUV61M iV41jedC vSFaeM91 PRy5zUuT
0oJIC3Va Qqzkc1gv je4Q2Meg dk0rVKiG oeBAo89G a7vgS7NZ evyG65d4 416FPnAO
94u4ShlL 4WvBDdsG b7ojJyo9 ZiQQO0wf B10gsyBQ jGPT5gw7 R8Vx6Fsb BjO6KzpN
Bj3mHgWs 4YGekJBa 6zVsqVxF sD4hphne ksucD8gt COU5FRCI BBkU9PQt oT7d90Ns
NSmKs5Jl USBtPA1Z oRTb1vl6 79Mn84dv QwGG1utJ HPZTHd1B zww6ZPif 9uIj6bme
GdE4BjN9 oB4VcrWQ 2TlAJuE6 52JMMxcx IsYoIj3g GWyL24vZ sv5dwT48 QrQTz5Xb
KnKtVDY9 5mW6B30b 6RPgdzOp Raes1DgQ Njv3rOyR u4pwGpGl s9hnUdVA WKR4IjQE
g72s3Okb J2IbdUWy 5LNfmINq 0QMAnPGx zTSbMK7h ic0qQhf6 OCB8mQ1p pxGhQ8Ps
wN0GjxDs seW9ricu 3DmdZUbJ lQF9rFWZ dvIC4RM4 CuUWo6k5 rdA4QByG wpHI2BSU
bvvSF2dz y6205hA5 7zGpRspW hn1E4HCh 1lJlhL2P g0niR3c9 dPByI3PG AtRm1bSV
I1Hs5Cg3 8CRJvebA jxh8jpAU x9828wlW 6KBcByOt orl3QmoL 6pYIHB7c MALID6LD
36KiInPB 5f2DLzhj iRB9AHah GUCn2Tzq aJ0ZiDy0 nizXx6SI dCEO0fvw Q5fMf08t
HmZVQd25 EIiKnbb4 AQun8igX 88TRMsB6 R1g0hL7D QPTRCH1V hs14l8C1 xVKhN5MD
sU5N2N4n tS7iB8jK 5Vcq8XrN GvT2bF5X hZS2jZpB vMI2ZxhC GXaYX5JV h5enQ53C
aKSh3ZGm QCxe8dT0 XI38fqmZ u0vA31e7 3wdaV3OF e98PZcs4 Bu7SceCn Pgkq0q2K
j71AznCc pNtp5VoC N2m1RNBn Opa4CrMl P3IFuG6s iuNhtTm3 k3jehDmQ TyG5kBdo
03eqXAB1 Dez29MWr ZZRRI0oh 1KC7CD3H 8Q6VMn5d b9DO8B1o 3M36hgOf N3JWSJIn
xXmfe86X FjCSO4bI w5BvyCZS rDB4aOPK H8hvo7M1 svWnLX2x Mglqr0yA udo253ND
~~~

~~~
$ pwgen -sy
TFKS^;4v y$@5i6CH !4?]G]Tf $E\<90z? ^'z5c+BH 9E>aS@(} qP,:Z2.K ]*7db:W:
\x7Nv9/X Q&ry9yw6; 9ur]=_V0 5-ea/3Tk MQ}jS4H3 1|r\TX%? PnI9!+T* ;I#36=)%
}_(@D7~E hRt0f?3p 7$XQG]j= '7/n7`X: |Byz#93A 4kR4'vw1 %j!f{5WL 7p{xmH?e
,jCt0nXH LSI[T1Z% ViF+O>5v 5(Ooaa%+ p@%Bs9dl DUv@HjB7 oH4q*h*0 +4?B,?a#
x9C4(/]" 9H(||>"8 k"gKzz5} B@W8s#dW \J9]j7l] 3O@;~{vM RNQ'g2M6 >ZQ}E1[]
%3t"i^aK Wp!1(Jw% }0RQ!y\E ~)(0L4.' p@|9uLZO q%%0n#L: ._9AgN}K s[vK?Fi8
vtPgD?~7 Q(>NZ"6j -ADl[88, '+FQj\g6 <,zS(N[2 v9_HEyc: %i5%B!d5 _BA36*Y$
]vS8N!YG 9v'-Dmd3 QQ1;@Zq/ X-3ov/!T Fi%+QI~2 #GY5~>V$ O}T!N8z# 8Gm/-'Al
gM\(bt3\ Ur[D7:I+ dZx@:<5r >^*IKAG2 StTJ3.2! a~FBKB6@ )r->m5Ia ui;7Uy*/
9x]jD;>p X6#TV*at 8koi'YTc N9jYz&qC; nC}9xy#; oyGvr*d4 Oh1}s.mI T$q5htKG
m\o}-6Ex 952Np-ly |vO9T\${ 6u3V)~H" R0fq6T3# [QJTM^a3 7y\M[|2j "s\4U}Hv
:Br^T4_G wKi8,,\_ q;+3K"&{ W1kp`q7& 0Ps?Gq#) cRQ5jnl| ;i6rMWbf [D<4~|Gb
`W_c)/7p A:H,4g2; U?Q{sB4, HMED>2`) .-,9Mlhk *9I\nYH/ bDb`(&,7 x8sLE.G[
F>(.'o0s J\N-0h=< Vrk0W2,n (M3vAm,S rs&ZKgD5; @:a$8N*5 9qm|BZiq {o&3qK`A
-R2/qwg? 3D>{\Y&| s2Bc!]bD tAfJ_1GS \7F[mWJK E#(7zIW@ )RT7lrG) !rzI4Gwn
.0AE=J"| z8Z]b%|N "$4,7X,p +j}!'Ui6 o%W$L/3{ n#+Rf`P9 x3q"Bd($ ""Xmh6S?
@:3T=z~A E+IFC`2C 5;YG]&/] ,1@!<*Qt <2Y8A4a@ ;5fOO0{B 7Prjv$Ms 2'g!6!3T
9r")N*J; z=K'75=! p|0K]&e;< {l5kvqL{ (mG4?0xt p5>F>)D5 j69;T^Jv ;.6sLj5?
qH;1<6Cq 7M+olge; \^n,S7B, @}^7Z]u@ 62__*C6b BNDnB)1@ xr^qwE7C a2'V*S<0
5&dk;?QN9 W0x(}QPE ~-6i-ufZ ik|v#s9L 6xdgc2Y= 7kcS#}if PG1}MI6V u(29L/?}
~~~


Les plus paranos pourront également changer des caractères des mots de passes
générés, ou les mélanger entre eux (afin d'éviter qu'un bug de `pwgen` ne
provoque une baisse de sécurité).

Il est également possible de passer en paramètre la longueur des mots de passe
que l'on veut générer.

Pour le reste :

    man pwgen

Il existe aussi un outil similaire nommé `apg`.
