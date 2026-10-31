<?php
// Incluído por LoginQueryCtrl::sendMailConfirm(), que define $user e $url
if (!isset($user) || $user == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
$mailBaseUrl  = $this->getBaseUrl();
$mailTitle    = "Crie uma nova senha";
$mailGreeting = "Olá, ".$user->getPerson()->getName().".";
$mailText     = "Recebemos um pedido para trocar a senha da sua conta. Clique no botão abaixo para criar a nova senha.";
$mailButton   = "Criar nova senha";
$mailUrl      = $url;
$mailLogin    = $user->getLogin();
$mailNotes    = array(
	"Não pediu a troca? Ignore esta mensagem: sua senha atual continua valendo.",
);
include(dirname(__FILE__)."/partials/mail.php");
