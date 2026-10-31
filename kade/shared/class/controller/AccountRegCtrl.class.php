<?php
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/DateTimeCustom.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Util.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Validation.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Pagination.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/mail/Mail.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PhoneVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/AddressVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonIndividualVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PersonEntityVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/UserVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/ClientVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/PaymentMethodVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/InstallmentVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/vo/AccountVO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/ClientDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/AccountDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/dao/PaymentMethodDAO.class.php");
require_once (dirname ( dirname ( __FILE__ ) ) . "/exception/ValidationException.class.php");
require_once (dirname ( __FILE__ ) . "/AbstractCtrl.class.php");
class AccountRegCtrl extends AbstractCtrl {
	private $accountEdit = null;
	private $accountDAO = null;
	public function __construct() {
		parent::__construct ();
		$this->setAction ( $_REQUEST ["action"] ?? null );

		$this->guardAction(array(), AbstractCtrl::ACCESS_ADMIN);
		
		switch ($this->getAction ()) {
			case "saveInter" :
				$this->onSaveInter ();
				break;
			case "edit" :
				$this->onEdit ();
				break;
			case "cancelCont" :
				$this->onCancelCont ();
				break;
			case "incParc" :
				$this->incParc ();
				break;
			default :
		}
	}
	public function onCancelCont() {
		$output = array (
				"result" => 0,
				"message" => "",
				"validationException" => array (),
				"account_id" => 0 
		);
		
		$_validationException = array ();
		$inTransaction = false;
		try {
			$account = $this->getRequestBean ( $_POST );
			$this->validateBean ( $account );
			if ($account->getId () <= 0) {
				throw new Exception ( "Conta inválida" );
			} elseif ($account->getStatus () != Account::UNPAID) {
				throw new Exception ( "Conta não pode ser cancelada! Status atual:" . $account->getDescStatus () );
			}
			
			$_installments = $account->getInstallmentList ();
			foreach ( $_installments as $installment ) {
				if ($installment->getStatus () == Installment::UNPAID) {
					$installment->setStatus ( Installment::CANCEL );
				}
			}
			$account->setInstallmentList ( $_installments );
			$account->setStatus ( Installment::CANCEL );
			
			$this->accountDAO = AccountDAO::getInstance ();
			
			$db = DataBase::getInstance ( $this->getDbConfig () );
			$inTransaction = $db->beginTransaction ();
			$this->accountDAO->update ( $db, $account );
			$this->message->setDesc ( "Registro atualizado com sucesso!" );
			
			$db->commit ();
			$db = null;
			$this->message->setType ( Message::SUCCESS );
			$output ["account_id"] = $account->getId ();
		} catch ( ValidationException $e ) {
			if ($db != null && $inTransaction)
				$db->rollback ();
			$db = null;
			
			$_validationException = $e->toArray ( true );
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $this->userMessage ( $e ) );
		} catch ( PDOException $e ) {
			if ($db != null && $inTransaction)
				$db->rollback ();
			$db = null;
			
			if (stripos ( $e->getMessage (), 'fk_account_client1' ) !== false) {
				$newItem = array (
						"name" => htmlentities ( "Cód.Cliente" ),
						"id" => htmlentities ( "account_client_id" ),
						"message" => htmlentities ( "Cliente não existe! Informe um outro" ) 
				);
				$_validationException [] = $newItem;
				
				$this->message->setDesc ( "Dados inválidos!" );
			} else {
				$this->message->setDesc ( $this->dbErrorMessage ( $e ) );
			}
			
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $this->dbErrorMessage ( $e ) );
		} catch ( Exception $e ) {
			if ($db != null && $inTransaction)
				$db->rollback ();
			$db = null;
			
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $this->userMessage ( $e ) );
		}
		
		$output ["result"] = $this->message->getType ();
		
		$output ["message"] = iconv ( "UTF-8", "UTF-8", str_replace ( "\\n", "<br/>", $this->message->getDesc () ) );
		$output ["validationException"] = $_validationException;
		
		header ( "Content-type: text/plain" );
		echo json_encode ( $output );
		exit ();
	}
	public function incParc() {
		$output = array (
				"result" => 0,
				"message" => "",
				"validationException" => array (),
				"html_inc_installment" => "" 
		);
		
		$_validationException = array ();
		$line = "";
		$excp = new ValidationException ( "Dados incompletos!" );
		
		try {
			
			$date = null;
			
			$length = $_POST ["account_validate_month"];
			if ($length <= 0) {
				$excp->add ( "Total de parcelas", "account_validate_month", "Total de parcelas: Valor inválido" );
			}
			$value = floatval ( $_POST ["installment_reg_value"] );
			if ($value <= 0) {
				$excp->add ( "Valor(R$)", "installment_reg_value", "Valor(R$): Valor inválido" );
			}
			
			if ($_POST ["installment_reg_dt"] != "") {
				$dt = explode ( "/", $_POST ["installment_reg_dt"] );
				$date = new DateTimeCustom ( "NOW" );
				$date->setDate ( $dt [2], $dt [1], $dt [0] );
			} else {
				$excp->add ( "Data Inicial", "installment_reg_dt", "Data Inicial: Data inválida" );
			}
			
			if ($excp->size () > 0) {
				throw $excp;
			}
			
			$day = $date->format ( "d" );
			$month = $date->format ( "m" );
			$year = $date->format ( "Y" );
			
			$dayHist = 0;
			
			$i = 1;
			while ( $i <= $length ) {
				$installment = new Installment ();
				$installment->setId ( $i );
				$installment->setDueDate ( $date );
				$installment->setValue ( $value );
				
				$line .= '<tr class="tr_hover">';
				
				$line .= '<td class="td_id">' . $installment->getId ();
				$line .= '<input type="hidden" class="acc_hidden_id" name="installment_id[]" id="installment_id_' . $installment->getId () . '" value="' . $installment->getId () . '" />';
				$line .= '</td>';
				
				$line .= '<td class="td_value">' . number_format ( $installment->getValue (), 2, ',', '.' );
				$line .= '<input type="hidden" name="installment_value[]" id="installment_value_' . $installment->getId () . '" value="' . $installment->getValue () . '" />';
				$line .= '</td>';
				
				$line .= '<td class="td_drhr">' . $installment->getDueDate ()->format ( "d/m/Y" );
				$line .= '<input type="hidden" name="installment_due_date[]" id="installment_due_date_' . $installment->getId () . '" value="' . $installment->getDueDate ()->format ( "d/m/Y" ) . '" />';
				$line .= '</td>';
				
				$line .= '<td class="td_sts ' . $installment->getStatus () . '">' . iconv ( "UTF-8", "UTF-8", $installment->getDescStatus () );
				$line .= '<input type="hidden" name="installment_status[]" id="installment_status_' . $installment->getId () . '" value="' . $installment->getStatus () . '" />';
				$line .= '</td>';
				
				$line .= '</tr>';
				
				if ($dayHist > 0)
					$day = $dayHist;
				
				$dayHist = 0;
				$month ++;
				if ($month > 12) {
					$month = 1;
					$year ++;
				}
				if ($day >= 28) {
					$dayHist = $day;
					$timeTmp = "01." . $month . "." . $year;
					$dateTmp = new DateTimeCustom ( $timeTmp );
					if ($day > $dateTmp->format ( "t" )) {
						$day = $dateTmp->format ( "t" );
					}
				}
				
				$date->setDate ( $year, $month, $day );
				
				$i ++;
			}
		} catch ( ValidationException $e ) {
			$_validationException = $e->toArray ( true );
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $this->userMessage ( $e ) );
		} catch ( Exception $e ) {
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $this->userMessage ( $e ) );
		}
		
		$output ["result"] = $this->message->getType ();
		$output ["message"] = iconv ( "UTF-8", "UTF-8", str_replace ( "\\n", "<br/>", $this->message->getDesc () ) );
		$output ["validationException"] = $_validationException;
		$output ["html_inc_installment"] = $line;
		
		header ( "Content-type: text/plain" );
		echo json_encode ( $output );
		exit ();
	}
	public function onEdit() {
		$this->accountEdit = new Account ();
		$this->accountEdit->setId ( $_GET ["account_id"] );
		
		if ($this->accountEdit->getId () > 0) {
			try {
				$criteria = new Criteria ();
				$criteria->eq ( "acc.id", $this->accountEdit->getId () );
				
				$this->accountDAO = AccountDAO::getInstance ();
				$this->accountEdit = $this->accountDAO->getSingle ( $this->getDbConfig (), $criteria );
				if ($this->accountEdit == null || $this->accountEdit->getId () <= 0)
					throw new Exception ( "Conta não encontrada!" );
			} catch ( PDOException $e ) {
				$this->clientEdit = null;
			} catch ( Exception $e ) {
				$this->clientEdit = null;
			}
		}
	}
	public function onSaveInter() {
		$output = array (
				"result" => 0,
				"message" => "",
				"validationException" => array (),
				"account_id" => 0 
		);
		
		$_validationException = array ();
		$inTransaction = false;
		try {
			$account = $this->getRequestBean ( $_POST );
			$this->validateBean ( $account );		
			$this->accountDAO = AccountDAO::getInstance ();
			
			$db = DataBase::getInstance ( $this->getDbConfig () );
			$inTransaction = $db->beginTransaction ();
			if ($account->getId () > 0) {
				$this->accountDAO->update ( $db, $account );
				$this->message->setDesc ( "Registro atualizado com sucesso!" );
			} else {
				
				$_installs = $account->getInstallmentList ( );
				$length    = sizeof($_installs);
					
				$dtInit	   = $_installs[0]->getDueDate();
				$dtEnd	   = $_installs[$length-1]->getDueDate();
				
					
				$countInstall     = $this->accountDAO->countByInterval($db,$account->getClient(),$dtInit,$dtEnd);
				if($countInstall > 0){
					throw new Exception("Já existe uma conta válida neste intervalo!");
				}
				
				$this->accountDAO->insert ( $db, $account );
				$this->message->setDesc ( "Registro inserido com sucesso!" );
			}
			
			$db->commit ();
			$db = null;
			$this->message->setType ( Message::SUCCESS );
			$output ["account_id"] = $account->getId ();
		} catch ( ValidationException $e ) {
			if ($db != null && $inTransaction)
				$db->rollback ();
			$db = null;
			
			$_validationException = $e->toArray ( true );
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $this->userMessage ( $e ) );
		} catch ( PDOException $e ) {
			if ($db != null && $inTransaction)
				$db->rollback ();
			$db = null;
			
			if (stripos ( $e->getMessage (), 'fk_account_client1' ) !== false) {
				$newItem = array (
						"name" => htmlentities ( "Cód.Cliente" ),
						"id" => htmlentities ( "account_client_id" ),
						"message" => htmlentities ( "Cliente não existe! Informe um outro" ) 
				);
				$_validationException [] = $newItem;
				
				$this->message->setDesc ( "Dados inválidos!" );
			} else {
				$this->message->setDesc ( $this->dbErrorMessage ( $e ) );
			}
			
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $this->dbErrorMessage ( $e ) );
		} catch ( Exception $e ) {
			if ($db != null && $inTransaction)
				$db->rollback ();
			$db = null;
			
			$this->message->setType ( Message::ERR );
			$this->message->setDesc ( $this->userMessage ( $e ) );
		}
		
		$output ["result"] = $this->message->getType ();
		
		$output ["message"] = iconv ( "UTF-8", "UTF-8", str_replace ( "\\n", "<br/>", $this->message->getDesc () ) );
		$output ["validationException"] = $_validationException;
		
		header ( "Content-type: text/plain" );
		echo json_encode ( $output );
		exit ();
	}
	public function getRequestBean(array $_data) {
		$account = new Account ();
		$client = new Client ();
		$user = new User ();
		$person = new Person ();
		
		$totalInstallment = 0;
		$i = 0;
		$length = 0;
		
		$account->setId ( ($_data["account_id"] ?? "") );
		
		$person->setId ( ($_data["account_client_id"] ?? "") );
		$person->setName(($_data["account_client_name"] ?? ""));
		if ($person->getId () > 0) {
			$criteria = new Criteria ();
			$criteria->eq ( "cli.user_person_id", $person->getId () );
			$criteria->eq ( "usr.status", User::CHEK );
			$clientDAO = ClientDAO::getInstance ();
			$client = $clientDAO->getSingle ( $this->getDbConfig (), $criteria );
			if ($client != null)
				$account->setClient ( $client );
		}
		
		$length = sizeof ( ($_data["installment_id"] ?? "") );
		$_installs = array ();
		while ( $i < $length ) {
			$installment = new Installment ();
			$installment->setId ( ($_data["installment_id"] ?? "") [$i] );
			$installment->setValue ( ($_data["installment_value"] ?? "") [$i] );
			if (($_data["installment_due_date"] ?? "") [$i] != "") {
				$installment->setDueDate ( new DateTimeCustom ( "Now" ) );
				$dt = explode ( "/", ($_data["installment_due_date"] ?? "") [$i] );
				$installment->getDueDate ()->setDate ( $dt [2], $dt [1], $dt [0] );
			}
			
			if (($_data["installment_payment_date"] ?? "") [$i] != "") {
				$installment->setPaymentDate ( new DateTimeCustom ( "Now" ) );
				$dt = explode ( "/", ($_data["installment_payment_date"] ?? "") [$i] );
				$installment->getPaymentDate ()->setDate ( $dt [2], $dt [1], $dt [0] );
			}
			
			$installment->setPaymentValue ( ($_data["installment_payment_value"] ?? "") [$i] );
			
			$paymentMethod = new PaymentMethod();
			$paymentMethod->setId(  ($_data["installment_payment_method"] ?? "") [$i] );
			$installment->setPaymentMethod ( $paymentMethod );
			
			$installment->setStatus ( ($_data["installment_status"] ?? "") [$i] );
			
			$_installs [] = $installment;
			$i ++;
		}
		
		$account->setInstallmentList ( $_installs );
		
		$totalInstallment = sizeof ( $account->getInstallmentList () );
		$account->setValidateMonth ( $totalInstallment );
		
		return $account;
	}
	public function validateBean(Account $account) {
		$client = null;
		$person = null;
		
		$e = new ValidationException ( "Dados incompletos!" );
		
		$client = $account->getClient ();
		if ($client != null) {
			$user = $client->getUser ();
			if ($user != null) {
				$person = $user->getPerson ();
			}
		}
		
		if ($person == null || $person->getId () <= 0) {
			$e->add ( "Cód. Cliente", "account_client_id", "Necessário Código de cliente válido" );
		} else {
			
			$_installs = $account->getInstallmentList ();
			$length = sizeof ( $_installs );
			if ($length <= 0) {
				$e->add ( "Parcela", "account_client_id", "Necessária 1 parcela no minímo" );
			}
			if ($length > 0) {
				foreach ( $_installs as $installment ) {
					if ($installment->getId () <= 0) {
						$e->add ( "Parcela", "account_client_id", "Id da parcela inválido" );
						break;
					} elseif ($installment->getDueDate () == null) {
						$e->add ( "Parcela", "account_client_id", "Data de Vencimento da parcela " . $installment->getId () . " é inválida" );
						break;
					} elseif ($installment->getValue () <= 0) {
						$e->add ( "Parcela", "account_client_id", "Valor da parcela " . $installment->getId () . " é inválido" );
						break;
					}
					
					if($installment->getStatus() == Installment::PAID){
						if ($installment->getPaymentMethod ()==null || $installment->getPaymentMethod()->getId() <= 0) {
							$e->add ( "Parcela", "account_client_id", "Forma de pagamento da parcela " . $installment->getId () . "  inválida" );
							break;
						} elseif ($installment->getPaymentDate () == null) {
							$e->add ( "Parcela", "account_client_id", "Data de Pagamento da parcela " . $installment->getId () . " é inválida" );
							break;
						} elseif ($installment->getPaymentValue () <= 0) {
							$e->add ( "Parcela", "account_client_id", "Valor pago da parcela " . $installment->getId () . " é inválido" );
							break;
						}
					}
				}
			}
		}
		// conclusão
		if ($e->size () > 0) {
			throw $e;
		}
	}
	public function getAccountEdit() {
		return $this->accountEdit;
	}
	public function getPaymentMethodList(){
		$paymentMethodDAO = PaymentMethodDAO::getInstance ();
		$_result = array ();
		
		try {
			$criteria = new Criteria();
			$db = DataBase::getInstance ( $this->getDbConfig ());
			$_result = $paymentMethodDAO->getArrayCache ( $db , $criteria);
			//$_result = $paymentMethodDAO->getArray ( $db , $criteria);
			// $db->commit ();
			$db = null;
				
			return $_result;
		} catch ( PDOException $e ) {
				
			/*
			 * if ($db->inTransaction()) $db->rollback ();
			*/
			$db = null;
				
			/*
			 * $this->message->setType ( Message::ERR ); $this->message->setDesc ( $this->dbErrorMessage ( $e ) );
			*/
				
			return $_result;
		}
	}
}
$ctrlAcctReg = new AccountRegCtrl ();
?>