<?	
if ($custumer == null){
	header("HTTP/1.0 404 Not Found");
	exit ();
}	
?>
<style type="text/css">
	body{font-size: 14px;font-family:Helvetica;color:#000;}
	a{font-weight:bold;color:blue}
	#div_login{font-weight:bold;}
	#p_obs{font-style:italic;}
</style>

<h2>Bem-vindo ao KADE Caminhões!!!</h2>

<p>
	Para ativar sua conta e confirmar seu login e senha, clique neste link: 
	<a href="<?=$url?>"><?=$url?></a>
</p>

<div id="div_login">Login: <?=$custumer->getUser()->getLogin();?></div>
<br/>
<p id="p_obs">
***OBSERVAÇÃO*** Para solicitar uma nova senha, <a href="<?=$_SERVER ["HTTP_HOST"]?>/request_new_password/">clique aqui</a> para redefinir sua senha. 
</p>

<p>
Se você recebeu esta mensagem por engano, é provável que outro usuário tenha 
inserido seu endereço de e-mail ao tentar criar uma conta para outro 
endereço de e-mail. Se você não clicar no link de verificação, a conta não 
será ativada. 
</p>

<p>
Se clicar no link acima não funcionar, copie e cole o URL em uma nova janela do navegador. 
</p>

<p>
Atenciosamente,<br/>
Equipe Kade Caminhões.
</p>

