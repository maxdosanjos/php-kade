<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Messenger.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/WhatsApp.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Telegram.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/MessengerDAO.class.php");
require_once (dirname ( __FILE__ ) . "/VehicleRegCtrl.class.php");
/**
 * Webhooks do WhatsApp e do Telegram: /webhook/whatsapp e /webhook/telegram (.htaccess).
 *
 * Mesmo cadastro do SMS (TIPO*CEP → VehicleRegCtrl::registerByMessage), com resposta ao
 * motorista na conversa. Cada mensagem vai para o log de mensagens (log_sms, coluna
 * channel) antes de ser tratada; uma mensagem repetida é ignorada.
 * Canal sem o segredo no .env responde 404.
 */
class MessengerCtrl extends AbstractCtrl {
	private $vehicleReg;
	private $dao;

	public function __construct(VehicleRegCtrl $vehicleReg) {
		parent::__construct ();
		$this->vehicleReg = $vehicleReg;
		$this->dao = MessengerDAO::getInstance ();
		$this->setAction ( $_GET ["action"] ?? null );

		$this->guardAction ( array (), AbstractCtrl::ACCESS_PUBLIC );

		switch ($this->getAction ()) {
			case "whatsapp" :
				$this->onWhatsApp ();
				break;
			case "telegram" :
				$this->onTelegram ();
				break;
		}
		$this->respond ( 404 );
	}

	public function onWhatsApp() {
		$whatsApp = new WhatsApp ();
		if (! $whatsApp->isConfigured ())
			$this->respond ( 404 );

		if (($_SERVER ["REQUEST_METHOD"] ?? "") === "GET") {
			$challenge = $whatsApp->verifyChallenge ( $_GET );
			if ($challenge === null)
				$this->respond ( 403 );
			$this->respond ( 200, $challenge );
		}

		$raw = ( string ) file_get_contents ( "php://input" );
		if (! $whatsApp->isSigned ( $raw, $_SERVER ["HTTP_X_HUB_SIGNATURE_256"] ?? "" )) {
			$this->logRejected ( Messenger::WHATSAPP, "Chamada recusada: assinatura inválida (confira o WHATSAPP_APP_SECRET)" );
			$this->respond ( 403 );
		}

		$replies = array ();
		foreach ( WhatsApp::parseMessages ( $raw, $whatsApp->phoneNumberId () ) as $msg ) {
			$logId = $this->claim ( Messenger::WHATSAPP, $msg ["id"], $msg ["from"], $msg ["type"] === "text" ? $msg ["text"] : "[" . $msg ["type"] . "]" );
			if ($logId == 0)
				continue;
			list ( $type, $log, $text ) = $this->answer ( VehicleTraveling::SOURCE_WHATSAPP, Messenger::brazilianPhone ( $msg ["from"] ), $msg ["text"] );
			$error = $whatsApp->send ( $msg ["from"], $text );
			if ($error !== "")
				$log .= " · Resposta não enviada: " . $error;
			$this->dao->updateLog ( $this->getDbConfig (), $logId, $type, $log );
			$replies [] = array ( "to" => $msg ["from"], "text" => $text );
		}
		// A Meta só quer o 200; as respostas no corpo servem para os testes
		$this->respondJson ( array ( "replies" => $replies ) );
	}

	public function onTelegram() {
		$telegram = new Telegram ();
		if (! $telegram->isConfigured ())
			$this->respond ( 404 );
		if (! $telegram->isAllowed ( $_SERVER ["HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN"] ?? "" )) {
			$this->logRejected ( Messenger::TELEGRAM, "Chamada recusada: segredo inválido (confira o TELEGRAM_WEBHOOK_SECRET)" );
			$this->respond ( 403 );
		}

		$update = Telegram::parseUpdate ( file_get_contents ( "php://input" ) );
		if ($update === null)
			$this->respond ( 200 );

		$phone = $this->dao->getLinkedPhone ( $this->getDbConfig (), Messenger::TELEGRAM, $update ["user_id"] );
		$body = $update ["contact_phone"] !== "" ? "[contato " . $update ["contact_phone"] . "]" : $update ["text"];
		$logId = $this->claim ( Messenger::TELEGRAM, $update ["update_id"], $phone !== "" ? $this->digits ( $phone ) : "tg:" . $update ["user_id"], $body );
		if ($logId == 0)
			$this->respond ( 200 );

		$keyboard = null;
		if ($update ["contact_phone"] !== "") {
			list ( $type, $log, $text, $keyboard ) = $this->linkTelegramPhone ( $update );
		} else if ($phone === "") {
			$type = Message::INFO;
			$log = "Pedido o número de telefone";
			$text = "Olá! Para anunciar seu caminhão na KADE, toque no botão abaixo e confirme o seu número de telefone. Ele aparece no anúncio para as transportadoras ligarem para você.";
			$keyboard = Telegram::sharePhoneKeyboard ();
		} else {
			list ( $type, $log, $text ) = $this->answer ( VehicleTraveling::SOURCE_TELEGRAM, $phone, $update ["text"] );
		}
		$this->dao->updateLog ( $this->getDbConfig (), $logId, $type, $log );
		$this->respondJson ( Telegram::reply ( $update ["chat_id"], $text, $keyboard ) );
	}

