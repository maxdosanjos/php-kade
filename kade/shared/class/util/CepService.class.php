<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/DBConfig.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/DataBase.class.php");

/**
 * Busca de CEP sem depender de um banco de CEP próprio (o kadecaminh_1 nunca
 * esteve no repositório e a base oficial dos Correios é paga e não pode ser
 * redistribuída).
 *
 * Ordem: cache local (tabela cep_cache, migração 008) → banco de CEP antigo, se
 * ainda configurado (DBCEP_*) → ViaCEP → BrasilAPI. O que vem de fora é gravado no
 * cache; se todas as fontes falharem, um registro vencido do cache ainda é usado.
 *
 * Resultado: array("cep", "street", "neighborhood", "city", "state", "source") ou null.
 */
class CepService {
	const CACHE_DAYS = 180;
	const TIMEOUT = 4; // segundos por fonte externa

	private $db;
	private $legacyConfig;

	/**
	 * @param PDO $db conexão com o banco principal (onde fica o cache)
	 * @param DBConfig|null $legacyConfig banco de CEP antigo (procedure procurar_cep), opcional
	 */
	public function __construct(PDO $db = null, DBConfig $legacyConfig = null) {
		$this->db = $db;
		$this->legacyConfig = $legacyConfig;
	}

	/**
	 * Só os 8 dígitos. CEP gravado como número (coluna address.cep é INT) perde o
	 * zero inicial: 7 dígitos voltam a ter o zero.
	 */
	public static function normalize($cep) {
		$digits = preg_replace ( '/\D/', '', ( string ) $cep );
		if (strlen ( $digits ) == 7)
			$digits = "0" . $digits;
		return (strlen ( $digits ) == 8 && $digits !== "00000000") ? $digits : "";
	}

	public function lookup($cep) {
		$cep = self::normalize ( $cep );
		if ($cep === "")
			return null;

		$cached = $this->fromCache ( $cep );
		if ($cached != null && ! $cached ["stale"])
			return $this->clean ( $cached );

		foreach ( array ("fromLegacyDb", "fromViaCep", "fromBrasilApi") as $source ) {
			$found = $this->$source ( $cep );
			if ($found != null) {
				$this->saveCache ( $found );
				return $found;
			}
		}
		// Fontes fora do ar: o dado vencido do cache é melhor que nenhum
		return $cached != null ? $this->clean ( $cached ) : null;
	}

	public static function parseViaCep($json) {
		$data = json_decode ( ( string ) $json, true );
		if (! is_array ( $data ) || ! empty ( $data ["erro"] ))
			return null;
		return self::result ( $data ["cep"] ?? "", $data ["logradouro"] ?? "", $data ["bairro"] ?? "", $data ["localidade"] ?? "", $data ["uf"] ?? "", "viacep" );
	}

	public static function parseBrasilApi($json) {
		$data = json_decode ( ( string ) $json, true );
		if (! is_array ( $data ) || isset ( $data ["errors"] ))
			return null;
		return self::result ( $data ["cep"] ?? "", $data ["street"] ?? "", $data ["neighborhood"] ?? "", $data ["city"] ?? "", $data ["state"] ?? "", "brasilapi" );
	}

	/** Valida e padroniza o que veio de qualquer fonte; sem cidade ou UF válida não serve */
	private static function result($cep, $street, $neighborhood, $city, $state, $source) {
		$cep = self::normalize ( $cep );
		$city = trim ( ( string ) $city );
		$state = strtoupper ( trim ( ( string ) $state ) );
		if ($cep === "" || $city === "" || ! preg_match ( '/^[A-Z]{2}$/', $state ))
			return null;
		return array (
				"cep" => $cep,
				"street" => mb_substr ( trim ( ( string ) $street ), 0, 255 ),
				"neighborhood" => mb_substr ( trim ( ( string ) $neighborhood ), 0, 72 ),
				"city" => mb_substr ( $city, 0, 60 ),
				"state" => $state,
				"source" => $source
		);
	}

	private function clean(array $row) {
		unset ( $row ["stale"] );
		return $row;
	}

