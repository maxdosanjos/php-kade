<?
	require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Properties.class.php");
	require_once (dirname ( __FILE__ ) ."/SMTP.class.php");
	require_once (dirname ( __FILE__ ) ."/PHPMailer.class.php");
	require_once (dirname ( __FILE__ ) ."/MailAttachment.class.php");
	require_once (dirname ( __FILE__ ) ."/EmailAddress.class.php");

	class Mail{
		private $pMail = null;
		
		public function __construct(){
			$data = Properties::getGroup("kadecaminhoes");
			
			$this->setPHPMail(new PHPMailer());
			$this->getPHPMail()->IsSMTP();
			$this->getPHPMail()->Host 				= $data["host"];
			$this->getPHPMail()->SMTPAuth 			= $data["auth"];
			$this->getPHPMail()->Username 			= $data["user"];
			$this->getPHPMail()->Password 			= $data["pass"];
			$this->getPHPMail()->Port             	= $data["port"];
			$this->getPHPMail()->WordWrap           = 100;		
			$this->getPHPMail()->IsHTML(true);	
		}
		
		public function getPHPMail(){
			return $this->pMail;
		}
		
		public function setPHPMail(PHPMailer $pMail){
			$this->pMail = $pMail;
		}
		
		public function getAvailableEmailList(EmailAddress $from){
			/*$db = DataBase::getInstance(WebMaxUtils::getCurrentDBConfig());
			// carregando cotas de e-mail
			$sql = "SELECT ec.email, ec.cota_diaria
					  FROM email_config AS ec";
			$query = $db->query($sql);
			$cotaArray = array();
			while($raw = $query->fetchObject()){
				$cotaArray[] = $raw;
			}
			
			// extraindo domínio
			$domain = $this->getDomain($from);
			if($domain == ""){
				throw new Exception($from->getEmail()." não pertence a um domínio autorizado para envio");
			}
			
			// carregando email enviados até agora no dia de hoje
			$sql = "SELECT ec.`email`, SUM(el.`delivery_attempts`) AS `total`, ec.`cota_diaria`
					  FROM `email_config` AS ec
				 LEFT JOIN `email_log` AS el ON ec.`email` = el.`email` AND el.`date` = '".date("Y-m-d")."' 
					 WHERE ec.email LIKE '%".$domain."' GROUP BY ec.email";
			$query = $db->query($sql);		
			$emailArray = array();
			while($raw = $query->fetchObject()){
				// só considera e-mails que tem cota
				if(intval($raw->total) < $raw->cota_diaria){
					$emailArray[] = $raw;
				}
			}*/
		}
		public function sendPHPMail(EmailAddress $from, array $recipients, $subject, $body, $bodyContentType = "text/html",$attachmentFileList = array(),$embeddedImageList=array(), $withCCO = false){
			// verificando se tem cotas disponíveis para o envio
			/*$emailArray = $this->getAvailableEmailList($from);
			if(sizeof($emailArray) <= 0){
				throw new Exception("Não há cotas disponíveis para envio de e-mails");
			}*/
			
			// pegando o primeiro e-mail da lista e carregando configuração
			$data = Properties::getGroup("smtp_kadecaminhoes");
			
			$this->setPHPMail(new PHPMailer());
			$this->getPHPMail()->IsSMTP();
			$this->getPHPMail()->Host 				= $data["host"];
			$this->getPHPMail()->SMTPAuth 			= $data["auth"];
			$this->getPHPMail()->Username 			= $data["user"];
			$this->getPHPMail()->Password 			= $data["pass"];
			$this->getPHPMail()->Port             	= $data["port"];
			$this->getPHPMail()->WordWrap           = 100;
			$this->getPHPMail()->IsHTML(true);
			$this->getPHPMail()->SMTPDebug = false;
			// colocando remetente como resposta
			$reply = new EmailAddress();
			$reply->setType("RPL");
			if($from->getEmail() == "")
				$reply->setEmail("contato@kadecaminhoes.com.br");
			else
				$reply->setEmail($from->getEmail());
			$recipients[] = $reply;
			
			// colocando remetente como CCO
			if($withCCO)
			{
				$reply = new EmailAddress();
				$reply->setType("BCC");
				if($from->getEmail() == "")
					$reply->setEmail("contato@kadecaminhoes.com.br");
				else
					$reply->setEmail($from->getEmail());
				$recipients[] = $reply;
			}
							
			$this->getPHPMail()->From = $this->getPHPMail()->Username;
			$this->getPHPMail()->FromName = $from->getName();
			$this->getPHPMail()->Subject = stripslashes($subject);
			
			$toCounter = 0;
			$recipientsStringArray = array();
			foreach($recipients AS $emailAddress){
				if($emailAddress == null || !($emailAddress instanceof EmailAddress) || $emailAddress->getEmail() == "" || $emailAddress->getEmail() == "sem@email"){
					continue;
				}
				switch($emailAddress->getType()){
				case "TO": // destinatário
				default:
					$this->getPHPMail()->AddAddress($emailAddress->getEmail(),$emailAddress->getName());
					$toCounter++;				
					break;
				case "CC": // cópia
					$this->getPHPMail()->AddCC($emailAddress->getEmail(),$emailAddress->getName());
					break;
				case "BCC": // cópia oculta
					$this->getPHPMail()->AddBCC($emailAddress->getEmail(),$emailAddress->getName());
					break;
				case "RPL": // email de resposta
					$this->getPHPMail()->AddReplyTo($emailAddress->getEmail(),$emailAddress->getName());
					break;
				case "CNF": // confirmação de leitura
					$this->ConfirmReadingTo = $emailAddress->getEmail();
					break;
				}
				
				$recipientsStringArray[] = "[".$emailAddress->getType()."] ".$emailAddress->getEmail();
			}
			
			// verificando se o email tem ao menos um destinatário
			if($toCounter == 0){
				throw new Exception("Nenhum destinatário informado.");
			}
			
			// anexos
			foreach($attachmentFileList AS $attachment){
				if($attachment instanceof MailAttachment)
					$this->getPHPMail()->AddAttachment($attachment->getPath(), $attachment->getName(),
							"base64", $attachment->getType());
				else
					$this->getPHPMail()->AddAttachment($attachment);
			}
			
			// corpo
			$bodyFooter = "<br/><hr/>[Kade Caminhões] ".date("d/m/Y H:i")." - Por: ".$_SESSION["nome"]." / E-mail: ".$this->getPHPMail()->Username;
			if($bodyContentType != "text/html"){
				$bodyFooter  = "\n---------------------------------------------------\n";
				$bodyFooter .= "[Kade Caminhões] ".date("d/m/Y H:i")." - Por: ".$_SESSION["nome"]." / E-mail: ".$this->getPHPMail()->Username;
			}
			
			// imagens incorporadas
			foreach($embeddedImageList AS $emb){
				$this->getPHPMail()->AddEmbeddedImage($emb["file"],$emb["cid"]);
			}
			
			$this->getPHPMail()->Body = $body.$bodyFooter;
			
			// conectando no SMTP	
			$this->getPHPMail()->SmtpConnect();
				
			// enviando
			if(!$this->getPHPMail()->Send()){
				$errorMessage = $this->getPHPMail()->ErrorInfo;
				try {
					$this->getPHPMail()->SmtpClose();
				}catch(Exception $e){
				}
				
				throw new Exception($errorMessage);
			}
			
			$this->getPHPMail()->SmtpClose();
		}
	}
?>