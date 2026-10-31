<?php
namespace Kade\Tests\Http;

use Kade\Tests\Support\Db;

/**
 * Ataques comuns contra as telas e ações: injeção de SQL, XSS, upload e CSV.
 */
class SecurityTest extends HttpTestCase
{
	/** @dataProvider orderInjections */
	public function testOrderingCannotInjectSql($column)
	{
		$response = $this->admin()->post('/view_custumers/', array('order_column' => $column, 'order_type' => 'ASC'));
		$this->assertSame(200, $response->status);
		$this->assertStringContainsString('Gerenciar clientes', $response->body);
		$this->assertStringNotContainsString('SQLSTATE', $response->body);
	}

	public function orderInjections()
	{
		return array(
			array('(SELECT SLEEP(5))'),
			array('per.name; DROP TABLE user'),
			array("per.name' --"),
		);
	}

	public function testOrderingInjectionDoesNotDelay()
	{
		$start = microtime(true);
		$this->admin()->post('/view_custumers/', array('order_column' => 'IF(1=1,SLEEP(3),1)', 'order_type' => 'ASC'));
		$this->assertLessThan(2.5, microtime(true) - $start, 'A ordenação executou SQL do usuário');
	}

	public function testSearchFilterIsEscaped()
	{
		$response = $this->admin()->post('/view_custumers/', array('type_person' => 'PF', 'custumer_name' => "x' OR '1'='1"));
		$this->assertSame(200, $response->status);
		$this->assertStringContainsString('0 clientes', $response->body, 'O filtro com aspas trouxe resultados: injeção de SQL');
	}

	public function testPaginationParametersAreSanitized()
	{
		$response = $this->admin()->post('/view_custumers/', array('pag' => '1; DROP TABLE user', 'reg_per_pag' => '-1 UNION SELECT 1'));
		$this->assertSame(200, $response->status);
		$this->assertStringNotContainsString('SQLSTATE', $response->body);
	}

	public function testSmsLogIsEscapedOnAdminPage()
	{
		$payload = '<script>alert("kade-xss")</script>';
		Db::exec("INSERT INTO log_sms (`from`, body, ip_trat, url_request, type_log, log) VALUES (?, ?, ?, ?, 'error', ?)",
			array('<b>x</b>', $payload, '9.9.9.9"><img src=x onerror=alert(1)>', '/x"onmouseover="alert(2)', '[{"message":"&lt;img src=x onerror=alert(3)&gt;"}]'));
		try {
			$body = $this->admin()->get('/view_log_sms/')->body;
			$this->assertStringContainsString(htmlspecialchars($payload, ENT_QUOTES, 'UTF-8'), $body);
			$this->assertStringNotContainsString($payload, $body);
			$this->assertStringNotContainsString('<img src=x', $body);
			$this->assertStringNotContainsString('"onmouseover="', $body);
		} finally {
			Db::exec("DELETE FROM log_sms WHERE ip_trat LIKE '9.9.9.9%'");
		}
	}

	public function testSmsFromUnknownGatewayIsRejectedAndLogged()
	{
		$http = $this->anonymous();
		$response = $http->get('/RegisterVehicleSMS.ctrlExt/043999990000/3*86010000', array('X-Real-IP: 9.9.9.8'));
		$this->assertSame(200, $response->status);
		try {
			$this->assertSame('IP inválido para tratamento de SMS', Db::value("SELECT log FROM log_sms WHERE ip_trat = '9.9.9.8' ORDER BY id DESC LIMIT 1"));
		} finally {
			Db::exec("DELETE FROM log_sms WHERE ip_trat = '9.9.9.8'");
		}
	}

