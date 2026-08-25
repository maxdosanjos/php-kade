<?php
/**
 * @autor Vinícius Cesar Dias
 */
class DBConfig {
	private $user;
	private $password;
	private $host;
	private $database;
	private $port;
	private $SGBD;
	private $strConnection;
	
	const MYSQL    = 0;//"MYSQL";
	const MSSQL    = 1;//"MSSQL";
	const ORACLE   = 2;//"ORACLE";
	
	/**
	 * Define a string de conexão PDO.
	 * @access public
	 * @since 09.04.2011
	 * @param string $strConnection
	 * @return string
	 */
	public function setStrConnection($strConnection)
	{
		$this->strConnection = $strConnection;
	}
	
	public function getStrConnection()
	{	
		if(empty($this->strConnection))
		{
			switch($this->SGBD)
			{
				case DBConfig::MYSQL:
					return "mysql:host=".$this->host.";port=".$this->port.";dbname=".$this->database.";charset=utf8mb4";
				break;
				case DBConfig::MSSQL:
					return "dblib:host=".$this->host.":".$this->port.";dbname=".$this->database."";
				break;
				case DBConfig::ORACLE:
					return "OCI:dbname=".$this->host.";charset=UTF-8";
				break;
				default;
					return null;
			}
		}
		return $this->strConnection;
	}
	
	/**
	 * Define o usuário.
	 * @access public
	 * @since 24.09.2009
	 * @param string $user
	 * @return DBConfig
	 */
	public function setUser($user)
	{
		$this->user = substr(trim($user),0,200); return $this;
	}

	/**
	 * Recupera o usuário.
	 * @access public
	 * @since 24.09.2009
	 * @return string
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Define a senha.
	 * @access public
	 * @since 24.09.2009
	 * @param string $pass
	 * @return DBConfig
	 */
	public function setPassword($pass)
	{
		$this->password = substr(trim($pass),0,200); return $this;
	}

	/**
	 * Recupera a senha.
	 * @access public
	 * @since 24.09.2009
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * Define o host.
	 * @access public
	 * @since 24.09.2009
	 * @param string $host
	 * @return DBConfig
	 */
	public function setHost($host)
	{
		$this->host = substr(trim($host),0,200); return $this;
	}

	/**
	 * Recupera o host.
	 * @access public
	 * @since 24.09.2009
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * Define o banco de dados.
	 * @access public
	 * @since 24.09.2009
	 * @param string $db
	 * @return DBConfig
	 */
	public function setDatabase($db)
	{
		$this->database = substr(trim($db),0,200); return $this;
	}

	/**
	 * Recupera a database.
	 * @access public
	 * @since 24.09.2009
	 * @return string
	 */
	public function getDatabase()
	{
		return $this->database;
	}
	
	/**
	 * Define a porta de comunicação
	 * @access public 
	 * @since 05.04.2010
	 * @return DBConfig
	 */
	public function setPort($port)
	{
		$this->port = abs(intval($port)); return $this;
	}
	
	/**
	 * Recupera a porta.
	 * @access public
	 * @since 05.04.2010
	 * @return int
	 */
	public function getPort()
	{
		return $this->port;
	}
	
	/**
	 * Define o SGBD
	 * @access public 
	 * @since 05.04.2010
	 * @return DBConfig
	 */
	public function setSGBD($SGBD)
	{
		$this->SGBD = trim($SGBD); return $this;
	}
	
	/**
	 * Recupera o SGBD.
	 * @access public
	 * @since 05.04.2010
	 * @return string
	 */
	public function getSGBD()
	{
		return $this->SGBD;
	}
}
?>
