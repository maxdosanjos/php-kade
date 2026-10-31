<?php
namespace Kade\Tests\Unit;

use PHPUnit\Framework\TestCase;

class PaginationTest extends TestCase
{
	protected function setUp(): void
	{
		$_REQUEST = array();
	}

	protected function tearDown(): void
	{
		$_REQUEST = array();
	}

	public function testDefaultsToFirstPageWithTenPerPage()
	{
		$pagination = new \Pagination(45);
		$this->assertSame(1, (int) $pagination->getPagCurrent());
		$this->assertSame(10, (int) $pagination->getEndLimit());
		$this->assertSame(0, (int) $pagination->getStartLimit());
	}

	public function testZeroResultsStillProducesValidLimit()
	{
		// Antes, 0 resultados gerava "LIMIT ,10" e um erro de SQL
		$pagination = new \Pagination(0);
		$this->assertSame(0, $pagination->getStartLimit());
	}

	public function testPageBeyondTheLastIsClamped()
	{
		$_REQUEST['pag'] = 99;
		$pagination = new \Pagination(45);
		$this->assertSame(5, (int) $pagination->getPagCurrent());
		$this->assertSame(40, (int) $pagination->getStartLimit());
	}

	public function testNegativeOrTextPageFallsBackToFirst()
	{
		$_REQUEST['pag'] = "-3 OR 1=1";
		$pagination = new \Pagination(45);
		$this->assertSame(1, (int) $pagination->getPagCurrent());
		$this->assertSame(0, (int) $pagination->getStartLimit());
	}

	/** @dataProvider perPageValues */
	public function testRecordsPerPageIsBounded($requested, $expected)
	{
		$_REQUEST['reg_per_pag'] = $requested;
		$pagination = new \Pagination(1000);
		$this->assertSame($expected, (int) $pagination->getEndLimit());
	}

	public function perPageValues()
	{
		return array(
			'normal'          => array(25, 25),
			'enorme'          => array(1000000, 100),
			'negativo'        => array(-5, 10),
			'texto'           => array('abc', 10),
		);
	}
}
