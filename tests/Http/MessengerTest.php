<?php
namespace Kade\Tests\Http;

use Kade\Tests\Support\Db;

/**
 * Webhooks do WhatsApp e do Telegram chamados como a Meta e o Telegram chamam.
 * Precisa de WHATSAPP_APP_SECRET, WHATSAPP_VERIFY_TOKEN e TELEGRAM_WEBHOOK_SECRET no .env
 * (sem WHATSAPP_TOKEN nenhuma resposta sai para a Meta). Usa um CEP fictício do cache
 * e apaga os veículos que cria.
 */
class MessengerTest extends HttpTestCase
{
	const CEP = '99999002';
	const CITY = 'Cidade Teste KADE';
	const TG_USER = '777000123';

	protected function setUp(): void
	{
		foreach (array('whatsapp.app_secret', 'whatsapp.verify_token', 'telegram.secret') as $key)
			if ((string) \Properties::get($key) === '')
				$this->markTestSkipped('Defina WHATSAPP_APP_SECRET, WHATSAPP_VERIFY_TOKEN e TELEGRAM_WEBHOOK_SECRET no .env');
		Db::exec("INSERT INTO cep_cache (cep, street, neighborhood, city, state, source, updated_at)
			VALUES (?, 'Rua do Teste', 'Centro', ?, 'PR', 'teste', NOW())
			ON DUPLICATE KEY UPDATE city = VALUES(city), state = VALUES(state), updated_at = NOW()", array(self::CEP, self::CITY));
	}

	protected function tearDown(): void
	{
		$this->deleteTestVehicles();
		Db::exec("DELETE FROM cep_cache WHERE cep = ?", array(self::CEP));
		Db::exec("DELETE FROM log_sms WHERE channel <> 'SMS' AND (msg_id LIKE 'teste.%' OR msg_id LIKE '9000%' OR msg_id IS NULL)");
		Db::exec("DELETE FROM messenger_contact WHERE channel = 'TELEGRAM' AND external_id = ?", array(self::TG_USER));
	}

	/** Os veículos de teste ficam no CEP fictício; o gatilho tira do mapa ao cancelar */
	private function deleteTestVehicles()
	{
		$rows = Db::pdo()->query("SELECT vt.id, vt.address_id, vc.person_id, vc.phone_contact_id, p.phone_id, p.address_id AS person_address
			FROM vehicle_traveling vt JOIN address a ON a.id = vt.address_id
			LEFT JOIN vehicle_contact vc ON vc.vehicle_traveling_id = vt.id LEFT JOIN person p ON p.id = vc.person_id
			WHERE a.cep = " . (int) self::CEP)->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($rows as $row) {
			Db::exec("UPDATE vehicle_traveling SET status = 'CANCEL' WHERE id = ?", array($row['id']));
			Db::exec("DELETE FROM vehicle_contact WHERE vehicle_traveling_id = ?", array($row['id']));
			Db::exec("DELETE FROM vehicle_traveling WHERE id = ?", array($row['id']));
			Db::exec("DELETE FROM person WHERE id = ?", array($row['person_id']));
			Db::exec("DELETE FROM phone WHERE id IN (?, ?)", array($row['phone_contact_id'], $row['phone_id']));
			Db::exec("DELETE FROM address WHERE id IN (?, ?)", array($row['address_id'], $row['person_address']));
		}
		Db::exec("DELETE FROM idx_traveling_city WHERE city = ?", array(self::CITY));
	}

	private function testVehicles()
	{
		$stmt = Db::pdo()->prepare("SELECT vt.source, ph.ddd, ph.phone FROM vehicle_traveling vt JOIN address a ON a.id = vt.address_id
			JOIN vehicle_contact vc ON vc.vehicle_traveling_id = vt.id JOIN phone ph ON ph.id = vc.phone_contact_id WHERE a.cep = ? ORDER BY vt.id");
		$stmt->execute(array((int) self::CEP));
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

	private function lastLog($channel)
	{
		$stmt = Db::pdo()->prepare("SELECT * FROM log_sms WHERE channel = ? ORDER BY id DESC LIMIT 1");
		$stmt->execute(array($channel));
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

	// ---- WhatsApp ----

	private function whatsApp($text, $id, $from = '554399990004', $signature = null)
	{
		$body = json_encode(array('object' => 'whatsapp_business_account', 'entry' => array(array('id' => '1', 'changes' => array(array(
			'field' => 'messages',
			'value' => array(
				'messaging_product' => 'whatsapp',
				'metadata' => array('phone_number_id' => \Properties::get('whatsapp.phone_number_id') ?: '123'),
				'messages' => array(array('from' => $from, 'id' => $id, 'timestamp' => '1', 'type' => 'text', 'text' => array('body' => $text))),
			),
		))))));
		$signature = $signature ?? 'sha256=' . hash_hmac('sha256', $body, \Properties::get('whatsapp.app_secret'));
		return $this->anonymous()->post('/webhook/whatsapp', $body, array('Content-Type: application/json', 'X-Hub-Signature-256: ' . $signature));
	}

	private function whatsAppReply($response)
	{
		$this->assertSame(200, $response->status, $response->body);
		return $response->json()['replies'][0]['text'] ?? null;
	}

	public function testWhatsAppWebhookVerification()
	{
		$token = urlencode(\Properties::get('whatsapp.verify_token'));
		$ok = $this->anonymous()->get('/webhook/whatsapp?hub.mode=subscribe&hub.verify_token=' . $token . '&hub.challenge=1158201444');
		$this->assertSame(200, $ok->status);
		$this->assertSame('1158201444', $ok->body);

		$this->assertSame(403, $this->anonymous()->get('/webhook/whatsapp?hub.mode=subscribe&hub.verify_token=errado&hub.challenge=1')->status);
	}

	public function testWhatsAppWithoutValidSignatureIsRefusedAndLogged()
	{
		$before = Db::value("SELECT COUNT(*) FROM vehicle_traveling");
		$response = $this->whatsApp('6*' . self::CEP, 'teste.forjada', '554399990004', 'sha256=' . str_repeat('0', 64));
		$this->assertSame(403, $response->status);
		$this->assertSame($before, Db::value("SELECT COUNT(*) FROM vehicle_traveling"));
		$this->assertStringContainsString('assinatura inválida', $this->lastLog('WHATSAPP')['log']);
	}

	public function testWhatsAppRegistersOnceAndReplies()
	{
		// Número sem o 9 da frente, como o WhatsApp entrega alguns celulares antigos
		$reply = $this->whatsAppReply($this->whatsApp('6*' . substr(self::CEP, 0, 5) . '-' . substr(self::CEP, 5), 'teste.registro'));
		$this->assertStringContainsString('Veículo anunciado', $reply);
		$this->assertStringContainsString('Local: ' . self::CITY . '/PR', $reply);
		$this->assertSame(array(array('source' => 'WHATSAPP', 'ddd' => '43', 'phone' => '999990004')), $this->testVehicles(), 'Veículo gravado com o 9 recolocado');

		$log = $this->lastLog('WHATSAPP');
		$this->assertSame('sucess', $log['type_log']);
		$this->assertSame('554399990004', $log['from']);
		$this->assertStringContainsString('Resposta não enviada', $log['log'], 'Sem WHATSAPP_TOKEN, o log avisa que a resposta não saiu');

		// A Meta reenvia a mesma mensagem quando a resposta demora: nada é gravado de novo
		$again = $this->whatsApp('6*' . self::CEP, 'teste.registro');
		$this->assertSame(array(), $again->json()['replies']);
		$this->assertCount(1, $this->testVehicles());
	}

	public function testWhatsAppRefusalExplainsWhy()
	{
		$reply = $this->whatsAppReply($this->whatsApp('6*00000002', 'teste.cep'));
		$this->assertStringContainsString('Não foi possível anunciar: CEP não encontrado', $reply);
		$this->assertSame('error', $this->lastLog('WHATSAPP')['type_log']);

		$reply = $this->whatsAppReply($this->whatsApp('999*' . self::CEP, 'teste.tipo'));
		$this->assertStringContainsString('Tipo de Veículo 999 inválido', $reply);
		$this->assertSame(array(), $this->testVehicles());
	}

	public function testWhatsAppHelpAndTypes()
	{
		$this->assertStringContainsString('Exemplo: 6*83203010', $this->whatsAppReply($this->whatsApp('bom dia', 'teste.oi')));
		$this->assertSame('info', $this->lastLog('WHATSAPP')['type_log']);
		$this->assertStringContainsString('6 - Carreta LS (Graneleira)', $this->whatsAppReply($this->whatsApp('TIPOS', 'teste.tipos')));
	}

	public function testWhatsAppFromAbroadIsNotRegistered()
	{
		$reply = $this->whatsAppReply($this->whatsApp('6*' . self::CEP, 'teste.exterior', '14155550100'));
		$this->assertStringContainsString('números do Brasil', $reply);
		$this->assertSame(array(), $this->testVehicles());
	}

	// ---- Telegram ----

	private function telegram(array $message, $updateId, $secret = null)
	{
		$body = json_encode(array('update_id' => $updateId, 'message' => $message + array(
			'message_id' => 1, 'date' => 1,
			'from' => array('id' => (int) self::TG_USER, 'is_bot' => false, 'first_name' => 'Motorista'),
			'chat' => array('id' => (int) self::TG_USER, 'type' => 'private'),
		)));
		return $this->anonymous()->post('/webhook/telegram', $body, array(
			'Content-Type: application/json',
			'X-Telegram-Bot-Api-Secret-Token: ' . ($secret ?? \Properties::get('telegram.secret')),
		));
	}

	public function testTelegramWithoutSecretIsRefused()
	{
		$this->assertSame(403, $this->telegram(array('text' => '6*' . self::CEP), 900001, 'errado')->status);
		$this->assertStringContainsString('segredo inválido', $this->lastLog('TELEGRAM')['log']);
	}

	public function testTelegramAsksForThePhoneThenRegisters()
	{
		// Sem número vinculado: pede o número com o botão, mesmo que a mensagem esteja certa
		$first = $this->telegram(array('text' => '6*' . self::CEP), 900002)->json();
		$this->assertSame('sendMessage', $first['method']);
		$this->assertSame(self::TG_USER, $first['chat_id']);
		$this->assertTrue($first['reply_markup']['keyboard'][0][0]['request_contact']);
		$this->assertSame(array(), $this->testVehicles());

		// Contato de outra pessoa (encaminhado) não vincula
		$forged = $this->telegram(array('contact' => array('phone_number' => '5543988887777', 'first_name' => 'Outro', 'user_id' => 1)), 900003)->json();
		$this->assertStringContainsString('seu próprio número', $forged['text']);
		$this->assertSame(false, Db::value("SELECT phone FROM messenger_contact WHERE channel = 'TELEGRAM' AND external_id = ?", array(self::TG_USER)));

		// O próprio contato vincula
		$linked = $this->telegram(array('contact' => array('phone_number' => '+5543999990005', 'first_name' => 'Motorista', 'user_id' => (int) self::TG_USER)), 900004)->json();
		$this->assertStringContainsString('Número confirmado: (43) 99999-0005', $linked['text']);
		$this->assertTrue($linked['reply_markup']['remove_keyboard']);

		$registered = $this->telegram(array('text' => '6*' . self::CEP), 900005)->json();
		$this->assertStringContainsString('Veículo anunciado', $registered['text']);
		$this->assertSame(array(array('source' => 'TELEGRAM', 'ddd' => '43', 'phone' => '999990005')), $this->testVehicles());
		$this->assertSame('5543999990005', $this->lastLog('TELEGRAM')['from']);

		// Atualização repetida: responde vazio e não grava de novo
		$again = $this->telegram(array('text' => '6*' . self::CEP), 900005);
		$this->assertSame(200, $again->status);
		$this->assertSame('', $again->body);
		$this->assertCount(1, $this->testVehicles());
	}

	public function testTelegramGroupMessagesAreIgnored()
	{
		$response = $this->telegram(array('text' => 'oi', 'chat' => array('id' => -1001, 'type' => 'group')), 900006);
		$this->assertSame(200, $response->status);
		$this->assertSame('', $response->body);
	}

	public function testAdminSeesTheChannelInTheMessageLog()
	{
		$this->whatsApp('bom dia', 'teste.log');
		$page = $this->admin()->post('/view_log_sms/', array('channel' => 'WHATSAPP'))->body;
		$this->assertStringContainsString('Log de mensagens', $page);
		$this->assertStringContainsString('554399990004', $page);
		$this->assertStringContainsString('<td class="k:whitespace-nowrap">WhatsApp</td>', $page);
	}
}