	/**
	 * Contato enviado pelo botão "Compartilhar meu número". O botão manda o contato do
	 * próprio usuário; um contato encaminhado de outra pessoa não vale.
	 * @return array(tipo do log, detalhe do log, texto para o motorista, teclado)
	 */
	private function linkTelegramPhone(array $update) {
		if ($update ["contact_user_id"] !== $update ["user_id"])
			return array ( Message::ERR, "Contato de outra pessoa", "Use o botão abaixo para enviar o seu próprio número.", Telegram::sharePhoneKeyboard () );
		$phone = Messenger::brazilianPhone ( $update ["contact_phone"] );
		if ($phone === "")
			return array ( Message::ERR, "Número fora do Brasil", "Por enquanto só aceitamos números do Brasil (+55).", Telegram::removeKeyboard () );
		$this->dao->linkPhone ( $this->getDbConfig (), Messenger::TELEGRAM, $update ["user_id"], $phone );
		$text = "Número confirmado: " . $this->phoneMask ( $phone ) . ". Ele aparece no anúncio para as transportadoras ligarem para você.\n\n" . Messenger::instructions ();
		return array ( Message::INFO, "Número confirmado", $text, Telegram::removeKeyboard () );
	}

	/**
	 * Resposta a uma mensagem de texto de quem já tem telefone.
	 * @return array(tipo do log, detalhe do log, texto para o motorista)
	 */
	private function answer($source, $phone, $text) {
		if (Messenger::isTypesRequest ( $text ))
			return array ( Message::INFO, "Lista de códigos enviada", Messenger::typesList ( $this->vehicleReg->getTypeVechicles () ) );
		$registration = Messenger::parseRegistration ( $text );
		if ($registration === null)
			return array ( Message::INFO, "Instruções enviadas", Messenger::instructions () );
		if ($phone === "")
			return array ( Message::ERR, "Número fora do Brasil", "Por enquanto só aceitamos números do Brasil (+55)." );

		$vehicle = $this->vehicleReg->registerByMessage ( $source, $phone, $registration ["type"], $registration ["cep"] );
		$result = Messenger::messageText ( $this->vehicleReg->getMessage () );
		if ($vehicle === null)
			return array ( Message::ERR, $result, Messenger::notRegistered ( $result ) );

		$typeName = $registration ["type"];
		foreach ( $this->vehicleReg->getTypeVechicles () as $vehicleType )
			if ($vehicleType->getId () == $registration ["type"])
				$typeName = $vehicleType->getDescr ();
		$address = $vehicle->getAddress ();
		return array ( Message::SUCCESS, $result, Messenger::registered ( $vehicle->getId (), $typeName, $address->getCity (), $address->getState (), $address->getCep () ) );
	}

	/** Grava a mensagem como "em processamento"; 0 se ela já tinha chegado */
	private function claim($channel, $msgId, $from, $body) {
		return $this->dao->insertLog ( $this->getDbConfig (), $channel, $msgId, $from, $body, $this->ip (), $_SERVER ["REQUEST_URI"] ?? "", Message::ERR, "Em processamento" );
	}

	private function logRejected($channel, $log) {
		try {
			$this->dao->insertLog ( $this->getDbConfig (), $channel, null, "", "", $this->ip (), $_SERVER ["REQUEST_URI"] ?? "", Message::ERR, $log );
		} catch ( PDOException $e ) {
			error_log ( "MessengerCtrl: " . $e->getMessage () );
		}
	}

	private function ip() {
		return $_SERVER ["HTTP_X_REAL_IP"] ?? $_SERVER ["REMOTE_ADDR"] ?? "";
	}

	/** "(43) 999990003" → "5543999990003", como o WhatsApp mostra o número no log */
	private function digits($phone) {
		return "55" . preg_replace ( '/\D/', "", $phone );
	}

	private function phoneMask($phone) {
		$vo = new Phone ();
		$vo->setPhoneByMask ( $phone );
		return $vo->getPhoneMask ();
	}

	private function respond($status, $body = "") {
		http_response_code ( $status );
		header ( "Content-Type: text/plain; charset=UTF-8" );
		echo $body;
		exit ();
	}

	private function respondJson(array $data) {
		header ( "Content-Type: application/json; charset=UTF-8" );
		echo json_encode ( $data, JSON_UNESCAPED_UNICODE );
		exit ();
	}
}

// VehicleRegCtrl.class.php cria o $ctrlVehReg ao ser incluído (o PHPStan não enxerga)
$ctrlMessenger = new MessengerCtrl ( $ctrlVehReg ?? new VehicleRegCtrl () );
?>
