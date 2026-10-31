<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Util.class.php");
class Phone {
	private $id;
	private $ddi;
	private $ddd;
	private $phone;

	public function __construct(){
		$this->setDdi("55");
	}

	public function setId($id){
		$this->id = Util::bigIntval($id);
	}

	public function getId(){
		return $this->id;
	}

	public function setDdi($ddi){
		$maxlength = 3;
		$ddi = trim($ddi);
		if(mb_strlen($ddi) > $maxlength){
			$ddi = mb_substr($ddi,0,$maxlength);
		}
		$this->ddi = $ddi;
	}

	public function getDdi(){
		return $this->ddi;
	}

	public function setDdd($ddd){
		$maxlength = 3;
		$ddd = trim($ddd);
		if(mb_strlen($ddd) > $maxlength){
			$ddd = mb_substr($ddd,0,$maxlength);
		}
		$this->ddd = $ddd;
	}

	public function getDdd(){
		return $this->ddd;
	}

	public function setPhone($phone){
		$maxlength = 11;
		$phone = trim($phone);
		$phone = preg_replace ("([^0-9])", "", $phone);
		if(mb_strlen($phone) > $maxlength){
			$phone = mb_substr($phone,0,$maxlength);
		}
		$this->phone = $phone;
	}

	public function getPhone(){
		return $this->phone;
	}
	
	public function setPhoneByMask($phone){
		$phone = trim($phone);		
		$this->setDdi("55");		
		$this->setDdd(mb_substr($phone,1,2));		
		$this->setPhone(mb_substr($phone,4,11));
	}
	public function getPhoneMask(){
		$phone = (string) $this->getPhone();
		if ($phone === "")
			return "";
		// Celular tem 9 dígitos (99999-9999); fixo, 8 (9999-9999)
		$split = mb_strlen($phone) >= 9 ? 5 : 4;
		return "(".$this->getDdd().") ".mb_substr($phone,0,$split)."-".mb_substr($phone,$split);
	}

	public function toString(){
		return "Phone [
				id => ".$this->getId().", 
				ddi => ".$this->getDdi().", 
				ddd => ".$this->getDdd().", 
				phone => ".$this->getPhone()."]";
	}
}
?>