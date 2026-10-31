<?php
namespace Kade\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
	/** @dataProvider cpfs */
	public function testIsCPF($cpf, $valid)
	{
		$this->assertSame($valid, \Validation::isCPF($cpf));
	}

	public function cpfs()
	{
		return array(
			'válido'           => array('52998224725', true),
			'válido com máscara' => array('529.982.247-25', true),
			'dígito errado'    => array('52998224724', false),
			'repetido'         => array('11111111111', false),
			'zeros'            => array('00000000000', false),
			'curto'            => array('5299822472', false),
			'vazio'            => array('', false),
		);
	}

	/** @dataProvider cnpjs */
	public function testIsCNPJ($cnpj, $valid)
	{
		$this->assertSame($valid, \Validation::isCNPJ($cnpj));
	}

	public function cnpjs()
	{
		return array(
			'válido'           => array('11222333000181', true),
			'válido com máscara' => array('11.222.333/0001-81', true),
			'dígito errado'    => array('11222333000182', false),
			'curto'            => array('1122233300018', false),
			'vazio'            => array('', false),
		);
	}

	/** @dataProvider emails */
	public function testIsEmail($email, $valid)
	{
		$this->assertSame($valid, (bool) \Validation::isEmail($email), "isEmail('{$email}')");
	}

	public function emails()
	{
		return array(
			'simples'               => array('joao@gmail.com', true),
			'domínio .com.br'       => array('contato@kadecaminhoes.com.br', true),
			'maiúsculas'            => array('Joao.Silva@Gmail.com', true),
			'TLD longo (.digital)'  => array('vendas@empresa.digital', true),
			'mais (+tag)'           => array('joao+fretes@gmail.com', true),
			'sem @'                 => array('joao.gmail.com', false),
			'sem domínio'           => array('joao@', false),
			'com espaço'            => array('joao silva@gmail.com', false),
			'lixo antes'            => array('<script>@x.com', false),
			'vazio'                 => array('', false),
		);
	}

	/** @dataProvider ceps */
	public function testIsCEP($cep, $valid)
	{
		$this->assertSame($valid, (bool) \Validation::isCEP($cep));
	}

	public function ceps()
	{
		return array(
			array('86010-000', true),
			array('86010000', false),
			array('8601-000', false),
			array('', false),
		);
	}

	/** @dataProvider dates */
	public function testIsDate($date, $valid)
	{
		$this->assertSame($valid, (bool) \Validation::isDate($date), "isDate('{$date}')");
	}

	public function dates()
	{
		return array(
			array('31/10/2026', true),
			array('2026-10-31', false),
			array('31/10/26', false),
			array('', false),
		);
	}
}
