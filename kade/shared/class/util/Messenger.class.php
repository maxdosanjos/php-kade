<?php
require_once (dirname ( __FILE__ ) . "/Properties.class.php");
require_once (dirname ( __FILE__ ) . "/Message.class.php");
/**
 * Cadastro de veículo por WhatsApp e Telegram: o que vale para os dois canais
 * (WhatsApp.class.php e Telegram.class.php tratam o formato de cada um).
 *
 * O motorista manda a mesma mensagem do SMS, TIPO*CEP (ex.: 6*83203010), e recebe a
 * resposta na conversa. Aqui também valem espaço ou vírgula no lugar do * e o CEP com
 * ponto e hífen (6 83.203-010).
 */
class Messenger {
	const WHATSAPP = "WHATSAPP";
	const TELEGRAM = "TELEGRAM";
	const EXAMPLE = "6*83203010";

	public static function channels() {
		return array ( "SMS" => "SMS", self::WHATSAPP => "WhatsApp", self::TELEGRAM => "Telegram" );
	}

	/** TIPO*CEP → array("type" => "6", "cep" => "83203010"); null fora desse formato */
	public static function parseRegistration($text) {
		if (! preg_match ( '/^\s*(\d{1,3})\s*(?:[*#,;\/-]|\s)\s*(\d{2}\.?\d{3}-?\d{3})\s*$/', ( string ) $text, $m ))
			return null;
		return array ( "type" => ( string ) ( int ) $m [1], "cep" => preg_replace ( '/\D/', "", $m [2] ) );
	}

	/** Pedido da lista de códigos: "tipos", "códigos", "/tipos"... */
	public static function isTypesRequest($text) {
		return ( bool ) preg_match ( '/^\s*\/?(tipos?|c[oó]digos?|lista)\s*$/iu', ( string ) $text );
	}

	/**
	 * Número internacional (WhatsApp, Telegram) → "(DD) NNNNNNNNN", o formato do cadastro.
	 * "" se não for um número do Brasil.
	 */
	public static function brazilianPhone($number) {
		$digits = preg_replace ( '/\D/', "", ( string ) $number );
		if (strlen ( $digits ) < 12 || strlen ( $digits ) > 13 || substr ( $digits, 0, 2 ) !== "55")
			return "";
		$ddd = substr ( $digits, 2, 2 );
		$local = substr ( $digits, 4 );
		// O WhatsApp entrega alguns celulares antigos sem o 9 da frente (55 43 9999-0000)
		if (strlen ( $local ) == 8 && $local [0] >= "6")
			$local = "9" . $local;
		if ($ddd [0] === "0" || (strlen ( $local ) == 9 && $local [0] !== "9"))
			return "";
		return "(" . $ddd . ") " . $local;
	}

	/** Texto de uma Message para o motorista e o log (a de validação vem em JSON com entidades HTML) */
	public static function messageText(Message $message) {
		$desc = ( string ) $message->getDesc ();
		$json = json_decode ( $desc, true );
		if (is_array ( $json )) {
			$parts = array ();
			foreach ( $json as $item )
				if (isset ( $item ["message"] ) && $item ["message"] !== "")
					$parts [] = html_entity_decode ( ( string ) $item ["message"], ENT_QUOTES, "UTF-8" );
			if ($parts)
				return implode ( " ", $parts );
		}
		return trim ( html_entity_decode ( strip_tags ( $desc ), ENT_QUOTES, "UTF-8" ) );
	}

	public static function instructions() {
		return "Para anunciar seu caminhão na KADE, envie o código do tipo de veículo, um asterisco e o CEP de onde ele está.\n\n"
			. "Exemplo: " . self::EXAMPLE . "\n\n"
			. "Mande TIPOS para ver a lista de códigos.";
	}

	/** @param VehicleType[] $types */
	public static function typesList(array $types) {
		$lines = array ();
		foreach ( $types as $type )
			$lines [] = $type->getId () . " - " . $type->getDescr ();
		if (! $lines)
			return "A lista de códigos está indisponível no momento. Tente novamente mais tarde.";
		return "Códigos dos veículos:\n\n" . implode ( "\n", $lines ) . "\n\nExemplo: " . self::EXAMPLE;
	}

	public static function registered($id, $typeName, $city, $state, $cep) {
		$cep = ( string ) $cep;
		return "✅ Veículo anunciado!\n\n"
			. "Tipo: " . $typeName . "\n"
			. "Local: " . $city . "/" . $state . " (CEP " . substr ( $cep, 0, 5 ) . "-" . substr ( $cep, 5 ) . ")\n"
			. "Registro nº " . $id . "\n\n"
			. "Transportadoras e embarcadores já podem ver seu caminhão no mapa e entrar em contato.";
	}

	public static function notRegistered($reason) {
		return "❌ Não foi possível anunciar: " . $reason . "\n\n" . self::instructions ();
	}

	/** Links "WhatsApp" e "Telegram" para as páginas do site; só os canais configurados no .env */
	public static function links() {
		$links = array ();
		$number = preg_replace ( '/\D/', "", ( string ) Properties::get ( "whatsapp.number" ) );
		if (strlen ( $number ) >= 12)
			$links [self::WHATSAPP] = "https://wa.me/" . $number;
		$bot = ltrim ( ( string ) Properties::get ( "telegram.bot" ), "@" );
		if (preg_match ( '/^[A-Za-z0-9_]{5,32}$/', $bot ))
			$links [self::TELEGRAM] = "https://t.me/" . $bot;
		return $links;
	}

	/** Nomes dos canais de links(), para o texto: "WhatsApp ou Telegram" */
	public static function linkNames(array $links) {
		return implode ( " ou ", array_intersect_key ( self::channels (), $links ) );
	}
}
