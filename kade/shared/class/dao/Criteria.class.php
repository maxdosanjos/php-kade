<?php
/**
 * Criteria
 * Classe para geração de instruções SQL. Baseadas em testes e 
 * na classe Criteria do Hibernate
 * @author Vinícius Cesar Dias
 * @criado 10/06/2010 16:00
 * @version 0.33
 */
class Criteria {
	public static $DIFFERENT_OPERATOR = "<>";
	public static $DEFAULT_DATEFORMAT = "Y-m-d";
	public static $DEFAULT_TIMEFORMAT = "H:i:s";
	public static $DEFAULT_DATETIMEFORMAT = "Y-m-d H:i:s";
	
	/* Campos */
	const FIELD_DATE = 0;
	const FIELD_TIME = 1;
	const FIELD_DATETIME = 2;
	const FIELD_CHAR = 3;
	const FIELD_INT = 7;
	const FIELD_DOUBLE = 8;
	
	/**
	 * Operadores lógicos
	 */
	const LOGIC_AND = "AND";
	const LOGIC_OR = "OR";
	
	/**
	 * Ordenações
	 */
	const ORDER_ASC = "ASC";
	const ORDER_DESC = "DESC";
	
	/**
	 * Remove a palavra WHERE 
	 */
	const NO_WHERE = 0;
	const NORMAL_WHERE = 1;
	
	/**
	 * Habilita quebra de linhas nas instruções
	 */
	private $enableBreakLines;
	
	/**
	 * Par de separadores de palavras reservadas
	 * utilizadas em instruções SQL
	 */
	private $separator = array("","");
	
	/**
	 * Número máximo de resultados da consulta
	 * utilizado pelo SQL Server na cláusula TOP
	 */
	private $maxResults;
	
	/**
	 * Faixa de registros da consulta utilizado
	 * pelo MySQL na cláusula LIMIT
	 */
	private $limit = array();
	
	/**
	 * Nome da tabela
	 */
	private $tableName;
	
	/**
	 * Lista de campos do SELECT
	 */
	private $field = array();
	
	/**
	 * Parâmetros da cláusula ORDER BY
	 */
	private $order = array();
	
	/**
	 * Parâmetros da cláusula WHERE
	 */
	private $where = array();
	
	/**
	 * Group By
	 */
	private $group = array();
	
	/**
	 * Construtor default
	 */
	public function __construct(){
		$this->init();
	}
			
	/**
	 * Inicializa o objeto instanciado
	 */
	private function init(){
		$this->maxResults = 0;
		$this->limit = array();
		$this->field = array();
		$this->order = array();
		$this->where = array();
		$this->group = array();
		$this->separator = array("","");
		$this->enableBreakLines = false;
		$this->tableName = "";
	}
	
	/**
	 * Limpa os dados do objeto
	 */
	public function clear(){
		$this->init();
	}
		
	/**
	 * Habilita quebra de linhas
	 */
	public function enableBreakLines(){
		$this->enableBreakLines = true;
	}
	
	/**
	 * Disabilita quabra de linhas
	 */
	public function disableBreakLines(){
		$this->enableBreakLines = false;
	}
	
	/**
	 * Verifica se a quebra de linha está habilitada
	 * @return true caso a quebra de linha está habilitada
	 * e false caso esteja desabilitada.
	 */
	public function isBreakLines(){
		return $this->enableBreakLines;
	}
	
	/**
	 * Retorna o caracter de quebra de linha se 
	 * estiver habilitado
	 * @return Caracterer de quebra de linha ou string vazia
	 */
	public function getBreakLineString(){
		$output = "";
		if($this->isBreakLines()){
			$output = "\n";
		}
		return $output;
	}
	
	/**
	 * Retorna o caracter de tabulação se 
	 * estiver habilitado
	 * @return Caracterer de tabulação ou string vazia
	 */
	public function getHorizontalTabulationString(){
		$output = "";
		if($this->isBreakLines()){
			$output = "\t";
		}
		return $output;
	}
	
	/**
	 * Define o par de separadores
	 * @param open Separador de abertura
	 * @param close Separador de fechamento
	 */
	public function setSeparator($open,$close){
		$this->separator[0] = $open;
		$this->separator[1] = $close;
	}
			
	/**
	 * Define o separador de abertura
	 * @param c Separador
	 */
	public function setOpenSeparator($c){
		$this->separator[0] = $c;
	}
		
