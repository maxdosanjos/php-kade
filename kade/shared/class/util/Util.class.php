<?
class Util {
	public static function bigIntval($value) {
		$value = trim ( $value );
		if (ctype_digit ( $value )) {
			return $value;
		}
		$value = preg_replace ( "/[^0-9](.*)$/", '', $value );
		if (ctype_digit ( $value )) {
			return $value;
		}
		return 0;
	}
	public static function getClientIP() {
		if (array_key_exists ( 'HTTP_CLIENT_IP', $_SERVER )) {
			$ip = trim ( $_SERVER ['HTTP_CLIENT_IP'] );
			if (self::validateIP ( $ip )) {
				return $ip;
			}
		}
		if (array_key_exists ( 'HTTP_X_FORWARDED_FOR', $_SERVER )) {
			$ip = trim ( $_SERVER ['HTTP_X_FORWARDED_FOR'] );
			if (self::validateIP ( $ip )) {
				return $ip;
			} elseif (strpos ( $ip, ',' ) !== false) {
				$ips = explode ( ',', $ip );
				foreach ( $ips as $ip ) {
					$ip = trim ( $ip );
					if (self::validateIP ( $ip )) {
						return $ip;
					}
				}
			} elseif (strpos ( $ip, ';' ) !== false) {
				$ips = explode ( ';', $ip );
				foreach ( $ips as $ip ) {
					$ip = trim ( $ip );
					if (self::validateIP ( $ip )) {
						return $ip;
					}
				}
			}
		}
		if (array_key_exists ( 'REMOTE_ADDR', $_SERVER )) {
			$ip = trim ( $_SERVER ['REMOTE_ADDR'] );
			if (self::validateIP ( $ip )) {
				return $ip;
			}
		}
		
		return '0.0.0.0';
	}
	
	/**
	 * Valida um IP v4
	 * 
	 * @param string $ip:
	 *        	IP a ser validado
	 * @return bool
	 * @see http://rubsphp.blogspot.com.br/2010/12/obter-o-ip-do-cliente.html
	 */
	public static function validateIP($ip) {
		// IPv4
		$vetor = explode ( '.', $ip );
		if (count ( $vetor ) != 4) {
			return false;
		}
		foreach ( $vetor as $i ) {
			if (! is_numeric ( $i ) || $i < 0 || $i > 255) {
				return false;
			}
		}
		return true;
	}
	/**
	 * Data no formato brasileiro (dd/mm/aaaa, dd.mm.aaaa ou dd-mm-aaaa).
	 * Retorna DateTimeCustom ou null se vazia/inválida.
	 */
	public static function parseDateBR($value) {
		if (! preg_match ( '/^\s*(\d{1,2})[\/.\-](\d{1,2})[\/.\-](\d{4})\s*$/', ( string ) $value, $m ))
			return null;
		if (! checkdate ( ( int ) $m [2], ( int ) $m [1], ( int ) $m [3] ))
			return null;
		$date = new DateTimeCustom ( "now" );
		$date->setDate ( ( int ) $m [3], ( int ) $m [2], ( int ) $m [1] );
		return $date;
	}
	/**
	 * Valor monetário: aceita "1.234,56", "70,00", "70" e também "70.5" (vindo do banco).
	 */
	public static function parseMoneyBR($value) {
		$value = trim ( str_replace ( array ( "R$", " " ), "", ( string ) $value ) );
		if ($value === "")
			return 0.0;
		if (strpos ( $value, "," ) !== false)
			$value = str_replace ( ",", ".", str_replace ( ".", "", $value ) );
		return is_numeric ( $value ) ? round ( ( float ) $value, 2 ) : 0.0;
	}
	/**
	 * Cabeçalhos de download de CSV e BOM UTF-8 (para o Excel mostrar os acentos).
	 */
	public static function csvStart($filename) {
		header ( "Content-Type: text/csv; charset=UTF-8" );
		header ( "Content-Disposition: attachment; filename=" . preg_replace ( '/[^A-Za-z0-9_.-]/', '_', $filename ) );
		header ( "Pragma: no-cache" );
		header ( "Expires: 0" );
		echo "\xEF\xBB\xBF";
	}
	/**
	 * Uma linha de CSV separada por ";" (padrão do Excel em português), com aspas quando
	 * preciso. Células que começam com = + - @ são prefixadas com ' para o Excel não as
	 * executar como fórmula: nomes e contatos vêm de cadastro público e de SMS.
	 */
	public static function csvRow(array $cells) {
		$out = array ();
		foreach ( $cells as $cell ) {
			$cell = ( string ) $cell;
			if ($cell !== "" && strpos ( "=+-@\t\r", $cell [0] ) !== false)
				$cell = "'" . $cell;
			if (preg_match ( '/[;"\r\n]/', $cell ))
				$cell = '"' . str_replace ( '"', '""', $cell ) . '"';
			$out [] = $cell;
		}
		return implode ( ";", $out ) . "\r\n";
	}
	public static function mask($val, $mask) {
		$maskared = '';
		$k = 0;
		for($i = 0; $i <= strlen ( $mask ) - 1; $i ++) {
			if ($mask [$i] == '#') {
				if (isset ( $val [$k] ))
					$maskared .= $val [$k ++];
			} else {
				if (isset ( $mask [$i] ))
					$maskared .= $mask [$i];
			}
		}
		return $maskared;
	}
}
?>