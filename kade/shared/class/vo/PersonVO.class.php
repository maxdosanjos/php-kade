<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Util.class.php");
require_once ( dirname ( __FILE__ ) . "/PhoneVO.class.php");
require_once ( dirname ( __FILE__ ) . "/AddressVO.class.php");
class Person {
	
	const PF = "PF";
	const PJ = "PJ";
	const NO = "NO";
	
	private $id 	  = null;
	private $name  	  = null;
	private $email    = null;
	private $type     = null;
	private $address  = null;
	private $phone    = null;

	public function __construct(){
		$this->type = Person::NO;
	}

	public function setId($id){
		$this->id = Util::bigIntval($id);
	}

	public function getId(){
		return $this->id;
	}

	public function setName($name){
		$maxlength = 255;
		$name = trim($name);
		if(mb_strlen($name) > $maxlength){
			$name = mb_substr($name,0,$maxlength);
		}
		$this->name = $name;
		
	}

	public function getName(){
		return $this->name;
	}

	public function setEmail($email){
		$maxlength = 255;
		$email = trim($email);
		if(mb_strlen($email) > $maxlength){
			$email = mb_substr($email,0,$maxlength);
		}
		$this->email = $email;
	}

	public function getEmail(){
		return $this->email;
	}

	public function setType($type){
		$type = trim($type);
		$_list = array_keys(Person::listType());
		if(in_array($type,$_list))
			$this->type = $type;
	}

	public function getType(){
		return $this->type;
	}

	public function setAddress(Address $address){
		$this->address = $address;
	}

	public function getAddress(){
		return $this->address;
	}
	
	public function setPhone(Phone $phone){
		$this->phone = $phone;
	}

	public function getPhone(){
		return $this->phone;
	}
	
	public static function listType(){
 		$_list = array(Person::PF=>"Pessoa Física",Person::PJ=>"Pessoa Jurídica", Person::NO=>"Pessoa");
 		return $_list; 
 	}
	public function getDescType(){
 		$list = Person::listType();
 		return $list[$this->type]; 
 	} 

	public function toString(){
		return "Person [
				id => ".$this->getId().", 
				name => ".$this->getName().", 
				email => ".$this->getEmail().", 
				type => ".$this->getType().", 
				address => ".$this->getAddress()."]";
	}
}
?>