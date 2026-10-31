<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/CepService.class.php");
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
					   '" . Criteria::escapeString($obj->getCep ()) . "'
					 , '" . Criteria::escapeString($obj->getStreet ()) . "'
					 , '" . Criteria::escapeString($obj->getNumber ()) . "'
					 , '" . Criteria::escapeString($obj->getComplement ()) . "'
					 , '" . Criteria::escapeString($obj->getNeighborhood ()) . "'
					 , '" . Criteria::escapeString($obj->getCity ()) . "'
					 , '" . Criteria::escapeString($obj->getState ()) . "'
					 , '" . Criteria::escapeString($obj->getStateNm ()) . "'
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
				   SET  `cep`			 = '" . Criteria::escapeString($obj->getCep ()) . "'
					  , `street`		 = '" . Criteria::escapeString($obj->getStreet ()) . "'
					  , `number`		 = '" . Criteria::escapeString($obj->getNumber ()) . "'
					  , `complement`	 = '" . Criteria::escapeString($obj->getComplement ()) . "'
					  , `neighborhood`   = '" . Criteria::escapeString($obj->getNeighborhood ()) . "'
					  , `city`			 = '" . Criteria::escapeString($obj->getCity ()) . "'
					  , `state`			 = '" . Criteria::escapeString($obj->getState ()) . "'
					  , `state_nm`		 = '" . Criteria::escapeString($obj->getStateNm ()) . "'
				WHERE 	`id`		     = '" . Criteria::escapeString($obj->getId ()) . "'";
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
	/**
	 * Preenche rua, bairro, cidade e UF pelo CEP. Fontes: cache local, banco de CEP
	 * antigo (se configurado), ViaCEP e BrasilAPI (veja CepService). $dbConfig é o
	 * banco principal, onde fica o cache. Antes os chamadores conectavam primeiro no
	 * banco de CEP: sem ele, a busca falhava sem nem tentar o ViaCEP.
	 */
	public function getAddressByZipCode($dbConfig, Address $address) {
		$cep = CepService::normalize ( $address->getCep () );
		if ($cep === "")
			throw new Exception ( "CEP inválido." );

		if ($dbConfig instanceof DBConfig)
			$db = DataBase::getInstance ( $dbConfig );
		else if ($dbConfig instanceof DataBase)
			$db = $dbConfig;
		else
			throw new PDOException ( "Conexão inválida" );

		$legacy = null;
		try {
			$legacy = $this->loadDBConfigCep ();
		} catch ( PDOException $e ) {
			// Banco de CEP antigo não configurado: só cache e APIs
		}

		$found = ( new CepService ( $db, $legacy ) )->lookup ( $cep );
		if ($found == null)
			throw new Exception ( "CEP não encontrado." );

		$_ufs = $this->getListUf ();
		$address->setCep ( $found ["cep"] );
		$address->setStreet ( $found ["street"] );
		$address->setNeighborhood ( $found ["neighborhood"] );
		$address->setCity ( $found ["city"] );
		$address->setState ( $found ["state"] );
		$address->setStateNm ( $_ufs [$found ["state"]] ?? "" );
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
		// Senha pode ser vazia (ex.: root local do Laragon); as demais chaves são obrigatórias
		$requiredKeys = array('user','host','port','database');
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