# KADE Caminhões

Aplicação web legada em PHP para anúncio e busca de caminhões (veículos em trânsito), com cadastro de assinantes, contas, parcelas e mapa de veículos. Esta é a versão preparada para rodar localmente (Laragon) com configuração via `.env`.

> Documentação complementar:
> [DOCUMENTATION.md](DOCUMENTATION.md) (arquitetura e API) ·
> [ENVIRONMENT_SETUP.md](ENVIRONMENT_SETUP.md) (ambiente, servidor, deploy) ·
> [QUICK_REFERENCE.md](QUICK_REFERENCE.md) (comandos e referência rápida)

## Requisitos

- PHP 7.4+ (o código usa `??`) com `short_open_tag = On`
- MySQL/MariaDB com suporte a `utf8mb4`
- Apache com `mod_rewrite` e `mod_headers` (as rotas dependem de [kade/.htaccess](kade/.htaccess))
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

3. **Configurar credenciais**: copie `.env.example` para `.env` na raiz do repositório e preencha os valores.

   ```cmd
   copy .env.example .env
   ```

4. **Acessar** `http://kade.test/`.

## Configuração

As credenciais ficam em `.env` (ignorado pelo git), lido por [Properties.class.php](kade/shared/class/util/Properties.class.php). Variáveis já definidas no servidor têm prioridade sobre o arquivo. O `.env` é procurado na raiz do repositório e no diretório acima dela; em produção, mantenha-o fora da pasta pública (o `.htaccess` também bloqueia o acesso a `.env*`).

| Grupo | Variáveis | Uso |
|---|---|---|
| `DBKADE_DEV_*` / `DBKADE_PRD_*` | `USER`, `PASSWORD`, `HOST`, `DATABASE`, `PORT` | Banco principal |
| `DBCEP_DEV_*` / `DBCEP_PRD_*` | idem | Banco de CEP (stored procedure `procurar_cep`) |
| `SMTP_*` | `HOST`, `PORT`, `USER`, `PASSWORD`, `AUTH` | Envio de e-mails (PHPMailer) |

### Ambiente DEV × PRD

O ambiente é escolhido pelo `HTTP_HOST` da requisição: **somente `localhost` e `kade.test` usam DEV**; qualquer outro host usa as variáveis `*_PRD_*`.

> **Atenção:** com o servidor embutido (`php -S localhost:8000 -t kade`) o host é `localhost:8000`, então a aplicação usa a configuração **PRD**. Para testar assim, preencha também as variáveis PRD com os dados locais ou use o Laragon. O servidor embutido também não processa o `.htaccess`, portanto as rotas amigáveis não funcionam nele.

### Busca de CEP

[AddressDAO.class.php](kade/shared/class/dao/AddressDAO.class.php) normaliza o CEP, tenta a procedure `procurar_cep` no banco de CEP e, se a conexão ou a consulta falhar, recorre à API pública [ViaCEP](https://viacep.com.br). O dump incluído não contém o banco de CEP, então em ambiente local o ViaCEP costuma ser o caminho usado.

## Estrutura

```
kade/                         raiz web
├── index.php                 front controller (charset, sessão, roteamento)
├── .htaccess                 rotas amigáveis, cache, bloqueio de .env
├── view/                     páginas (uma por rota)
├── der/                      dump do banco
└── shared/
    ├── class/
    │   ├── controller/       *Ctrl  — lógica das ações (JSON/HTML)
    │   ├── dao/              *DAO   — acesso a dados
    │   ├── vo/               *VO    — objetos de valor
    │   ├── util/             Properties, Validation, Message, ...
    │   └── mail/             PHPMailer
    ├── view/                 parciais compartilhados (ex.: header_ext.php)
    ├── js/  css/  images/    assets (jQuery, jQuery UI, Tailwind helpers)
    └── cront_exp.txt         controle do job de expiração de mensagens
```

### Rotas

O `.htaccess` reescreve as URLs:

| URL | Destino |
|---|---|
| `/<pagina>/...` | `index.php?page=<pagina>` → [ViewExtCtrl](kade/shared/class/controller/ViewExtCtrl.class.php) escolhe a view (padrão: `maps.php`) |
| `/<Controlador>.ctrlExt/<acao>` | `shared/class/controller/<Controlador>.class.php?action=<acao>` |
| `/RegisterVehicleSMS.ctrlExt/<from>/<body>` | cadastro de veículo via SMS |

Páginas principais: `cad_veiculo`, `cad_assinante`, `busca_veiculo`, `login`, `data_user`, `quem_somos`, `terms_use`. Várias exigem login e redirecionam para `login`.

## Convenções

- Arquivos de classe em `PascalCase.class.php`, incluídos com `require_once(dirname(__FILE__)."/...")`; views em `lowercase_underscore.php`.
- Controllers terminam em `Ctrl`, DAOs em `DAO`, VOs em `VO`. Indentação com tabs.
- Arquivos em **UTF-8 sem BOM**. Um BOM ou qualquer saída antes do `header()`/`json_encode` quebra as respostas.
- **Não renomeie** `name`/`id` de campos de formulário: o JavaScript em `shared/js/` depende deles.

## Verificação antes de um commit

```bash
# sintaxe de todos os arquivos PHP
find kade -name "*.php" -exec php -l {} \; | grep -v "No syntax errors"

# charset das respostas
curl -I http://kade.test/
```

Depois, teste manualmente os fluxos principais: cadastro de assinante, cadastro de veículo, busca por CEP e login. Não faça commit de credenciais reais.

## Problemas comuns

| Sintoma | Causa provável | Solução |
|---|---|---|
| `Unexpected token '<'` no front-end | Warning/notice do PHP impresso antes do JSON | Veja a resposta bruta em DevTools → Network; rode `php -l`; proteja acessos a `$_POST`/`$_SESSION` com `isset`/`??` |
| `Access denied for user ...` | `.env` ausente ou com credenciais erradas, ou ambiente PRD selecionado sem querer | Confira o `.env` e o host usado (veja [Ambiente DEV × PRD](#ambiente-dev--prd)) |
| Acentos quebrados (`Ã§`) | Arquivo salvo fora de UTF-8 ou banco importado sem `utf8mb4` | Salve em UTF-8 sem BOM; reimporte com `--default-character-set=utf8mb4` |
| Código PHP aparece na página | `short_open_tag` desligado | Ative no `php.ini` (o `.htaccess` já tenta via `php_flag`) |
| 404 em todas as rotas | `mod_rewrite` desativado ou `AllowOverride None` | Ative o módulo e permita `.htaccess` no vhost |

## Segurança

As credenciais de banco e SMTP que existiam no código foram removidas, mas continuam no histórico do git. **Rotacione essas senhas** no provedor caso ainda não tenha feito isso.
