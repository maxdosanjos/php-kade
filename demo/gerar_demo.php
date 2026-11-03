<?php
/**
 * Gera demo/kade_demo.sql: um banco completo para demonstração (estrutura atual +
 * dados fictícios). Veja a seção "Dados de demonstração" do README.
 *
 *   php demo/gerar_demo.php
 *
 * - A estrutura (tabelas, gatilhos, procedures) é lida do banco DEV do .env, que precisa
 *   estar com todas as migrações.
 * - Os dados são inventados com semente fixa: o arquivo sai igual a cada geração (menos
 *   o hash das senhas, que muda sempre).
 * - As datas são relativas ao momento em que o SQL é carregado (NOW() - INTERVAL ...):
 *   os anúncios "de agora" estão sempre frescos. Recarregue antes de cada demonstração.
 * - Cidades e CEPs reais (demo/cidades.json, conferidos no ViaCEP); pessoas, empresas,
 *   CPFs, CNPJs e telefones inventados. E-mails em @example.com, celulares com prefixo 90,
 *   IPs das faixas de documentação (RFC 5737).
 */
if (PHP_SAPI !== "cli")
	exit ( 1 );
$_SERVER ["HTTP_HOST"] = "kade.test"; // banco DEV
require dirname ( __DIR__ ) . "/kade/shared/class/util/Properties.class.php";
require dirname ( __DIR__ ) . "/kade/shared/class/dao/AddressDAO.class.php";

const SENHA_TESTE = "Teste@123"; // contas dos testes automáticos (phpunit.xml.dist)
const SENHA_DEMO = "Demo@123";
const VEICULOS_ATIVOS = 150;
const VEICULOS_HISTORICO = 230;

mt_srand ( 20261102 );

$cfg = Properties::getGroup ( "dbkade_dev" );
$pdo = new PDO ( sprintf ( "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4", $cfg ["host"], $cfg ["port"] ?: 3306, $cfg ["database"] ), $cfg ["user"], $cfg ["password"], array ( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ) );

/** Expressão SQL que vai crua para o arquivo (datas relativas ao momento da carga) */
class Raw {
	public $sql;
	public function __construct($sql) {
		$this->sql = $sql;
	}
}
function minutesAgo($m) {
	return new Raw ( "NOW() - INTERVAL " . ( int ) $m . " MINUTE" );
}
function daysFromToday($d) {
	return new Raw ( "CURDATE() " . ($d < 0 ? "-" : "+") . " INTERVAL " . abs ( ( int ) $d ) . " DAY" );
}
function pick(array $list) {
	return $list [mt_rand ( 0, count ( $list ) - 1 )];
}
/** Chave sorteada conforme os pesos: array(chave => peso) */
function weighted(array $weights) {
	$r = mt_rand ( 1, array_sum ( $weights ) );
	foreach ( $weights as $key => $w )
		if (($r -= $w) <= 0)
			return $key;
	return array_key_last ( $weights );
}
function digits($n) {
	$s = "";
	for($i = 0; $i < $n; $i ++)
		$s .= mt_rand ( 0, 9 );
	return $s;
}
/** CPF inventado com dígitos verificadores válidos (o cadastro confere) */
function cpf() {
	do
		$b = digits ( 9 );
	while ( count ( array_unique ( str_split ( $b ) ) ) == 1 );
	for($t = 9; $t < 11; $t ++) {
		for($s = 0, $i = 0; $i < $t; $i ++)
			$s += $b [$i] * (($t + 1) - $i);
		$b .= ((10 * $s) % 11) % 10;
	}
	return $b;
}
function cnpj() {
	$b = digits ( 8 ) . "0001";
	foreach ( array ( array ( 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2 ), array ( 6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2 ) ) as $weights ) {
		$s = 0;
		foreach ( $weights as $i => $f )
			$s += $b [$i] * $f;
		$b .= ($s % 11 < 2) ? 0 : 11 - $s % 11;
	}
	return $b;
}
/** Celular fictício: 90 + 7 dígitos */
function mobile() {
	return "90" . digits ( 7 );
}

