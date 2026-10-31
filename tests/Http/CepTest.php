<?php
namespace Kade\Tests\Http;

use Kade\Tests\Support\Db;

/**
 * Busca de CEP pelo site. Usa um CEP fictício gravado no cache: não depende de internet.
 */
class CepTest extends HttpTestCase
{
	const CEP = '99999001';

	protected function setUp(): void
	{
		Db::exec("INSERT INTO cep_cache (cep, street, neighborhood, city, state, source, updated_at)
			VALUES (?, 'Rua do Teste', 'Centro', 'Cidade Teste KADE', 'PR', 'teste', NOW())
			ON DUPLICATE KEY UPDATE city = VALUES(city), state = VALUES(state), updated_at = NOW()", array(self::CEP));
	}

	protected function tearDown(): void
	{
		Db::exec("DELETE FROM cep_cache WHERE cep = ?", array(self::CEP));
		Db::exec("DELETE FROM log_sms WHERE body LIKE '%*00000001'");
	}

	private function search($cep)
	{
		return $this->anonymous()->post('/AddressRegCtrl.ctrlExt/searchCEP', array('zipcode' => $cep))->json();
	}

	public function testCepFromCacheFillsTheAddress()
	{
		$json = $this->search('99999-001');
		$this->assertSame(1, $json['result'] ?? null, json_encode($json));
		$this->assertSame('Cidade Teste KADE', $json['data']['city']);
		$this->assertSame('PR', $json['data']['UF']);
		$this->assertSame('Paraná', $json['data']['UF_NM']);
		$this->assertSame(self::CEP, $json['data']['zipcode']);
	}

	public function testInvalidCep()
	{
		$json = $this->search('123');
		$this->assertSame(0, $json['result']);
		$this->assertSame('CEP inválido.', $json['message']);
	}

	public function testSmsWithUnknownCepIsRefusedAndNothingIsSaved()
	{
		// Antes o veículo era gravado sem cidade e sem UF (e não aparecia no mapa)
		$gateway = \Properties::getGroup('server_sms')['http_x_real_ip'];
		$before = Db::value("SELECT COUNT(*) FROM vehicle_traveling");
		$this->anonymous()->get('/RegisterVehicleSMS.ctrlExt/043999990000/3*00000001', array('X-Real-IP: ' . $gateway));
		$this->assertSame($before, Db::value("SELECT COUNT(*) FROM vehicle_traveling"), 'Veículo gravado com CEP não encontrado');
		$this->assertStringContainsString('CEP não encontrado', (string) Db::value("SELECT log FROM log_sms WHERE body = '3*00000001' ORDER BY id DESC LIMIT 1"));
	}
}
