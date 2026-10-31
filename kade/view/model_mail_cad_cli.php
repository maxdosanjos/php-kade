<?php
// Incluído por CustumerRegCtrl::sendMailConfirm(), que define $custumer e $url
if (!isset($custumer) || $custumer == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}
$mailBaseUrl  = $this->getBaseUrl();
$mailTitle    = "Bem-vindo à KADE Caminhões!";
$mailGreeting = "Olá, ".$custumer->getUser()->getPerson()->getName().".";
$mailText     = "Falta só um passo: confirme seu cadastro para ativar a sua conta.";
$mailButton   = "Ativar minha conta";
$mailUrl      = $url;
$mailLogin    = $custumer->getUser()->getLogin();
$mailNotes    = array(
	'Esqueceu a senha? Peça uma nova em <a href="'.htmlspecialchars($mailBaseUrl."/request_new_password/", ENT_QUOTES, "UTF-8").'" style="color:#0b3b6f;">'.htmlspecialchars($mailBaseUrl."/request_new_password/", ENT_QUOTES, "UTF-8").'</a>',
	"Não fez este cadastro? Alguém pode ter digitado o seu e-mail por engano. É só ignorar esta mensagem: sem a confirmação, a conta não é ativada.",
);
include(dirname(__FILE__)."/partials/mail.php");
