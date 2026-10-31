<?php
namespace Kade\Tests\Http;

/**
 * Rotas, códigos de status e o que nunca deve ser servido.
 */
class RoutesTest extends HttpTestCase
{
	/** @dataProvider publicPages */
	public function testPublicPageRenders($path, $expectedText)
	{
		$response = $this->anonymous()->get($path);
		$this->assertSame(200, $response->status, $path);
		$this->assertStringContainsString('text/html; charset=UTF-8', $response->header('content-type'));
		$this->assertStringContainsString($expectedText, $response->body, $path);
		$this->assertStringContainsString('shared/css/app.css', $response->body, 'Página sem o CSS novo');
		$this->assertDoesNotMatchRegularExpression('/<b>(Warning|Notice|Fatal error|Deprecated)<\/b>/', $response->body, "Erro do PHP impresso em {$path}");
	}

	public function publicPages()
	{
		return array(
			'home'            => array('/', 'Veículos disponíveis por estado'),
			'quem somos'      => array('/quem_somos/', 'Quem somos'),
			'termos'          => array('/terms_use/', 'Termos de uso e condições gerais'),
			'login'           => array('/login/', 'user_password'),
			'nova senha'      => array('/request_new_password/', 'newpwd_mail'),
			'cadastro'        => array('/cad_assinante/', 'custumer_captcha'),
			'cadastro veículo'=> array('/cad_veiculo/', 'vehicle_zipcode'),
		);
	}

	/** @dataProvider unknownPaths */
	public function testUnknownAddressesAre404($path)
	{
		$response = $this->anonymous()->get($path);
		$this->assertSame(404, $response->status, $path);
		$this->assertStringContainsString('Página não encontrada', $response->body);
	}

	public function unknownPaths()
	{
		return array(
			'página inexistente' => array('/pagina_que_nao_existe/'),
			'vários níveis'      => array('/a/b/c/d'),
			'imagem ausente'     => array('/shared/images/nao_existe.png'),
			'php ausente'        => array('/nao_existe.php'),
		);
	}

	/** @dataProvider privatePaths */
	public function testPrivateFilesAreNeverServed($path)
	{
		$response = $this->anonymous()->get($path);
		$this->assertContains($response->status, array(403, 404), "{$path} respondeu {$response->status}");
		$this->assertStringNotContainsString('Fatal error', $response->body);
		$this->assertStringNotContainsString('CREATE TABLE', $response->body);
	}

	public function privatePaths()
	{
		return array(
			'dump do banco'      => array('/der/kade_full_import.sql'),
			'migração'           => array('/der/migrations/002_senhas_bcrypt.sql'),
			'view direta'        => array('/view/maps.php'),
			'parcial direto'     => array('/view/partials/header.php'),
			'modelo de e-mail'   => array('/view/model_mail_cad_cli.php'),
			'caminho codificado' => array('/%76iew/partials/header.php'),
			'classe'             => array('/shared/class/util/Properties.class.php'),
			'fonte do CSS'       => array('/shared/css/src/app.css'),
			'.env'               => array('/.env'),
			'.htaccess'          => array('/.htaccess'),
		);
	}

	/** @dataProvider headerPaths */
	public function testSecurityHeaders($path)
	{
		$response = $this->anonymous()->get($path);
		$this->assertSame('SAMEORIGIN', $response->header('x-frame-options'), "{$path}: pode ser embutido em iframe de outro site");
		$this->assertSame('nosniff', $response->header('x-content-type-options'), $path);
		$this->assertSame('strict-origin-when-cross-origin', $response->header('referrer-policy'), $path);
		$this->assertSame('', $response->header('x-powered-by'), "{$path}: anuncia a versão do PHP");
	}

	public function headerPaths()
	{
		return array(array('/login/'), array('/'), array('/MapsExtCtrl.ctrlExt/searchMaps'), array('/nao_existe.php'));
	}

	public function testStaticAssetsAreServed()
	{
		$http = $this->anonymous();
		foreach (array('/shared/css/app.css', '/shared/js/System.class.js', '/shared/images/logo.png', '/shared/images/favicon.ico') as $path)
			$this->assertSame(200, $http->get($path)->status, $path);
	}

	public function testMaintenancePage()
	{
		$response = $this->anonymous()->get('/manutencao.html');
		$this->assertSame(200, $response->status);
		$this->assertStringContainsString('Estamos em manutenção', $response->body);
	}

	public function testAdminPagesFallBackToHomeForVisitors()
	{
		// Páginas do administrador não aparecem para quem não é administrador
		foreach (array('/view_custumers/', '/view_log_sms/', '/load_banner/', '/gnrt_parc/') as $path) {
			$body = $this->anonymous()->get($path)->body;
			$this->assertStringNotContainsString('Imagens da home', $body, $path);
			$this->assertStringNotContainsString('Log de SMS</h1>', $body, $path);
		}
	}
}
