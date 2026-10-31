<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
require_once ( dirname ( __FILE__ ) . "/AccountVO.class.php");
require_once ( dirname ( __FILE__ ) . "/PaymentMethodVO.class.php");
class Installment {
	const PAID 	 = "PAID";
	const UNPAID = "UNPAID";
	const CANCEL = "CANCEL";
	
	private $id;
	private $account;
	private $value;
	private $paymentValue;
	private $dueDate;
	private $paymentDate;
	private $status;
	private $paymentMethod;

	public function __construct(){
		$this->status  	  	   = Installment::UNPAID;
	}

	public function setId($id){
		$this->id = $id;
	}

	public function getId(){
		return $this->id;
	}

	public function setAccount(Account $account){
		$this->account = $account;
	}

	public function getAccount(){
		return $this->account;
	}

	public function setValue($value){
		$this->value = $value;
	}

	public function getValue(){
		return $this->value;
	}

	public function setPaymentValue($paymentValue){
		$this->paymentValue = $paymentValue;
	}

	public function getPaymentValue(){
		return $this->paymentValue;
	}

	public function setDueDate($dueDate){
		$this->dueDate = $dueDate;
	}

	public function getDueDate(){
		return $this->dueDate;
	}

	public function setPaymentDate($paymentDate){
		$this->paymentDate = $paymentDate;
	}

	public function getPaymentDate(){
		return $this->paymentDate;
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
 		$_list = array(Installment::PAID=>"Pago",Installment::UNPAID=>"Não pago",Installment::CANCEL=>"Cancelado");
 		return $_list; 
 	}
	public function getDescStatus(){
 		$list = Installment::listStatus();
 		return $list[$this->status]; 
 	} 

	public function setPaymentMethod(PaymentMethod $paymentMethod){
		$this->paymentMethod = $paymentMethod;
	}

	public function getPaymentMethod(){
		return $this->paymentMethod;
	}

	public function toString(){
		return "Installment [
				id => ".$this->getId().", 
				account => ".$this->getAccount().", 
				value => ".$this->getValue().", 
				paymentValue => ".$this->getPaymentValue().", 
				dueDate => ".$this->getDueDate().", 
				paymentDate => ".$this->getPaymentDate().", 
				status => ".$this->getStatus().", 
				paymentMethod => ".$this->getPaymentMethod()."]";
	}
	public function getArray(){
		$output = array();
		$output["id"] = $this->getId();
		$output["paymentMethod"] = ($this->getAccount()!=null?$this->getAccount()->getArray():"");
		$output["value"] = $this->getValue();
		$output["paymentValue"] = $this->getPaymentValue();
		$output["dueDate"] = ($this->getDueDate()!=null?$this->getDueDate()->format("d.m.Y"):"");
		$output["paymentDate"] = ($this->getPaymentDate()!=null?$this->getPaymentDate()->format("d.m.Y"):"");
		$output["status"] = $this->getStatus();
		$output["paymentMethod"] = ($this->getPaymentMethod()!=null?$this->getPaymentMethod()->getArray():"");
		
		return $output;
	}
	public function getJson(){		
		$output = $this->getArray();
		return json_encode($output);
	}
}
?>