# KADE Caminhões

Aplicação web legada em PHP para anúncio e busca de caminhões (veículos em trânsito), com cadastro de assinantes, contas, parcelas e mapa de veículos. Esta é a versão preparada para rodar localmente (Laragon) com configuração via `.env`.

> Documentação complementar:
> [DOCUMENTATION.md](DOCUMENTATION.md) (arquitetura e API) ·
> [ENVIRONMENT_SETUP.md](ENVIRONMENT_SETUP.md) (ambiente, servidor, deploy) ·
> [QUICK_REFERENCE.md](QUICK_REFERENCE.md) (comandos e referência rápida)

## Requisitos

- PHP 7.4+ (testado no 8.3) com `short_open_tag = On` e as extensões `pdo_mysql`, `mbstring` e `gd` (captcha)
- MySQL/MariaDB com suporte a `utf8mb4`
- Apache com `mod_rewrite` e `mod_headers` (as rotas dependem de [kade/.htaccess](kade/.htaccess))
- Node.js 18+ apenas para gerar o CSS (`npm run build:css`); o servidor não precisa de Node
- Recomendado: [Laragon](https://laragon.org/) no Windows

## Início rápido (Laragon)

1. **Publicar a pasta `kade/` no Laragon** (junction, prompt como administrador):

   ```cmd
   mklink /J C:\laragon\www\kade D:\GitHub\php-kade\kade
   ```

   O Laragon cria o host virtual `http://kade.test/`.

2. **Importar o banco** a partir de [kade/der/kade_full_import.sql](kade/der/kade_full_import.sql) (cria o banco `kadecaminh`):

   ```cmd
   mysql -u root --default-character-set=utf8mb4 < kade\der\kade_full_import.sql
   ```

3. **Aplicar as migrações** de [kade/der/migrations](kade/der/migrations), em ordem (veja [Migrações](#migrações)):

   ```cmd
   for %f in (kade\der\migrations\*.sql) do mysql -u root --default-character-set=utf8mb4 kadecaminh < %f
   ```

4. **Configurar credenciais**: copie `.env.example` para `.env` na raiz do repositório e preencha os valores.

   ```cmd
   copy .env.example .env
   ```

5. **Acessar** `http://kade.test/`. Os e-mails locais podem ser vistos no Mailpit do Laragon (veja [E-mails em ambiente local](#e-mails-em-ambiente-local)).

## Configuração

As credenciais ficam em `.env` (ignorado pelo git), lido por [Properties.class.php](kade/shared/class/util/Properties.class.php). Variáveis já definidas no servidor têm prioridade sobre o arquivo. O `.env` é procurado na raiz do repositório e no diretório acima dela; em produção, mantenha-o fora da pasta pública (o `.htaccess` também bloqueia o acesso a `.env*`).

| Grupo | Variáveis | Uso |
|---|---|---|
| `DBKADE_DEV_*` / `DBKADE_PRD_*` | `USER`, `PASSWORD`, `HOST`, `DATABASE`, `PORT` | Banco principal |
| `DBCEP_DEV_*` / `DBCEP_PRD_*` | idem | **Opcional.** Banco de CEP antigo (procedure `procurar_cep`); deixe vazio se não tiver (veja [Busca de CEP](#busca-de-cep)) |
| `SMTP_*` | `HOST`, `PORT`, `USER`, `PASSWORD`, `AUTH` | Envio de e-mails (PHPMailer 6) |
| `SMTP_FROM` | e-mail | Remetente dos e-mails; use um endereço do mesmo domínio do SMTP (SPF/DKIM) |
| `APP_URL` | URL | Endereço público do site, usado nos links dos e-mails e no `sitemap.xml`. **Obrigatório em produção** (ex.: `https://www.kadecaminhoes.com.br`) |
| `SMS_GATEWAY_IP` | IP(s) separados por vírgula | IP do gateway de SMS. Sem a variável, vale o IP antigo (`200.195.184.186`); vazia, só o `SMS_TOKEN` é conferido (veja [Segurança](#segurança)) |
| `SMS_TOKEN` | texto de 16 a 128 caracteres (`A-Z a-z 0-9 _ -`) | **Recomendado.** Segredo que o gateway passa na URL do SMS |
| `MAPA_CONTAGEM` | `real` (padrão) ou `ilustrativa` | Números do mapa da home: veículos ativos de fato, ou a coluna `qty_illus` (o número inflado que o site antigo mostrava) |

### Ambiente DEV × PRD

O ambiente é escolhido pelo `HTTP_HOST` da requisição: **somente `localhost` e `kade.test` usam DEV**; qualquer outro host usa as variáveis `*_PRD_*`.

> **Atenção:** com o servidor embutido (`php -S localhost:8000 -t kade`) o host é `localhost:8000`, então a aplicação usa a configuração **PRD**. Para testar assim, preencha também as variáveis PRD com os dados locais ou use o Laragon. O servidor embutido também não processa o `.htaccess`, portanto as rotas amigáveis não funcionam nele.

### E-mails em ambiente local

O Laragon traz o [Mailpit](https://mailpit.axllent.org/). Com `SMTP_HOST=127.0.0.1`, `SMTP_PORT=1025` e `SMTP_USER` vazio, os e-mails (confirmação de cadastro, nova senha) não saem da máquina e aparecem em `http://localhost:8025`.

### Busca de CEP

O site não depende mais de um banco de CEP próprio. O antigo (`kadecaminh_1`) nunca esteve no repositório, e a base oficial dos Correios é paga e não pode ser redistribuída. [CepService.class.php](kade/shared/class/util/CepService.class.php) procura nesta ordem:

1. **Cache local**, a tabela `cep_cache` do banco principal (migração 008). Ela já nasce com os CEPs dos endereços cadastrados e guarda cada resposta das APIs por 180 dias.
2. **Banco de CEP antigo**, só se as variáveis `DBCEP_*` estiverem preenchidas.
3. [ViaCEP](https://viacep.com.br).
4. [BrasilAPI](https://brasilapi.com.br), que agrega Correios e outras fontes.

Cada API tem 4 segundos para responder. Se todas falharem, um registro vencido do cache ainda é usado. Um CEP em cache responde na hora; um novo leva cerca de 1 segundo.

Cidade e UF do veículo vêm sempre do CEP, no servidor. O cadastro por SMS com CEP que não existe é recusado ("CEP não encontrado") em vez de gravar o veículo sem localização. A coluna `address.cep` é numérica, então CEPs que começam com zero (estado de SP) voltam do banco com 7 dígitos; o código completa o zero.

### Mapa da home

O mapa, a tabela ao lado e o "N veículos ativos agora" leem o índice `idx_traveling_city`, que os gatilhos mantêm (migração 004). Ele tem duas contagens por cidade:
- `qty_real`: veículos ativos de fato. É o padrão.
- `qty_illus`: um número ilustrativo, maior que o real, que o site antigo exibia. Com `MAPA_CONTAGEM=ilustrativa` no `.env`, o site volta a mostrá-lo.

Estados e cidades sem veículos não aparecem na tabela.

## Migrações

O dump em `kade/der` é antigo. As migrações em [kade/der/migrations](kade/der/migrations) levam o banco ao estado que o código espera e devem rodar **em ordem, uma única vez**, depois de um backup:

| Arquivo | O que faz |
|---|---|
| `001_alinhar_schema_com_codigo.sql` | Ajusta colunas e chaves que o código usa e o dump não tinha |
| `002_senhas_bcrypt.sql` | Remove os gatilhos que faziam hash MD5 da senha e aumenta a coluna para bcrypt. As senhas antigas continuam valendo e são convertidas no próximo login |
| `003_collation_unica.sql` | Coloca banco e tabelas em `utf8mb4_unicode_ci` (havia mistura, que quebrava joins e comparações) |
| `004_indice_mapa.sql` | Recria procedures e gatilhos do índice do mapa (contagem dupla corrigida) e recalcula os totais |
| `005_parcelas.sql` | Parcelas sem forma de pagamento até serem pagas, status padrão e formas de pagamento iniciais |
| `006_validade_links.sql` | Data de criação dos links enviados por e-mail; o link de nova senha vence em 24 horas |
| `007_tentativas_login.sql` | Tabela `login_attempt`, usada no limite de tentativas de login |
| `008_cache_cep.sql` | Tabela `cep_cache` (busca de CEP sem banco de CEP próprio), já preenchida com os CEPs dos endereços cadastrados |

O código novo depende das migrações: rode-as **antes** de publicar os arquivos.

```bash
mysqldump -u USUARIO -p --routines --triggers kadecaminh > backup_antes_migracoes.sql
for f in kade/der/migrations/*.sql; do mysql -u USUARIO -p --default-character-set=utf8mb4 kadecaminh < "$f"; done
```

## Visual (Tailwind)

O visual usa [Tailwind CSS v4](https://tailwindcss.com/) com prefixo `k:` (ex.: `k:flex`, `k:md:px-6`), para não colidir com o CSS legado ainda carregado pelos diálogos do jQuery UI.

- Fonte: [kade/shared/css/src/app.css](kade/shared/css/src/app.css) (cores da marca, componentes `.k-btn`, `.k-field`, `.k-table`, `.k-badge`...).
- Saída: `kade/shared/css/app.css`, **versionada** (o servidor não roda Node). Depois de mudar classes em views ou JS, rode:

  ```bash
  npm ci            # uma vez
  npm run build:css
  ```

- O Tailwind lê `kade/view`, `kade/shared/js`, `error.php` e `manutencao.html`. Classes escritas em controllers não são vistas: use as classes de componente (`.k-badge`, `.k-btn`...).
- Elementos que o JS mostra/esconde (`.show()`, `.toggle()`) não devem ter utilitários de `display` (`k:flex`, `k:hidden`...), porque os utilitários usam `!important`.
- Partes comuns das views ficam em [kade/view/partials](kade/view/partials): cabeçalho, rodapé, menu da área interna, abertura/fechamento das páginas públicas e internas, paginação, mensagens e o layout dos e-mails.

### Imagens da home

O administrador troca a foto do topo e os três banners de "Destaques" em **Área do administrador → Imagens da home** (JPG até 1 MB). Use apenas fotos próprias ou licenciadas. Sem foto do topo, o topo fica só azul.

A foto do topo que vem com o projeto (`kade/shared/images/hero.jpg`, recortada em 1920 × 800) é [Highway 395, Indio](https://commons.wikimedia.org/wiki/File:Highway_395,_Indio,_United_States_(Unsplash).jpg), de Robson Hatsukami Morgan, em domínio público (CC0): pode ser usada comercialmente sem dar crédito.

## Estrutura

```
kade/                         raiz web
├── index.php                 front controller (charset, sessão, roteamento)
├── .htaccess                 rotas amigáveis, cache, bloqueio de .env
├── view/                     páginas (uma por rota) e partials/
├── error.php  manutencao.html  páginas de erro (403/404) e de manutenção
├── der/                      dump do banco e migrations/
└── shared/
    ├── class/
    │   ├── controller/       *Ctrl  — lógica das ações (JSON/HTML)
    │   ├── dao/              *DAO   — acesso a dados
    │   ├── vo/               *VO    — objetos de valor
    │   ├── util/             Properties, Validation, Message, ...
    │   └── mail/             Mail + PHPMailer 6
    ├── js/  images/          scripts das telas (jQuery) e imagens
    ├── css/src/app.css       fonte do Tailwind (gera css/app.css)
    └── cront_exp.txt         horário da última expiração de anúncios (gerado; fora do git)
```

### Rotas

O `.htaccess` reescreve as URLs:

| URL | Destino |
|---|---|
| `/<pagina>/...` | `index.php?page=<pagina>` → [ViewExtCtrl](kade/shared/class/controller/ViewExtCtrl.class.php) escolhe a view (`/` é a home, `maps.php`; página desconhecida responde 404) |
| `/<Controlador>.ctrlExt/<acao>` | `shared/class/controller/<Controlador>.class.php?action=<acao>` |
| `/RegisterVehicleSMS.ctrlExt/[<token>/]<from>/<body>` | cadastro de veículo via SMS (`<token>` = `SMS_TOKEN`, obrigatório quando definido) |
| `/sitemap.xml` | `sitemap.php`, com os endereços montados a partir do `APP_URL` |

Páginas principais: `cad_veiculo`, `cad_assinante`, `busca_veiculo`, `login`, `data_user`, `quem_somos`, `terms_use`. Várias exigem login e redirecionam para `login`.

## Convenções

- Arquivos de classe em `PascalCase.class.php`, incluídos com `require_once(dirname(__FILE__)."/...")`; views em `lowercase_underscore.php`.
- Controllers terminam em `Ctrl`, DAOs em `DAO`, VOs em `VO`. Indentação com tabs.
- Arquivos em **UTF-8 sem BOM**. Um BOM ou qualquer saída antes do `header()`/`json_encode` quebra as respostas.
- **Não renomeie** `name`/`id` de campos de formulário: o JavaScript em `shared/js/` depende deles.

## Testes

As ferramentas de teste são só de desenvolvimento (`composer install` e `npm ci` na raiz); nada disso vai para o servidor.

| Comando | O que verifica | Precisa de |
|---|---|---|
| `composer test:unit` | Regras isoladas: datas, valores, CSV, escape do `Criteria`, paginação, senhas, CPF/CNPJ/e-mail, formatação | Só PHP |
| `composer test:http` | O site de verdade: rotas e 404, arquivos internos bloqueados, permissões 401/403, login (bloqueio por tentativas, sessão, cookie), injeção de SQL, XSS, upload, CSV, cabeçalhos de segurança, nova senha pelo e-mail (Mailpit) | Site em `KADE_TEST_URL`, banco DEV do `.env` |
| `composer test` | As duas suítes acima | Idem |
| `npm run test:e2e` | No Chrome: todas as páginas no computador e no celular, sem erro de JavaScript, sem rolagem lateral, acessibilidade (axe-core, WCAG 2 A/AA) e fluxos (mapa, login, nova senha, imagens da home, parcelas, ordenação pelo teclado) | Site em `KADE_TEST_URL` e Chrome instalado (`CHROME_PATH` para outro caminho) |
| `composer analyse` | Análise estática (PHPStan): classes, métodos e variáveis inexistentes | Só PHP |

Os testes HTTP e de navegador entram com um administrador e um cliente comum, ambos ativos. Os padrões ficam em [phpunit.xml.dist](phpunit.xml.dist) (`KADE_TEST_ADMIN_LOGIN`, `KADE_TEST_USER_LOGIN` e senhas); para mudar só na sua máquina, copie para `phpunit.xml` ou defina as variáveis no ambiente. Se o site não responder, essas suítes são puladas.

Antes de um commit:

```bash
composer test && composer analyse && npm run test:e2e
npm run build:css   # se mudou classes do Tailwind
```

Não faça commit de credenciais reais.

## Problemas comuns

| Sintoma | Causa provável | Solução |
|---|---|---|
| `Unexpected token '<'` no front-end | Warning/notice do PHP impresso antes do JSON | Veja a resposta bruta em DevTools → Network; rode `php -l`; proteja acessos a `$_POST`/`$_SESSION` com `isset`/`??` |
| `Access denied for user ...` | `.env` ausente ou com credenciais erradas, ou ambiente PRD selecionado sem querer | Confira o `.env` e o host usado (veja [Ambiente DEV × PRD](#ambiente-dev--prd)) |
| Acentos quebrados (`Ã§`) | Arquivo salvo fora de UTF-8 ou banco importado sem `utf8mb4` | Salve em UTF-8 sem BOM; reimporte com `--default-character-set=utf8mb4` |
| Código PHP aparece na página | `short_open_tag` desligado | Ative no `php.ini` (o `.htaccess` já tenta via `php_flag`) |
| 404 em todas as rotas | `mod_rewrite` desativado ou `AllowOverride None` | Ative o módulo e permita `.htaccess` no vhost |

## Segurança

Regras atuais:

- Cada controller declara quem pode chamar cada ação com `guardAction()` (público, usuário logado ou administrador); sem permissão, a resposta é 401/403 em JSON.
- Senhas com bcrypt (`password_hash`); links de confirmação e de nova senha usam tokens aleatórios de uso único. O link de nova senha vale 24 horas e só é enviado ao e-mail do cadastro, se o e-mail digitado conferir; a resposta é a mesma com ou sem cadastro.
- Login: 10 senhas erradas para o mesmo login em 15 minutos bloqueiam esse login por 15 minutos (tabela `login_attempt`). A mensagem de erro é a mesma para login inexistente e senha errada, e o id da sessão muda a cada login.
- `view/`, `der/`, `shared/class/` e `shared/css/src/` não são servidos direto pelo Apache (404).
- Cabeçalhos: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy` e HSTS (só em HTTPS); sem `X-Powered-By`. O cookie de sessão é `HttpOnly`, `SameSite=Lax` e `Secure` em HTTPS, e todas as ações que alteram dados leem só `POST`.
- Consultas montadas pelo `Criteria` escapam valores e só aceitam nomes de coluna válidos em `ORDER BY`; o restante usa prepared statements.
- Usuário comum só altera o próprio cadastro e só cancela veículos que cadastrou.
- Tudo o que vem do usuário ou de SMS é escapado nas views (`htmlspecialchars`); exportações CSV neutralizam fórmulas.
- **Cadastro por SMS:** a rota é pública. Ela confere o IP do gateway (`SMS_GATEWAY_IP`) no cabeçalho `X-Real-IP` ou no IP da conexão. O `X-Real-IP` só é confiável se o proxy na frente do PHP o sobrescrever; se o servidor recebe as requisições direto, qualquer pessoa pode forjá-lo. Por isso, defina `SMS_TOKEN` e cadastre no gateway a URL `https://<site>/RegisterVehicleSMS.ctrlExt/<SMS_TOKEN>/<telefone>/<mensagem>`. Com o segredo definido, chamadas sem ele são recusadas, e o log de SMS mostra `***` no lugar dele. Regras em [SmsGateway.class.php](kade/shared/class/util/SmsGateway.class.php).


As credenciais de banco e SMTP que existiam no código foram removidas, mas continuam no histórico do git. **Rotacione essas senhas** no provedor caso ainda não tenha feito isso.
