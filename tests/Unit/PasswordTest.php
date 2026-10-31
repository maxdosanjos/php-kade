<?php
namespace Kade\Tests\Unit;

use PHPUnit\Framework\TestCase;

class PasswordTest extends TestCase
{
	public function testHashIsBcryptAndSalted()
	{
		$a = \UserDAO::hashPassword('Teste@123');
		$b = \UserDAO::hashPassword('Teste@123');
		$this->assertStringStartsWith('$2y$', $a);
		$this->assertNotSame($a, $b, 'O mesmo texto deve gerar hashes diferentes (salt)');
	}

	public function testVerifyBcrypt()
	{
		$hash = \UserDAO::hashPassword('Senha forte ç 123');
		$this->assertTrue(\UserDAO::verifyPassword('Senha forte ç 123', $hash));
		$this->assertFalse(\UserDAO::verifyPassword('senha forte ç 123', $hash));
		$this->assertFalse(\UserDAO::verifyPassword('', $hash));
	}

	public function testLegacyMd5StillWorksAndAsksForRehash()
	{
		$legacy = md5('antiga123');
		$this->assertTrue(\UserDAO::verifyPassword('antiga123', $legacy));
		$this->assertTrue(\UserDAO::verifyPassword('antiga123', strtoupper($legacy)));
		$this->assertFalse(\UserDAO::verifyPassword('outra', $legacy));
		$this->assertTrue((bool) \UserDAO::needsRehash($legacy));
	}

	public function testCurrentHashDoesNotNeedRehash()
	{
		$this->assertFalse((bool) \UserDAO::needsRehash(\UserDAO::hashPassword('x')));
	}

	public function testEmptyOrGarbageHashNeverMatches()
	{
		$this->assertFalse(\UserDAO::verifyPassword('', ''));
		$this->assertFalse(\UserDAO::verifyPassword('admin', 'admin'));
		$this->assertFalse(\UserDAO::verifyPassword('x', null));
	}
}