// ---------------------------------------------------------------- tabelas em memória
$rows = array ();
$ids = array ( "phone" => 0, "address" => 0, "person" => 0, "vehicle" => 999, "account" => 0 );
$logs = array ();
function add($table, array $row) {
	global $rows;
	$rows [$table] [] = $row;
}
function nextId($key) {
	global $ids;
	return ++ $ids [$key];
}

$ufs = AddressDAO::getInstance ()->getListUf ();
$cities = json_decode ( file_get_contents ( __DIR__ . "/cidades.json" ), true );
$cityWeights = array_column ( $cities, "peso" );
function cityOf($name) {
	global $cities;
	foreach ( $cities as $c )
		if ($c ["city"] === $name)
			return $c;
	throw new Exception ( "Cidade fora de demo/cidades.json: " . $name );
}

function newPhone($ddd, $number = null) {
	$id = nextId ( "phone" );
	add ( "phone", array ( "id" => $id, "phone" => $number ?? mobile (), "ddd" => $ddd, "ddi" => "55" ) );
	return $id;
}
function newAddress(array $city, $number = null) {
	global $ufs;
	$id = nextId ( "address" );
	add ( "address", array ( "id" => $id, "cep" => ( int ) $city ["cep"], "street" => $city ["street"], "number" => $number, "complement" => null,
			"neighborhood" => $city ["neighborhood"], "city" => $city ["city"], "state" => $city ["state"], "state_nm" => $ufs [$city ["state"]] ) );
	return $id;
}
/**
 * Pessoa com endereço e telefone próprios, como o cadastro grava.
 * $type: PF, PJ ou NO (veio por mensagem: só o telefone). $doc: cpf, gender, cnpj, contact.
 */
function newPerson($type, $name, $email, array $city, $ddd, $phone, array $doc = array ()) {
	$phoneId = newPhone ( $ddd, $phone );
	$id = nextId ( "person" );
	add ( "person", array ( "id" => $id, "name" => $name, "email" => $email, "type" => $type, "address_id" => newAddress ( $city, ( string ) mt_rand ( 10, 3999 ) ), "phone_id" => $phoneId ) );
	if ($type == "PF")
		add ( "person_individual", array ( "person_id" => $id, "cpf" => $doc ["cpf"] ?? cpf (), "gender" => $doc ["gender"] ?? null, "dateBirth" => null ) );
	if ($type == "PJ")
		add ( "person_entity", array ( "person_id" => $id, "cnpj" => $doc ["cnpj"] ?? cnpj (), "ie" => null, "contact" => $doc ["contact"] ?? "" ) );
	return array ( "id" => $id, "phone_id" => $phoneId, "ddd" => $ddd, "name" => $name );
}

// ---------------------------------------------------------------- nomes
$firstM = array ( "João", "José", "Antônio", "Carlos", "Paulo", "Pedro", "Lucas", "Marcos", "Luiz", "Rafael", "Daniel", "Marcelo", "Bruno", "Eduardo", "Rodrigo", "Fernando",
		"Gustavo", "André", "Ricardo", "Sérgio", "Valdir", "Adilson", "Claudinei", "Edson", "Gilmar", "Jair", "Leandro", "Márcio", "Nelson", "Osmar", "Reinaldo", "Sidnei", "Valmir", "Wilson" );
$firstF = array ( "Maria", "Ana", "Juliana", "Fernanda", "Patrícia", "Aline", "Camila", "Simone", "Cristiane", "Luciana", "Rosângela", "Sandra" );
$last = array ( "Silva", "Santos", "Oliveira", "Souza", "Rodrigues", "Ferreira", "Alves", "Pereira", "Lima", "Gomes", "Costa", "Ribeiro", "Martins", "Carvalho", "Almeida",
		"Lopes", "Soares", "Fernandes", "Vieira", "Barbosa", "Rocha", "Dias", "Nascimento", "Andrade", "Moreira", "Nunes", "Marques", "Machado", "Mendes", "Freitas",
		"Cardoso", "Ramos", "Gonçalves", "Teixeira", "Moraes", "Pinto", "Schmidt", "Becker", "Zanella", "Bortolini" );
/** array(nome, gênero) */
function personName($female) {
	global $firstM, $firstF, $last;
	return array ( pick ( $female ? $firstF : $firstM ) . " " . pick ( $last ) . (mt_rand ( 1, 3 ) == 1 ? " " . pick ( $last ) : ""), $female ? "FEMININO" : "MASCULINO" );
}