	/**
	 * Retorna o separador de abertura
	 * @return Separador
	 */
	public function getOpenSeparator(){
		return $this->separator[0];
	}
	
	/**
	 * Define o separador de fechamento
	 * @param c Separador
	 */
	public function setCloseSeparator($c){
		$this->separator[1] = $c;
	}
	
	/**
	 * Retorna o separador de fechamento
	 * @return Separador
	 */
	public function getCloseSeparator(){
		return $this->separator[1];
	}
		
	/**
	 * Define o nome da tabela
	 * @param tableName
	 */
	public function setTableName($tableName){
		if(trim($tableName)!=""){
			$this->tableName = $this->applySeparator($tableName);
		}
	}

	/**
	 * Retorna o nome da tabela
	 * @return Nome da tabela
	 */
	public function getTableName() {
		return $this->tableName;
	}
	
	/**
	 * Define o máximo de resultados da consulta
	 * @param num Número de resultados
	 */
	public function setMaxResults($num){
		$this->maxResults = abs($num);
	}
	
	/**
	 * Apelido para setMaxResults
	 */
	public function setTop($num){
		$this->setMaxResults($num);
	}
	
	/**
	 * Retorna o máximo de resultados da consulta
	 * @return Número de resultados
	 */
	public function getMaxResults(){
		return $this->maxResults;
	}
	
	/**
	 * Define a faixa de registros retornados por
	 * uma consulta do MySQL usando a clásula LIMIT
	 * @param start O ponteiro dos registros começando 
	 * dos primeiros resultados da busca
	 * @param length Quando registros o ponteiro vai avançar
	 * na consulta
	 */
	public function setLimit($start,$length){
		$this->limit[0] = $start;
		$this->limit[1] = abs($length);
	}
		
