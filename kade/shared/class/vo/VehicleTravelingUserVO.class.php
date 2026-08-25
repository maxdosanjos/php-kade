<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
require_once ( dirname ( __FILE__ ) . "/UserVO.class.php");
require_once ( dirname ( __FILE__ ) . "/VehicleTravelingVO.class.php");
class VehicleTravelingUser {
	const   ONLY_VIEW = "ONLY_VIEW";
	const   UTILIZED  = "UTILIZED";
	
	private $user;
	private $vehicleTraveling;
	private $dateHrUsed;
	private $status;

	public function __construct(){
		$this->dateHrUsed = new DateTimeCustom("Now");
		$this->status	  = VehicleTravelingUser::ONLY_VIEW;
	}

	public function setUser(User $user){
		$this->user = $user;
	}

	public function getUser(){
		return $this->user;
	}

	public function setVehicleTraveling(VehicleTraveling $vehicleTraveling){
		$this->vehicleTraveling = $vehicleTraveling;
		if($vehicleTraveling!=null)
			$this->setStatus($vehicleTraveling->getStatus());
	}

	public function getVehicleTraveling(){
		return $this->vehicleTraveling;
	}

	public function setDateHrUsed(DateTimeCustom $dateHrUsed){
		$this->dateHrUsed = $dateHrUsed;
	}

	public function getDateHrUsed(){
		return $this->dateHrUsed;
	}
	public function setStatus($status){
		$status = trim($status);
		$_list = array_keys(VehicleTraveling::listStatus());
		if(in_array($status,$_list))
			$this->status = $status;
	}
	public function getStatus(){
		return $this->status;
	}
	
	public static function listStatus(){
 		$_list = array(VehicleTravelingUser::ONLY_VIEW=>"Somente visualizado",VehicleTravelingUser::UTILIZED=>"Informação utilizada");
 		return $_list; 
 	}
	public function getDescStatus(){
 		$list = VehicleTravelingUser::listStatus();
 		return $list[$this->type]; 
 	} 

	public function toString(){
		return "VehicleTravelingUser [
				user => ".$this->getUser().", 
				vehicleTraveling => ".$this->getVehicleTraveling().", 
				dateHrUsed => ".$this->getDateHrUsed()."]";
	}
}
?>