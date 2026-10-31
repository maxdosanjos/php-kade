<?php
/**
 * Properties
* Classe com as configurações gerais do sistema
* Este arquivo não possui nenhuma dependência, é só importar e usar
*/
class Properties {
	const PRD = "PRD";
	const DEV = "DEV";
	private static $env = array();
	private static $data = array (
			"INSTANCE" => Properties::DEV,
			//DataBase DEV
			"dbkade_dev.user" => null,
			"dbkade_dev.password" => null,
			"dbkade_dev.host" => null,
			"dbkade_dev.database" => null,
			"dbkade_dev.port" => null,
			"dbkade_dev.DBMS" => "mysql",

			"dbcep_dev.user" => null,
			"dbcep_dev.password" => null,
			"dbcep_dev.host" => null,
			"dbcep_dev.database" => null,
			"dbcep_dev.port" => null,
			"dbcep_dev.DBMS" => "mysql",

			//DataBase PRD
			"dbkade_prd.user" => null,
			"dbkade_prd.password" => null,
			"dbkade_prd.host" => null,
			"dbkade_prd.database" => null,
			"dbkade_prd.port" => null,
			"dbkade_prd.DBMS" => "mysql",

			"dbcep_prd.user" => null,
			"dbcep_prd.password" => null,
			"dbcep_prd.host" => null,
			"dbcep_prd.database" => null,
			"dbcep_prd.port" => null,
			"dbcep_prd.DBMS" => "mysql",

			// email
			"smtp_kadecaminhoes.host" => null,
			"smtp_kadecaminhoes.port" => null,
			"smtp_kadecaminhoes.user" => null,
			"smtp_kadecaminhoes.pass" => null,
			"smtp_kadecaminhoes.auth" => false,

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

	private static function loadEnv() {
		$envFiles = array(
			dirname(__FILE__) . "/../../../../../.env",
			dirname(__FILE__) . "/../../../../.env"
		);

		foreach ($envFiles as $envFile) {
			if (!is_readable($envFile)) {
				continue;
			}
			$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			foreach ($lines as $line) {
				$line = trim($line);
				if ($line === "" || strpos($line, "#") === 0) {
					continue;
				}
				if (preg_match('/^(?:export\s+)?([A-Z][A-Z0-9_]*)\s*=\s*(.*)$/', $line, $matches)) {
					$value = trim($matches[2]);
					if (strlen($value) >= 2 &&
						(($value[0] === '"' && substr($value, -1) === '"') ||
						($value[0] === "'" && substr($value, -1) === "'"))) {
						$value = substr($value, 1, -1);
					}
					self::$env[$matches[1]] = $value;
				}
			}
			break;
		}
	}

	private static function env($key, $default = null) {
		$value = getenv($key);
		if ($value !== false) {
			return $value;
		}
		return array_key_exists($key, self::$env) ? self::$env[$key] : $default;
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
		self::loadEnv();
		self::$data["dbkade_dev.user"] = self::env("DBKADE_DEV_USER");
		self::$data["dbkade_dev.password"] = self::env("DBKADE_DEV_PASSWORD");
		self::$data["dbkade_dev.host"] = self::env("DBKADE_DEV_HOST");
		self::$data["dbkade_dev.database"] = self::env("DBKADE_DEV_DATABASE");
		self::$data["dbkade_dev.port"] = self::env("DBKADE_DEV_PORT");
		self::$data["dbcep_dev.user"] = self::env("DBCEP_DEV_USER");
		self::$data["dbcep_dev.password"] = self::env("DBCEP_DEV_PASSWORD");
		self::$data["dbcep_dev.host"] = self::env("DBCEP_DEV_HOST");
		self::$data["dbcep_dev.database"] = self::env("DBCEP_DEV_DATABASE");
		self::$data["dbcep_dev.port"] = self::env("DBCEP_DEV_PORT");
		self::$data["dbkade_prd.user"] = self::env("DBKADE_PRD_USER");
		self::$data["dbkade_prd.password"] = self::env("DBKADE_PRD_PASSWORD");
		self::$data["dbkade_prd.host"] = self::env("DBKADE_PRD_HOST");
		self::$data["dbkade_prd.database"] = self::env("DBKADE_PRD_DATABASE");
		self::$data["dbkade_prd.port"] = self::env("DBKADE_PRD_PORT");
		self::$data["dbcep_prd.user"] = self::env("DBCEP_PRD_USER");
		self::$data["dbcep_prd.password"] = self::env("DBCEP_PRD_PASSWORD");
		self::$data["dbcep_prd.host"] = self::env("DBCEP_PRD_HOST");
		self::$data["dbcep_prd.database"] = self::env("DBCEP_PRD_DATABASE");
		self::$data["dbcep_prd.port"] = self::env("DBCEP_PRD_PORT");
		self::$data["smtp_kadecaminhoes.host"] = self::env("SMTP_HOST");
		self::$data["smtp_kadecaminhoes.port"] = self::env("SMTP_PORT");
		self::$data["smtp_kadecaminhoes.user"] = self::env("SMTP_USER");
		self::$data["smtp_kadecaminhoes.pass"] = self::env("SMTP_PASSWORD");
		self::$data["smtp_kadecaminhoes.auth"] = filter_var(
			self::env("SMTP_AUTH", false),
			FILTER_VALIDATE_BOOLEAN
		);
		// Remetente dos e-mails (precisa ser do domínio do SMTP para não cair em spam)
		self::$data["smtp_kadecaminhoes.from"] = self::env("SMTP_FROM");
		// URL pública do site, usada nos links enviados por e-mail (ex.: https://www.kadecaminhoes.com.br)
		self::$data["app.url"] = self::env("APP_URL");
		$host = $_SERVER["HTTP_HOST"] ?? "localhost";
		if ($host === "localhost" || $host === "kade.test") {
			Properties::set("INSTANCE", self::DEV);
		}else{
			Properties::set("INSTANCE", self::PRD);
		}
	}
}

Properties::constructor();
