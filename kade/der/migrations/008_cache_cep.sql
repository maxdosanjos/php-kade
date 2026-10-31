-- 008: cache de CEP no banco principal.
-- O banco de CEP próprio (kadecaminh_1, procedure procurar_cep) deixa de ser
-- obrigatório: a busca usa este cache, o banco antigo se ainda existir, o ViaCEP e a
-- BrasilAPI (CepService). O que vem das APIs fica aqui por 180 dias; com as APIs fora
-- do ar, um registro vencido ainda é usado.
-- Rodar antes de publicar o código que usa a tabela.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `cep_cache` (
	`cep` CHAR(8) NOT NULL,
	`street` VARCHAR(255) NOT NULL DEFAULT '',
	`neighborhood` VARCHAR(72) NOT NULL DEFAULT '',
	`city` VARCHAR(60) NOT NULL,
	`state` CHAR(2) NOT NULL,
	`source` VARCHAR(20) NOT NULL DEFAULT '',
	`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`cep`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ponto de partida: os CEPs que já estão nos endereços cadastrados (address.cep é
-- numérico, então volta o zero inicial). Entram como vencidos: na primeira busca a
-- API confirma o dado, e se ela estiver fora do ar o endereço cadastrado é usado.
INSERT IGNORE INTO `cep_cache` (`cep`, `street`, `neighborhood`, `city`, `state`, `source`, `updated_at`)
SELECT LPAD(a.`cep`, 8, '0'), MAX(a.`street`), MAX(COALESCE(a.`neighborhood`, '')), MAX(a.`city`), MAX(a.`state`), 'cadastro', '2000-01-01'
  FROM `address` a
 WHERE a.`cep` >= 1000000 AND a.`city` <> '' AND CHAR_LENGTH(a.`state`) = 2
 GROUP BY LPAD(a.`cep`, 8, '0');
