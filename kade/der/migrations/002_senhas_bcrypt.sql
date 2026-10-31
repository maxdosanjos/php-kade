-- =============================================================================
-- 002 - Senhas passam a ser geradas no PHP com password_hash (bcrypt).
--
-- Os gatilhos ON_BEFORE_INSERT/ON_BEFORE_UPDATE aplicavam MD5() na senha em
-- todo INSERT/UPDATE de `user` com senha não vazia, sem comparar com a anterior:
--   * o cadastro (que já enviava MD5) gravava md5(md5(senha)) e o usuário não entrava;
--   * a redefinição de senha tinha o mesmo efeito;
--   * qualquer outro UPDATE (ex.: confirmar cadastro, mudar status) aplicava MD5
--     sobre o hash salvo e invalidava a senha.
-- O hash agora é feito só no PHP. Hashes MD5 existentes continuam válidos: no
-- próximo login correto são regravados em bcrypt (LoginQueryCtrl::onLogin).
--
-- Rodar UMA vez, depois da 001:
--   mysql -u root kadecaminh < kade/der/migrations/002_senhas_bcrypt.sql
-- =============================================================================

DROP TRIGGER IF EXISTS `ON_BEFORE_INSERT`;
DROP TRIGGER IF EXISTS `ON_BEFORE_UPDATE`;

-- bcrypt tem 60 caracteres; 255 deixa espaço para algoritmos futuros do password_hash
ALTER TABLE `user` MODIFY `password` VARCHAR(255) NOT NULL;
