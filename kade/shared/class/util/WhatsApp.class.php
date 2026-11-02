<?php
require_once (dirname ( __FILE__ ) . "/Properties.class.php");
/**
 * WhatsApp Cloud API (oficial, da Meta): webhook /webhook/whatsapp e envio da resposta.
 *
 * - Verificação (GET): a Meta manda hub.mode=subscribe, hub.verify_token e hub.challenge;
 *   com o WHATSAPP_VERIFY_TOKEN certo, o site devolve o challenge.
 * - Mensagens (POST): o corpo vem assinado no cabeçalho X-Hub-Signature-256 (HMAC-SHA256
 *   com o WHATSAPP_APP_SECRET). Sem a assinatura certa, nada é lido.
 * - Resposta: POST na Graph API com o WHATSAPP_TOKEN. A Meta não cobra a resposta a uma
 *   mensagem do motorista dentro de 24 horas.
 */
class WhatsApp {
	const GRAPH = "https://graph.facebook.com/";
	const TIMEOUT = 8;

	private $config;

	public function __construct(array $config = null) {
		$this->config = $config ?? Properties::getGroup ( "whatsapp" );
	}

	private function conf($key) {
		return trim ( ( string ) ($this->config [$key] ?? "") );
	}

	/** Sem o segredo do app o webhook fica desligado */
	public function isConfigured() {
		return $this->conf ( "app_secret" ) !== "";
	}

	/** Challenge a devolver na verificação do webhook, ou null. O PHP troca o ponto de hub.mode por _ */
	public function verifyChallenge(array $query) {
		$token = $this->conf ( "verify_token" );
		$challenge = ( string ) ($query ["hub_challenge"] ?? "");
		if ($token === "" || ($query ["hub_mode"] ?? "") !== "subscribe" || ! preg_match ( '/^[A-Za-z0-9_-]{1,128}$/', $challenge ))
			return null;
		return hash_equals ( $token, ( string ) ($query ["hub_verify_token"] ?? "") ) ? $challenge : null;
	}

	public function isSigned($rawBody, $signature) {
		$secret = $this->conf ( "app_secret" );
		return $secret !== "" && hash_equals ( "sha256=" . hash_hmac ( "sha256", ( string ) $rawBody, $secret ), ( string ) $signature );
	}

	public function phoneNumberId() {
		return $this->conf ( "phone_number_id" );
	}

	/**
	 * Mensagens recebidas no corpo do webhook. Confirmações de entrega (statuses), reações
	 * (emoji em cima de uma mensagem) e mensagens para outro número do mesmo app ficam de fora.
	 * Cada item: array("id", "from", "type", "text"); text fica "" quando não é texto.
	 */
	public static function parseMessages($rawBody, $phoneNumberId = "") {
		$data = json_decode ( ( string ) $rawBody, true );
		$out = array ();
		if (! is_array ( $data ) || ($data ["object"] ?? "") !== "whatsapp_business_account")
			return $out;
		foreach ( ( array ) ($data ["entry"] ?? array ()) as $entry ) {
			foreach ( ( array ) ($entry ["changes"] ?? array ()) as $change ) {
				$value = $change ["value"] ?? array ();
				if (($change ["field"] ?? "") !== "messages" || ! is_array ( $value ))
					continue;
				if ($phoneNumberId !== "" && ( string ) ($value ["metadata"] ["phone_number_id"] ?? "") !== $phoneNumberId)
					continue;
				foreach ( ( array ) ($value ["messages"] ?? array ()) as $msg ) {
					$type = ( string ) ($msg ["type"] ?? "");
					if (! isset ( $msg ["id"], $msg ["from"] ) || $type === "reaction")
						continue;
					$out [] = array (
							"id" => ( string ) $msg ["id"],
							"from" => preg_replace ( '/\D/', "", ( string ) $msg ["from"] ),
							"type" => $type,
							"text" => $type === "text" ? trim ( ( string ) ($msg ["text"] ["body"] ?? "") ) : ""
					);
				}
			}
		}
		return $out;
	}

	/** Envia um texto. Retorna "" se deu certo ou o motivo da falha (vai para o log) */
	public function send($to, $text) {
		$token = $this->conf ( "token" );
		$phoneId = $this->phoneNumberId ();
		if ($token === "" || $phoneId === "")
			return "WHATSAPP_TOKEN ou WHATSAPP_PHONE_NUMBER_ID não definido";
		$version = $this->conf ( "api_version" ) ?: "v23.0";
		$url = self::GRAPH . rawurlencode ( $version ) . "/" . rawurlencode ( $phoneId ) . "/messages";
		$payload = json_encode ( array (
				"messaging_product" => "whatsapp",
				"recipient_type" => "individual",
				"to" => ( string ) $to,
				"type" => "text",
				"text" => array ( "preview_url" => false, "body" => ( string ) $text )
		), JSON_UNESCAPED_UNICODE );
		list ( $status, $body ) = $this->httpPost ( $url, $payload, array ( "Authorization: Bearer " . $token, "Content-Type: application/json" ) );
		if ($status >= 200 && $status < 300)
			return "";
		$error = json_decode ( ( string ) $body, true ) ["error"] ["message"] ?? "";
		return "HTTP " . $status . ($error !== "" ? ": " . $error : "");
	}

	/** @return array(status, corpo); status 0 quando não houve resposta. Sobrescrito nos testes. */
	protected function httpPost($url, $payload, array $headers) {
		$ch = curl_init ( $url );
		curl_setopt_array ( $ch, array (
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $payload,
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CONNECTTIMEOUT => 3,
				CURLOPT_TIMEOUT => self::TIMEOUT
		) );
		$body = curl_exec ( $ch );
		$status = $body === false ? 0 : curl_getinfo ( $ch, CURLINFO_RESPONSE_CODE );
		curl_close ( $ch );
		return array ( $status, $body === false ? "" : $body );
	}
}
