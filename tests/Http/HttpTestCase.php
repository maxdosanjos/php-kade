<?php
namespace Kade\Tests\Http;

use Kade\Tests\Support\Http;
use PHPUnit\Framework\TestCase;

/**
 * Base dos testes que chamam o site de verdade (KADE_TEST_URL).
 * Pula tudo se o site não responder; pula o que precisa de login se as
 * contas de teste (KADE_TEST_*_LOGIN) não entrarem.
 */
abstract class HttpTestCase extends TestCase
{
	private static $siteUp = null;
	private static $sessions = array();

	public static function setUpBeforeClass(): void
	{
		if (self::$siteUp === null)
			self::$siteUp = Http::siteIsUp();
		if (!self::$siteUp)
			self::markTestSkipped('Site fora do ar em ' . (getenv('KADE_TEST_URL') ?: 'http://kade.test'));
	}

	protected function anonymous()
	{
		return new Http();
	}

	/** Navegador logado como administrador (uma sessão por execução) */
	protected function admin()
	{
		return $this->loggedAs('ADMIN');
	}

	/** Navegador logado como cliente comum */
	protected function user()
	{
		return $this->loggedAs('USER');
	}

	private function loggedAs($role)
	{
		if (!isset(self::$sessions[$role])) {
			$http = new Http();
			$login = getenv("KADE_TEST_{$role}_LOGIN");
			if (!$http->login($login, getenv("KADE_TEST_{$role}_PASSWORD")))
				$this->markTestSkipped("Não foi possível entrar como \"{$login}\": confira KADE_TEST_{$role}_LOGIN/PASSWORD no phpunit.xml");
			self::$sessions[$role] = $http;
		}
		return self::$sessions[$role];
	}

	protected function assertJsonError($response, $message = '')
	{
		$json = $response->json();
		$this->assertIsArray($json, $message . ' (resposta não é JSON: ' . substr($response->body, 0, 200) . ')');
		$this->assertSame('error', $json['result'] ?? null, $message);
	}
}