// ---------------------------------------------------------------- usuários e assinantes
$passTest = password_hash ( SENHA_TESTE, PASSWORD_BCRYPT );
$passDemo = password_hash ( SENHA_DEMO, PASSWORD_BCRYPT );
$clients = array ();
function newUser($login, $password, $status, array $person, $profile, $daysAgo) {
	global $clients;
	add ( "user", array ( "login" => $login, "person_id" => $person ["id"], "password" => $password, "status" => $status, "text" => null,
			"date_cad" => minutesAgo ( $daysAgo * 1440 + mt_rand ( 0, 600 ) ), "dt_hr_alt" => null, "user_alt_login" => null ) );
	add ( "user_profile", array ( "user_person_id" => $person ["id"], "profile_id" => $profile, "enabled" => 1 ) );
	if ($profile == "CLIE") {
		add ( "client", array ( "user_person_id" => $person ["id"] ) );
		$clients [$login] = $person + array ( "login" => $login, "status" => $status );
	}
}

// Contas dos testes automáticos: mesmo login e senha do phpunit.xml.dist
$londrina = cityOf ( "Londrina" );
$curitiba = cityOf ( "Curitiba" );
newUser ( "admin", $passTest, "CHEK", newPerson ( "PF", "Administrador KADE", "admin@example.com", $londrina, $londrina ["ddd"], mobile (), array ( "gender" => "MASCULINO" ) ), "ADMN", 400 );
newUser ( "clienteteste", $passTest, "CHEK", newPerson ( "PF", "Cliente Teste", "cliente.teste@example.com", $curitiba, $curitiba ["ddd"], mobile (), array ( "gender" => "FEMININO" ) ), "CLIE", 120 );

// Assinantes: transportadoras, embarcadores e agenciadores. CHEK ativo, PEND aguardando
// confirmação do e-mail, BLCK bloqueado, CANC cancelado
$companies = array (
		array ( "rotasul", "Expresso Rota Sul Transportes Ltda", "Maringá", "CHEK" ),
		array ( "valedoivai", "Transportadora Vale do Ivaí Ltda", "Londrina", "CHEK" ),
		array ( "bortolini", "Rodoviário Bortolini Ltda", "Chapecó", "CHEK" ),
		array ( "cerealplanalto", "Cerealista Planalto Ltda", "Rio Verde", "CHEK" ),
		array ( "tresfronteiras", "Logística Três Fronteiras Ltda", "Foz do Iguaçu", "CHEK" ),
		array ( "zanella", "Transportes Zanella & Filhos Ltda", "Passo Fundo", "CHEK" ),
		array ( "camposverdes", "Armazéns Gerais Campos Verdes Ltda", "Sorriso", "CHEK" ),
		array ( "horizonte", "Fertilizantes Horizonte Ltda", "Paranaguá", "CHEK" ),
		array ( "serraazul", "Madeireira Serra Azul Ltda", "Lages", "CHEK" ),
		array ( "litoralnorte", "Distribuidora Litoral Norte Ltda", "Itajaí", "CHEK" ),
		array ( "novasafra", "Cooperativa Agroindustrial Nova Safra", "Cascavel", "CHEK" ),
		array ( "boapastagem", "Laticínios Boa Pastagem Ltda", "Uberlândia", "CHEK" ),
		array ( "cargacerta", "Agência Carga Certa Ltda", "Campinas", "PEND" ),
		array ( "triangulo", "Triângulo Fretes e Logística Ltda", "Uberaba", "BLCK" ),
);
foreach ( $companies as list ( $login, $name, $cityName, $status ) ) {
	$city = cityOf ( $cityName );
	list ( $contact ) = personName ( mt_rand ( 1, 4 ) == 1 );
	newUser ( $login, $passDemo, $status, newPerson ( "PJ", $name, $login . "@example.com", $city, $city ["ddd"], mobile (), array ( "contact" => $contact ) ), "CLIE", mt_rand ( 30, 700 ) );
}
$agents = array (
		array ( "marcosfretes", "Marcos Ribeiro Alves", "MASCULINO", "Dourados", "CHEK" ),
		array ( "anacargas", "Ana Paula Moreira", "FEMININO", "Rondonópolis", "CHEK" ),
		array ( "gilmaragencia", "Gilmar Becker", "MASCULINO", "Ponta Grossa", "CHEK" ),
		array ( "sidneifrete", "Sidnei Gonçalves", "MASCULINO", "Goiânia", "CHEK" ),
		array ( "patricialog", "Patrícia Nunes Cardoso", "FEMININO", "Ribeirão Preto", "CHEK" ),
		array ( "valmircargas", "Valmir Schmidt", "MASCULINO", "Luís Eduardo Magalhães", "CHEK" ),
		array ( "jairtransp", "Jair Pinto", "MASCULINO", "Toledo", "CANC" ),
		array ( "alineagro", "Aline Freitas", "FEMININO", "Primavera do Leste", "PEND" ),
);
foreach ( $agents as list ( $login, $name, $gender, $cityName, $status ) ) {
	$city = cityOf ( $cityName );
	newUser ( $login, $passDemo, $status, newPerson ( "PF", $name, $login . "@example.com", $city, $city ["ddd"], mobile (), array ( "gender" => $gender ) ), "CLIE", mt_rand ( 30, 500 ) );
}
// Quem vê e usa os veículos: assinantes ativos
$activeClients = array_values ( array_filter ( $clients, function ($c) {
	return $c ["status"] === "CHEK";
} ) );

