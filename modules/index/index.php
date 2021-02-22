<?php

defined('_IS_VALIDATION_') or die('Acesso não permitido.');

set_include_path('.' . PATH_SEPARATOR . './../horusnet/library/fpdf/'
                     . PATH_SEPARATOR . './../horusnet/library/phpMailer/'
                     . PATH_SEPARATOR . './../horusnet/classes/'
                     . PATH_SEPARATOR . get_include_path());

function __autoload($class)
{
    require_once($class . '.php');
}

$config = new Configuration();

$config->user = 'HORUS_WEB';

//$config->debugView = false;

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', $config->displayErrors);
setlocale(LC_ALL, $config->locale);
date_default_timezone_set($config->timezone);

define('PATH_ABSOLUTE', $config->pathAbsolute);
define('PATH_RELATIVE', $config->pathRelative);
define('PATH_INCLUDE', $config->pathInclude);
define('PATH_APPLICATION', $config->pathApplication);
define('PATH_APPLICATION_CSS', $config->pathApplicationCSS);
define('PATH_APPLICATION_JAVASCRIPT', $config->pathApplicationJavascript);
define('PATH_APPLICATION_IMAGES', $config->pathApplicationImages);
define('PATH_APPLICATION_FILES', $config->pathApplicationFiles);
define('PATH_APPLICATION_MODULES', $config->pathApplicationModules);
define('PATH_MODULE', $config->pathModule);
define('PATH_MODULE_VIEWS', $config->pathModuleViews);
define('PATH_URL_APPLICATION', $config->pathUrlApplication);
define('PATH_URL_MODULE', $config->pathUrlModule);
define('PATH_IMAGES', $config->pathImages);
define('PATH_JAVASCRIPT', $config->pathJavascript);
define('PATH_CSS', $config->pathCSS);

set_include_path('.' . PATH_SEPARATOR . './' . PATH_APPLICATION . 'classes/'
                     . PATH_SEPARATOR . get_include_path());

$controller = new Controller($config);