	public function testExpiredResetLinkIsRefused()
	{
		$personId = Db::value("SELECT person_id FROM user WHERE login = ?", array(getenv('KADE_TEST_USER_LOGIN')));
		$hashBefore = Db::value("SELECT password FROM user WHERE person_id = ?", array($personId));
		$token = bin2hex(random_bytes(16));
		Db::exec("INSERT INTO link_confirm (user_person_id, hash, created_at) VALUES (?, ?, NOW() - INTERVAL 25 HOUR)
			ON DUPLICATE KEY UPDATE hash = VALUES(hash), created_at = VALUES(created_at)", array($personId, $token));
		try {
			$page = $this->anonymous()->get('/confirm_new_pwd/vua=' . $token);
			$this->assertStringNotContainsString('custumer_password', $page->body, 'Link vencido abriu o formulário de nova senha');

			$this->anonymous()->post('/LoginQueryCtrl.ctrlExt/confirmNewPwd', array('vua' => $token, 'custumer_password' => 'Invasor@123'));
			$this->assertSame($hashBefore, Db::value("SELECT password FROM user WHERE person_id = ?", array($personId)), 'Link vencido trocou a senha');
		} finally {
			Db::exec("DELETE FROM link_confirm WHERE hash = ?", array($token));
		}
	}

	public function testCsvExportNeutralizesFormulas()
	{
		$csv = $this->admin()->get('/CustumerQueryCtrl.ctrlExt/exportCSV')->body;
		$this->assertStringStartsWith("\xEF\xBB\xBF", $csv, 'CSV sem BOM: o Excel mostra acentos errados');
		foreach (array_filter(preg_split('/\r\n/', substr($csv, 3))) as $line)
			foreach (str_getcsv($line, ';') as $cell)
				$this->assertDoesNotMatchRegularExpression('/^[=+@]/', $cell, "Célula executável no Excel: {$cell}");
	}

	/** @dataProvider badUploads */
	public function testBannerUploadRejectsBadFiles($bannerId, $content, $filename)
	{
		$tmp = tempnam(sys_get_temp_dir(), 'kade_up_');
		file_put_contents($tmp, $content);
		try {
			$response = $this->admin()->post('/MapsExtCtrl.ctrlExt/loadBanner', array(
				'banner_id' => $bannerId,
				'banner_file' => new \CURLFile($tmp, 'image/jpeg', $filename)));
			$this->assertJsonError($response);
		} finally {
			unlink($tmp);
		}
		$this->assertFileDoesNotExist(KADE_ROOT . '/shared/images/' . basename($filename));
	}

	public function badUploads()
	{
		$jpeg = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');
		return array(
			'PHP disfarçado de JPG' => array('hero', "GIF89a<?php echo 'x'; ?>", 'shell.jpg'),
			'extensão .php'         => array('hero', $jpeg, 'shell.php'),
			'caminho no id'         => array('../../shell', $jpeg, 'banner.jpg'),
			'id inexistente'        => array('9', $jpeg, 'banner.jpg'),
		);
	}

	public function testBannerUploadAndRemovalRoundTrip()
	{
		$hero = KADE_ROOT . '/shared/images/hero.jpg';
		if (file_exists($hero))
			$this->markTestSkipped('Já existe uma foto do topo; o teste não a substitui');
		$response = $this->admin()->post('/MapsExtCtrl.ctrlExt/loadBanner', array(
			'banner_id' => 'hero',
			'banner_file' => new \CURLFile(KADE_ROOT . '/shared/images/banner_ori/banner_slider1.jpg', 'image/jpeg', 'foto.jpg')));
		$this->assertSame('sucess', $response->json()['result'] ?? null, $response->body);
		$this->assertFileExists($hero);
		$this->assertStringContainsString('shared/images/hero.jpg', $this->anonymous()->get('/')->body, 'A home não usou a foto enviada');

		$response = $this->admin()->post('/MapsExtCtrl.ctrlExt/removeBanner', array('banner_id' => 'hero'));
		$this->assertSame('sucess', $response->json()['result'] ?? null);
		clearstatcache();
		$this->assertFileDoesNotExist($hero);
	}
}
