<?php
namespace Kade\Tests\Unit;

use PHPUnit\Framework\TestCase;

/** CepService sem rede e sem banco: as respostas das APIs são simuladas */
class FakeCepService extends \CepService
{
	public $responses = array();
	public $calls = array();

	protected function httpGet($url)
	{
		$this->calls[] = $url;
		foreach ($this->responses as $prefix => $body)
			if (strpos($url, $prefix) === 0)
				return $body;
		return null;
	}
}

class CepServiceTest extends TestCase
{
	const VIACEP = 'https://viacep.com.br/';
	const BRASILAPI = 'https://brasilapi.com.br/';

	/** @dataProvider ceps */
	public function testNormalize($input, $expected)
	{
		$this->assertSame($expected, \CepService::normalize($input));
	}

	public function ceps()
	{
		return array(
			'com hífen'                   => array('86010-000', '86010000'),
			'com ponto e espaço'          => array(' 86.010-000 ', '86010000'),
			'do banco, sem o zero (SP)'   => array('1001000', '01001000'),
			'número'                      => array(1001000, '01001000'),
			'curto'                       => array('123', ''),
			'zeros'                       => array('00000000', ''),
			'texto'                       => array('abc', ''),
			'vazio'                       => array(null, ''),
		);
	}

	public function testParseViaCep()
	{
		$json = '{"cep":"01001-000","logradouro":"Praça da Sé","complemento":"lado ímpar","bairro":"Sé","localidade":"São Paulo","uf":"sp","ibge":"3550308"}';
		$this->assertSame(array(
			'cep' => '01001000', 'street' => 'Praça da Sé', 'neighborhood' => 'Sé',
			'city' => 'São Paulo', 'state' => 'SP', 'source' => 'viacep',
		), \CepService::parseViaCep($json));
	}

	/** @dataProvider viaCepMisses */
	public function testParseViaCepMisses($json)
	{
		$this->assertNull(\CepService::parseViaCep($json));
	}

	public function viaCepMisses()
	{
		return array(
			'não encontrado (bool)'   => array('{"erro": true}'),
			'não encontrado (texto)'  => array('{"erro": "true"}'),
			'sem UF'                  => array('{"cep":"86010-000","localidade":"Londrina","uf":""}'),
			'UF inválida'             => array('{"cep":"86010-000","localidade":"Londrina","uf":"Paraná"}'),
			'sem cidade'              => array('{"cep":"86010-000","localidade":"","uf":"PR"}'),
			'HTML de erro'            => array('<html>502 Bad Gateway</html>'),
			'nada'                    => array(null),
		);
	}

	public function testParseBrasilApi()
	{
		$json = '{"cep":"13010000","state":"SP","city":"Campinas","neighborhood":"Centro","street":"Avenida Doutor Moraes Salles","service":"correios"}';
		$found = \CepService::parseBrasilApi($json);
		$this->assertSame('Campinas', $found['city']);
		$this->assertSame('SP', $found['state']);
		$this->assertSame('brasilapi', $found['source']);
		$this->assertNull(\CepService::parseBrasilApi('{"name":"CepPromiseError","message":"Todos os serviços de CEP retornaram erro.","errors":[]}'));
	}

	public function testLongValuesAreCutToColumnSize()
	{
		$found = \CepService::parseViaCep(json_encode(array('cep' => '86010000', 'logradouro' => str_repeat('a', 300), 'bairro' => str_repeat('b', 100), 'localidade' => str_repeat('c', 80), 'uf' => 'PR')));
		$this->assertSame(255, mb_strlen($found['street']));
		$this->assertSame(72, mb_strlen($found['neighborhood']));
		$this->assertSame(60, mb_strlen($found['city']));
	}

	public function testViaCepFirst()
	{
		$service = new FakeCepService();
		$service->responses = array(self::VIACEP => '{"cep":"86010-000","logradouro":"Rua A","bairro":"Centro","localidade":"Londrina","uf":"PR"}');
		$found = $service->lookup('86010-000');
		$this->assertSame('Londrina', $found['city']);
		$this->assertCount(1, $service->calls, 'Com o ViaCEP respondendo, a BrasilAPI não deve ser chamada');
		$this->assertStringContainsString('/ws/86010000/json/', $service->calls[0]);
	}

	public function testFallsBackToBrasilApi()
	{
		$service = new FakeCepService();
		$service->responses = array(
			self::VIACEP => '{"erro": true}',
			self::BRASILAPI => '{"cep":"99999999","state":"PR","city":"Sarandi","neighborhood":"","street":""}',
		);
		$found = $service->lookup('99999999');
		$this->assertSame('Sarandi', $found['city']);
		$this->assertSame('brasilapi', $found['source']);
	}

	public function testAllSourcesDownGivesNull()
	{
		$service = new FakeCepService();
		$this->assertNull($service->lookup('86010000'));
		$this->assertCount(2, $service->calls);
	}

	public function testInvalidCepCallsNothing()
	{
		$service = new FakeCepService();
		$this->assertNull($service->lookup('123'));
		$this->assertSame(array(), $service->calls);
	}
}
