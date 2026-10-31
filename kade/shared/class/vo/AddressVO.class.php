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
		// A coluna address.cep é numérica: CEPs de SP voltam do banco com 7 dígitos
		if (ctype_digit($cep) && strlen($cep) == 7)
			$cep = "0" . $cep;
		$maxlength = 8;
		if(mb_strlen($cep) > $maxlength){
			$cep = mb_substr($cep,0,$maxlength);
		}
		$this->cep = $cep;
	}

	public function getCep(){
		return $this->cep;
	}

	public function setStreet($street){
		$maxlength = 255;
		$street = trim((string)$street);
		if(mb_strlen($street) > $maxlength){
			$street = mb_substr($street,0,$maxlength);
		}
		
		$_arr = explode(" ",$street);
		
		$i = 0;
		$length = sizeof($_arr);
		while ($i < $length) {
					if(mb_strlen($_arr[$i]) > 2)
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
		if(mb_strlen($number) > $maxlength){
			$number = mb_substr($number,0,$maxlength);
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
		if(mb_strlen($complement) > $maxlength){
			$complement = mb_substr($complement,0,$maxlength);
		}
		
		$_arr = explode(" ",$complement);
		
		$i = 0;
		$length = sizeof($_arr);
		while ($i < $length) {
			if(mb_strlen($_arr[$i]) > 2)
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
		if(mb_strlen($neighborhood) > $maxlength){
			$neighborhood = mb_substr($neighborhood,0,$maxlength);
		}
		
		$_arr = explode(" ",$neighborhood);
		
		$i = 0;
		$length = sizeof($_arr);
		while ($i < $length) {
			if(mb_strlen($_arr[$i]) > 2)
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
		if(mb_strlen($city) > $maxlength){
			$city = mb_substr($city,0,$maxlength);
		}
		
		$_arr = explode(" ",$city);
		
		$i = 0;
		$length = sizeof($_arr);
		while ($i < $length) {
			if(mb_strlen($_arr[$i]) > 2)
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
		if(mb_strlen($state) > $maxlength){
			$state = mb_substr($state,0,$maxlength);
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
		if(mb_strlen($stateNm) > $maxlength){
			$stateNm = mb_substr($stateNm,0,$maxlength);
		}
		$this->stateNm = $stateNm;
	}
	
	public function getStateNm(){
		return $this->stateNm;
	}
	
	public function getCepMask(){
		//echo $this->cep;
		if($this->cep!="" && $this->cep!="0")
			return mb_substr($this->cep,0,5)."-".mb_substr($this->cep,5);
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