<?php
class VehicleType {
	private $id;
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

	public function setDescr($descr){
		$maxlength = 50;
		$descr = trim((string) $descr);
		if(mb_strlen($descr) > $descr){
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
		return "VehicleType [
				id => ".$this->getId().", 
				descr => ".$this->getDescr().", 
				enabled => ".$this->getEnabled()."]";
	}
}
?>