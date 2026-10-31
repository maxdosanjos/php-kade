<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
require_once ( dirname ( __FILE__ ) . "/PersonVO.class.php");
require_once ( dirname ( __FILE__ ) . "/UserProfileVO.class.php");
class User {
	const   PEND = "PEND";
	const   CHEK = "CHEK";
	const   BLCK = "BLCK";
	const   CANC = "CANC";

	private $login;
	private $password;
	private $status;
	private $text;
	private $dateCad;
	private $userAlt;
	private $dateAlt;
	private $person;
	private $profileList;

	public function __construct(){
		$this->dateCad    = new DateTimeCustom("Now");
		$this->status  	  = User::PEND;
		$this->profileList = array();
	}

	public function setLogin($login){
		$maxlength = 20;
		$login = trim($login);
		if(mb_strlen($login) > $maxlength){
			$name = mb_substr($login,0,$maxlength);
		}				
		$this->login = $login;
	}

	public function getLogin(){
		return $this->login;
	}

	public function setPassword($password){
		$maxlength = 40;
		$password = trim($password);
		if(mb_strlen($password) > $maxlength){
			$name = mb_substr($password,0,$maxlength);
		}						
		$this->password = $password;
	}

	public function getPassword(){
		return $this->password;
	}

	public function setStatus($status){
		$status = trim($status);
		$_list = array_keys(User::listStatus());
		if(in_array($status,$_list))
			$this->status = $status;
	}
	public function getStatus(){
		return $this->status;
	}
	
	public static function listStatus(){
 		$_list = array(User::PEND=>"Pendente de confirmação",User::CHEK=>"Ativo",User::BLCK=>"Bloqueado",User::CANC=>"Cancelado");
 		return $_list; 
 	}
	public function getDescStatus(){
 		$list = User::listStatus();
 		return $list[$this->status]; 
 	} 

	public function setText($text){
		$maxlength = 65535;
		$text = trim($text);
		if(mb_strlen($text) > $maxlength){
			$name = mb_substr($text,0,$maxlength);
		}								
		$this->text = $text;
	}

	public function getText(){
		return $this->text;
	}

	public function setDateCad(DateTimeCustom $dateCad){
		$this->dateCad = $dateCad;
	}

	public function getDateCad(){
		return $this->dateCad;
	}

	public function setUserAlt(UserVO $userAlt){
		$this->userAlt = $userAlt;
	}

	public function getUserAlt(){
		return $this->userAlt;
	}
	public function getPerson(){
		return $this->person;
	}
	public function setPerson(Person $person){
		$this->person = $person;
	}
	
	public function getProfileList(){
		return $this->profileList;
	}
	
	public function setProfileList(array $profileList){
		$length = count($profileList);
		$i = 0;
		if($length > 0)
		{
			while($i < $length)
			{
				if($profileList[$i] instanceof UserProfile ){
					$this->profileList[] = $profileList[$i];
				}
				$i++;
			}
			
		}	
		
	}
	
	public static function isLogged() {
		$user = User::getLogged();
		if ($user != null && $user instanceof User) 
			return true;
		return false;
	}
	public static function getLogged() {
		$user = null;
		
		if(!empty($_SESSION ["user"]))
			$user = unserialize($_SESSION ["user"]);
			
		if ($user != null && $user instanceof User) 
			return $user;
		
		return null;
	}
	
	public static function isUserIntern(){
		$inter 	   = false;
		$user 	   = User::getLogged();
		if($user!=null)
		{
			$_profiles = $user->getProfileList( );
			if(is_array($_profiles)){
				foreach ($_profiles as $profile){
					if($profile->getProfile()->getIntern( ) == '1'){
						$inter = true;
					}
				}
			}
		}	
		return $inter;
	}
	
	public static function isUserSuper(){
		$super 	   = false;
		$user 	   = User::getLogged();
		if($user!=null)
		{
			$_profiles = $user->getProfileList( );
			if(is_array($_profiles)){
				foreach ($_profiles as $profile){
					if($profile->getProfile()->getIntern( ) == '1'){
						$super = true;
					}
				}
			}
		}
		return $super;
	}

	public function toString(){
		return "User [
				login => ".$this->getLogin().", 
				password => ".$this->getPassword().", 
				status => ".$this->getStatus().", 
				text => ".$this->getText().", 
				dateCad => ".$this->getDateCad().", 
				type => ".$this->getType().", 
				userAlt => ".$this->getUserAlt()."]";
	}
}
?>