-- 006: validade dos links enviados por e-mail.
-- O link de nova senha valia para sempre; agora vale por 24 horas a partir do envio
-- (UserDAO::findLinkPersonId). Links já enviados passam a contar a partir de agora.
-- Rodar antes de publicar o código que usa a coluna.
SET NAMES utf8mb4;

ALTER TABLE `link_confirm`
	ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `hash`;
