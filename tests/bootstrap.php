<?php
/*
 * Bootstrap dos testes. Roda fora do Apache, então simula o host local para o
 * Properties escolher a configuração DEV do .env.
 */
if (!ini_get('short_open_tag')) {
	fwrite(STDERR, "As classes do site usam \"<?\": rode com php -d short_open_tag=1 (ou \"composer test\").\n");
	exit(1);
}

$_SERVER['HTTP_HOST'] = 'kade.test';

define('KADE_ROOT', dirname(__DIR__) . '/kade');
define('KADE_CLASS', KADE_ROOT . '/shared/class');

require dirname(__DIR__) . '/vendor/autoload.php';

require_once KADE_CLASS . '/util/Properties.class.php';
require_once KADE_CLASS . '/util/DateTimeCustom.class.php';
require_once KADE_CLASS . '/util/Util.class.php';
require_once KADE_CLASS . '/util/Validation.class.php';
require_once KADE_CLASS . '/util/Pagination.class.php';
require_once KADE_CLASS . '/util/Message.class.php';
require_once KADE_CLASS . '/dao/Criteria.class.php';
require_once KADE_CLASS . '/dao/UserDAO.class.php';
require_once KADE_CLASS . '/vo/PhoneVO.class.php';
require_once KADE_CLASS . '/vo/PersonVO.class.php';
require_once KADE_CLASS . '/vo/InstallmentVO.class.php';
require_once KADE_CLASS . '/mail/Mail.class.php';
require_once KADE_ROOT . '/view/partials/helpers.php';