// ---------------------------------------------------------------- contas e parcelas
$planValues = array ( 3 => 189.9, 6 => 159.9, 12 => 129.9 );
$late = array ( "bortolini", "sidneifrete" ); // parcelas em atraso, para a demonstração
foreach ( $clients as $login => $client ) {
	if ($client ["status"] === "PEND")
		continue;
	// A Rota Sul (login sugerido para a demonstração) tem plano anual em andamento
	$months = array ( "clienteteste" => 6, "rotasul" => 12 ) [$login] ?? pick ( array ( 3, 6, 12, 12 ) );
	$started = array ( "rotasul" => 4 ) [$login] ?? mt_rand ( 1, min ( 10, $months + 2 ) ); // meses atrás
	$accountId = nextId ( "account" );
	$allPaid = true;
	$installments = array ();
	for($n = 1; $n <= $months; $n ++) {
		$due = - ($started - $n + 1) * 30 + 10; // dias a partir de hoje
		$value = $planValues [$months];
		if ($client ["status"] === "CANC" || ($client ["status"] === "BLCK" && $due > 0))
			$status = "CANCEL";
		else if ($due < 0 && ! (in_array ( $login, $late ) && $due > - 70))
			$status = "PAID";
		else
			$status = "UNPAID";
		$allPaid = $allPaid && $status === "PAID";
		$installments [] = array ( "account_id" => $accountId, "id" => $n, "value" => $value, "payment_value" => $status === "PAID" ? ( string ) $value : "0",
				"due_date" => daysFromToday ( $due ), "payment_date" => $status === "PAID" ? daysFromToday ( $due - mt_rand ( 0, 4 ) ) : null, "status" => $status,
				"payment_method_id" => $status === "PAID" ? weighted ( array ( 1 => 3, 2 => 6, 3 => 2, 4 => 1 ) ) : null );
	}
	add ( "account", array ( "id" => $accountId, "date_hr_proc" => minutesAgo ( $started * 30 * 1440 + mt_rand ( 60, 900 ) ),
			"status" => $client ["status"] === "CANC" ? "CANCEL" : ($allPaid ? "PAID" : "UNPAID"), "client_user_person_id" => $client ["id"], "validate_month" => $months ) );
	foreach ( $installments as $installment )
		add ( "installment", $installment );
}

// ---------------------------------------------------------------- motoristas e veículos
// Tipos mais comuns no frete rodoviário (graneleiros, baús, sider) saem mais vezes
$typeWeights = array_fill ( 1, 37, 1 );
foreach ( array ( 6 => 14, 3 => 10, 1 => 6, 8 => 7, 10 => 6, 11 => 5, 23 => 7, 25 => 5, 26 => 6, 18 => 4, 28 => 4, 32 => 4, 36 => 4, 7 => 3, 4 => 3, 12 => 3 ) as $t => $w )
	$typeWeights [$t] = $w;

