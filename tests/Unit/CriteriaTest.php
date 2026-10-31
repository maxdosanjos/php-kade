<?php
namespace Kade\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * O Criteria monta o WHERE/ORDER/LIMIT das buscas com valores que vêm da
 * requisição: estes testes garantem que nada sai do literal SQL.
 */
class CriteriaTest extends TestCase
{
	/** @dataProvider injectionPayloads */
	public function testEscapeStringNeutralizesQuotes($payload)
	{
		$escaped = \Criteria::escapeString($payload);
		// Nenhuma aspa simples sem barra antes: o valor não fecha o literal
		$this->assertDoesNotMatchRegularExpression("/(?<!\\\\)'/", $escaped);
	}

	public function injectionPayloads()
	{
		return array(
			array("' OR '1'='1"),
			array("x'; DROP TABLE user; --"),
			array("\\' OR 1=1 -- "),
			array("O'Brien"),
		);
	}

	public function testEscapeStringEscapesControlCharacters()
	{
		$this->assertSame('a\\0b\\nc\\rd\\Ze\\\\f\\"g', \Criteria::escapeString("a\0b\nc\rd\x1ae\\f\"g"));
	}

	public function testEqQuotesAndEscapesStrings()
	{
		$criteria = new \Criteria();
		$criteria->eq('usr.login', "admin' -- ");
		$where = $criteria->getWhere();
		$this->assertStringContainsString("'admin\\' -- '", $where);
	}

	public function testFunctionLikeValueIsNotPassedRaw()
	{
		// Antes, um valor no formato "nome(...)" ia cru para o SQL
		$criteria = new \Criteria();
		$criteria->eq('usr.login', "sleep(5)");
		$this->assertStringContainsString("'sleep(5)'", $criteria->getWhere());
	}

	public function testNumbersStayUnquoted()
	{
		$criteria = new \Criteria();
		$criteria->eq('cli.user_person_id', 38);
		$this->assertMatchesRegularExpression('/= *38\b/', $criteria->getWhere());
	}

	public function testLikeEscapesValue()
	{
		$criteria = new \Criteria();
		$criteria->like('per.name', "%' UNION SELECT password FROM user -- %");
		$this->assertStringContainsString("\\' UNION", $criteria->getWhere());
	}

	public function testInEscapesEveryValue()
	{
		$criteria = new \Criteria();
		$criteria->in('vt.status', array('ACTV', "x') OR ('1'='1"));
		$where = $criteria->getWhere();
		$this->assertStringContainsString("'ACTV'", $where);
		$this->assertStringContainsString("\\') OR (\\'1\\'=\\'1", $where);
	}

	/** @dataProvider validOrderColumns */
	public function testAddOrderAcceptsColumnNames($column)
	{
		$criteria = new \Criteria();
		$criteria->addOrder($column, \Criteria::ORDER_DESC);
		$this->assertStringContainsString('DESC', $criteria->getOrder());
	}

	public function validOrderColumns()
	{
		return array(array('id'), array('acc.date_hr_proc'), array('qty_illus'));
	}

	/** @dataProvider invalidOrderColumns */
	public function testAddOrderIgnoresAnythingButColumnNames($column)
	{
		$criteria = new \Criteria();
		$criteria->addOrder($column, \Criteria::ORDER_ASC);
		$this->assertSame('', trim($criteria->getOrder()), "ORDER BY aceitou: {$column}");
	}

	public function invalidOrderColumns()
	{
		return array(
			'subconsulta'    => array('(SELECT password FROM user LIMIT 1)'),
			'comando extra'  => array('id; DROP TABLE user'),
			'comentário'     => array('id -- '),
			'condição'       => array('IF(1=1,id,login)'),
			'três partes'    => array('a.b.c'),
			'vazio'          => array(''),
		);
	}

	public function testSetLimitForcesIntegers()
	{
		$criteria = new \Criteria();
		$criteria->setLimit("10; DROP TABLE user", "-20");
		$this->assertSame(' LIMIT 10,20', $criteria->getLimit());
	}

	public function testNoLimitWhenLengthIsZero()
	{
		$criteria = new \Criteria();
		$criteria->setLimit(0, 0);
		$this->assertSame('', $criteria->getLimit());
	}
}
