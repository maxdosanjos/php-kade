<?php
class Address {
	private $id;
	private $cep;
	private $street;
	private $number;
	private $complement;
	private $neighborhood;
	private $city;
	private $state;
	private $stateNm;

	public function __construct(){
	}

	public function setId($id){
		$this->id = Util::bigIntval($id);
	}

	public function getId(){
		return $this->id;
	}

	public function setCep($cep){
		$cep = trim((string)$cep);
		$cep = preg_replace("/[\-\.]/i","",$cep);
		$maxlength = 8;
		if(strlen($cep) > $maxlength){
			$cep = substr($cep,0,$maxlength);
		}
		$this->cep = $cep;
	}

	public function getCep(){
		return $this->cep;
	}

	public function setStreet($street){
		$maxlength = 255;
		$street = trim((string)$street);
		if(strlen($street) > $maxlength){
			$street = substr($street,0,$maxlength);
		}
		
		$_arr = explode(" ",$street);
		
		$i = 0;
		$length = sizeof($_arr);
		while ($i < $length) {
					if(strlen($_arr[$i]) > 2)
						$_arr[$i] = ucwords($_arr[$i]);
			$i++;
		}	
		
		$this->street = implode(" ",$_arr);
	}

	public function getStreet(){
		return $this->street;
	}

	public function setNumber($number){
		$maxlength = 5;
		$number = trim((string)$number);
		if(strlen($number) > $maxlength){
			$number = substr($number,0,$maxlength);
		}
		$this->number = $number;
	}

	public function getNumber(){
		return $this->number;
	}

	public function setComplement($complement){
		$complement = trim((string)$complement);
		$maxlength = 255;
		$complement = trim((string)$complement);
		if(strlen($complement) > $maxlength){
			$complement = substr($complement,0,$maxlength);
		}
		
		$_arr = explode(" ",$complement);
		
		$i = 0;
		$length = sizeof($_arr);
		while ($i < $length) {
			if(strlen($_arr[$i]) > 2)
				$_arr[$i] = ucwords($_arr[$i]);
			$i++;
		}
		
		$this->complement = implode(" ",$_arr);
	}

	public function getComplement(){
		return $this->complement;
	}

	public function setNeighborhood($neighborhood){
			$neighborhood = trim((string)$neighborhood);
		$maxlength = 72;
			$neighborhood = trim((string)$neighborhood);
		if(strlen($neighborhood) > $maxlength){
			$neighborhood = substr($neighborhood,0,$maxlength);
		}
		
		$_arr = explode(" ",$neighborhood);
		
		$i = 0;
		$length = sizeof($_arr);
		while ($i < $length) {
			if(strlen($_arr[$i]) > 2)
				$_arr[$i] = ucwords($_arr[$i]);
			$i++;
		}
		
		$this->neighborhood = implode(" ",$_arr);
	}

	public function getNeighborhood(){
		return $this->neighborhood;
	}

	public function setCity($city){
			$city = trim((string)$city);
		$maxlength = 60;
			$city = trim((string)$city);
		if(strlen($city) > $maxlength){
			$city = substr($city,0,$maxlength);
		}
		
		$_arr = explode(" ",$city);
		
		$i = 0;
		$length = sizeof($_arr);
		while ($i < $length) {
			if(strlen($_arr[$i]) > 2)
				$_arr[$i] = ucwords($_arr[$i]);
			$i++;
		}
		
		$this->city = implode(" ",$_arr);
	}

	public function getCity(){
		return $this->city;
	}

	public function setState($state){
			$state = trim((string)$state);
		$maxlength = 2;
			$state = strtoupper(trim((string)$state));
		if(strlen($state) > $maxlength){
			$state = substr($state,0,$maxlength);
		}
		$this->state = $state;
	}

	public function getState(){
		return $this->state;
	}
	public function setStateNm($stateNm){
			$stateNm = trim((string)$stateNm);
		$maxlength = 72;
			$stateNm = trim((string)$stateNm);
		if(strlen($stateNm) > $maxlength){
			$stateNm = substr($stateNm,0,$maxlength);
		}
		$this->stateNm = $stateNm;
	}
	
	public function getStateNm(){
		return $this->stateNm;
	}
	
	public function getCepMask(){
		//echo $this->cep;
		if($this->cep!="" && $this->cep!="0")
			return substr($this->cep,0,5)."-".substr($this->cep,5);
		return "";
	}

	public function toString(){
		return "Address [
				id => ".$this->getId().", 
				cep => ".$this->getCep().", 
				street => ".$this->getStreet().", 
				number => ".$this->getNumber().", 
				complement => ".$this->getComplement().", 
				neighborhood => ".$this->getNeighborhood().", 
				city => ".$this->getCity().", 
				state => ".$this->getState()."]";
	}
}
?>