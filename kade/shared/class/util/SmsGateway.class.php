<?php
require_once (dirname ( __FILE__ ) . "/Properties.class.php");
/**
 * Quem pode chamar a rota pública do SMS (/RegisterVehicleSMS.ctrlExt/...).
 *
 * Aceita a chamada quando:
 * - o IP do gateway (SMS_GATEWAY_IP, vários separados por vírgula) bate com o
 *   X-Real-IP, que o proxy da hospedagem preenche, ou com o IP da conexão; e
 * - se SMS_TOKEN estiver no .env, a URL traz esse segredo:
 *   /RegisterVehicleSMS.ctrlExt/<token>/<telefone>/<tipo*cep>
 *
 * O X-Real-IP sozinho pode ser forjado quando o Apache recebe a conexão direto;
 * o segredo fecha essa brecha. Sem IP e sem segredo configurados, nada passa.
 */
class SmsGateway {
	const ROUTE = "RegisterVehicleSMS.ctrlExt";

	public static function isAllowed(array $server, $token, $ips = null, $secret = null) {
		$prop = Properties::getGroup ( "server_sms" );
		$ips = array_filter ( array_map ( "trim", explode ( ",", (string) ($ips ?? $prop ["http_x_real_ip"] ?? "") ) ) );
		$secret = (string) ($secret ?? $prop ["token"] ?? "");

		if (! $ips && $secret === "")
			return false;
		if ($ips && ! array_intersect ( $ips, array ( $server ["HTTP_X_REAL_IP"] ?? "", $server ["REMOTE_ADDR"] ?? "" ) ))
			return false;
		return $secret === "" || hash_equals ( $secret, (string) $token );
	}

	/** URL da requisição sem o segredo, para gravar no log do SMS (que o admin vê) */
	public static function maskToken($url) {
		return preg_replace ( '#(' . preg_quote ( self::ROUTE, '#' ) . '/)[A-Za-z0-9_-]{16,128}(?=/[0-9]+/)#i', '$1***', (string) $url );
	}
}