$drivers = array ();
for($i = 0; $i < 130; $i ++) {
	list ( $name, $gender ) = personName ( mt_rand ( 1, 100 ) <= 4 );
	$drivers [] = array ( "name" => $name, "gender" => $gender, "cpf" => cpf (), "ddd" => $cities [weighted ( $cityWeights )] ["ddd"], "phone" => mobile (), "person" => null, "tg" => null );
}

const IP_SMS = "203.0.113.10"; // faixas de documentação (RFC 5737): não são IPs de ninguém
const IP_WHATSAPP = "198.51.100.20";
const IP_TELEGRAM = "198.51.100.30";

/** Linha do log de mensagens, como SmsGateway/MessengerCtrl gravam */
function logMessage($source, array $driver, $body, $minutesAgo, $type, $log, $key, $linked = true) {
	global $logs;
	$local = $driver ["ddd"] . $driver ["phone"];
	$row = array ( "id" => 0, "channel" => $source, "msg_id" => null, "from" => "0" . $local, "body" => $body, "ip_trat" => IP_SMS,
			"url_request" => "/RegisterVehicleSMS.ctrlExt/***/0" . $local . "/" . $body, "type_log" => $type, "log" => $log, "date_hr" => minutesAgo ( $minutesAgo ) );
	if ($source === "WHATSAPP")
		$row = array_merge ( $row, array ( "msg_id" => "wamid.DEMO" . $key, "from" => "55" . $local, "ip_trat" => IP_WHATSAPP, "url_request" => "/webhook/whatsapp" ) );
	if ($source === "TELEGRAM")
		$row = array_merge ( $row, array ( "msg_id" => ( string ) (is_int ( $key ) ? 700000 + $key : 800000 + ( int ) substr ( $key, 1 )),
				"from" => $linked ? "55" . $local : "tg:" . $driver ["tg"], "ip_trat" => IP_TELEGRAM, "url_request" => "/webhook/telegram" ) );
	$logs [] = array ( $minutesAgo, $row );
}

/**
 * Um veículo como o site grava: endereço, pessoa (no site, reaproveitada pelo CPF/CNPJ),
 * telefone e contato; mais quem viu ou usou o contato e o log da mensagem quando veio
 * por SMS, WhatsApp ou Telegram. $owner: veículo da própria empresa logada.
 */
