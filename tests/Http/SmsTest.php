<?php
namespace Kade\Tests\Http;

use Kade\Tests\Support\Db;

/**
 * Rota pública do SMS: só o gateway grava veículo, e o segredo da URL (SMS_TOKEN)
 * não aparece no log que o admin vê.
 */
class SmsTest extends HttpTestCase
{
	const TOKEN = 'teste-segredo-sms-0123456789';

	protected function tearDown(): void
	{
		Db::exec("DELETE FROM log_sms WHERE body IN ('3*00000002', '3*86010000') AND `from` = '043999990002'");
	}

	private function lastLog($body)
	{
		$stmt = Db::pdo()->prepare("SELECT url_request, log FROM log_sms WHERE body = ? AND `from` = '043999990002' ORDER BY id DESC LIMIT 1");
		$stmt->execute(array($body));
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

	public function testWithoutTheGatewayIpNothingIsSaved()
	{
		$before = Db::value("SELECT COUNT(*) FROM vehicle_traveling");
		$this->anonymous()->get('/RegisterVehicleSMS.ctrlExt/043999990002/3*86010000');
		$this->assertSame($before, Db::value("SELECT COUNT(*) FROM vehicle_traveling"), 'Veículo gravado sem vir do gateway');
		$this->assertStringContainsString('Chamada de SMS não autorizada', (string) ($this->lastLog('3*86010000')['log'] ?? ''));
	}

	public function testUrlWithTokenReachesTheSmsRouteAndTheLogHidesIt()
	{
		// CEP inexistente: a rota é atendida, mas nenhum veículo é gravado
		$gateway = \Properties::getGroup('server_sms')['http_x_real_ip'];
		$this->anonymous()->get('/RegisterVehicleSMS.ctrlExt/' . self::TOKEN . '/043999990002/3*00000002', array('X-Real-IP: ' . $gateway));
		$log = $this->lastLog('3*00000002');
		$this->assertNotFalse($log, 'A rota com o segredo não chegou ao tratamento do SMS');
		$this->assertStringContainsString('/RegisterVehicleSMS.ctrlExt/***/043999990002/', $log['url_request']);
		$this->assertStringNotContainsString(self::TOKEN, $log['url_request']);
	}
}
