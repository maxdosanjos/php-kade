<?php
/**
 * Properties
* Classe com as configurações gerais do sistema
* Este arquivo não possui nenhuma dependência, é só importar e usar
*/
class Properties {
	const PRD = "PRD";
	const DEV = "DEV";
	private static $data = array (
			"INSTANCE" => Properties::DEV,
			//DataBase DEV
			"dbkade_dev.user" => "root",
			"dbkade_dev.password" => "",
			"dbkade_dev.host" => "localhost",
			"dbkade_dev.database" => "kadecaminh",
			"dbkade_dev.port" => 3306,
			"dbkade_dev.DBMS" => "mysql",
			
			"dbcep_dev.user" => "root",
			"dbcep_dev.password" => "manager",
			"dbcep_dev.host" => "localhost",
			"dbcep_dev.database" => "kadecaminh_1",
			"dbcep_dev.port" => 3306,
			"dbkade_dev.DBMS" => "mysql",
			
			//DataBase PRD
			"dbkade_prd.user" => "kadecaminh",
			"dbkade_prd.password" => "kaDE2014",
			"dbkade_prd.host" => "dbmy0063.whservidor.com",
			"dbkade_prd.database" => "kadecaminh",
			"dbkade_prd.port" => 3306,
			"dbkade_prd.DBMS" => "mysql",

			"dbcep_prd.user" => "kadecaminh_1",
			"dbcep_prd.password" => "kadeCEP2014",
			"dbcep_prd.host" => "dbmy0011.whservidor.com",
			"dbcep_prd.database" => "kadecaminh_1",
			"dbcep_prd.port" => 3306,
			"dbcep_prd.DBMS" => "mysql", 
			
			// email			
			"smtp_kadecaminhoes.host" => "smtp.kadecaminhoes.com.br",
			"smtp_kadecaminhoes.port" => 587,
			"smtp_kadecaminhoes.user" => "contato@kadecaminhoes.com.br",
			"smtp_kadecaminhoes.pass" => "kaDE2014",
			"smtp_kadecaminhoes.auth" => true,
			
			//Server SMS
			"server_sms.http_x_real_ip" => "200.195.184.186",
			
			//Arquivo JOB Expirar MSGS
			"job_msg_expired.file_job_expired" => "cront_exp.txt",
			
			//Intervalo Expirar MSGS
			"job_msg_expired.interval_job_expired" => "16"
	);
	/**
	 * Retorna um valor de uma chave
	 */
	public static function get($key) {
		if (! array_key_exists ( $key, self::$data )) {
			return null;
		}
		return self::$data [$key];
	}
	public static function set($key, $value) {
		if ($key != null) {
			self::$data [$key] = $value;
		}
	}
	
	/**
	 * Retorna um conjunto de valores levando
	 * em consideração o prefixo da chave
	 */
	public static function getGroup($prefix, $preservePrefix = false) {
		$output = array ();
		foreach ( self::$data as $key => $value ) {
			if (strpos ( $key, $prefix ) === 0) {
				if ($preservePrefix) {
					$output [$key] = $value;
				} else {
					// pegando o texto logo após o ponto
					if (strpos ( $key, '.' ) !== false) {
						$tmp = explode ( '.', $key );
						if (sizeof ( $tmp ) == 2) {
							$output [$tmp [1]] = $value;
						}
					}
				}
			}
		}
		return $output;
	}
	
	public static function constructor(){
		$host = $_SERVER["HTTP_HOST"] ?? "localhost";
		if ($host === "localhost" || $host === "kade.test") {
			Properties::set("INSTANCE", self::DEV);
		}else{
			Properties::set("INSTANCE", self::PRD);
		}
	}
}

Properties::constructor();
	