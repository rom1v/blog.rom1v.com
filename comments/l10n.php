<?php
$lang = $_POST['lang'] ?: 'fr';

$l10n = array(
    'fr'=> array(
        'comment-sent' => 'Commentaire envoyé',
        'comment-result' => 'Merci pour votre commentaire. Il sera publié après validation.',
        'comment-redir' => 'Vous allez être redirigé vers la page à partir de laquelle vous avez envoyé ce commentaire.',
        'comment-redir-manual' => '<a href="%url">Cliquez ici</a> si vous n\'êtes pas redirigé automatiquement.',
        'comment-submission-refused' => 'Soumission refusée.',
        'comment-not-sent' => 'Le commentaire n\'a pas pu être envoyé.',
    ),
    'en' => array(
        'comment-sent' => 'Comment sent',
        'comment-result' => 'Thank you for your comment. It will be published after validation.',
        'comment-redir' => 'You will be redirected to the page from where you sent this comment.',
        'comment-redir-manual' => '<a href="%url">Click here</a> if you\'re not redirected automatically.',
        'comment-submission-refused' => 'Submission refused.',
        'comment-not-sent' => 'Comment could not be sent.',
    ),
);
