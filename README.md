# KADE Caminhões — README

Resumo

Este repositório contém a aplicação web legada "KADE Caminhões" (PHP). As modificações recentes visam estabilizar a aplicação em ambiente local (Laragon), corrigir problemas de charset/UTF-8, melhorar a busca por CEP (com fallback ViaCEP), e reduzir quebras de JSON causadas por warnings PHP. Este README explica como configurar, executar e validar localmente.

Rápido (Quickstart) — Laragon (recomendado)

- Preparar um atalho (opcional) para que o código seja servido por Laragon (exemplo usado no projeto):
  cmd /c mklink /J C:\laragon\www\kade D:\GitHub\php-kade\kade

- Importar banco (ex.: MySQL do Laragon). Há um dump canônico em:
  - [kade_full_import.sql] (D:/GitHub/php-kade/kade/der/kade_full_import.sql)
  - Caso o arquivo tenha sido alterado, há um backup: [kade_full_import.sql.bak] (D:/GitHub/php-kade/kade/der/kade_full_import.sql.bak)

  Importar (exemplo):
  - Abra o prompt do MySQL ou phpMyAdmin e importe `kade\der\kade_full_import.sql`
  - Para garantir charset UTF-8: use a opção --default-character-set=utf8mb4 ao importar com mysql client.

- Servir a aplicação:
  - Via Laragon: aponte para C:\laragon\www\kade (symlink acima) e acesse http://kade.test/
  - Ou com PHP embutido (apenas para testes rápidos):
    php -S localhost:8000 -t kade

Comandos úteis (lint, checagens)

- Sintaxe PHP (checar um arquivo):
  php -l kade\shared\class\controller\VehicleRegCtrl.class.php
  php -l kade\shared\class\dao\AddressDAO.class.php
  php -l kade\view\cad_veiculo.php

- Verificar header HTTP para charset (exemplo):
  curl -I http://kade.test/
  curl -I http://kade.test/cad_veiculo/

Arquitetura de alto nível

- Estrutura principal
  - kade/ — raiz da aplicação web
  - kade/index.php — front controller
  - kade/view/ — páginas (views) orientadas à rota
  - kade/shared/class/controller/ — controladores (Ctrl *.class.php)
  - kade/shared/class/dao/ — acesso a dados (DAO)
  - kade/shared/class/vo/ — value objects (VO)
  - kade/shared/css/ e kade/shared/js/ — assets estáticos
  - kade/der/ — dumps e modelos de BD (SQL)

- Padrão de inclusão
  - O projeto usa require_once(dirname(__FILE__)."/...") e nomes de arquivo como PascalCase.class.php
  - Views seguem nomeação lowercase_underscore

Principais convenções e pontos importantes

- Charset
  - O projeto foi padronizado para UTF-8 (sem BOM). Verifique que seus editores salvem arquivos em UTF-8 sem BOM.
  - index.php já define header("Content-Type: text/html; charset=UTF-8") — confirme em servidores Apache/htaccess que o header não seja sobrescrito.

- Formulários e endpoints
  - Não renomeie campos de formulário ou names/ids usados pelo JavaScript; muitas ações dependem de nomes específicos.
  - Algumas rotas retornam JSON; se houver warnings PHP impressos antes do json_encode, o resultado quebra no cliente — use php -l para checar arquivos modificados.

- Naming & estilo
  - Controllers terminam com Ctrl, DAOs com DAO, VOs com VO
  - Código legado usa short tags em alguns pontos (<?) — verifique seu php.ini (short_open_tag)
  - Indentação com tabs (legacy style)

O que foi alterado recentemente (arquivos-chave)

- Cabeçalho e front-end
  - [kade/shared/view/header_ext.php] (D:/GitHub/php-kade/kade/shared/view/header_ext.php) — novo partial centralizado para header/hero.
  - [kade/shared/css/tailwind-helpers.css] (D:/GitHub/php-kade/kade/shared/css/tailwind-helpers.css) — overrides Tailwind / correções visuais.
  - [kade/view/main_ext.php] (D:/GitHub/php-kade/kade/view/main_ext.php) — agora inclui header_ext.php.

