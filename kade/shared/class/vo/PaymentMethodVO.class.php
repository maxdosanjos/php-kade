<?php
class PaymentMethod {
	private $id;
	private $name;
	private $descr;
	private $enabled;

	public function __construct(){
	}

	public function setId($id){
		$this->id = intval($id);
	}

	public function getId(){
		return $this->id;
	}

	public function setName($name){
		$maxlength = 45;
		$name = trim((string) $name);
		if(mb_strlen($name) > $maxlength){
			$name = mb_substr($name,0,$maxlength);
		}
		$this->name = $name;
	}

	public function getName(){
		return $this->name;
	}

	public function setDescr($descr){
		$maxlength = 100;
		$descr = trim((string) $descr);
		if(mb_strlen($descr) > $maxlength){
			$descr = mb_substr($descr,0,$maxlength);
		}
		$this->descr = $descr;
	}

	public function getDescr(){
		return $this->descr;
	}
	
	public function setEnabled($enabled){
		$enabled = intval($enabled);
		if($enabled != '0')
			$enabled = '1';		
		$this->enabled = $enabled;
	}

	public function getEnabled(){
		return $this->enabled;
	}

	public function toString(){
		return "PaymentMethod [
				id => ".$this->getId().", 
				name => ".$this->getName().", 
				enabled => ".$this->getEnabled().", 
				descr => ".$this->getDescr()."]";
	}
	
	public function getArray(){
		$output = array();
		$output["id"] 	   = $this->getId();
		$output["name"]    = iconv ( "UTF-8", "UTF-8",$this->getName());
		$output["enabled"] = $this->getEnabled();
		$output["descr"]   = iconv ( "UTF-8", "UTF-8",$this->getDescr());
		
		return $output;
	}
}
?>