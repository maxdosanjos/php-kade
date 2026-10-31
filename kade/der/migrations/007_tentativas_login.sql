-- 007: limite de tentativas de login.
-- Cada senha errada fica registrada; com 10 erros para o mesmo login em 15 minutos,
-- o login fica bloqueado por 15 minutos (LoginQueryCtrl::onLogin). Antes não havia
-- limite e era possível testar senhas sem parar, inclusive a do administrador.
-- Rodar antes de publicar o código que usa a tabela.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `login_attempt` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`login` VARCHAR(45) NOT NULL,
	`ip` VARCHAR(45) NOT NULL DEFAULT '',
	`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `idx_login_attempt_login` (`login`, `created_at`),
	KEY `idx_login_attempt_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
