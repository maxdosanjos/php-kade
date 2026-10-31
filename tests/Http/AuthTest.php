<?php
namespace Kade\Tests\Http;

/**
 * Login, sessão e saída.
 */
class AuthTest extends HttpTestCase
{
	public static function tearDownAfterClass(): void
	{
		// As senhas erradas destes testes não podem acumular e bloquear as contas de teste
		\Kade\Tests\Support\Db::exec("DELETE FROM login_attempt WHERE login IN (?, ?) OR login LIKE 'teste_bloqueio_%' OR login LIKE 'nao_existe_%' OR login LIKE 'admin%'",
			array(getenv('KADE_TEST_USER_LOGIN'), getenv('KADE_TEST_ADMIN_LOGIN')));
	}

	public function testRepeatedFailuresBlockTheLogin()
	{
		$login = 'teste_bloqueio_' . mt_rand();
		$http = $this->anonymous();
		for ($i = 0; $i < \UserDAO::LOGIN_MAX_FAILURES; $i++)
			$http->post('/LoginQueryCtrl.ctrlExt/onLogin', array('user_login' => $login, 'user_password' => 'errada' . $i));
		$this->assertStringContainsString('Usuário ou senha inválidos', $http->get('/login/')->body);

		$http->post('/LoginQueryCtrl.ctrlExt/onLogin', array('user_login' => $login, 'user_password' => 'mais uma'));
		$this->assertStringContainsString('Muitas tentativas com este login', $http->get('/login/')->body);
	}

	public function testBlockedLoginRefusesEvenTheRightPasswordUntilItExpires()
	{
		$login = getenv('KADE_TEST_USER_LOGIN');
		$db = \Kade\Tests\Support\Db::pdo();
		$insert = $db->prepare("INSERT INTO login_attempt (login, ip, created_at) VALUES (?, 'teste', ?)");
		try {
			for ($i = 0; $i < \UserDAO::LOGIN_MAX_FAILURES; $i++)
				$insert->execute(array($login, date('Y-m-d H:i:s')));
			$this->assertFalse($this->anonymous()->login($login, getenv('KADE_TEST_USER_PASSWORD')), 'Login bloqueado entrou com a senha certa');

			// Tentativas de mais de 15 minutos atrás não contam
			$db->prepare("UPDATE login_attempt SET created_at = NOW() - INTERVAL 16 MINUTE WHERE login = ?")->execute(array($login));
			$this->assertTrue($this->anonymous()->login($login, getenv('KADE_TEST_USER_PASSWORD')));
			$this->assertSame(0, (int) \Kade\Tests\Support\Db::value("SELECT COUNT(*) FROM login_attempt WHERE login = ?", array($login)), 'Login certo deve zerar as falhas');
		} finally {
			\Kade\Tests\Support\Db::exec("DELETE FROM login_attempt WHERE login = ?", array($login));
		}
	}

	public function testWrongPasswordGoesBackToLoginWithGenericMessage()
	{
		$http = $this->anonymous();
		$response = $http->post('/LoginQueryCtrl.ctrlExt/onLogin', array('user_login' => getenv('KADE_TEST_USER_LOGIN'), 'user_password' => 'senha-errada'));
		$this->assertSame(302, $response->status);
		$this->assertStringContainsString('/login/', $response->header('location'));
		// Mesma mensagem para usuário inexistente e senha errada: não revela quais logins existem
		$this->assertStringContainsString('Usuário ou senha inválidos', $http->get('/login/')->body);
	}

	public function testUnknownUserGetsTheSameMessage()
	{
		$http = $this->anonymous();
		$http->post('/LoginQueryCtrl.ctrlExt/onLogin', array('user_login' => 'nao_existe_' . mt_rand(), 'user_password' => 'x'));
		$this->assertStringContainsString('Usuário ou senha inválidos', $http->get('/login/')->body);
	}

	/** @dataProvider injectionLogins */
	public function testSqlInjectionDoesNotLogIn($login, $password)
	{
		$this->assertFalse($this->anonymous()->login($login, $password));
	}

	public function injectionLogins()
	{
		return array(
			array("' OR '1'='1", "' OR '1'='1"),
			array("admin' -- ", 'x'),
			array("admin'#", 'x'),
			array('admin', "' OR '1'='1"),
		);
	}

	public function testLoginRenewsSessionIdAndSetsSafeCookie()
	{
		$http = $this->anonymous();
		$first = $http->get('/login/');
		$before = $http->sessionId();
		$this->assertNotNull($before, 'Sem cookie de sessão antes do login');

		$cookie = implode("\n", $first->headers('set-cookie'));
		$this->assertMatchesRegularExpression('/PHPSESSID=[^;]+;.*HttpOnly/i', $cookie);
		$this->assertMatchesRegularExpression('/SameSite=Lax/i', $cookie);

		$this->assertTrue($http->login(getenv('KADE_TEST_USER_LOGIN'), getenv('KADE_TEST_USER_PASSWORD')));
		$this->assertNotSame($before, $http->sessionId(), 'O id da sessão deve mudar no login (fixação de sessão)');
	}

	public function testLoggedUserSeesPrivatePageAndLogoutEndsSession()
	{
		$http = $this->anonymous();
		$this->assertTrue($http->login(getenv('KADE_TEST_USER_LOGIN'), getenv('KADE_TEST_USER_PASSWORD')));
		$this->assertStringContainsString('Minhas parcelas', $http->get('/view_parc/')->body);

		$http->get('/LoginQueryCtrl.ctrlExt/onLogof');
		$this->assertSame(401, $http->post('/InstallmentQueryCtrl.ctrlExt/searchArray')->status);
	}

	public function testLoginNeverRedirectsOffSite()
	{
		$http = $this->anonymous();
		$response = $http->post('/LoginQueryCtrl.ctrlExt/onLogin', array(
			'user_login' => getenv('KADE_TEST_USER_LOGIN'), 'user_password' => getenv('KADE_TEST_USER_PASSWORD'),
			'last_page' => 'https://site-malicioso.example/'));
		$this->assertMatchesRegularExpression('#^(/|https?://[^/]*kade[^/]*/)#', $response->header('location'));
	}
}
