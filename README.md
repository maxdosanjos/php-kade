# KADE Caminhões

Aplicação web legada em PHP para anúncio e busca de caminhões (veículos em trânsito), com cadastro de assinantes, contas, parcelas e mapa de veículos. Esta é a versão preparada para rodar localmente (Laragon) com configuração via `.env`.

> Documentação complementar:
> [DOCUMENTATION.md](DOCUMENTATION.md) (arquitetura e API) ·
> [ENVIRONMENT_SETUP.md](ENVIRONMENT_SETUP.md) (ambiente, servidor, deploy) ·
> [QUICK_REFERENCE.md](QUICK_REFERENCE.md) (comandos e referência rápida)

🎬 **Demonstração:** home e mapa, cadastro de veículo pelo site e pelo WhatsApp, cadastro de assinante, busca e uso de um contato e as telas do administrador, com os [dados de demonstração](#dados-de-demonstração). Prévia acelerada abaixo; o vídeo completo (3 min, MP4) [abre aqui](demo/kade_demo.mp4?raw=true).

[![Demonstração do KADE Caminhões (prévia acelerada)](demo/kade_demo.gif)](demo/kade_demo.mp4?raw=true)


https://github.com/user-attachments/assets/21368566-33e2-46b3-8b4c-7ca8c325ff12



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
| `WHATSAPP_APP_SECRET`, `WHATSAPP_VERIFY_TOKEN` | textos | Ligam o webhook do WhatsApp: a chave secreta do app na Meta (confere a assinatura de cada mensagem) e um texto que você inventa para a verificação do webhook. Sem o primeiro, `/webhook/whatsapp` responde 404 (veja [WhatsApp e Telegram](#whatsapp-e-telegram)) |
| `WHATSAPP_TOKEN`, `WHATSAPP_PHONE_NUMBER_ID` | token permanente e id do número na Meta | Envio das respostas. Sem eles, o veículo é cadastrado, mas o motorista não recebe resposta |
| `WHATSAPP_API_VERSION` | ex.: `v23.0` (padrão) | Versão da Graph API usada no envio |
| `WHATSAPP_NUMBER` | número com DDI (ex.: `5543996761700`) | Mostra o botão "WhatsApp" no cadastro de veículo e em "Quem somos" |
| `TELEGRAM_WEBHOOK_SECRET` | 1 a 256 caracteres (`A-Z a-z 0-9 _ -`) | Liga o webhook do Telegram: o mesmo segredo informado no `setWebhook`. Sem ele, `/webhook/telegram` responde 404 |
| `TELEGRAM_BOT` | nome do robô (ex.: `KadeCaminhoesBot`) | Mostra o botão "Telegram" nas mesmas páginas |

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

Cidade e UF do veículo vêm sempre do CEP, no servidor. O cadastro por mensagem (SMS, WhatsApp, Telegram) com CEP que não existe é recusado ("CEP não encontrado") em vez de gravar o veículo sem localização. A coluna `address.cep` é numérica, então CEPs que começam com zero (estado de SP) voltam do banco com 7 dígitos; o código completa o zero.

### Mapa da home

O mapa, a tabela ao lado e o "N veículos ativos agora" leem o índice `idx_traveling_city`, que os gatilhos mantêm (migração 004). Ele tem duas contagens por cidade:
- `qty_real`: veículos ativos de fato. É o padrão.
- `qty_illus`: um número ilustrativo, maior que o real, que o site antigo exibia. Com `MAPA_CONTAGEM=ilustrativa` no `.env`, o site volta a mostrá-lo.

Estados e cidades sem veículos não aparecem na tabela.

### WhatsApp e Telegram

O motorista manda a mesma mensagem do SMS, `TIPO*CEP` (ex.: `6*83203010`), e recebe a resposta na conversa: tipo, cidade e número do registro, ou o motivo da recusa ("CEP não encontrado", "Tipo de Veículo 99 inválido"). `TIPOS` devolve a lista de códigos; qualquer outra mensagem devolve as instruções. Também valem espaço ou vírgula no lugar do `*` e o CEP com ponto e hífen.

- **WhatsApp** (API oficial da Meta, a Cloud API): o telefone do anúncio é o número do WhatsApp de quem escreveu. Alguns celulares antigos chegam sem o 9 da frente, que é recolocado. Só números do Brasil são aceitos.
- **Telegram**: o Telegram não informa o telefone. Na primeira conversa o robô pede o número com o botão "Compartilhar meu número" e guarda o vínculo (tabela `messenger_contact`). Só vale o contato do próprio usuário, não um contato encaminhado.
- As mensagens aparecem em **Área do administrador → Log de mensagens**, com o canal. Uma mensagem repetida (a Meta e o Telegram reenviam quando a resposta demora) é ignorada.
- Custo: a Meta não cobra a resposta enviada em até 24 horas depois da mensagem do motorista, que é o caso aqui (confira a tabela de preços atual da Meta). O Telegram é gratuito.

Código: [MessengerCtrl.class.php](kade/shared/class/controller/MessengerCtrl.class.php) (webhooks), [Messenger](kade/shared/class/util/Messenger.class.php), [WhatsApp](kade/shared/class/util/WhatsApp.class.php) e [Telegram](kade/shared/class/util/Telegram.class.php). O cadastro em si é o mesmo do SMS (`VehicleRegCtrl::registerByMessage`). Os dois serviços só chamam endereços `https` públicos: o teste com mensagem real é feito em produção ou por um túnel (cloudflared, ngrok). Localmente, `composer test:http` simula as duas plataformas com os segredos do `.env`.

**Ligar o WhatsApp** (precisa de uma conta empresarial na Meta e de um número só para o robô):
1. Em [developers.facebook.com/apps](https://developers.facebook.com/apps), crie um app do tipo Empresa e adicione o produto **WhatsApp**.
2. Em **WhatsApp → Configuração da API**, cadastre e verifique o número. Copie o **ID do número de telefone** para `WHATSAPP_PHONE_NUMBER_ID`.
3. Nas configurações do portfólio empresarial, em **Usuários do sistema**, crie um usuário, dê acesso ao app e gere um token permanente com a permissão `whatsapp_business_messaging`. Ele vai em `WHATSAPP_TOKEN` (o token temporário da tela de teste vence em 24 horas).
4. Em **Configurações do app → Básico**, copie a **Chave secreta do app** para `WHATSAPP_APP_SECRET`.
5. Invente um texto para `WHATSAPP_VERIFY_TOKEN` e publique o `.env`. Em **WhatsApp → Configuração**, informe a URL de callback `https://<site>/webhook/whatsapp` e esse texto, e assine o campo **messages**.
6. Para mostrar o botão no site, defina `WHATSAPP_NUMBER`.

**Ligar o Telegram:**
1. No Telegram, fale com o `@BotFather`, mande `/newbot` e escolha o nome. Ele devolve o token do robô: guarde-o (o site não precisa dele).
2. Defina no `.env` um texto aleatório em `TELEGRAM_WEBHOOK_SECRET` e o nome do robô em `TELEGRAM_BOT`, e publique.
3. Registre o webhook uma vez, trocando `<TOKEN>`, `<site>` e `<SEGREDO>`:

   ```bash
   curl https://api.telegram.org/bot<TOKEN>/setWebhook -d url=https://<site>/webhook/telegram -d secret_token=<SEGREDO> -d 'allowed_updates=["message"]'
   ```

   `https://api.telegram.org/bot<TOKEN>/getWebhookInfo` mostra se deu certo e o último erro.

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
| `009_whatsapp_telegram.sql` | Origens `WHATSAPP` e `TELEGRAM` nos veículos, canal e id da mensagem no log, e a tabela `messenger_contact` (telefone de quem usa o Telegram) |

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

Os três banners de "Destaques" que vêm com o projeto (atendimento, apoio e como funciona) são gerados a partir de [design/destaques/banners.html](design/destaques/banners.html), em 2200 × 600 px. Para mudar um texto ou o telefone, edite o HTML e rode `node design/destaques/render.mjs`, que grava `kade/shared/images/banner_slider1..3.jpg`. Ele usa o mesmo Chrome dos testes de navegador. Os logos de apoio foram recortados do banner antigo.

## Dados de demonstração

[demo/kade_demo.sql](demo/kade_demo.sql) é um banco completo (estrutura atual + dados fictícios) para apresentar o site:
- 386 veículos, 152 deles ativos em 82 cidades de 24 estados, vindos do site, de SMS, do WhatsApp e do Telegram, mais o histórico de 40 dias (vencidos, utilizados, cancelados);
- 23 assinantes (transportadoras, embarcadores e agenciadores), com contas e parcelas pagas, em aberto e em atraso;
- o log de mensagens dos três canais, com acertos, erros de digitação e pedidos de ajuda.

As datas são relativas ao momento da carga, então os anúncios "de agora" chegam sempre frescos. Depois, eles vencem como qualquer anúncio (cerca de 16 horas): recarregue antes de cada apresentação. A carga **apaga as tabelas** do banco escolhido:

```bash
mysqldump -u root --routines --triggers kadecaminh > backup_antes_demo.sql
mysql -u root --default-character-set=utf8mb4 kadecaminh < demo/kade_demo.sql
```

Para não mexer no seu banco, crie outro (`CREATE DATABASE kade_demo DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`), carregue nele e aponte `DBKADE_DEV_DATABASE` para ele.

| Login | Senha | Quem é |
|---|---|---|
| `admin` | `Teste@123` | Administrador |
| `clienteteste` | `Teste@123` | Cliente usado pelos testes automáticos (que passam com esses dados) |
| `rotasul` | `Demo@123` | Transportadora com plano anual em andamento, caminhões próprios em "Registros realizados" e fretes em "Registros utilizados". Boa para a demonstração |
| demais assinantes | `Demo@123` | `bortolini` e `sidneifrete` têm parcelas em atraso; `cargacerta` e `alineagro` aguardam a confirmação do e-mail; `triangulo` está bloqueado e `jairtransp`, cancelado. A lista completa está em "Gerenciar clientes" |

Só as cidades e os CEPs são reais ([demo/cidades.json](demo/cidades.json), conferidos no ViaCEP). Pessoas e empresas são inventadas: CPFs e CNPJs têm dígitos verificadores válidos, mas são aleatórios; os e-mails usam `@example.com`, os celulares começam com 90 e os IPs são de faixas reservadas para documentação. Para mudar os dados, edite [demo/gerar_demo.php](demo/gerar_demo.php) e rode `php demo/gerar_demo.php`. Ele lê a estrutura do banco DEV, que precisa estar com todas as migrações, e gera sempre os mesmos dados (só o hash das senhas muda).

**Vídeo de demonstração.** [demo/gravar_video.mjs](demo/gravar_video.mjs) percorre o site com legendas e um cursor visível, por uns 3 minutos: home e mapa, cadastro de veículo, uma mensagem pelo WhatsApp, cadastro de assinante com o e-mail de confirmação no Mailpit, busca e registros do assinante `rotasul` e as telas do administrador. Ele grava `demo/kade_demo.mp4`, o vídeo linkado no topo deste README. A prévia em GIF (o GitHub não mostra MP4 do repositório) sai do vídeo, 4× mais rápida:

```bash
ffmpeg -i demo/kade_demo.mp4 -filter_complex "setpts=PTS/4,fps=8,scale=800:-1:flags=lanczos,split[a][b];[a]palettegen=max_colors=96:stats_mode=diff[p];[b][p]paletteuse=dither=bayer:bayer_scale=4:diff_mode=rectangle" demo/kade_demo.gif
``` Precisa dos dados de demonstração carregados, do Mailpit e do ffmpeg, e cria registros no banco: recarregue a demonstração depois.

```bash
FFMPEG_PATH=caminho/para/ffmpeg node demo/gravar_video.mjs
```

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
| `/webhook/whatsapp`, `/webhook/telegram` | [MessengerCtrl](kade/shared/class/controller/MessengerCtrl.class.php): mensagens do WhatsApp e do Telegram (veja [WhatsApp e Telegram](#whatsapp-e-telegram)) |
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
| `composer test:http` | O site de verdade: rotas e 404, arquivos internos bloqueados, permissões 401/403, login (bloqueio por tentativas, sessão, cookie), injeção de SQL, XSS, upload, CSV, cabeçalhos de segurança, nova senha pelo e-mail (Mailpit), SMS, webhooks do WhatsApp e do Telegram | Site em `KADE_TEST_URL`, banco DEV do `.env`. Os testes dos webhooks precisam de `WHATSAPP_APP_SECRET`, `WHATSAPP_VERIFY_TOKEN` e `TELEGRAM_WEBHOOK_SECRET` (valores locais quaisquer) no `.env`; sem eles, são pulados |
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
- Tudo o que vem do usuário, de SMS, do WhatsApp e do Telegram é escapado nas views (`htmlspecialchars`); exportações CSV neutralizam fórmulas.
- **Cadastro por SMS:** a rota é pública. Ela confere o IP do gateway (`SMS_GATEWAY_IP`) no cabeçalho `X-Real-IP` ou no IP da conexão. O `X-Real-IP` só é confiável se o proxy na frente do PHP o sobrescrever; se o servidor recebe as requisições direto, qualquer pessoa pode forjá-lo. Por isso, defina `SMS_TOKEN` e cadastre no gateway a URL `https://<site>/RegisterVehicleSMS.ctrlExt/<SMS_TOKEN>/<telefone>/<mensagem>`. Com o segredo definido, chamadas sem ele são recusadas, e o log de SMS mostra `***` no lugar dele. Regras em [SmsGateway.class.php](kade/shared/class/util/SmsGateway.class.php).
- **WhatsApp e Telegram:** as rotas são públicas, mas só tratam mensagens assinadas. No WhatsApp, o cabeçalho `X-Hub-Signature-256` tem de bater com o HMAC-SHA256 do corpo com o `WHATSAPP_APP_SECRET`. No Telegram, o cabeçalho `X-Telegram-Bot-Api-Secret-Token` tem de ser o `TELEGRAM_WEBHOOK_SECRET`. Chamadas sem isso recebem 403 e aparecem no log de mensagens; sem o segredo no `.env`, o canal fica desligado (404). O token do robô do Telegram não fica no servidor.


As credenciais de banco e SMTP que existiam no código foram removidas, mas continuam no histórico do git. **Rotacione essas senhas** no provedor caso ainda não tenha feito isso.
