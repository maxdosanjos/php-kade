<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Util.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
require_once (dirname ( __FILE__ ) . "/VehicleTypeVO.class.php");
require_once (dirname ( __FILE__ ) . "/VehicleContactVO.class.php");
require_once (dirname ( __FILE__ ) . "/VehicleTravelingUserVO.class.php");
require_once (dirname ( __FILE__ ) . "/AddressVO.class.php");
class VehicleTraveling {
	private $id;
	private $vehicleType;
	private $personContact;
	private $dateHrProc;
	private $source;
	private $address;
	private $ip;
	private $userUtilized;
	private $status;
	
	const SOURCE_WEB = "WEB";
	const SOURCE_SMS = "SMS";
	const SOURCE_WHATSAPP = "WHATSAPP";
	const SOURCE_TELEGRAM = "TELEGRAM";
	const ONLY_VIEW = "ONLY_VIEW";
	const UTILIZED = "UTILIZED";
	const NONE = "NONE";
	const CANCEL = "CANCEL";
	const EXPIRED = "EXPIRED";
	
	public function __construct() {
		$this->dateHrProc = new DateTimeCustom ( "Now" );
		$this->source = VehicleTraveling::SOURCE_WEB;
	}
	public function setId($id) {
		$this->id = Util::bigIntval ( $id );
	}
	public function getId() {
		return $this->id;
	}
	public function setUserUtilized(VehicleTravelingUser $userUtilized) {
		$this->userUtilized = $userUtilized;
	}
	public function getUserUtilized() {
		return $this->userUtilized;
	}
	public function setIp($ip) {
		$maxlength = 15;
		$ip = trim((string) $ip);
		if (strlen ( $ip ) > $maxlength) {
			$name = substr ( $ip, 0, $maxlength );
		}
		$this->ip = $ip;
	}
	public function getIp() {
		return $this->ip;
	}
	public function setVehicleType(VehicleType $vehicleType) {
		$this->vehicleType = $vehicleType;
	}
	public function getVehicleType() {
		return $this->vehicleType;
	}
	public function setPersonContact(VehicleContact $personContact) {
		$this->personContact = $personContact;
	}
	public function getPersonContact() {
		return $this->personContact;
	}
	public function setDateHrProc(DateTimeCustom $dateHrProc) {
		$this->dateHrProc = $dateHrProc;
	}
	public function getDateHrProc() {
		return $this->dateHrProc;
	}
	public function setSource($source) {
		$source = trim((string) $source);
		$_list = array_keys ( VehicleTraveling::listSource () );
		if (in_array ( $source, $_list ))
			$this->source = $source;
	}
	public function getSource() {
		return $this->source;
	}
	public static function listSource() {
		$_list = array (
				VehicleTraveling::SOURCE_WEB => "Via WEB",
				VehicleTraveling::SOURCE_SMS => "Via SMS",
				VehicleTraveling::SOURCE_WHATSAPP => "Via WhatsApp",
				VehicleTraveling::SOURCE_TELEGRAM => "Via Telegram"
		);
		return $_list;
	}
	public function getDescSource() {
		$list = VehicleTraveling::listSource ();
		return $list [$this->source];
	}
	public function setStatus($status) {
		$status = trim((string) $status);
		$_list = array_keys ( VehicleTraveling::listStatus () );
		if (in_array ( $status, $_list ))
			$this->status = $status;
	}
	public function getStatus() {
		return $this->status;
	}
	public static function listStatus() {
		$_list = array (
				VehicleTraveling::ONLY_VIEW => "Somente visualizado",
				VehicleTraveling::UTILIZED => "Informação utilizada",
				VehicleTraveling::NONE => "Nenhuma ação",
				VehicleTraveling::CANCEL => "Cancelada" ,
				VehicleTraveling::EXPIRED => "Expirado" 
				
		);
		return $_list;
	}
	public function getDescStatus() {
		$list = VehicleTraveling::listStatus ();
		return $list [$this->status];
	}
	public function setAddress(Address $address) {
		$this->address = $address;
	}
	public function getAddress() {
		return $this->address;
	}
	public function toString() {
		return "VehicleTraveling [
				id => " . $this->getId () . ", 
				vehicleType => " . $this->getVehicleType () . ", 
				personContact => " . $this->getPersonContact () . ", 
				dateHrProc => " . $this->getDateHrProc () . ", 
				source => " . $this->getSource () . ", 
				address => " . $this->getAddress () . "]";
	}
}
?>