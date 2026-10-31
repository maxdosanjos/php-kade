<?php
	require_once (dirname ( dirname ( __FILE__ ) ) . "/util/Properties.class.php");
	// PHPMailer 6 (LGPL 2.1). O 2.0.4 que existia aqui usava eregi() (fatal no PHP 8)
	// e tinha falhas conhecidas, como a CVE-2016-10033.
	require_once (dirname ( __FILE__ ) ."/PHPMailer6/Exception.php");
	require_once (dirname ( __FILE__ ) ."/PHPMailer6/PHPMailer.php");
	require_once (dirname ( __FILE__ ) ."/PHPMailer6/SMTP.php");
	require_once (dirname ( __FILE__ ) ."/MailAttachment.class.php");
	require_once (dirname ( __FILE__ ) ."/EmailAddress.class.php");

	class Mail{
		const DEFAULT_FROM = "contato@kadecaminhoes.com.br";
		private $pMail = null;

		public function getPHPMail(){
			return $this->pMail;
		}

		public function setPHPMail(\PHPMailer\PHPMailer\PHPMailer $pMail){
			$this->pMail = $pMail;
		}

		/**
		 * Cria o PHPMailer configurado com o SMTP do .env (SMTP_HOST, SMTP_PORT, SMTP_USER,
		 * SMTP_PASSWORD, SMTP_AUTH, SMTP_FROM). Sem usuário, conecta sem autenticação
		 * (ex.: Mailpit do Laragon em 127.0.0.1:1025).
		 */
		private function createMailer(){
			$data = Properties::getGroup("smtp_kadecaminhoes");

			$mail = new \PHPMailer\PHPMailer\PHPMailer ( false );
			$mail->isSMTP ();
			$mail->Host     = ( string ) ($data["host"] ?? "");
			$mail->Port     = ( int ) ($data["port"] ?? 587);
			$mail->Username = ( string ) ($data["user"] ?? "");
			$mail->Password = ( string ) ($data["pass"] ?? "");
			$mail->SMTPAuth = ! empty ( $data["auth"] ) && $mail->Username !== "";
			$mail->CharSet  = "UTF-8";
			$mail->WordWrap = 100;
			$mail->isHTML ( true );
			return $mail;
		}

		public function sendPHPMail(EmailAddress $from, array $recipients, $subject, $body, $bodyContentType = "text/html",$attachmentFileList = array(),$embeddedImageList=array(), $withCCO = false){
			$this->setPHPMail($this->createMailer());
			$mail = $this->getPHPMail();

			// remetente como resposta
			$reply = new EmailAddress();
			$reply->setType("RPL");
			$reply->setEmail($from->getEmail() == "" ? self::DEFAULT_FROM : $from->getEmail());
			$recipients[] = $reply;

			// remetente como cópia oculta
			if($withCCO)
			{
				$bcc = new EmailAddress();
				$bcc->setType("BCC");
				$bcc->setEmail($from->getEmail() == "" ? self::DEFAULT_FROM : $from->getEmail());
				$recipients[] = $bcc;
			}

			// O envelope sai sempre de um endereço do próprio domínio (SPF/DKIM); o "from" pedido vira resposta
			$sender = ( string ) Properties::get ( "smtp_kadecaminhoes.from" );
			if ($sender === "")
				$sender = filter_var ( $mail->Username, FILTER_VALIDATE_EMAIL ) ? $mail->Username : self::DEFAULT_FROM;
			$mail->setFrom ( $sender, $from->getName () != "" ? $from->getName () : "KADE Caminhões" );
			$mail->Subject = ( string ) $subject;

			$toCounter = 0;
			foreach($recipients AS $emailAddress){
				if($emailAddress == null || !($emailAddress instanceof EmailAddress) || $emailAddress->getEmail() == "" || $emailAddress->getEmail() == "sem@email"){
					continue;
				}
				switch($emailAddress->getType()){
				case "CC": // cópia
					$mail->addCC($emailAddress->getEmail(),$emailAddress->getName());
					break;
				case "BCC": // cópia oculta
					$mail->addBCC($emailAddress->getEmail(),$emailAddress->getName());
					break;
				case "RPL": // e-mail de resposta
					$mail->addReplyTo($emailAddress->getEmail(),$emailAddress->getName());
					break;
				case "CNF": // confirmação de leitura
					$mail->ConfirmReadingTo = $emailAddress->getEmail();
					break;
				case "TO": // destinatário
				default:
					if ($mail->addAddress($emailAddress->getEmail(),$emailAddress->getName()))
						$toCounter++;
					break;
				}
			}

			if($toCounter == 0){
				throw new Exception("Nenhum destinatário informado.");
			}

			// anexos
			foreach($attachmentFileList AS $attachment){
				if($attachment instanceof MailAttachment)
					$mail->addAttachment($attachment->getPath(), $attachment->getName(), "base64", $attachment->getType());
				else
					$mail->addAttachment($attachment);
			}

			// imagens incorporadas
			foreach($embeddedImageList AS $emb){
				$mail->addEmbeddedImage($emb["file"],$emb["cid"]);
			}

			if ($bodyContentType == "text/html") {
				$mail->isHTML ( true );
				$mail->Body    = $body;
				$mail->AltBody = trim ( html_entity_decode ( strip_tags ( preg_replace ( '/<(style|script)\b[^>]*>.*?<\/\1>/is', '', $body ) ), ENT_QUOTES, "UTF-8" ) );
			} else {
				$mail->isHTML ( false );
				$mail->Body = $body;
			}

			if(!$mail->send()){
				throw new Exception($mail->ErrorInfo);
			}
		}
	}
?>
