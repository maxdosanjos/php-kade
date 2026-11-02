-- 009: cadastro de veículo por WhatsApp e Telegram (MessengerCtrl, /webhook/...).
-- Rodar antes de publicar o código que usa as colunas e a tabela novas.
SET NAMES utf8mb4;

-- Origem dos veículos anunciados pelos novos canais
ALTER TABLE `vehicle_traveling`
	MODIFY `source` ENUM('WEB','SMS','WHATSAPP','TELEGRAM') NOT NULL DEFAULT 'WEB';

-- O log de SMS passa a guardar as mensagens dos três canais. msg_id é o id da mensagem
-- no WhatsApp/Telegram: eles reenviam a mesma mensagem quando a resposta demora, e o
-- índice único impede que o veículo seja gravado duas vezes. No SMS fica NULL.
ALTER TABLE `log_sms`
	ADD COLUMN `channel` VARCHAR(10) NOT NULL DEFAULT 'SMS' AFTER `id`,
	ADD COLUMN `msg_id` VARCHAR(128) NULL DEFAULT NULL AFTER `channel`,
	ADD UNIQUE KEY `uq_log_sms_msg` (`channel`, `msg_id`);

-- O Telegram não informa o telefone de quem escreve: na primeira conversa o motorista
-- compartilha o número pelo botão do robô e o vínculo fica aqui
CREATE TABLE IF NOT EXISTS `messenger_contact` (
	`channel` VARCHAR(10) NOT NULL,
	`external_id` VARCHAR(64) NOT NULL,
	`phone` VARCHAR(20) NOT NULL,
	`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`channel`, `external_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
