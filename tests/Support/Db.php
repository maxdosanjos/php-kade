<?php
namespace Kade\Tests\Support;

/**
 * Conexão PDO com o banco DEV do .env, para preparar e conferir dados nos testes HTTP.
 */
class Db
{
	private static $pdo = null;

	public static function pdo()
	{
		if (self::$pdo === null) {
			$cfg = \Properties::getGroup('dbkade_dev');
			$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $cfg['host'], $cfg['port'] ?: 3306, $cfg['database']);
			self::$pdo = new \PDO($dsn, $cfg['user'], $cfg['password'], array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
		}
		return self::$pdo;
	}

	public static function value($sql, array $params = array())
	{
		$stmt = self::pdo()->prepare($sql);
		$stmt->execute($params);
		return $stmt->fetchColumn();
	}

	public static function exec($sql, array $params = array())
	{
		$stmt = self::pdo()->prepare($sql);
		$stmt->execute($params);
		return $stmt->rowCount();
	}
}