function newVehicle($minutesAgo, $status, array &$driver, $source = null, array $owner = null, $cityIndex = null) {
	global $cities, $cityWeights, $typeWeights, $activeClients;
	$id = nextId ( "vehicle" );
	$city = $cities [$cityIndex ?? weighted ( $cityWeights )];
	$type = weighted ( $typeWeights );
	$source = $source ?? weighted ( array ( "WEB" => 34, "SMS" => 26, "WHATSAPP" => 30, "TELEGRAM" => 10 ) );
	if ($source === "TELEGRAM")
		$driver ["tg"] = $driver ["tg"] ?? ( string ) mt_rand ( 100000000, 999999999 ); // passa a usar o robô

	if ($owner !== null) {
		$personId = $owner ["id"];
		$contactName = $owner ["name"];
		$phoneId = newPhone ( $owner ["ddd"] );
	} else if ($source === "WEB") {
		if ($driver ["person"] === null)
			$driver ["person"] = newPerson ( "PF", $driver ["name"], null, $city, $driver ["ddd"], $driver ["phone"], array ( "cpf" => $driver ["cpf"], "gender" => $driver ["gender"] ) ) ["id"];
		$personId = $driver ["person"];
		$contactName = $driver ["name"];
		$phoneId = newPhone ( $driver ["ddd"], $driver ["phone"] );
	} else {
		// Por mensagem só vem o telefone: pessoa sem nome, como o site grava
		$person = newPerson ( "NO", "", null, $city, $driver ["ddd"], $driver ["phone"] );
		$personId = $person ["id"];
		$contactName = "";
		$phoneId = $person ["phone_id"];
	}
	$utilizedBy = $status === "UTILIZED" ? pick ( $activeClients ) ["id"] : null;
	$ip = array ( "WEB" => "192.0.2." . mt_rand ( 1, 254 ), "SMS" => IP_SMS, "WHATSAPP" => IP_WHATSAPP, "TELEGRAM" => IP_TELEGRAM ) [$source];

	add ( "vehicle_traveling", array ( "id" => $id, "vehicle_type_id" => $type, "date_hr_proc" => minutesAgo ( $minutesAgo ), "source" => $source,
			"address_id" => newAddress ( $city ), "ip" => $ip, "status" => $status, "user_person_id_ut" => $utilizedBy ) );
	add ( "vehicle_contact", array ( "vehicle_traveling_id" => $id, "person_id" => $personId, "name" => $contactName, "phone_contact_id" => $phoneId ) );

	// Quem viu o contato e quem fechou o frete
	if ($status === "ONLY_VIEW" || $status === "UTILIZED") {
		$viewers = array ();
		for($v = mt_rand ( 1, 3 ); $v > 0; $v --)
			$viewers [pick ( $activeClients ) ["id"]] = true;
		if ($utilizedBy !== null)
			$viewers [$utilizedBy] = true;
		foreach ( array_keys ( $viewers ) as $k => $viewer )
			add ( "vehicle_traveling_user", array ( "vehicle_traveling_id" => $id, "user_person_id" => $viewer,
					"date_hr_used" => minutesAgo ( max ( 1, $minutesAgo - 5 - $k * 7 - mt_rand ( 0, 20 ) ) ), "status" => "ONLY_VIEW" ) );
		if ($utilizedBy !== null)
			add ( "vehicle_traveling_user", array ( "vehicle_traveling_id" => $id, "user_person_id" => $utilizedBy,
					"date_hr_used" => minutesAgo ( max ( 0, $minutesAgo - 40 - mt_rand ( 0, 60 ) ) ), "status" => "UTILIZED" ) );
	}

	if ($owner === null && $source !== "WEB")
		logMessage ( $source, $driver, $type . "*" . $city ["cep"], $minutesAgo, "sucess", "Registro " . $id . " realizado com sucesso", $id );
}

// Anúncios ativos (últimas 12 horas, mais nas últimas) e histórico (últimos 40 dias).
// Cada cidade tem ao menos um caminhão ativo, para o mapa cobrir o país; o resto segue os pesos
$timeline = array ();
$everyCity = range ( 0, count ( $cities ) - 1 );
for($i = 0; $i < VEICULOS_ATIVOS; $i ++)
	$timeline [] = array ( ( int ) (pow ( mt_rand ( 0, 1000 ) / 1000, 1.6 ) * 715) + 3, mt_rand ( 1, 100 ) <= 22 ? "ONLY_VIEW" : "NONE", $everyCity [$i] ?? null );
for($i = 0; $i < VEICULOS_HISTORICO; $i ++)
	$timeline [] = array ( mt_rand ( 1, 40 ) * 1440 + mt_rand ( 0, 1439 ), weighted ( array ( "EXPIRED" => 58, "UTILIZED" => 32, "CANCEL" => 10 ) ), null );
// Caminhões da própria Expresso Rota Sul (aparecem em "Registros realizados" desse login)
foreach ( array ( array ( 95, "NONE" ), array ( 260, "NONE" ), array ( 2900, "EXPIRED" ), array ( 7300, "UTILIZED" ), array ( 15000, "EXPIRED" ), array ( 26000, "CANCEL" ) ) as list ( $minutes, $status ) )
	$timeline [] = array ( $minutes, $status, null, "rotasul" );
usort ( $timeline, function ($a, $b) {
	return $b [0] - $a [0]; // do mais antigo para o mais novo: os números crescem com o tempo
} );
$noDriver = array ();
foreach ( $timeline as $entry ) {
	if (isset ( $entry [3] ))
		newVehicle ( $entry [0], $entry [1], $noDriver, "WEB", $clients [$entry [3]] );
	else
		newVehicle ( $entry [0], $entry [1], $drivers [mt_rand ( 0, count ( $drivers ) - 1 )], null, null, $entry [2] );
}

