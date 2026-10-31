<?php
/**
 * Início da sessão com cookie protegido. Usado pelo index.php e pelos controllers
 * chamados direto pelas rotas *.ctrlExt.
 */
class Session {
	public static function start() {
		if (session_status () !== PHP_SESSION_NONE)
			return;
		$https = (! empty ( $_SERVER ["HTTPS"] ) && $_SERVER ["HTTPS"] !== "off")
			|| (($_SERVER ["HTTP_X_FORWARDED_PROTO"] ?? "") === "https");
		ini_set ( 'session.use_strict_mode', '1' );
		session_set_cookie_params ( array (
			"lifetime" => 0,
			"path"     => "/",
			"secure"   => $https,
			"httponly" => true,
			"samesite" => "Lax"
		) );
		session_start ();
	}
}
