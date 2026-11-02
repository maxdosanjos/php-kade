<?php
require_once (dirname ( __FILE__ ) . "/Properties.class.php");
/**
 * Robô do Telegram: webhook /webhook/telegram.
 *
 * - O Telegram manda no cabeçalho X-Telegram-Bot-Api-Secret-Token o segredo definido no
 *   setWebhook (TELEGRAM_WEBHOOK_SECRET). Sem ele, nada é lido.
 * - A resposta vai no próprio retorno do webhook ({"method": "sendMessage", ...}), então o
 *   site não precisa do token do robô.
 * - O Telegram não informa o telefone de quem escreve: na primeira conversa o robô pede o
 *   número com o botão "Compartilhar meu número" e o vínculo fica em messenger_contact.
 */
class Telegram {
	private $secret;

	public function __construct($secret = null) {
		$this->secret = trim ( ( string ) ($secret ?? Properties::get ( "telegram.secret" )) );
	}

	public function isConfigured() {
		return $this->secret !== "";
	}

	public function isAllowed($headerSecret) {
		return $this->secret !== "" && hash_equals ( $this->secret, ( string ) $headerSecret );
	}

	/**
	 * Mensagem de uma conversa privada com o robô, ou null (grupos, edições, entrada e
	 * saída do robô e outros tipos de atualização são ignorados).
	 * Itens: update_id, chat_id, user_id, text, contact_phone, contact_user_id.
	 */
	public static function parseUpdate($rawBody) {
		$data = json_decode ( ( string ) $rawBody, true );
		$msg = is_array ( $data ) ? ($data ["message"] ?? null) : null;
		if (! is_array ( $msg ) || ! isset ( $data ["update_id"], $msg ["chat"] ["id"], $msg ["from"] ["id"] ) || ($msg ["chat"] ["type"] ?? "") !== "private")
			return null;
		return array (
				"update_id" => ( string ) $data ["update_id"],
				"chat_id" => ( string ) $msg ["chat"] ["id"],
				"user_id" => ( string ) $msg ["from"] ["id"],
				"text" => trim ( ( string ) ($msg ["text"] ?? "") ),
				"contact_phone" => ( string ) ($msg ["contact"] ["phone_number"] ?? ""),
				"contact_user_id" => ( string ) ($msg ["contact"] ["user_id"] ?? "")
		);
	}

	/** Botão que manda o número do próprio usuário */
	public static function sharePhoneKeyboard() {
		return array (
				"keyboard" => array ( array ( array ( "text" => "📱 Compartilhar meu número", "request_contact" => true ) ) ),
				"resize_keyboard" => true,
				"one_time_keyboard" => true
		);
	}

	public static function removeKeyboard() {
		return array ( "remove_keyboard" => true );
	}

	/** Corpo do retorno do webhook que faz o Telegram enviar a mensagem */
	public static function reply($chatId, $text, array $keyboard = null) {
		$reply = array ( "method" => "sendMessage", "chat_id" => ( string ) $chatId, "text" => ( string ) $text );
		if ($keyboard !== null)
			$reply ["reply_markup"] = $keyboard;
		return $reply;
	}
}