// Motoristas que já confirmaram o número no robô do Telegram
foreach ( $drivers as $d )
	if ($d ["tg"] !== null)
		add ( "messenger_contact", array ( "channel" => "TELEGRAM", "external_id" => $d ["tg"], "phone" => "(" . $d ["ddd"] . ") " . $d ["phone"],
				"created_at" => minutesAgo ( 45 * 1440 ), "updated_at" => minutesAgo ( 45 * 1440 ) ) );

// Mensagens que não viraram anúncio: erros de digitação, ajuda, pedido do número
$others = array (
		array ( "SMS", "6*8601000", "error", "CEP inválido" ),
		array ( "SMS", "45*80010000", "error", "Tipo de Veículo 45 inválido!" ),
		array ( "SMS", "3*00000000", "error", "CEP não encontrado!" ),
		array ( "WHATSAPP", "bom dia", "info", "Instruções enviadas" ),
		array ( "WHATSAPP", "tipos", "info", "Lista de códigos enviada" ),
		array ( "WHATSAPP", "6*99999999", "error", "CEP não encontrado!" ),
		array ( "WHATSAPP", "Carreta LS em Rio Verde", "info", "Instruções enviadas" ),
		array ( "WHATSAPP", "[audio]", "info", "Instruções enviadas" ),
		array ( "TELEGRAM", "/start", "info", "Pedido o número de telefone" ),
		array ( "TELEGRAM", "[contato]", "info", "Número confirmado" ),
		array ( "TELEGRAM", "tipos", "info", "Lista de códigos enviada" ),
);
for($i = 0; $i < 36; $i ++) {
	list ( $source, $body, $type, $log ) = $others [$i % count ( $others )];
	$d = $drivers [mt_rand ( 0, count ( $drivers ) - 1 )];
	$d ["tg"] = $d ["tg"] ?? ( string ) mt_rand ( 100000000, 999999999 );
	if ($body === "[contato]")
		$body = "[contato +55" . $d ["ddd"] . $d ["phone"] . "]";
	logMessage ( $source, $d, $body, mt_rand ( 10, 40 * 1440 ), $type, $log, "X" . $i, ! in_array ( $log, array ( "Pedido o número de telefone", "Número confirmado" ) ) );
}
usort ( $logs, function ($a, $b) {
	return $b [0] - $a [0];
} );
foreach ( $logs as $i => list ( , $row ) )
	add ( "log_sms", array_merge ( $row, array ( "id" => $i + 1 ) ) );

// CEPs das cidades no cache: a busca de CEP da demonstração não depende de internet
foreach ( $cities as $c )
	add ( "cep_cache", array ( "cep" => $c ["cep"], "street" => $c ["street"], "neighborhood" => $c ["neighborhood"], "city" => $c ["city"], "state" => $c ["state"],
			"source" => "viacep", "updated_at" => new Raw ( "NOW()" ) ) );

// ---------------------------------------------------------------- SQL
$out = fopen ( __DIR__ . "/kade_demo.sql", "w" );
$w = function ($s) use ($out) {
	fwrite ( $out, $s . "\n" );
};
$value = function ($v) use ($pdo) {
	if ($v === null)
		return "NULL";
	if ($v instanceof Raw)
		return $v->sql;
	if (is_int ( $v ) || is_float ( $v ))
		return ( string ) $v;
	return $pdo->quote ( ( string ) $v );
};

$w ( "-- KADE Caminhões: banco de DEMONSTRAÇÃO (estrutura atual + dados fictícios)." );
$w ( "-- Gerado por demo/gerar_demo.php. Não edite à mão: rode o gerador de novo." );
$w ( "--" );
$w ( "-- Carregue num banco à parte ou no banco local (as tabelas dele são APAGADAS):" );
$w ( "--   mysql -u root --default-character-set=utf8mb4 NOME_DO_BANCO < demo/kade_demo.sql" );
$w ( "--" );
$w ( "-- Logins: admin / " . SENHA_TESTE . " (administrador), clienteteste / " . SENHA_TESTE . " (cliente dos testes" );
$w ( "-- automáticos) e os assinantes fictícios com a senha " . SENHA_DEMO . " (ex.: rotasul). As datas são relativas" );
$w ( "-- ao momento da carga: recarregue antes de cada demonstração." );
$w ( "" );
$w ( "SET NAMES utf8mb4;" );
$w ( "SET FOREIGN_KEY_CHECKS = 0;" );
$w ( "" );

