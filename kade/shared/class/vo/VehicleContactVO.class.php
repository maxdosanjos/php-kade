<?php
require_once ( dirname ( __FILE__ ) . "/PhoneVO.class.php");
require_once ( dirname ( __FILE__ ) . "/PersonVO.class.php");
require_once ( dirname ( __FILE__ ) . "/VehicleTravelingVO.class.php");
class VehicleContact{
	
	private $phoneContact     = null;
	private $vehicleTraveling = null;
	private $person           = null;
	private $name             = null;
	
	public function __construct(){
	}
	
	public function setName($name){
		$maxlength = 255;
		$name = trim((string) $name);
		if(mb_strlen($name) > $maxlength){
			$name = mb_substr($name,0,$maxlength);
		}
		$this->name = $name;
	
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function setPhoneContact(Phone $phoneContact){
		$this->phoneContact = $phoneContact;
	}

	public function getPhoneContact(){
		return $this->phoneContact;
	}
	
	public function setPerson(Person $person){
		$this->person = $person;
	}
	
	public function getPerson(){
		return $this->person;
	}
	public function setVehicleTraveling(VehicleTraveling $vehicleTraveling){
		$this->vehicleTraveling = $vehicleTraveling;
	}
	
	public function getVehicleTraveling(){
		return $this->vehicleTraveling;
	}
}
?>