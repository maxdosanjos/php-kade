<?php
namespace Kade\Tests\Unit;

use PHPUnit\Framework\TestCase;

/** Webhook do WhatsApp: verificação, assinatura, leitura das mensagens e envio da resposta */
class WhatsAppTest extends TestCase
{
	const SECRET = 'segredo-do-app-meta';

	private function whatsApp(array $config = array())
	{
		return new \WhatsApp($config + array('app_secret' => self::SECRET, 'verify_token' => 'meu-verify-token'));
	}

	public function testVerificationChallenge()
	{
		$wa = $this->whatsApp();
		$query = array('hub_mode' => 'subscribe', 'hub_verify_token' => 'meu-verify-token', 'hub_challenge' => '1158201444');
		$this->assertSame('1158201444', $wa->verifyChallenge($query));
		$this->assertNull($wa->verifyChallenge(array('hub_verify_token' => 'errado') + $query));
		$this->assertNull($wa->verifyChallenge(array('hub_mode' => 'unsubscribe') + $query));
		$this->assertNull($wa->verifyChallenge(array('hub_challenge' => '<script>') + $query), 'O challenge volta na resposta: só caracteres seguros');
		$this->assertNull($this->whatsApp(array('verify_token' => ''))->verifyChallenge($query), 'Sem WHATSAPP_VERIFY_TOKEN ninguém verifica');
	}

	public function testSignature()
	{
		$body = '{"object":"whatsapp_business_account"}';
		$signature = 'sha256=' . hash_hmac('sha256', $body, self::SECRET);
		$this->assertTrue($this->whatsApp()->isSigned($body, $signature));
		$this->assertFalse($this->whatsApp()->isSigned($body . ' ', $signature), 'Corpo alterado');
		$this->assertFalse($this->whatsApp()->isSigned($body, 'sha256=' . hash_hmac('sha256', $body, 'outro-segredo')));
		$this->assertFalse($this->whatsApp()->isSigned($body, ''));
		$this->assertFalse($this->whatsApp(array('app_secret' => ''))->isSigned($body, 'sha256=' . hash_hmac('sha256', $body, '')), 'Sem segredo nada passa');
		$this->assertFalse($this->whatsApp(array('app_secret' => ''))->isConfigured());
	}

	private static function payload(array $messages, $phoneNumberId = '123', array $extra = array())
	{
		return json_encode(array('object' => 'whatsapp_business_account', 'entry' => array(array('id' => '1', 'changes' => array(
			array('field' => 'messages', 'value' => array('messaging_product' => 'whatsapp', 'metadata' => array('phone_number_id' => $phoneNumberId), 'messages' => $messages) + $extra),
		)))));
	}

	public function testParseMessages()
	{
		$raw = self::payload(array(
			array('from' => '554399990003', 'id' => 'wamid.A', 'type' => 'text', 'text' => array('body' => ' 6*83203010 ')),
			array('from' => '554399990003', 'id' => 'wamid.B', 'type' => 'image', 'image' => array('id' => 'x')),
			array('from' => '554399990003', 'id' => 'wamid.C', 'type' => 'reaction', 'reaction' => array('emoji' => '👍')),
		));
		$this->assertSame(array(
			array('id' => 'wamid.A', 'from' => '554399990003', 'type' => 'text', 'text' => '6*83203010'),
			array('id' => 'wamid.B', 'from' => '554399990003', 'type' => 'image', 'text' => ''),
		), \WhatsApp::parseMessages($raw, '123'));

		$this->assertSame(array(), \WhatsApp::parseMessages($raw, '999'), 'Mensagem para outro número do app');
		$this->assertCount(2, \WhatsApp::parseMessages($raw), 'Sem WHATSAPP_PHONE_NUMBER_ID, vale qualquer número');
		$this->assertSame(array(), \WhatsApp::parseMessages(self::payload(array(), '123', array('statuses' => array(array('id' => 'wamid.A', 'status' => 'read'))))), 'Confirmação de leitura não é mensagem');
		$this->assertSame(array(), \WhatsApp::parseMessages('{"object":"page"}'));
		$this->assertSame(array(), \WhatsApp::parseMessages('não é json'));
	}

	public function testSendPostsTheTextToTheGraphApi()
	{
		$wa = new FakeWhatsApp(array('token' => 'TOKEN', 'phone_number_id' => '123', 'api_version' => 'v23.0'));
		$this->assertSame('', $wa->send('554399990003', 'Olá ✅'));
		$this->assertSame('https://graph.facebook.com/v23.0/123/messages', $wa->sent['url']);
		$this->assertContains('Authorization: Bearer TOKEN', $wa->sent['headers']);
		$payload = json_decode($wa->sent['payload'], true);
		$this->assertSame('whatsapp', $payload['messaging_product']);
		$this->assertSame('554399990003', $payload['to']);
		$this->assertSame('Olá ✅', $payload['text']['body']);

		$wa->response = array(401, '{"error":{"message":"Error validating access token"}}');
		$this->assertSame('HTTP 401: Error validating access token', $wa->send('554399990003', 'x'));
	}

	public function testSendWithoutTokenDoesNotCallTheApi()
	{
		$wa = new FakeWhatsApp(array('phone_number_id' => '123'));
		$this->assertStringContainsString('WHATSAPP_TOKEN', $wa->send('554399990003', 'x'));
		$this->assertNull($wa->sent);
	}
}

class FakeWhatsApp extends \WhatsApp
{
	public $sent = null;
	public $response = array(200, '{"messages":[{"id":"wamid.X"}]}');

	protected function httpPost($url, $payload, array $headers)
	{
		$this->sent = array('url' => $url, 'payload' => $payload, 'headers' => $headers);
		return $this->response;
	}
}