- Estabilidade & JSON
  - [kade/index.php] (D:/GitHub/php-kade/kade/index.php) — definições de charset e medidas temporárias para evitar warnings exibidos em tela durante desenvolvimento.
  - [kade/shared/class/controller/CustumerRegCtrl.class.php] (D:/GitHub/php-kade/kade/shared/class/controller/CustumerRegCtrl.class.php)
  - [kade/shared/class/controller/VehicleRegCtrl.class.php] (D:/GitHub/php-kade/kade/shared/class/controller/VehicleRegCtrl.class.php)
    - Melhoria: buffering de saída para capturar warnings e evitar JSON inválido; validações null-safe; captcha com expiração; honeypot e form_ts anti-bot.

- Banco de CEP
  - [kade/shared/class/dao/AddressDAO.class.php] (D:/GitHub/php-kade/kade/shared/class/dao/AddressDAO.class.php)
    - Agora normaliza CEP, tenta lookup via stored procedure `procurar_cep` e, se falhar, faz fallback para ViaCEP (https://viacep.com.br).
    - loadDBConfigCep() valida keys (user/password/host/port/database) e reporta errors claros.

- Dumps e backups
  - [kade/der/kade_full_import.sql] (D:/GitHub/php-kade/kade/der/kade_full_import.sql) — dump canônico (UTF-8/utf8mb4 recomendado).
  - [kade/der/kade_full_import.sql.bak] (D:/GitHub/php-kade/kade/der/kade_full_import.sql.bak) — backup criado ao remover BOM.

Diagnóstico rápido de problemas comuns

- "Unexpected token '<'" no frontend JSON
  - Causa: PHP warnings/notices impressos antes do JSON. A resposta não é JSON válido.
  - Como diagnosticar: abrir DevTools → Network → inspecionar resposta bruta do XHR, procurar warnings com paths e linhas.
  - Remediação: rodar php -l no(s) arquivo(s) indicado(s); checar logs do PHP/Apache; corrigir acessos a índices ($_POST/$_SESSION) sem isset.

- "Access denied for user 'root'@'localhost'" durante busca de CEP
  - Causa: configuração dbcep_dev em [kade/shared/class/util/Properties.class.php] com credenciais diferentes do ambiente (Laragon).
  - Solução: ajustar as chaves `dbcep_dev.*` no arquivo Properties.class.php para as credenciais do seu Laragon, ou deixe que o fallback ViaCEP responda (o DAO faz fallback automaticamente se a conexão falhar).

Sugestões de melhorias futuras

- Mover Tailwind e dependências para build local (npm) para permitir purge e melhor performance em produção.
- Substituir captcha com reCAPTCHA/hCaptcha para proteção mais robusta (requer chaves públicas/privadas).
- Cache local dos resultados do ViaCEP para reduzir latência e dependência externa.

Contribuindo / verificações locais antes do PR

- Sempre executar php -l nos arquivos modificados.
- Testar manualmente os fluxos principais no Laragon (cadastro de cliente, cadastro de veículo, busca por CEP, login).
- Não commitar dados sensíveis (senhas reais, credenciais) — use placeholders ou variáveis de ambiente.

Configuração de credenciais

- Copie `.env.example` para `.env` na raiz do repositório e preencha os valores do ambiente.
- O arquivo `.env` é carregado automaticamente por `Properties.class.php`; variáveis já definidas no servidor têm prioridade.
- Em produção, prefira definir as variáveis diretamente no servidor e manter o `.env` fora da pasta pública.
- As credenciais que estavam no código foram removidas. Como elas já fizeram parte do repositório, altere/rotacione as senhas de banco e SMTP no provedor.
