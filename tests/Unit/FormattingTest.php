<?php
namespace Kade\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Formatação exibida nas telas e nos e-mails.
 */
class FormattingTest extends TestCase
{
	public function testPhoneMaskForMobileAndLandline()
	{
		$phone = new \Phone();
		$phone->setDdd('43');
		$phone->setPhone('999990000');
		$this->assertSame('(43) 99999-0000', $phone->getPhoneMask());

		$phone->setPhone('3333-4444');
		$this->assertSame('(43) 3333-4444', $phone->getPhoneMask());
	}

	public function testPhoneKeepsOnlyDigitsAndTruncates()
	{
		$phone = new \Phone();
		$phone->setPhone('(43) 9 9999-0000 ramal 12345');
		$this->assertSame('43999990000', $phone->getPhone());
	}

	public function testPhoneByMask()
	{
		$phone = new \Phone();
		$phone->setPhoneByMask('(43) 999990000');
		$this->assertSame('43', $phone->getDdd());
		$this->assertSame('999990000', $phone->getPhone());
	}

	public function testEmptyPhoneHasNoMask()
	{
		$this->assertSame('', (new \Phone())->getPhoneMask());
	}

	/** @dataProvider documents */
	public function testFormatDocument($input, $expected)
	{
		$this->assertSame($expected, k_fmt_doc($input));
	}

	public function documents()
	{
		return array(
			'CPF'          => array('52998224725', '529.982.247-25'),
			'CPF formatado'=> array('529.982.247-25', '529.982.247-25'),
			'CNPJ'         => array('11222333000181', '11.222.333/0001-81'),
			'outro'        => array('123', '123'),
			'vazio'        => array('', ''),
		);
	}

	public function testHtmlEmailToPlainText()
	{
		$html = "<!DOCTYPE html><html><head><title>t</title><style>p{color:red}</style></head><body>\n"
			. "<table><tr><td>\n\t\t<h1>Bem-vindo à KADE!</h1>\n\t\t<p>Olá, João.</p>\n"
			. "<a href=\"http://x\">Ativar</a></td></tr></table>\n"
			. "<p>Atenciosamente,<br>Equipe &amp; amigos</p></body></html>";
		$text = \Mail::htmlToText($html);
		$this->assertStringNotContainsString('color:red', $text);
		$this->assertStringNotContainsString('<', $text);
		$this->assertStringContainsString("Bem-vindo à KADE!\n", $text);
		$this->assertStringContainsString("Atenciosamente,\nEquipe & amigos", $text);
		$this->assertDoesNotMatchRegularExpression("/\n{3,}|\t|  /", $text, 'Sobrou indentação ou linhas em branco demais');
	}
}