foreach ( $pdo->query ( "SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'" )->fetchAll ( PDO::FETCH_COLUMN ) as $table ) {
	$create = $pdo->query ( "SHOW CREATE TABLE `" . $table . "`" )->fetch ( PDO::FETCH_NUM ) [1];
	$w ( "DROP TABLE IF EXISTS `" . $table . "`;" );
	$w ( preg_replace ( '/ AUTO_INCREMENT=\d+/', "", $create ) . ";" );
	$w ( "" );
}

// Sem DEFINER: carrega com qualquer usuário do MySQL
$noDefiner = function ($sql) {
	return preg_replace ( '/\s*DEFINER=`[^`]*`@`[^`]*`/', "", $sql );
};
$w ( "DELIMITER ;;" );
foreach ( $pdo->query ( "SHOW PROCEDURE STATUS WHERE Db = DATABASE()" )->fetchAll ( PDO::FETCH_ASSOC ) as $proc ) {
	$w ( "DROP PROCEDURE IF EXISTS `" . $proc ["Name"] . "`;;" );
	$w ( $noDefiner ( $pdo->query ( "SHOW CREATE PROCEDURE `" . $proc ["Name"] . "`" )->fetch ( PDO::FETCH_ASSOC ) ["Create Procedure"] ) . ";;" );
}
foreach ( $pdo->query ( "SHOW TRIGGERS" )->fetchAll ( PDO::FETCH_ASSOC ) as $trigger )
	$w ( $noDefiner ( $pdo->query ( "SHOW CREATE TRIGGER `" . $trigger ["Trigger"] . "`" )->fetch ( PDO::FETCH_ASSOC ) ["SQL Original Statement"] ) . ";;" );
$w ( "DELIMITER ;" );
$w ( "" );

// Dados de referência copiados do banco atual
foreach ( array ( "profile", "vehicle_type", "payment_method" ) as $table )
	$rows [$table] = $pdo->query ( "SELECT * FROM `" . $table . "` ORDER BY 1" )->fetchAll ( PDO::FETCH_ASSOC );

// Endereços antes dos veículos: o gatilho do mapa lê o endereço de cada veículo inserido
$order = array ( "profile", "vehicle_type", "payment_method", "phone", "address", "person", "person_individual", "person_entity", "user", "user_profile", "client",
		"account", "installment", "vehicle_traveling", "vehicle_contact", "vehicle_traveling_user", "messenger_contact", "log_sms", "cep_cache" );
foreach ( $order as $table ) {
	if (empty ( $rows [$table] ))
		continue;
	$w ( "-- " . $table . " (" . count ( $rows [$table] ) . ")" );
	$keys = array_keys ( $rows [$table] [0] );
	$columns = "`" . implode ( "`, `", $keys ) . "`";
	foreach ( array_chunk ( $rows [$table], 100 ) as $chunk ) {
		$values = array ();
		foreach ( $chunk as $row ) {
			if (array_keys ( $row ) !== $keys)
				throw new Exception ( "Colunas fora de ordem em " . $table . ": " . implode ( ",", array_keys ( $row ) ) );
			$values [] = "(" . implode ( ", ", array_map ( $value, array_values ( $row ) ) ) . ")";
		}
		$w ( "INSERT INTO `" . $table . "` (" . $columns . ") VALUES\n" . implode ( ",\n", $values ) . ";" );
	}
	$w ( "" );
}
$w ( "SET FOREIGN_KEY_CHECKS = 1;" );
fclose ( $out );

$active = count ( array_filter ( $rows ["vehicle_traveling"], function ($v) {
	return in_array ( $v ["status"], array ( "NONE", "ONLY_VIEW" ) );
} ) );
printf ( "demo/kade_demo.sql: %d veículos (%d ativos), %d assinantes, %d parcelas, %d mensagens no log, %d cidades\n", count ( $rows ["vehicle_traveling"] ), $active,
		count ( $clients ), count ( $rows ["installment"] ), count ( $rows ["log_sms"] ), count ( $cities ) );
