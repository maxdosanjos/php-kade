<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Util.class.php");
class Profile {
	const   ADMIN = "ADMN";
	const   FUNC  = "FUNC";
	const   CLIE  = "CLIE";

	private $id;
	private $descr;
	private $enabled;
	private $super;
	private $intern;

	public function __construct(){
	}

	public function setId($id){
		$maxlength = 4;
		$id = trim($id);
		if(mb_strlen($id) > $maxlength){
			$name = mb_substr($id,0,$maxlength);
		}								
		$this->id = $id;
	}

	public function getId(){
		return $this->id;
	}

	public function setDescr($descr){
		$maxlength = 45;
		$descr = trim($descr);
		if(mb_strlen($descr) > $maxlength){
			$name = mb_substr($descr,0,$maxlength);
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

	public function setSuper($super){
		$super = intval($super);
		if($super != '0')
			$super = '1';		
		$this->super = $super;
	}

	public function getSuper(){
		return $this->super;
	}

	public function setIntern($intern){
		$intern = intval($intern);
		if($intern != '0')
			$intern = '1';		
		$this->intern = $intern;
	}

	public function getIntern(){
		return $this->intern;
	}

	public function toString(){
		return "Profile [
				id => ".$this->getId().", 
				descr => ".$this->getDescr().", 
				enabled => ".$this->getEnabled().", 
				super => ".$this->getSuper().", 
				intern => ".$this->getIntern()."]";
	}
}
?>