	private function fromCache($cep) {
		if ($this->db == null)
			return null;
		try {
			$stmt = $this->db->prepare ( "SELECT `cep`, `street`, `neighborhood`, `city`, `state`, `source`,
					`updated_at` < NOW() - INTERVAL " . self::CACHE_DAYS . " DAY AS `stale`
				   FROM `cep_cache` WHERE `cep` = ?" );
			$stmt->execute ( array ( $cep ) );
			$row = $stmt->fetch ( PDO::FETCH_ASSOC );
			if (! $row)
				return null;
			$row ["stale"] = ( bool ) $row ["stale"];
			return $row;
		} catch ( PDOException $e ) {
			error_log ( "CepService cache: " . $e->getMessage () );
			return null;
		}
	}

	private function saveCache(array $found) {
		if ($this->db == null)
			return;
		try {
			$stmt = $this->db->prepare ( "INSERT INTO `cep_cache` (`cep`, `street`, `neighborhood`, `city`, `state`, `source`, `updated_at`)
				VALUES (?, ?, ?, ?, ?, ?, NOW())
				ON DUPLICATE KEY UPDATE `street` = VALUES(`street`), `neighborhood` = VALUES(`neighborhood`),
					`city` = VALUES(`city`), `state` = VALUES(`state`), `source` = VALUES(`source`), `updated_at` = NOW()" );
			$stmt->execute ( array ( $found ["cep"], $found ["street"], $found ["neighborhood"], $found ["city"], $found ["state"], $found ["source"] ) );
		} catch ( PDOException $e ) {
			error_log ( "CepService cache: " . $e->getMessage () );
		}
	}

	private function fromLegacyDb($cep) {
		if ($this->legacyConfig == null)
			return null;
		try {
			$db = DataBase::getInstance ( $this->legacyConfig );
			$stmt = $db->prepare ( "CALL procurar_cep(?)" );
			$stmt->execute ( array ( $cep ) );
			$row = $stmt->fetch ( PDO::FETCH_ASSOC );
			$stmt->closeCursor ();
			if (! $row)
				return null;
			return self::result ( $cep, $row ["logradouro"] ?? "", $row ["bairro"] ?? "", $row ["cidade"] ?? "", $row ["uf"] ?? "", "dne" );
		} catch ( PDOException $e ) {
			error_log ( "CepService banco de CEP: " . $e->getMessage () );
			return null;
		}
	}

	private function fromViaCep($cep) {
		return self::parseViaCep ( $this->httpGet ( "https://viacep.com.br/ws/" . $cep . "/json/" ) );
	}

	private function fromBrasilApi($cep) {
		return self::parseBrasilApi ( $this->httpGet ( "https://brasilapi.com.br/api/cep/v2/" . $cep ) );
	}

	/** GET com tempo curto; null em erro ou status diferente de 200. Sobrescrito nos testes. */
	protected function httpGet($url) {
		if (function_exists ( "curl_init" )) {
			$ch = curl_init ( $url );
			curl_setopt_array ( $ch, array (
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_CONNECTTIMEOUT => 2,
					CURLOPT_TIMEOUT => self::TIMEOUT,
					CURLOPT_USERAGENT => "KADE Caminhoes (busca de CEP)",
					CURLOPT_HTTPHEADER => array ( "Accept: application/json" )
			) );
			$body = curl_exec ( $ch );
			$status = curl_getinfo ( $ch, CURLINFO_RESPONSE_CODE );
			curl_close ( $ch );
			return ($body !== false && $status == 200) ? $body : null;
		}
		$ctx = stream_context_create ( array ( "http" => array ( "timeout" => self::TIMEOUT, "ignore_errors" => true, "header" => "Accept: application/json\r\nUser-Agent: KADE Caminhoes\r\n" ) ) );
		$body = @file_get_contents ( $url, false, $ctx );
		$ok = isset ( $http_response_header [0] ) && strpos ( $http_response_header [0], " 200" ) !== false;
		return ($body !== false && $ok) ? $body : null;
	}
}
