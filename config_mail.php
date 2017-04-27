<?php require_once('vendor/autoload.php');

// Mail it
$transport = Swift_SmtpTransport::newInstance('mail2.swd.ca', 25);

$mailer = Swift_Mailer::newInstance($transport);

global $mailer;