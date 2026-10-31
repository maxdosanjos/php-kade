<?php
namespace Kade\Tests\Support;

/**
 * Cliente HTTP mínimo (curl) com cookies próprios: cada instância é um navegador.
 * Não segue redirecionamentos: os testes conferem o Location.
 */
class Http
{
	private $baseUrl;
	private $cookieFile;

	public function __construct($baseUrl = null)
	{
		$this->baseUrl = rtrim($baseUrl ?? (getenv('KADE_TEST_URL') ?: 'http://kade.test'), '/');
		$this->cookieFile = tempnam(sys_get_temp_dir(), 'kade_cookie_');
	}

	public function __destruct()
	{
		@unlink($this->cookieFile);
	}

	public static function siteIsUp($baseUrl = null)
	{
		$http = new self($baseUrl);
		try {
			return $http->get('/terms_use/')->status === 200;
		} catch (\RuntimeException $e) {
			return false;
		}
	}

	public function get($path, array $headers = array())
	{
		return $this->request('GET', $path, null, $headers);
	}

	/** @param array|string $data campos (urlencoded ou multipart se houver CURLFile) ou corpo cru */
	public function post($path, $data = array(), array $headers = array())
	{
		return $this->request('POST', $path, $data, $headers);
	}

	public function request($method, $path, $data = null, array $headers = array())
	{
		$ch = curl_init($this->baseUrl . $path);
		$responseHeaders = array();
		curl_setopt_array($ch, array(
			CURLOPT_CUSTOMREQUEST  => $method,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_COOKIEFILE     => $this->cookieFile,
			CURLOPT_COOKIEJAR      => $this->cookieFile,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_HEADERFUNCTION => function ($ch, $line) use (&$responseHeaders) {
				$parts = explode(':', $line, 2);
				if (count($parts) == 2)
					$responseHeaders[] = array(strtolower(trim($parts[0])), trim($parts[1]));
				return strlen($line);
			},
		));
		if ($data !== null) {
			$hasFile = is_array($data) && array_filter($data, function ($v) { return $v instanceof \CURLFile; });
			curl_setopt($ch, CURLOPT_POSTFIELDS, (is_array($data) && !$hasFile) ? http_build_query($data) : $data);
		}
		$body = curl_exec($ch);
		if ($body === false) {
			$error = curl_error($ch);
			curl_close($ch);
			throw new \RuntimeException("Falha ao acessar {$path}: {$error}");
		}
		$status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
		curl_close($ch);
		// O cookie jar só é gravado no curl_close: relê no próximo request
		return new HttpResponse($status, $responseHeaders, $body);
	}

	/**
	 * Entra pelo formulário de login. Retorna true se o site redirecionou para fora do /login/.
	 */
	public function login($login, $password)
	{
		$response = $this->post('/LoginQueryCtrl.ctrlExt/onLogin', array('user_login' => $login, 'user_password' => $password));
		return $response->status === 302 && strpos($response->header('location'), '/login/') === false;
	}

	public function sessionId()
	{
		foreach (file($this->cookieFile) ?: array() as $line) {
			$cols = explode("\t", trim($line));
			if (count($cols) >= 7 && $cols[5] === 'PHPSESSID')
				$id = $cols[6];
		}
		return $id ?? null;
	}
}

class HttpResponse
{
	public $status;
	public $headers;
	public $body;

	public function __construct($status, array $headers, $body)
	{
		$this->status = $status;
		$this->headers = $headers;
		$this->body = $body;
	}

	public function header($name)
	{
		foreach ($this->headers as $h)
			if ($h[0] === strtolower($name))
				return $h[1];
		return '';
	}

	/** Todos os valores de um cabeçalho repetido (ex.: Set-Cookie) */
	public function headers($name)
	{
		$out = array();
		foreach ($this->headers as $h)
			if ($h[0] === strtolower($name))
				$out[] = $h[1];
		return $out;
	}

	public function json()
	{
		// Algumas respostas JSON saem com BOM ou espaços antes
		return json_decode(trim($this->body, "\xEF\xBB\xBF \r\n\t"), true);
	}
}
