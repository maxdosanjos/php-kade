<?php
namespace Kade\Tests\Http;

use Kade\Tests\Support\Db;

/**
 * Recuperação de senha de ponta a ponta, lendo o e-mail no Mailpit.
 * Só roda se o .env apontar o SMTP para o Mailpit (KADE_TEST_MAILPIT responde).
 */
class MailFlowTest extends HttpTestCase
{
	private $mailpit;

	protected function setUp(): void
	{
		$this->mailpit = rtrim(getenv('KADE_TEST_MAILPIT') ?: 'http://localhost:8025', '/');
		if (@file_get_contents($this->mailpit . '/api/v1/messages?limit=1') === false)
			$this->markTestSkipped("Mailpit não responde em {$this->mailpit}");
	}

	private function userData()
	{
		$pdo = Db::pdo();
		$stmt = $pdo->prepare("SELECT p.id, p.email, pi.cpf FROM user u JOIN person p ON p.id = u.person_id JOIN person_individual pi ON pi.person_id = p.id WHERE u.login = ?");
		$stmt->execute(array(getenv('KADE_TEST_USER_LOGIN')));
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);
		if (!$row)
			$this->markTestSkipped('A conta de teste precisa ser pessoa física');
		return $row;
	}

	private function latestMessageTo($email)
	{
		$list = json_decode(file_get_contents($this->mailpit . '/api/v1/search?query=' . urlencode('to:' . $email)), true);
		$this->assertNotEmpty($list['messages'] ?? array(), "Nenhum e-mail para {$email}");
		return json_decode(file_get_contents($this->mailpit . '/api/v1/message/' . $list['messages'][0]['ID']), true);
	}

	public function testPasswordResetEmailHasWorkingSingleUseLink()
	{
		$user = $this->userData();
		$response = $this->anonymous()->post('/LoginQueryCtrl.ctrlExt/requestNewPwd', array(
			'type_person' => 'PF', 'newpwd_cpf' => $user['cpf'], 'newpwd_mail' => strtoupper($user['email'])));
		$this->assertSame('sucess', $response->json()['result'] ?? null, $response->body);

		$message = $this->latestMessageTo($user['email']);
		$this->assertSame('Requisição de nova senha KADE Caminhões', $message['Subject']);
		$this->assertMatchesRegularExpression('#https?://[^/]+/confirm_new_pwd/vua=([a-f0-9]{32})#', $message['Text']);
		preg_match('#(https?://[^/]+)/confirm_new_pwd/vua=([a-f0-9]{32})#', $message['Text'], $m);
		$this->assertSame(rtrim(getenv('KADE_TEST_URL') ?: 'http://kade.test', '/'), $m[1], 'Link do e-mail não usa o APP_URL');
		$this->assertStringContainsString('Criar nova senha', $message['HTML']);
		$this->assertStringNotContainsString('<', $message['Text'], 'Versão texto com HTML');

		try {
			$page = $this->anonymous()->get('/confirm_new_pwd/vua=' . $m[2]);
			$this->assertSame(200, $page->status);
			$this->assertStringContainsString(htmlspecialchars(getenv('KADE_TEST_USER_LOGIN')), $page->body);
		} finally {
			Db::exec("DELETE FROM link_confirm WHERE hash = ?", array($m[2]));
		}
	}

	public function testPasswordResetNeverMailsAnAddressOtherThanTheRegisteredOne()
	{
		// Antes o link ia para o e-mail digitado: bastava saber o CPF para tomar a conta
		$user = $this->userData();
		$intruder = 'intruso.' . mt_rand() . '@example.com';
		$response = $this->anonymous()->post('/LoginQueryCtrl.ctrlExt/requestNewPwd', array(
			'type_person' => 'PF', 'newpwd_cpf' => $user['cpf'], 'newpwd_mail' => $intruder));
		$this->assertSame('sucess', $response->json()['result'] ?? null);

		$list = json_decode(file_get_contents($this->mailpit . '/api/v1/search?query=' . urlencode('to:' . $intruder)), true);
		$this->assertSame(0, (int) ($list['messages_count'] ?? -1), 'O link de nova senha foi enviado para um e-mail que não é o do cadastro');
	}

	public function testPasswordResetAnswersTheSameWithOrWithoutAccount()
	{
		$user = $this->userData();
		$known = $this->anonymous()->post('/LoginQueryCtrl.ctrlExt/requestNewPwd', array(
			'type_person' => 'PF', 'newpwd_cpf' => $user['cpf'], 'newpwd_mail' => 'nao.confere@example.com'))->json();
		// CPF válido sem cadastro
		if (Db::value("SELECT COUNT(*) FROM person_individual WHERE cpf = '93541134780'"))
			$this->markTestSkipped('O CPF de teste 935.411.347-80 tem cadastro neste banco');
		$unknown = $this->anonymous()->post('/LoginQueryCtrl.ctrlExt/requestNewPwd', array(
			'type_person' => 'PF', 'newpwd_cpf' => '935.411.347-80', 'newpwd_mail' => 'nao.confere@example.com'))->json();
		$this->assertSame($known, $unknown, 'A resposta revela se o CPF tem cadastro');
	}

	public function testInvalidResetTokenIsRefused()
	{
		$page = $this->anonymous()->get('/confirm_new_pwd/vua=' . str_repeat('0', 32));
		$this->assertStringNotContainsString('custumer_password', $page->body, 'Token inválido abriu o formulário de nova senha');
	}
}