	/**
	 * Constrói uma função IN
	 * @param name Nome do campo
	 * @param data Valores que serão inseridos no IN
	 * @param logic Operador lógico
	 */
	public function in($name,array $data,$logic=self::LOGIC_AND){
		$data = $this->addQuotesIfString($data);
		if(sizeof($data)>0){
			$buffer = implode(",", $data);			
			$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." IN(".$buffer.")");			
		}		
	}
			
	public function notin($name,array $data,$logic=self::LOGIC_AND){
		$data = $this->addQuotesIfString($data);
		$length = sizeof($data);
		if($length>0){
			$buffer = implode(",", $data);
			$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." NOT IN(".$buffer.")");
		}		
	}
		
	/**
	 * Verifica se o campo é nulo
	 * @param name Nome do campo
	 * @param logic Operador lógico
	 */
	public function isNull($name,$logic=self::LOGIC_AND){
		$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." IS NULL");
	}
			
	public function isNotNull($name,$logic=self::LOGIC_AND){
		$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." IS NOT NULL");
	}
	
	/**
	 * Condição LIKE	
	 * @param name Nome do campo
	 * @param value Valor do campo
	 * @param logic Operador Lógico
	 */
	public function like($name,$value,$logic=self::LOGIC_AND){
		$value = $this->addQuotesIfString($value);
		$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." LIKE ".$value);		
	}
	
	public function notlike($name,$value,$logic=self::LOGIC_AND){
		$value = $this->addQuotesIfString($value);
		$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." NOT LIKE ".$value);
	}
			
	public function startsWith($name,$value,$logic=self::LOGIC_AND){
		$value = $this->addQuotesIfString($value."%");
		$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." LIKE ".$value);
	}
	
	public function notStartsWith($name,$value,$logic=self::LOGIC_AND){
		$value = $this->addQuotesIfString($value."%");
		$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." NOT LIKE ".$value);
	}
	
	public function endsWith($name,$value,$logic=self::LOGIC_AND){
		$value = $this->addQuotesIfString("%".$value);
		$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." LIKE ".$value);
	}
	
	public function notEndsWith($name,$value,$logic=self::LOGIC_AND){
		$value = $this->addQuotesIfString("%".$value);
		$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." NOT LIKE ".$value);
	}
	
	public function notContains($name,$value,$logic=self::LOGIC_AND){
		$value = $this->addQuotesIfString("%".$value."%");
		$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." NOT LIKE ".$value);
	}
	
	public function contains($name,$value,$logic=self::LOGIC_AND){
		$value = $this->addQuotesIfString("%".$value."%");
		$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." LIKE ".$value);
	}
	
	/**
	 * Condição igual
	 * @param name Nome do campo
	 * @param value Valor do campo
	 * @param logic Operador Lógico
	 */
	public function eq($name,$value,$logic=self::LOGIC_AND){
		$value = $this->addQuotesIfString($value);		
		$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." = ".$value);
	}
	public function addWhereFree($sql,$logic=self::LOGIC_AND){
		$this->where[] = array(self::getLogicName($logic),$sql);
	}
	
	public function noteq($name,$value,$logic=self::LOGIC_AND){
		$value = $this->addQuotesIfString($value);
		$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." ".self::$DIFFERENT_OPERATOR." ".$value);
	}
			
	/**
	 * Menor que
	 * @param name
	 * @param value
	 * @param logic
	 */
	public function lt($name,$value,$logic=self::LOGIC_AND){
		$value = $this->addQuotesIfString($value);
		$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." < ".$value);
	}
		
	/**
	 * Menor ou maior que
	 * @param name
	 * @param value
	 * @param logic
	 */
	public function le($name,$value,$logic=self::LOGIC_AND){
		$value = $this->addQuotesIfString($value);
		$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." <= ".$value);
	}
			
	public function gt($name,$value,$logic=self::LOGIC_AND){
		$value = $this->addQuotesIfString($value);
		$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." > ".$value);
	}
	
	public function ge($name,$value,$logic=self::LOGIC_AND){
		$value = $this->addQuotesIfString($value);
		$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." >= ".$value);
	}
					
	/**
	 * Condição ENTRE
	 * @param name Nome do campo
	 * @param arg1 Argumento 1
	 * @param arg2 Argumento 2
	 * @param logic Operador lógico
	 */
	public function between($name,$arg1,$arg2,$fieldType,$logic=self::LOGIC_AND){
		if($fieldType == self::FIELD_DATETIME | $fieldType == self::FIELD_DATE | $fieldType == self::FIELD_TIME){
			if($fieldType == self::FIELD_DATETIME){
				$arg1 = $arg1->format(self::$DEFAULT_DATETIMEFORMAT);
				$arg2 = $arg2->format(self::$DEFAULT_DATETIMEFORMAT);
			}else if($fieldType == self::FIELD_DATE){
				$arg1 = $arg1->format(self::$DEFAULT_DATEFORMAT);
				$arg2 = $arg2->format(self::$DEFAULT_DATEFORMAT);
			}else if($fieldType == self::FIELD_TIME){
				$arg1 = $arg1->format(self::$DEFAULT_TIMEFORMAT);
				$arg2 = $arg2->format(self::$DEFAULT_TIMEFORMAT);
			}else if($fieldType == self::FIELD_CHAR){
				$arg1 = trim($arg1);
				$arg2 = trim($arg2);
			}else if($fieldType == self::FIELD_INT){
				$arg1 = intval($arg1);
				$arg2 = intval($arg2);
			}else if($fieldType == self::FIELD_DOUBLE){
				$arg1 = floatval($arg1);
				$arg2 = floatval($arg2);
			}
		}
		$arg1 = $this->addQuotesIfString($arg1);
		$arg2 = $this->addQuotesIfString($arg2);
		$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." BETWEEN ".$arg1." AND ".$arg2);
	}
	
	public function notbetween($name,$arg1,$arg2,$fieldType,$logic=self::LOGIC_AND){
		if($fieldType == self::FIELD_DATETIME | $fieldType == self::FIELD_DATE | $fieldType == self::FIELD_TIME){
			if($fieldType == self::FIELD_DATETIME){
				$arg1 = $arg1->format(self::$DEFAULT_DATETIMEFORMAT);
				$arg2 = $arg2->format(self::$DEFAULT_DATETIMEFORMAT);
			}else if($fieldType == self::FIELD_DATE){
				$arg1 = $arg1->format(self::$DEFAULT_DATEFORMAT);
				$arg2 = $arg2->format(self::$DEFAULT_DATEFORMAT);
			}else if($fieldType == self::FIELD_TIME){
				$arg1 = $arg1->format(self::$DEFAULT_TIMEFORMAT);
				$arg2 = $arg2->format(self::$DEFAULT_TIMEFORMAT);
			}
		}
		$arg1 = $this->addQuotesIfString($arg1);
		$arg2 = $this->addQuotesIfString($arg2);
		$this->where[] = array(self::getLogicName($logic),$this->applySeparator($name)." NOT BETWEEN ".$arg1." AND ".$arg2);		
	}
				
	/**
	 * Adiciona um critério diretamente na instrução SQL
	 * @param sql Código SQL
	 * @param logic Operador lógico
	 */
	public function appendCritery($sql,$logic=self::LOGIC_AND){
		$this->where[] = array(self::getLogicName($logic),$sql);
	}
		
	/**
	 * Efetua a fusão de uma critéria com a critéria 
	 * atualmente instanciada
	 * @param criteria Criteria a ser fundida
	 */
	public function merge(Criteria $criteria,$logic=self::LOGIC_AND){
		if($criteria==null){
			return;
		}
		if($criteria->getWhere()!=""){
			$this->appendCritery("(".$criteria->getWhere(self::NO_WHERE).")",$logic);
		}
	}
			
	/**
	 * Adiciona um campo na lista
	 * @param name conteúdo do campo
	 */
	public function addField($name){
		$this->field[] = $this->applySeparator($name);
	}
	
	/**
	 * Adiciona um vetor de campos na lista
	 * @param data vetor de campos
	 */
	public function addArrayField(array $data){
		for($i=0;$i<sizeof($data);$i++){
			$this->field[] = $this->applySeparator($data[$i]);	
		}
	}
	
	/**
	 * Adiciona uma ordem na lista
	 * @param name Nome do campo
	 * @param ordem Tipo de Ordenação
	 */
	public function addOrder($name,$order){
		$this->order[] = array($this->applySeparator($name),self::getOrderName($order));
	}
	
	public function removeOrder(){
		$this->order = array();
	}
	
	public function addGroup($fieldName){
		$this->group[] = $this->applySeparator($fieldName);
	}
	
	/**
	 * Retorna a cláusula SELECT montada de acordo
	 * com os campos.
	 * Se nenhum campo foi definido, retorna asterisco no 
	 * lugar do campo
	 * @return Cláusula SELECT
	 */
	public function getSelect(){
		if($this->getTableName()==""){
			return "";
		}
		$count = sizeof($this->field);
		if($count==0){
			return "SELECT *".$this->getBreakLineString()
				 ." FROM ".$this->getBreakLineString()
				 .$this->getHorizontalTabulationString().$this->getTableName();
		}else{
			$temp = "";
			for($i=0;$i<$count;$i++){
				if($i!=0) $temp .= ",".$this->getBreakLineString();
				$temp .=  $this->getHorizontalTabulationString().$this->applySeparator($this->field[$i]);
			}
			return "SELECT ".$temp.$this->getBreakLineString()
				   ." FROM ".$this->getTableName();
		}
	}
	
	/**
	 * O mesmo método que o getWhere(), porém permite
	 * definir se a primeira palavra seja WHERE ou se 
	 * fica vazia
	 * @param flag true para retornar sem WHERE
	 * @return Cláusula WHERE
	 */
	public function getWhere($flag=self::NORMAL_WHERE){
		$output = "";
		$index = 0;						
		$count = sizeof($this->where);
		for($i=0;$i<$count;$i++){
			$value = $this->where[$i];
			if($index==0){
				if($flag==self::NO_WHERE){
					$output .= " ".$value[1].$this->getBreakLineString();					
				}else{
					$output .= $this->getBreakLineString()." WHERE ".$value[1].$this->getBreakLineString();
				}
			}else{
				$output .= $this->getHorizontalTabulationString()." ".$value[0]." ".$value[1].$this->getBreakLineString();
			}
			$index++;
		}
		return $output;
	}
		
	/**
	 * Retorna a cláusula ORDER BY montada de acordo
	 * com os valores definidos pelos métodos. Caso
	 * nenhuma ordenação foi definida, retorna uma 
	 * string vazia.
	 * @return Cláusula ORDER
	 */
	public function getOrder(){
		$output = "";		
		$index = 0;
		$count = sizeof($this->order);
		for($i=0;$i<$count;$i++){
			$value = $this->order[$i];
			if($index==0){
				$output .= " ORDER BY ".$value[0]." ".$value[1].$this->getBreakLineString();
			}else{
				$output .= $this->getHorizontalTabulationString().",".$value[0]." ".$value[1].$this->getBreakLineString();
			}
			$index++;
		}
		return $output;
	}
	
	public function getGroup(){
		$output = "";		
		$index = 0;
		$count = sizeof($this->group);
		for($i=0;$i<$count;$i++){
			$value = $this->group[$i];
			if($index==0){
				$output .= " GROUP BY ".$value.$this->getBreakLineString();
			}else{
				$output .= $this->getHorizontalTabulationString().",".$value." ".$this->getBreakLineString();
			}
			$index++;
		}
		return $output;
	}
	
	/**
	 * Retorna uma cláusula LIMIT. Caso nenhuma configuração
	 * foi definida, retorna uma string vazia.
	 * @return Cláusula LIMIT
	 */
	public function getLimit(){
		return (is_array($this->limit) && count($this->limit) == 2 && $this->limit[1]>0)?" LIMIT ".$this->limit[0].",".$this->limit[1]:"";
	}
	
	/**
	 * Retorna a cláusula TOP do SQL Server. Caso nenhuma
	 * configuração foi definida, retorna uma string vazia.
	 * @return Cláusula TOP
	 */
	public function getTop(){
		return ($this->getMaxResults()>0)?" TOP ".$this->getMaxResults():"";
	}
	
	/**
	 * Retorna o nome do operador lógico de 
	 * acordo com sua constante
	 * @param logic
	 * @return Nome do operador lógico
	 */
	public static function getLogicName($logic){
		return ($logic == self::LOGIC_OR)?"OR":"AND";
	}
	
	/**
	 * Retorna o nome da ordenação
	 * acordo com sua constante
	 * @param logic
	 * @return Nome da ordenação
	 */
	public static function getOrderName($code){
		return($code == self::ORDER_ASC)?"ASC":"DESC";
	}
			
	/**
	 * Verifica se o nome contém um dos separadores definidos
	 * @param name Nome do campo a ser avaliado
	 * @return true se encontrou algum separador ou false se não encontrou
	 */
	public function findSeparator($name){
		if($name==""){
			return false;
		}		
		if($this->getOpenSeparator() == '' | $this->getCloseSeparator() == ''){
			return false;
		}
		$pattern = "/^[\\".$this->getOpenSeparator()."]{1}.+[\\".$this->getCloseSeparator()."]{1}$/";				
		return ((preg_match($pattern,$name)==1)?true:false);
	}
	
	/**
	 * Aplica o separador no nome
	 * @param name Nome do campo, tabela etc
	 * @return O nome com o separador aplicado
	 */
	public function applySeparator($name){	
		if(!$this->isFunction($name)){
			if(!$this->findSeparator($name)){				
				$pattern = "/[\.]?([a-zA-Z_]{1}[a-zA-Z0-9_-]*)([\.])?/";
				$replace = $this->getOpenSeparator()."\\1".$this->getCloseSeparator()."\\2";				
				$name = preg_replace($pattern,$replace,$name);				
			}
		}
		return $name;
	}
		
	public function addQuotesIfString($obj){
		if(is_array($obj))
		{
			$length = sizeof($obj);
			$i = 0;
			$_tmp = array();
			while($i < $length)
			{
				$_tmp[] = $this->addQuotesIfString($obj[$i]);
				$i++;
			}
			
			return $_tmp;
		}	
		return (is_string($obj) && !$this->isFunction($obj)) && $obj != "?"?"'".$obj."'":$obj;
	}
	
	public function addArrayQuotesIfString(array $obj){
		for($i=0;$i<sizeof($obj);$i++){
			$obj[$i] = $this->addQuotesIfString($obj[$i]);
		}
		return $obj;
	}
	
	/**
	 * Verifica se o nome contém uma função
	 * @param name Nome do campo a ser avaliado
	 * @return true se é função ou false se não é função
	 */
	public function isFunction($name){
		$pattern = "/^[a-zA-Z_]{1}[a-zA-Z0-9_]*[\(](.*)[\)]$/";
		$output = preg_match($pattern,$name,$match);		
		return ($output==1)?true:false;
	}
				
	/**
	 * Retorna o comando SQL Completo
	 * @return Comando SQL
	 */
	public function toString($flag=false){
		$output = "";
		if($flag){
			$output .= "\n------------------------- START SQL -------------------------------\n";
		}
		$output .= $this->getSelect();
		$output .= $this->getWhere();
		$output .= $this->getGroup();
		$output .= $this->getOrder();
		$output .= $this->getLimit();
		if($flag){
			$output .= "-------------------------- END SQL --------------------------------\n";
		}
		return $output;
	}
	/*public function __toString($flag=false){
		return $this->toString($flag);
	}*/
}
?>