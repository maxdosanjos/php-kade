<?php
namespace Kade\Tests\Unit;

use PHPUnit\Framework\TestCase;

/** O que vale para WhatsApp e Telegram: mensagem TIPO*CEP, telefone e respostas */
class MessengerTest extends TestCase
{
	/** @dataProvider registrations */
	public function testParseRegistration($text, $expected)
	{
		$this->assertSame($expected, \Messenger::parseRegistration($text));
	}

	public function registrations()
	{
		$ok = array('type' => '6', 'cep' => '83203010');
		return array(
			'como no SMS'             => array('6*83203010', $ok),
			'espaços e CEP com hífen' => array(' 6 * 83203-010 ', $ok),
			'CEP com ponto'           => array('6*83.203-010', $ok),
			'zero à esquerda no tipo' => array('06*83203010', $ok),
			'separado por espaço'     => array('6 83203010', $ok),
			'separado por vírgula'    => array('6,83203010', $ok),
			'separado por #'          => array('6#83203010', $ok),
			'CEP curto'               => array('6*8320301', null),
			'texto junto'             => array('6*83203010 obrigado', null),
			'só o CEP'                => array('83203010', null),
			'texto'                   => array('bom dia', null),
			'vazio'                   => array('', null),
		);
	}

	/** @dataProvider typesRequests */
	public function testTypesRequest($text, $expected)
	{
		$this->assertSame($expected, \Messenger::isTypesRequest($text));
	}

	public function typesRequests()
	{
		return array(
			array('tipos', true), array('TIPOS', true), array('/tipos', true), array(' Tipo ', true),
			array('códigos', true), array('Codigo', true), array('lista', true),
			array('tipos de caminhão', false), array('6*83203010', false), array('', false),
		);
	}

	/** @dataProvider phones */
	public function testBrazilianPhone($number, $expected)
	{
		$this->assertSame($expected, \Messenger::brazilianPhone($number));
	}

	public function phones()
	{
		return array(
			'celular'                       => array('5543999990003', '(43) 999990003'),
			'celular sem o 9 (WhatsApp)'    => array('554399990003', '(43) 999990003'),
			'com + e máscara (Telegram)'    => array('+55 (43) 99999-0003', '(43) 999990003'),
			'fixo'                          => array('554332220000', '(43) 32220000'),
			'fora do Brasil'                => array('14155550100', ''),
			'incompleto'                    => array('5543', ''),
			'DDD inválido'                  => array('5500999990003', ''),
			'9 dígitos sem começar com 9'   => array('5543899990003', ''),
		);
	}

	public function testMessageTextFromValidationJsonAndPlainText()
	{
		$validation = new \Message(json_encode(array(
			array('name' => 'CEP', 'id' => 'vehicle_zipcode', 'message' => htmlentities('CEP não encontrado!')),
			array('name' => 'Tipo', 'id' => 'vehicle_type', 'message' => htmlentities('Tipo de Veículo 99 inválido!')),
		)), \Message::ERR);
		$this->assertSame('CEP não encontrado! Tipo de Veículo 99 inválido!', \Messenger::messageText($validation));

		// Message::setDesc troca quebras de linha por <br />
		$this->assertSame("Registro 10 realizado\ncom sucesso", \Messenger::messageText(new \Message("Registro 10 realizado\ncom sucesso", \Message::SUCCESS)));
	}

	public function testReplies()
	{
		$registered = \Messenger::registered(1010, 'Carreta LS (Graneleira)', 'Paranaguá', 'PR', '83203010');
		$this->assertStringContainsString('Veículo anunciado', $registered);
		$this->assertStringContainsString('Tipo: Carreta LS (Graneleira)', $registered);
		$this->assertStringContainsString('Local: Paranaguá/PR (CEP 83203-010)', $registered);
		$this->assertStringContainsString('Registro nº 1010', $registered);

		$refused = \Messenger::notRegistered('CEP não encontrado!');
		$this->assertStringStartsWith('❌ Não foi possível anunciar: CEP não encontrado!', $refused);
		$this->assertStringContainsString(\Messenger::EXAMPLE, $refused, 'A recusa deve lembrar o formato');

		$type = new \VehicleType();
		$type->setId(6);
		$type->setDescr('Carreta LS (Graneleira)');
		$this->assertStringContainsString("6 - Carreta LS (Graneleira)", \Messenger::typesList(array($type)));
		$this->assertStringContainsString('indisponível', \Messenger::typesList(array()));
	}

	public function testLinksOnlyForConfiguredChannels()
	{
		$saved = array(\Properties::get('whatsapp.number'), \Properties::get('telegram.bot'));
		try {
			\Properties::set('whatsapp.number', '');
			\Properties::set('telegram.bot', '');
			$this->assertSame(array(), \Messenger::links());
			$this->assertSame('', \Messenger::linkNames(\Messenger::links()));

			\Properties::set('whatsapp.number', '+55 (43) 99676-1700');
			$this->assertSame(array('WHATSAPP' => 'https://wa.me/5543996761700'), \Messenger::links());
			$this->assertSame('WhatsApp', \Messenger::linkNames(\Messenger::links()));

			\Properties::set('telegram.bot', '@KadeCaminhoesBot');
			$this->assertSame('https://t.me/KadeCaminhoesBot', \Messenger::links()['TELEGRAM']);
			$this->assertSame('WhatsApp ou Telegram', \Messenger::linkNames(\Messenger::links()));

			// Nome de robô inválido não vira link
			\Properties::set('telegram.bot', 'bot"><script>');
			$this->assertArrayNotHasKey('TELEGRAM', \Messenger::links());
		} finally {
			\Properties::set('whatsapp.number', $saved[0]);
			\Properties::set('telegram.bot', $saved[1]);
		}
	}
}
