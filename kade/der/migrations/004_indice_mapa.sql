-- =============================================================================
-- 004 - Índice de veículos por cidade (mapa da home) contado uma vez só.
--
-- Os gatilhos de vehicle_traveling e o PHP (VehicleTravelingDAO) atualizavam o
-- índice ao mesmo tempo: cada cadastro somava 2 e cada uso/cancelamento
-- subtraía 2. O EXPIRED só era tratado pelo PHP, e nada comparava o status
-- anterior (marcar de novo um registro já utilizado subtraía outra vez e, com
-- a coluna UNSIGNED, chegava a dar erro).
--
-- Agora só os gatilhos mantêm o índice, comparando o status antigo e o novo:
-- conta enquanto o veículo está ativo (NONE ou ONLY_VIEW). No fim, o qty_real é
-- recalculado a partir dos veículos ativos; o qty_illus mantém a mesma
-- diferença que tinha em relação ao qty_real.
--
-- Rodar UMA vez, depois da 003 (precisa do cliente mysql por causa do DELIMITER):
--   mysql -u root kadecaminh < kade/der/migrations/004_indice_mapa.sql
-- =============================================================================

-- Acentos corretos mesmo quando o terminal não é UTF-8 (ex.: cp850 no Windows)
SET NAMES utf8mb4;

DROP TRIGGER IF EXISTS `ao_inserir`;
DROP TRIGGER IF EXISTS `ao_atualizar`;
DROP PROCEDURE IF EXISTS `add_idx_traveling_city`;
DROP PROCEDURE IF EXISTS `remove_idx_traveling_city`;

DELIMITER ;;

CREATE PROCEDURE `add_idx_traveling_city`(p_address_id BIGINT)
BEGIN
	DECLARE ld_state CHAR(2) DEFAULT NULL;
	DECLARE ld_state_nm VARCHAR(72);
	DECLARE ld_city VARCHAR(60);
	DECLARE CONTINUE HANDLER FOR NOT FOUND BEGIN END;

	SELECT state, state_nm, city INTO ld_state, ld_state_nm, ld_city
	  FROM address WHERE id = p_address_id;

	IF (ld_state IS NOT NULL AND ld_state != '') THEN
		INSERT INTO idx_traveling_city (state, city, state_nm, qty_real, qty_illus)
		VALUES (ld_state, ld_city, ld_state_nm, 1, 1)
		ON DUPLICATE KEY UPDATE qty_real = qty_real + 1, qty_illus = qty_illus + 1;
	END IF;
END ;;

CREATE PROCEDURE `remove_idx_traveling_city`(p_address_id BIGINT)
BEGIN
	DECLARE ld_state CHAR(2) DEFAULT NULL;
	DECLARE ld_city VARCHAR(60);
	DECLARE CONTINUE HANDLER FOR NOT FOUND BEGIN END;

	SELECT state, city INTO ld_state, ld_city
	  FROM address WHERE id = p_address_id;

	IF (ld_state IS NOT NULL AND ld_state != '') THEN
		-- Nunca abaixo de zero (as colunas são UNSIGNED)
		UPDATE idx_traveling_city
		   SET qty_real  = GREATEST(CAST(qty_real AS SIGNED) - 1, 0)
		     , qty_illus = GREATEST(CAST(qty_illus AS SIGNED) - 1, 0)
		 WHERE state = ld_state AND city = ld_city;
	END IF;
END ;;

CREATE TRIGGER `ao_inserir` AFTER INSERT ON `vehicle_traveling` FOR EACH ROW
BEGIN
	IF (NEW.status IN ('NONE', 'ONLY_VIEW')) THEN
		CALL add_idx_traveling_city(NEW.address_id);
	END IF;
END ;;

CREATE TRIGGER `ao_atualizar` AFTER UPDATE ON `vehicle_traveling` FOR EACH ROW
BEGIN
	DECLARE was_active BOOLEAN DEFAULT (OLD.status IN ('NONE', 'ONLY_VIEW'));
	DECLARE is_active BOOLEAN DEFAULT (NEW.status IN ('NONE', 'ONLY_VIEW'));

	IF (was_active AND NOT is_active) THEN
		CALL remove_idx_traveling_city(OLD.address_id);
	ELSEIF (is_active AND NOT was_active) THEN
		CALL add_idx_traveling_city(NEW.address_id);
	END IF;
END ;;

DELIMITER ;

-- Recalcula a contagem real a partir dos veículos ativos (corrige a duplicação antiga).
-- O SET é avaliado da esquerda para a direita: qty_illus usa o qty_real antigo.
UPDATE idx_traveling_city i
LEFT JOIN (
	SELECT a.state, a.city, COUNT(*) AS c
	  FROM vehicle_traveling vt
	  JOIN address a ON a.id = vt.address_id
	 WHERE vt.status IN ('NONE', 'ONLY_VIEW')
	 GROUP BY a.state, a.city
) r ON r.state = i.state AND r.city = i.city
SET i.qty_illus = GREATEST(CAST(i.qty_illus AS SIGNED) - CAST(i.qty_real AS SIGNED) + COALESCE(r.c, 0), 0),
    i.qty_real  = COALESCE(r.c, 0);
