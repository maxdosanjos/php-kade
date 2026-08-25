<?php
require_once (dirname ( __FILE__ ) . "/DBConfig.class.php");
require_once (dirname ( __FILE__ ) . "/DataBase.class.php");
require_once (dirname ( __FILE__ ) . "/Criteria.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/AddressVO.class.php");
class AddressDAO {
	private static $res = null;
	private function __construct() {
	}
	public static function getInstance() {
		if (self::$res == null)
			self::$res = new self ();
		return self::$res;
	}
	public function insert($dbConfig, Address $obj) {
		$sql = "INSERT INTO `address` 
					(   `cep`
					  , `street`
					  , `number`
					  , `complement`
					  , `neighborhood`
					  , `city`
					  , `state`
					  , `state_nm`
					 )
				VALUES 
					(
					   '" . $obj->getCep () . "'
					 , '" . $obj->getStreet () . "'
					 , '" . $obj->getNumber () . "'
					 , '" . $obj->getComplement () . "'
					 , '" . $obj->getNeighborhood () . "'
					 , '" . $obj->getCity () . "'
					 , '" . $obj->getState () . "'
					 , '" . $obj->getStateNm () . "'
					 )";
		if ($dbConfig instanceof DBConfig)
			$db = DataBase::getInstance ( $dbConfig );
		else if ($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException ( "Conexão Inválida" );
			
			// echo $sql;
		
		$out = $db->exec ( $sql );
		$id = $db->lastInsertId ();
		
		if ($dbConfig instanceof DBConfig)
			$db = null;
		$obj->setId ( $id );
		
		if($out == 0)
			throw new PDOException("Erro ao criar Endereço!");
	}
	public function update($dbConfig, Address $obj) {
		$sql = "UPDATE  `address` 
				   SET  `cep`			 = '" . $obj->getCep () . "'
					  , `street`		 = '" . $obj->getStreet () . "'
					  , `number`		 = '" . $obj->getNumber () . "'
					  , `complement`	 = '" . $obj->getComplement () . "'
					  , `neighborhood`   = '" . $obj->getNeighborhood () . "'
					  , `city`			 = '" . $obj->getCity () . "'
					  , `state`			 = '" . $obj->getState () . "'
					  , `state_nm`		 = '" . $obj->getStateNm () . "'
				WHERE 	`id`		     = '" . $obj->getId () . "'";
		if ($dbConfig instanceof DBConfig)
			$db = DataBase::getInstance ( $dbConfig );
		else if ($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException ( "Conexão Inválida" );
			
			// echo $sql;
		
		$out = $db->exec ( $sql );		
		if ($dbConfig instanceof DBConfig)
			$db = null;
	}
	public function getSingle($dbConfig, Criteria $criteria = null) {
		$sql = "
			    SELECT 	  addr.id
					   	, addr.cep
						, addr.street
						, addr.number
						, addr.complement
						, addr.neighborhood
						, addr.city
						, addr.state
						, addr.state_nm
				  FROM 	address addr
				";
		if ($criteria != null) {
			$sql .= $criteria->getWHERE ();
			$sql .= $criteria->getORDER ();
			$sql .= $criteria->getLIMIT ();
		}
		
		if ($dbConfig instanceof DBConfig)
			$db = DataBase::getInstance ( $dbConfig );
		else if ($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException ( "Conexão inválida" );
		
		$result = $db->query ( $sql );
		
		$address = null;
		$objDAO = $result->fetchObject ();
		if ($objDAO->id != null) {
			$address = new Address ();
			$address->setId ( $objDAO->id );
			$address->setCep ( $objDAO->cep );
			$address->setStreet ( $objDAO->street );
			$address->setNumber ( $objDAO->number );
			$address->setComplement ( $objDAO->complement );
			$address->setNeighborhood ( $objDAO->neighborhood );
			$address->setCity ( $objDAO->city );
			$address->setState ( $objDAO->state );
			$address->setStateNm ( $objDAO->state_nm );
		}
		if ($dbConfig instanceof DBConfig)
			$db = null;
		return $address;
	}
	public function getAddressByZipCode($dbConfig, Address $address) {
		// Normalize CEP to digits only
		$rawCep = (string) $address->getCep();
		$cepDigits = preg_replace('/\D/', '', $rawCep);
		if (strlen($cepDigits) != 8) {
			throw new PDOException("CEP inválido");
		}
		// Try local DB lookup first (stored procedure 'procurar_cep')
		$sql = "CALL procurar_cep('" . $cepDigits . "');";
		$db = null;
		// Attempt DB lookup inside try/catch so connection failures fall back to external API		try {
			if ($dbConfig instanceof DBConfig)
				$db = DataBase::getInstance ( $dbConfig );
			else if ($dbConfig instanceof DataBase)
				$db = $dbConfig;
			else
				throw new PDOException ( "Conexão inválida" );
		
			$result = @$db->query ( $sql );
			if ($result && ($objDAO = $result->fetchObject())) {
				$address->setCep($cepDigits);
				$address->setState ( isset($objDAO->uf) ? $objDAO->uf : '' );
				$address->setStateNm ( isset($objDAO->uf_name) ? $objDAO->uf_name : '' );
				$address->setCity ( isset($objDAO->cidade) ? $objDAO->cidade : '' );
				$address->setNeighborhood ( isset($objDAO->bairro) ? $objDAO->bairro : '' );
				$address->setStreet ( isset($objDAO->logradouro) ? $objDAO->logradouro : '' );
				if ($dbConfig instanceof DBConfig)
					$db = null;
				return;
			}
		} catch (Exception $e) {
			// Log DB lookup failures and continue to fallback (do not propagate exception)
			error_log("AddressDAO.getAddressByZipCode DB lookup failed: " . $e->getMessage());
			if ($dbConfig instanceof DBConfig)
				$db = null;
		}
		
		// Fallback: query ViaCEP public API (https://viacep.com.br)
		$viacepUrl = "https://viacep.com.br/ws/" . $cepDigits . "/json/";
		$ctx = stream_context_create(array('http' => array('timeout' => 5)));
		$json = @file_get_contents($viacepUrl, false, $ctx);
		if ($json !== false) {
			$data = json_decode($json);
			if ($data && !isset($data->erro)) {
				$address->setCep($cepDigits);
				$address->setStreet(isset($data->logradouro) ? $data->logradouro : '');
				$address->setNeighborhood(isset($data->bairro) ? $data->bairro : '');
				$address->setCity(isset($data->localidade) ? $data->localidade : '');
				$address->setState(isset($data->uf) ? $data->uf : '');
				$address->setStateNm('');
				if ($dbConfig instanceof DBConfig)
					$db = null;
				return;
			}
		}
		
		if ($dbConfig instanceof DBConfig)
			$db = null;
		
		throw new PDOException ( "CEP não encontrado" );
	}
	public function loadDBConfigCep() {
		if (Properties::get ( "INSTANCE" ) == "PRD") {
			$data = Properties::getGroup ( "dbcep_prd" );
		} else {
			$data = Properties::getGroup ( "dbcep_dev" );
		}
		
		if (! is_array ( $data ) || sizeof ( $data ) == 0) {
			throw new PDOException ( "Conexão inválida" );
		}
		// Validate required keys exist before attempting to build DBConfig
		$requiredKeys = array('user','password','host','port','database');
		foreach ($requiredKeys as $rk) {
			if (!isset($data[$rk]) || $data[$rk] === '') {
				throw new PDOException("Conexão CEP não configurada (missing {$rk})");
			}
		}
		
		$dbConfigCep = new DBConfig ();
		$dbConfigCep->setUser ( $data ["user"] );
		$dbConfigCep->setPassword ( $data ["password"] );
		$dbConfigCep->setHost ( $data ["host"] );
		$dbConfigCep->setPort ( $data ["port"] );
		$dbConfigCep->setDatabase ( $data ["database"] );
		
		$dbms = isset($data['DBMS']) ? strtolower($data['DBMS']) : 'mysql';
		switch ($dbms) {
			case "mssql" :
			case "sqlserver" :
				$dbConfigCep->setSGBD ( DBConfig::MSSQL );
				break;
			default :
				$dbConfigCep->setSGBD ( DBConfig::MYSQL );
				break;
		}
		
		return $dbConfigCep;
	}
	
	public function getListUf(){
		$_result = array();
	
	
		$_result["AC"] = "Acre";
		$_result["AL"] = "Alagoas";
		$_result["AP"] = "Amapá";
		$_result["AM"] = "Amazonas";
		$_result["BA"] = "Bahia";
		$_result["CE"] = "Ceará";
		$_result["DF"] = "Distrito Federal";
		$_result["ES"] = "Espírito Santo";
		$_result["GO"] = "Goiás";
		$_result["MA"] = "Maranhão";
		$_result["MT"] = "Mato Grosso";
		$_result["MS"] = "Mato Grosso do Sul";
		$_result["MG"] = "Minas Gerais";
		$_result["PR"] = "Paraná";
		$_result["PB"] = "Paraíba";
		$_result["PA"] = "Pará";
		$_result["PE"] = "Pernambuco";
		$_result["PI"] = "Piauí";
		$_result["RN"] = "Rio Grande do Norte";
		$_result["RS"] = "Rio Grande do Sul";
		$_result["RJ"] = "Rio de Janeiro";
		$_result["RO"] = "Rondônia";
		$_result["RR"] = "Roraima";
		$_result["SC"] = "Santa Catarina";
		$_result["SE"] = "Sergipe";
		$_result["SP"] = "São Paulo";
		$_result["TO"] = "Tocantins";
	
		return $_result;
	
	}
	
}
?>