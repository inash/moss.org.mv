<?php

/**
 * Website/application for moss.org.mv.
 * This interface aims to provide what is necessary for the website and any
 * dynamic feature that might be relevant to the website of MOSS.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon May 25, 2009 4:48 AM
 */

/* Set include paths. */
$includes = array(
    realpath(dirname(__FILE__).'/../library'),
    get_include_path());
set_include_path(join(PATH_SEPARATOR, $includes));

/* Define constants. */
define('APP_PATH', realpath(dirname(__FILE__).'/../application').'/');
define('APP_ENV', (getenv('APP_ENV') ? getenv('APP_ENV') : 'production'));
define('PUB_PATH', dirname(__FILE__).'/');

/* Include required library files. */
require_once 'Zend/Application.php';

$app = new Zend_Application(APP_ENV, APP_PATH.'configs/application.ini');
$app->bootstrap();

try {
    $app->run();
} catch (Exception $e) {
	Zend_Debug::dump($e);
}

exit;

require_once 'Zend/Controller/Front.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/Session.php';
require_once 'Zend/Layout.php';
require_once 'Zend/Debug.php';
require_once 'Zend/Config/Ini.php';
require_once 'Zend/Db.php';
require_once 'Zend/Db/Table/Abstract.php';
require_once 'Zend/Loader.php';
require_once 'Zend/Registry.php';

/* Initialize configuration. */
$config = new Zend_Config_Ini('config.ini', 'staging');
Zend_Registry::set('config', $config);

/* Set error reporting to all. This is for debugging purposes. Hosted
 * configuration must set the additional ini option display_errors to false and
 * enable logging to a file. */
error_reporting(E_ALL);
ini_set('error_log', 'php-errors.txt');
if ($config->app->exceptions == true) {
    ini_set('display_errors', true);
    ini_set('log_errors', false);
} else {
    ini_set('display_errors', false);
    ini_set('log_errors', true);
}

date_default_timezone_set($config->timezone);

/* Start session management. */
Zend_Session::start();

/* Initialize database connection. */
$db = Zend_Db::factory($config->database);
Zend_Registry::set('db', $db);
Zend_Db_Table_Abstract::setDefaultAdapter($db);

/* Initialize the front controller. */
$front = Zend_Controller_Front::getInstance();
$front->addModuleDirectory($config->app->path->application);
$front->throwExceptions($config->app->exceptions);
$front->setBaseUrl($config->app->baseUrl);
$front->setParam('useDefaultControllerAlways', true);

/* Custom routes. */
$router = $front->getRouter();
$router->addRoute('activate',
    new Zend_Controller_Router_Route('activate/:hash', array(
        'module'     => 'default',
        'controller' => 'register',
        'action'     => 'activate')));

$router->addRoute('admin',
    new Zend_Controller_Router_Route('admin/:action', array(
        'module'     => 'admin',
        'controller' => 'index',
        'action'     => 'activity')));

/* Start layout for view. */
$layout = Zend_Layout::startMvc();
$layout->setLayoutPath($config->app->path->application.'/layouts');

try {
	$front->dispatch();
} catch (Exception $e) {
	Zend_Debug::dump($e);
}

