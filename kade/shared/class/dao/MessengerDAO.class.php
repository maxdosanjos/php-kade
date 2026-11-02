<?php
require_once (dirname ( __FILE__ ) . "/DBConfig.class.php");
require_once (dirname ( __FILE__ ) . "/DataBase.class.php");
/**
 * Log das mensagens do WhatsApp e do Telegram (mesma tabela log_sms, coluna channel) e
 * vínculo usuário do Telegram → telefone (messenger_contact). Migração 009.
 * Tudo vem de rotas públicas: só prepared statement e cortado ao tamanho das colunas.
 */
class MessengerDAO {
	private static $res = null;
	private function __construct() {
	}
	public static function getInstance() {
		if (self::$res == null)
			self::$res = new self ();
		return self::$res;
	}

	private function db($dbConfig) {
		if ($dbConfig instanceof DataBase)
			return $dbConfig;
		if ($dbConfig instanceof DBConfig)
			return DataBase::getInstance ( $dbConfig );
		throw new PDOException ( "Conexão inválida" );
	}

	/**
	 * Grava a mensagem recebida antes de tratá-la e devolve o id do log. Devolve 0 se a
	 * mesma mensagem (canal + msg_id) já chegou: a Meta e o Telegram reenviam quando a
	 * resposta demora, e o veículo não pode ser gravado duas vezes.
	 */
	public function insertLog($dbConfig, $channel, $msgId, $from, $body, $ip, $url, $type, $log) {
		$db = $this->db ( $dbConfig );
		$stmt = $db->prepare ( "INSERT IGNORE INTO `log_sms` (`channel`, `msg_id`, `from`, `body`, `ip_trat`, `url_request`, `type_log`, `log`)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?)" );
		$stmt->execute ( array (
				mb_substr ( ( string ) $channel, 0, 10 ),
				$msgId === null ? null : mb_substr ( ( string ) $msgId, 0, 128 ),
				mb_substr ( ( string ) $from, 0, 45 ),
				mb_substr ( ( string ) $body, 0, 2000 ),
				mb_substr ( ( string ) $ip, 0, 45 ),
				mb_substr ( ( string ) $url, 0, 2000 ),
				mb_substr ( ( string ) $type, 0, 45 ),
				mb_substr ( ( string ) $log, 0, 4000 )
		) );
		return $stmt->rowCount () > 0 ? ( int ) $db->lastInsertId () : 0;
	}

	public function updateLog($dbConfig, $id, $type, $log) {
		$stmt = $this->db ( $dbConfig )->prepare ( "UPDATE `log_sms` SET `type_log` = ?, `log` = ? WHERE `id` = ?" );
		$stmt->execute ( array ( mb_substr ( ( string ) $type, 0, 45 ), mb_substr ( ( string ) $log, 0, 4000 ), ( int ) $id ) );
	}

	/** Telefone "(DD) NNNNNNNNN" vinculado ao usuário do canal, ou "" */
	public function getLinkedPhone($dbConfig, $channel, $externalId) {
		$stmt = $this->db ( $dbConfig )->prepare ( "SELECT `phone` FROM `messenger_contact` WHERE `channel` = ? AND `external_id` = ?" );
		$stmt->execute ( array ( ( string ) $channel, ( string ) $externalId ) );
		return ( string ) $stmt->fetchColumn ();
	}

	public function linkPhone($dbConfig, $channel, $externalId, $phone) {
		$stmt = $this->db ( $dbConfig )->prepare ( "INSERT INTO `messenger_contact` (`channel`, `external_id`, `phone`) VALUES (?, ?, ?)
			ON DUPLICATE KEY UPDATE `phone` = VALUES(`phone`)" );
		$stmt->execute ( array ( ( string ) $channel, mb_substr ( ( string ) $externalId, 0, 64 ), mb_substr ( ( string ) $phone, 0, 20 ) ) );
	}
}
