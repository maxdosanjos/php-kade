<?php
namespace Kade\Tests\Unit;

use PHPUnit\Framework\TestCase;

/** Webhook do Telegram: segredo, leitura das mensagens e resposta */
class TelegramTest extends TestCase
{
	public function testSecret()
	{
		$telegram = new \Telegram('segredo-telegram');
		$this->assertTrue($telegram->isAllowed('segredo-telegram'));
		$this->assertFalse($telegram->isAllowed('outro'));
		$this->assertFalse($telegram->isAllowed(''));
		$this->assertFalse((new \Telegram(''))->isConfigured());
		$this->assertFalse((new \Telegram(''))->isAllowed(''), 'Sem TELEGRAM_WEBHOOK_SECRET nada passa');
	}

	private static function update(array $message, $key = 'message')
	{
		return json_encode(array('update_id' => 900001, $key => $message + array(
			'message_id' => 1, 'date' => 1,
			'from' => array('id' => 777001, 'is_bot' => false, 'first_name' => 'Motorista'),
			'chat' => array('id' => 777001, 'type' => 'private'),
		)));
	}

	public function testParseText()
	{
		$this->assertSame(array(
			'update_id' => '900001', 'chat_id' => '777001', 'user_id' => '777001',
			'text' => '6*83203010', 'contact_phone' => '', 'contact_user_id' => '',
		), \Telegram::parseUpdate(self::update(array('text' => ' 6*83203010 '))));
	}

	public function testParseSharedContact()
	{
		$update = \Telegram::parseUpdate(self::update(array('contact' => array('phone_number' => '+5543999990003', 'first_name' => 'M', 'user_id' => 777001))));
		$this->assertSame('+5543999990003', $update['contact_phone']);
		$this->assertSame('777001', $update['contact_user_id']);
		$this->assertSame('', $update['text']);
	}

	public function testIgnoresWhatIsNotAPrivateMessage()
	{
		$this->assertNull(\Telegram::parseUpdate(self::update(array('text' => 'oi', 'chat' => array('id' => -100, 'type' => 'group')))), 'Grupo');
		$this->assertNull(\Telegram::parseUpdate(self::update(array('text' => 'oi'), 'edited_message')), 'Mensagem editada');
		$this->assertNull(\Telegram::parseUpdate('{"update_id":1,"my_chat_member":{}}'));
		$this->assertNull(\Telegram::parseUpdate('não é json'));
	}

	public function testReply()
	{
		$this->assertSame(array('method' => 'sendMessage', 'chat_id' => '777001', 'text' => 'Olá'), \Telegram::reply(777001, 'Olá'));
		$reply = \Telegram::reply(777001, 'Olá', \Telegram::sharePhoneKeyboard());
		$this->assertTrue($reply['reply_markup']['keyboard'][0][0]['request_contact']);
		$this->assertSame(array('remove_keyboard' => true), \Telegram::reply(1, 'x', \Telegram::removeKeyboard())['reply_markup']);
	}
}
