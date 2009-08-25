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
define('APPLICATION_PATH', realpath(dirname(__FILE__).'/../application').'/');
define('APP_ENV', (getenv('APP_ENV') ? getenv('APP_ENV') : 'production'));
define('PUB_PATH', dirname(__FILE__).'/');

/* Include required library files. */
require_once 'Zend/Application.php';
require_once 'Pub/Model.php';

/* Initialize and bootstrap application. */
$app = new Zend_Application(APP_ENV, APPLICATION_PATH.'configs/application.ini');
$app->bootstrap();

/* Get the bootstrap instance with the loaded configuration. */
$bootstrap = $app->getBootstrap();

try {
    $app->run();
} catch (Exception $e) {
	echo $e->getMessage();
}
