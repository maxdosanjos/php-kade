<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Util.class.php");
require_once ( dirname ( __FILE__ ) . "/InstallmentVO.class.php");
require_once ( dirname ( __FILE__ ) . "/ClientVO.class.php");

class Account {
	
	const PAID 	 = "PAID";
	const UNPAID = "UNPAID";
	const CANCEL = "CANCEL";
	
	private $id;
	private $dtHrProc;
	private $status;
	private $client;
	private $validateMonth;
	private $installmentList;

	public function __construct(){
		$this->dtHrProc    	   = new DateTimeCustom("Now");
		$this->status  	  	   = Account::UNPAID;
		$this->installmentList = array();
	}

	public function setId($id){
		$this->id = Util::bigIntval($id);
	}

	public function getId(){
		return $this->id;
	}

	public function setDtHrProc(DateTimeCustom $dtHrProc){
		$this->dtHrProc = $dtHrProc;
	}

	public function getDtHrProc(){
		return $this->dtHrProc;
	}

	public function setStatus($status){
		$status = trim((string) $status);
		$_list = array_keys(Installment::listStatus());
		if(in_array($status,$_list))
			$this->status = $status;
	}

	public function getStatus(){
		return $this->status;
	}
	public static function listStatus(){
 		$_list = array(Account::PAID=>"Quitada",Account::UNPAID=>"em Aberto",Account::CANCEL=>"Cancelada");
 		return $_list; 
 	}
	public function getDescStatus(){
 		$list = Account::listStatus();
 		return $list[$this->status]; 
 	} 

	public function setClient(Client $client){
		$this->client = $client;
	}

	public function getClient(){
		return $this->client;
	}

	public function setValidateMonth($validateMonth){
		$this->validateMonth = intval($validateMonth);
	}

	public function getValidateMonth(){
		return $this->validateMonth;
	}
	public function getInstallmentList(){
		return $this->installmentList;
	}
	
	public function setInstallmentList(array $installmentList){
		$length = count($installmentList);
		$i = 0;
		if($length > 0)
		{
			while($i < $length)
			{
				if($installmentList[$i] instanceof Installment ){
					$installmentList[$i]->setAccount($this);
					$this->installmentList[] = $installmentList[$i];
				}
				$i++;
			}
				
		}
		$this->reloadStatus();	
	}
	
	public function reloadStatus(){
		$totCanc 	= 0;
		$totPaid 	= 0;
		$totNotPaid = 0;
		$length = count($this->installmentList);
		$i = 0;
		if($length > 0)
		{
			while($i < $length)
			{
				if($this->installmentList[$i]->getStatus() == Installment::PAID){
					$totPaid++;
				}elseif($this->installmentList[$i]->getStatus() == Installment::CANCEL){
					$totCanc++;					
				}else{
					$totNotPaid++;
				}
				$i++;
			}
		
		}
		
		if ($length == 0 || $totCanc == $length) {
			$this->setStatus(Account::CANCEL);
		}elseif(($totPaid+$totCanc) == $length || $totPaid == $length){
			$this->setStatus(Account::PAID);
		}else{
			$this->setStatus(Account::UNPAID);
		}
	}

	public function toString(){
		return "Account [
				id => ".$this->getId().", 
				dtHrProc => ".$this->getDtHrProc().", 
				status => ".$this->getStatus().", 
				validateMonth => ".$this->getValidateMonth()."]";
	}
	
	public function getArray(){
		$output = array();
		
		$output["id"] = $this->getId();
		$output["dtHrProc"] = ($this->getDtHrProc()!=null?$this->getDtHrProc()->format("d.m.Y"):"");
		$output["status"] = $this->getStatus();
		$output["validateMonth"] = $this->getValidateMonth();
		
		return $output;
	}
}
?>