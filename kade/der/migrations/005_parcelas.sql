-- =============================================================================
-- 005 - Parcelas: criar conta e registrar pagamento.
--
-- * installment.payment_method_id era NOT NULL sem padrão, mas a parcela nasce
--   sem forma de pagamento: criar qualquer conta falhava ("Field
--   'payment_method_id' doesn't have a default value").
-- * installment.status aceitava NULL sem padrão e o INSERT não o informa: a
--   parcela nascia sem status e não aparecia nos filtros "em aberto"/"pagas".
-- * payment_method vazio impedia registrar pagamento. As formas abaixo só são
--   inseridas se a tabela estiver vazia (não duplica em produção).
--
-- Rodar UMA vez, depois da 004:
--   mysql -u root kadecaminh < kade/der/migrations/005_parcelas.sql
-- =============================================================================

-- Acentos corretos mesmo quando o terminal não é UTF-8 (ex.: cp850 no Windows)
SET NAMES utf8mb4;

ALTER TABLE `installment`
	MODIFY `payment_method_id` INT UNSIGNED NULL DEFAULT NULL;

UPDATE `installment` SET `status` = 'UNPAID' WHERE `status` IS NULL;

ALTER TABLE `installment`
	MODIFY `status` ENUM('PAID','UNPAID','CANCEL') NOT NULL DEFAULT 'UNPAID';

INSERT INTO `payment_method` (`name`, `descr`, `enabled`)
SELECT t.name, t.descr, t.enabled
  FROM (
	SELECT 'Boleto' AS name, 'Boleto bancário' AS descr, 1 AS enabled
	UNION ALL SELECT 'PIX', 'Transferência via PIX', 1
	UNION ALL SELECT 'Transferência', 'TED/DOC ou depósito em conta', 1
	UNION ALL SELECT 'Dinheiro', 'Pagamento em espécie', 1
	UNION ALL SELECT 'Cortesia', 'Parcela abonada (não aparece para novos pagamentos)', 0
  ) t
 WHERE NOT EXISTS (SELECT 1 FROM `payment_method`);
