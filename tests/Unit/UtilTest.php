<?php
namespace Kade\Tests\Unit;

use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
	/** @dataProvider validDates */
	public function testParseDateBRAcceptsBrazilianFormats($input, $expected)
	{
		$date = \Util::parseDateBR($input);
		$this->assertNotNull($date);
		$this->assertSame($expected, $date->format('Y-m-d'));
	}

	public function validDates()
	{
		return array(
			'barra'           => array('31/12/2026', '2026-12-31'),
			'ponto (máscara)' => array('01.02.2027', '2027-02-01'),
			'hífen'           => array('5-3-2026', '2026-03-05'),
			'com espaços'     => array('  10/10/2026 ', '2026-10-10'),
			'29/02 bissexto'  => array('29/02/2028', '2028-02-29'),
		);
	}

	/** @dataProvider invalidDates */
	public function testParseDateBRRejectsInvalidDates($input)
	{
		$this->assertNull(\Util::parseDateBR($input));
	}

	public function invalidDates()
	{
		return array(
			'vazio'             => array(''),
			'null'              => array(null),
			'31 de fevereiro'   => array('31/02/2026'),
			'29/02 não bissexto'=> array('29/02/2027'),
			'mês 13'            => array('01/13/2026'),
			'formato ISO'       => array('2026-10-31'),
			'ano com 2 dígitos' => array('31/10/26'),
			'texto'             => array('amanhã'),
			'injeção'           => array("31/10/2026' OR '1'='1"),
		);
	}

	/** @dataProvider moneyValues */
	public function testParseMoneyBR($input, $expected)
	{
		$this->assertSame($expected, \Util::parseMoneyBR($input));
	}

	public function moneyValues()
	{
		return array(
			'milhar e centavos' => array('1.234,56', 1234.56),
			'centavos'          => array('70,00', 70.0),
			'inteiro'           => array('70', 70.0),
			'ponto decimal (banco)' => array('70.5', 70.5),
			'com R$'            => array('R$ 1.000,00', 1000.0),
			'vazio'             => array('', 0.0),
			'texto'             => array('abc', 0.0),
			'arredonda'         => array('10,999', 11.0),
		);
	}

	public function testCsvRowUsesSemicolonAndCrlf()
	{
		$this->assertSame("a;b;c\r\n", \Util::csvRow(array('a', 'b', 'c')));
	}

	public function testCsvRowQuotesWhenNeeded()
	{
		$this->assertSame("\"a;b\";\"diz \"\"oi\"\"\";\"linha\nnova\"\r\n", \Util::csvRow(array('a;b', 'diz "oi"', "linha\nnova")));
	}

	/** @dataProvider formulaCells */
	public function testCsvRowNeutralizesSpreadsheetFormulas($cell)
	{
		$row = \Util::csvRow(array($cell));
		$this->assertStringStartsWith("'", trim($row, "\"\r\n"), "A célula \"{$cell}\" seria executada como fórmula no Excel");
	}

	public function formulaCells()
	{
		return array(
			array('=HYPERLINK("http://mal.example","clique")'),
			array('+5511999990000'),
			array('-2+3'),
			array('@SUM(A1:A9)'),
			array("\t=1+1"),
		);
	}

	public function testCsvRowKeepsAccentsAndNumbers()
	{
		$this->assertSame("João;Caçamba;123\r\n", \Util::csvRow(array('João', 'Caçamba', 123)));
	}

	public function testMask()
	{
		$this->assertSame('86010-000', \Util::mask('86010000', '#####-###'));
		$this->assertSame('529.982.247-25', \Util::mask('52998224725', '###.###.###-##'));
	}

	public function testValidateIP()
	{
		$this->assertTrue(\Util::validateIP('200.195.184.186'));
		$this->assertFalse(\Util::validateIP('256.1.1.1'));
		$this->assertFalse(\Util::validateIP('1.2.3'));
		$this->assertFalse(\Util::validateIP('1.2.3.4; DROP TABLE x'));
	}
}
