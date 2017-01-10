<?php

// submit.php -- Receive comments and e-mail them to someone
// Copyright (C) 2011 Matt Palmer <mpalmer@hezmatt.org>
//
//  This program is free software; you can redistribute it and/or modify it
//  under the terms of the GNU General Public License version 3, as
//  published by the Free Software Foundation.
//
//  This program is distributed in the hope that it will be useful, but
//  WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
//  General Public License for more details.
//
//  You should have received a copy of the GNU General Public License along
//  with this program; if not, see <http://www.gnu.org/licences/>


// Format of the date you want to use in your comments.  See
// http://php.net/manual/en/function.date.php for the insane details of this
// format.
$DATE_FORMAT = "Y-m-d H:i:sP";

// Where the comment e-mails should be sent to.  This will also be used as
// the From: address.  Whilst you could, in theory, change this to take the
// address out of the form, it's *incredibly* highly recommended you don't,
// because that turns you into an open relay, and that's not cool.
$EMAIL_ADDRESS = "rom+blog@rom1v.com";

// The subject of all blog comment e-mails.  If you're running lots of these,
// you might want to customise it, or if you were running a generic comment
// handler you could take it out of the form, but really, who cares what your
// comment e-mails are titled, as long as you can recognise it?
$SUBJECT = "Nouveau commentaire ®om's blog";

// The contents of the following file (relative to this PHP file) will be
// displayed after the comment is received.  Customise it to your heart's
// content.
$COMMENT_SENT = "sent.html";

/****************************************************************************
 * HERE BE CODE
 ****************************************************************************/

if (!isset($_POST["post_id"])) {
	echo "Soumission invalide.";
	return;
}

$msg = "post_id: " . $_POST["post_id"] . "\n";
$msg .= "email: " . $_POST["email"] . "\n";
$msg .= "---\n";
$msg .= "- id: ?\n";
$msg .= "  author: " . $_POST["name"] . "\n";
if ($_POST["url"] !== '')
{
	$msg .= "  author-url: " . $_POST["url"] . "\n";
}
$msg .= "  date: " . date($DATE_FORMAT) . "\n";
$msg .= "  contents: |\n" . $_POST["comment"];

$headers = "From: $EMAIL_ADDRESS\n";
$headers .= "Content-Type: text/plain; charset=utf-8";

if (mail($EMAIL_ADDRESS, $SUBJECT, $msg, $headers))
{
	include $COMMENT_SENT;
}
else
{
	echo "Le commentaire n'a pas pu être envoyé.";
}
