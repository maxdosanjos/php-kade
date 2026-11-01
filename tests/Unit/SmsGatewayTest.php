<?php
namespace Kade\Tests\Unit;

use PHPUnit\Framework\TestCase;

/** Quem pode chamar a rota pública do SMS */
class SmsGatewayTest extends TestCase
{
	const GATEWAY = '200.195.184.186';
	const TOKEN = 'segredo-do-gateway-123';

	/** @dataProvider calls */
	public function testIsAllowed($server, $token, $ips, $secret, $expected)
	{
		$this->assertSame($expected, \SmsGateway::isAllowed($server, $token, $ips, $secret));
	}

	public function calls()
	{
		$proxy = array('REMOTE_ADDR' => '127.0.0.1', 'HTTP_X_REAL_IP' => self::GATEWAY);
		$direct = array('REMOTE_ADDR' => self::GATEWAY);
		$other = array('REMOTE_ADDR' => '203.0.113.9');
		return array(
			// Só IP (como era antes)
			'IP pelo proxy'                     => array($proxy, '', self::GATEWAY, '', true),
			'IP da conexão direta'              => array($direct, '', self::GATEWAY, '', true),
			'outro IP'                          => array($other, '', self::GATEWAY, '', false),
			'um dos IPs da lista'               => array($direct, '', '10.0.0.1, ' . self::GATEWAY, '', true),
			// IP + segredo
			'IP certo, segredo certo'           => array($proxy, self::TOKEN, self::GATEWAY, self::TOKEN, true),
			'IP certo, sem o segredo'           => array($proxy, '', self::GATEWAY, self::TOKEN, false),
			'IP certo, segredo errado'          => array($proxy, 'segredo-errado-0000000', self::GATEWAY, self::TOKEN, false),
			'X-Real-IP forjado, sem o segredo'  => array(array('REMOTE_ADDR' => '203.0.113.9', 'HTTP_X_REAL_IP' => self::GATEWAY), '', self::GATEWAY, self::TOKEN, false),
			'segredo certo, outro IP'           => array($other, self::TOKEN, self::GATEWAY, self::TOKEN, false),
			// Só segredo (SMS_GATEWAY_IP vazio)
			'só segredo, certo'                 => array($other, self::TOKEN, '', self::TOKEN, true),
			'só segredo, errado'                => array($other, 'x', '', self::TOKEN, false),
			// Nada configurado: ninguém passa
			'nada configurado'                  => array($direct, '', '', '', false),
		);
	}

	public function testTokenIsMaskedInTheLoggedUrl()
	{
		$this->assertSame(
			'/RegisterVehicleSMS.ctrlExt/***/5543999990000/3*86010000',
			\SmsGateway::maskToken('/RegisterVehicleSMS.ctrlExt/' . self::TOKEN . '/5543999990000/3*86010000')
		);
		// Sem segredo na URL, nada muda
		$url = '/RegisterVehicleSMS.ctrlExt/5543999990000/3*86010000';
		$this->assertSame($url, \SmsGateway::maskToken($url));
	}
}
