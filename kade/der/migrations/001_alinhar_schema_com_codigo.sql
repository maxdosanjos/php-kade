-- =============================================================================
-- 001 - Alinha o schema do dump (kade_full_import.sql) com o que o código usa.
--
-- O dump ainda identifica o usuário por login (user_login) em várias tabelas,
-- mas os DAOs usam o id da pessoa (user_person_id). Sem esta migração, quebram:
-- busca de veículos, registros realizados/utilizados, contas, cadastro de
-- clientes, redefinição de senha e a expiração automática de registros.
--
-- Rodar UMA vez, depois de importar o dump:
--   mysql -u root kadecaminh < kade/der/migrations/001_alinhar_schema_com_codigo.sql
-- Os dados existentes são convertidos de login para person_id via tabela user.
-- As FKs apontam para person(id): o MySQL 8.4 não aceita FK para user(person_id),
-- que não tem índice único.
-- =============================================================================

-- Acentos corretos mesmo quando o terminal não é UTF-8 (ex.: cp850 no Windows)
SET NAMES utf8mb4;

SET FOREIGN_KEY_CHECKS = 0;

-- client: user_login -> user_person_id ----------------------------------------
ALTER TABLE `client` ADD COLUMN `user_person_id` BIGINT UNSIGNED NULL FIRST;
UPDATE `client` c JOIN `user` u ON u.login = c.user_login SET c.user_person_id = u.person_id;

-- account: client_user_login -> client_user_person_id -------------------------
ALTER TABLE `account` ADD COLUMN `client_user_person_id` BIGINT UNSIGNED NULL AFTER `status`;
UPDATE `account` a JOIN `user` u ON u.login = a.client_user_login SET a.client_user_person_id = u.person_id;
ALTER TABLE `account`
	DROP FOREIGN KEY `fk_account_client1`,
	DROP INDEX `fk_account_client1`,
	DROP COLUMN `client_user_login`,
	MODIFY `client_user_person_id` BIGINT UNSIGNED NOT NULL;

ALTER TABLE `client`
	DROP FOREIGN KEY `fk_client_users1`,
	DROP PRIMARY KEY,
	DROP COLUMN `user_login`,
	MODIFY `user_person_id` BIGINT UNSIGNED NOT NULL,
	ADD PRIMARY KEY (`user_person_id`),
	ADD CONSTRAINT `fk_client_person1` FOREIGN KEY (`user_person_id`) REFERENCES `person` (`id`);

ALTER TABLE `account`
	ADD INDEX `fk_account_client1` (`client_user_person_id`),
	ADD CONSTRAINT `fk_account_client1` FOREIGN KEY (`client_user_person_id`) REFERENCES `client` (`user_person_id`);

-- link_confirm (confirmação de cadastro e redefinição de senha) ----------------
ALTER TABLE `link_confirm` ADD COLUMN `user_person_id` BIGINT UNSIGNED NULL FIRST;
UPDATE `link_confirm` l JOIN `user` u ON u.login = l.user_login SET l.user_person_id = u.person_id;
ALTER TABLE `link_confirm`
	DROP FOREIGN KEY `fk_link_confirm_user1`,
	DROP PRIMARY KEY,
	DROP COLUMN `user_login`,
	MODIFY `user_person_id` BIGINT UNSIGNED NOT NULL,
	ADD PRIMARY KEY (`user_person_id`),
	ADD CONSTRAINT `fk_link_confirm_person1` FOREIGN KEY (`user_person_id`) REFERENCES `person` (`id`);

-- vehicle_traveling_user: user_login -> user_person_id -------------------------
ALTER TABLE `vehicle_traveling_user` ADD COLUMN `user_person_id` BIGINT UNSIGNED NULL AFTER `vehicle_traveling_id`;
UPDATE `vehicle_traveling_user` v JOIN `user` u ON u.login = v.user_login SET v.user_person_id = u.person_id;
ALTER TABLE `vehicle_traveling_user`
	DROP FOREIGN KEY `fk_vehicle_traveling_has_users_users1`,
	DROP PRIMARY KEY,
	DROP INDEX `fk_vehicle_traveling_has_users_users1`,
	DROP COLUMN `user_login`,
	MODIFY `user_person_id` BIGINT UNSIGNED NOT NULL,
	ADD PRIMARY KEY (`vehicle_traveling_id`, `user_person_id`, `date_hr_used`),
	ADD INDEX `fk_vehicle_traveling_user_person1` (`user_person_id`),
	ADD CONSTRAINT `fk_vehicle_traveling_user_person1` FOREIGN KEY (`user_person_id`) REFERENCES `person` (`id`);

-- vehicle_traveling: quem utilizou o registro e status EXPIRED -----------------
-- (MapsExtCtrl::expiredRegisters grava EXPIRED, que não existia no enum)
ALTER TABLE `vehicle_traveling`
	ADD COLUMN `user_person_id_ut` BIGINT UNSIGNED NULL AFTER `status`,
	MODIFY `status` ENUM('ONLY_VIEW','UTILIZED','NONE','CANCEL','EXPIRED') NOT NULL DEFAULT 'NONE',
	ADD INDEX `fk_vehicle_traveling_person_ut` (`user_person_id_ut`),
	ADD CONSTRAINT `fk_vehicle_traveling_person_ut` FOREIGN KEY (`user_person_id_ut`) REFERENCES `person` (`id`);

-- payment_method: flag usada por PaymentMethodDAO ------------------------------
ALTER TABLE `payment_method` ADD COLUMN `enabled` TINYINT(1) NOT NULL DEFAULT 1;

SET FOREIGN_KEY_CHECKS = 1;
