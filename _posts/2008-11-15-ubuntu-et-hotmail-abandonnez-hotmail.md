---
layout: post
title: 'Ubuntu et hotmail : abandonnez hotmail !'
date: 2008-11-15 09:51:00+01:00
---

De nombreuses personnes ont rapporté des problèmes avec l'utilisation du webmail
**hotmail** ces derniers jours. En effet, la zone de texte réservée à l'écriture
du message ne fonctionne pas.

Avant de vous présenter la solution pour contourner le problème, je voudrais
vous proposer d'utiliser un compte de messagerie autre qu'_hotmail_ pour vos
mails.

Pourquoi? Parce que ce service ne vous propose même pas le minimum : pas d'accès
**POP3**, et encore moins **IMAP**. Concrètement, cela signifie que vous n'êtes
pas libres de consulter vos mails dans un logiciel de messagerie quelconque,
ceci afin de mettre en avant _Windows Live Mail_ et _Outlook_ (à moins
d'utiliser des plugins plus ou moins performants qui font croire à hotmail que
vous y accédez par un navigateur web).

Abandonner votre mail _hotmail_ ne vous empêche pas de conserver votre adresse
pour utiliser _MSN Messenger_.

Évidemment, changer de compte e-mail ne se fait pas du jour au lendemain, mais
le temps que vos contacts prennent connaissance de votre nouvelle adresse, il
est possible de mettre en place une redirection : sur _hotmail_, cliquez sur
Options → Autres options → Transfert du courrier vers un autre compte de
messagerie, et indiquez votre nouvelle adresse. Ainsi, tous les mails qui vous
seront envoyés sur votre adresse _hotmail_ seront redirigés vers votre nouvelle
adresse.

_Rectification : **hotmail** ne souhaitant pas que ses utilisateurs quittent
l'écosystème **Microsoft**, il n'est possible de rediriger que sur une adresse
se terminant par `@hotmail.com`, `@hotmail.fr`, `@msn.com` ou `@live.com`.
Heureusement que tous les fournisseurs de services n'ont pas la même politique,
il est grand temps d'utiliser un service qui respecte un peu plus vos libertés
d'action !_

Personnellement, j'utilise [gmail](http://mail.google.com), car il propose un
accès **IMAP** et une grande capacité de stockage. Cette adresse **gmail** peut
également être utilisée comme adresse de messagerie instantanée
[jabber](http://fr.wikipedia.org/wiki/Jabber).

Bon, revenons au problème d'édition de mails sous **Ubuntu**, au cas où vous
voudriez à tout prix conserver votre adresse e-mail _hotmail_. Pour contourner
le problème, il suffit de ne pas indiquer qu'on utilise **Ubuntu**.  Pour cela,
dans **Firefox**, tapez `about:config` dans la barre d'adresse, cherchez la
ligne `general.useragent.vendor`, double-cliquez dessus, et supprimez le texte
"Ubuntu".

Et là magie, _hotmail_ refonctionne correctement, comme sous Windows.
