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
    '../application',
    '../application/models',
    '../library');
set_include_path(get_include_path() . PATH_SEPARATOR . join(PATH_SEPARATOR, $includes));

/* Include required library files. */
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

/* Set error reporting to all. This is for debugging purposes. Hosted
 * configuration must set the additional ini option display_errors to false and
 * enable logging to a file. */
error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('log_errors', false);
ini_set('error_log', 'php-errors.txt');

/* Start session management. */
Zend_Session::start();

/* Initialize configuration. */
$config = new Zend_Config_Ini('config.ini', 'staging');
Zend_Registry::set('config', $config);

/* Initialize database connection. */
$db = Zend_Db::factory($config->database);
Zend_Registry::set('db', $db);
Zend_Db_Table_Abstract::setDefaultAdapter($db);

/* Initialize the front controller. */
$front = Zend_Controller_Front::getInstance();
$front->addModuleDirectory('../application');
$front->throwExceptions(true);
$front->setBaseUrl('/moss/');
$front->setParam('useDefaultControllerAlways', true);

/* Custom routes. */
$router = $front->getRouter();
$router->addRoute('activate',
    new Zend_Controller_Router_Route('activate/:hash', array(
        'module'     => 'default',
        'controller' => 'register',
        'action'     => 'activate')));

/* Start layout for view. */
$layout = Zend_Layout::startMvc();
$layout->setLayoutPath('../application/layouts/');

try {
	$front->dispatch();
} catch (Exception $e) {
	Zend_Debug::dump($e);
}

