<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
require_once ( dirname ( __FILE__ ) . "/PersonVO.class.php");
class PersonIndividual extends Person  {
	
	const GENDER_MASC = "MASCULINO";
	const GENDER_FEM  = "FEMININO";
	
	private $cpf = null;
	private $gender = null;
	private $dateBirth = null;

	public function __construct(){
		$this->setType(Person::PF);
		$this->setGender(PersonIndividual::GENDER_MASC);
	}

	public function setCpf($cpf){
		$cpf = trim($cpf);
		$cpf = preg_replace("/[\-\.]/i","",$cpf);
		$maxlength = 11;
		if(mb_strlen($cpf) > $maxlength){
			$cpf = mb_substr($cpf,0,$maxlength);
		}
		$this->cpf = $cpf;
	}

	public function getCpf(){
		return $this->cpf;
	}

	public function setGender($gender){
		$gender = trim($gender);
		$_list = array_keys(PersonIndividual::listGender());
		if(in_array($gender,$_list))
			$this->gender = $gender;
	}

	public function getGender(){
		return $this->gender;
	}

	public function setDateBirth(DateTimeCustom $dateBirth){
		$this->dateBirth = $dateBirth;
	}

	public function getDateBirth(){
		return $this->dateBirth;
	}
	public static function listGender(){
 		$_list = array(PersonIndividual::GENDER_MASC=>"Masculino",PersonIndividual::GENDER_FEM=>"Feminino");
 		return $_list; 
 	}
	public function getDescGender(){
 		$list = PersonIndividual::listGender();
 		return $list[$this->gender]; 
 	} 

	public function toString(){
		return "PersonIndividual [
				cpf => ".$this->getCpf().", 
				gender => ".$this->getGender().", 
				dateBirth => ".$this->getDateBirth()."]";
	}
}
?>