<?php

use Nopadi\FS\Json;
use Nopadi\FS\ReadArray;
use Nopadi\Support\ServiceProvider;

/*Variaveis da aplicação*/
$app = new Json('config/app/app.json');

define('NP_TIMEZONE',$app->get('timezone'));

date_default_timezone_set(NP_TIMEZONE);

define('NP_NAME',$app->get('name'));
define('NP_DESCRIPTION',$app->get('description'));
define('NP_VERSION',$app->get('version'));
define('NP_AUTHOR',$app->get('author'));
define('NP_LANG',$app->get('lang'));
define('NP_STATUS',$app->get('status'));
define('NP_MODE',$app->get('mode'));
define('NP_LOGS',$app->get('logs'));
define('NP_CHARSET',$app->get('charset'));

define('NP_DATETIME',date('Y-m-d H:i:s'));
define('NP_DATE',date('Y-m-d'));
define('NP_TIME',date('H:i:s'));

switch(NP_MODE){
	
	case 'dev' : $db_php = 'config/database/database-dev.php'; break;
	case 'test' : $db_php = 'config/database/database-test.php'; break;
	case 'deploy' : $db_php = 'config/database/database-deploy.php'; break;
	default : $db_php = 'config/database/database.php';
	
}

/*Variaveis da base de dados*/
$db = new ReadArray($db_php);

define('NP_DB_HOST',$db->get('host'));
define('NP_DB_NAME',$db->get('name'));
define('NP_DB_USER',$db->get('user'));
define('NP_DB_PASS',$db->get('pass'));
define('NP_DB_SGBD',$db->get('sgbd'));
define('NP_DB_PREF',$db->get('pref'));

/*Variaveis do servidor de e-mail*/
$email = new ReadArray('config/smtp/smtp.php');

define('NP_EMAIL',$email->get('email'));
define('NP_EMAIL_NAME',$email->get('name'));
define('NP_EMAIL_HOST',$email->get('host'));
define('NP_EMAIL_PORT',$email->get('port'));
define('NP_EMAIL_AUTH',$email->get('auth'));
define('NP_EMAIL_USER',$email->get('user'));
define('NP_EMAIL_PASS',$email->get('pass'));

ReadArray::addFile('functions.php');

$kernel = new ReadArray('config/kernel.php');
$services = new ServiceProvider;
$services->bootServices($kernel->get('providers',[]));

error_reporting(E_ALL);
ini_set('display_errors', true);
