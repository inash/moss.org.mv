<?php

/**
 * Website/application for moss.org.mv.
 * This interface aims to provide what is necessary for the website and any
 * dynamic feature that might be relevant to the website of MOSS.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon May 25, 2009 4:48 AM
 * @version $Id$
 */

$includes = array(
    '../application',
    '../library');

set_include_path(get_include_path() . PATH_SEPARATOR . join(PATH_SEPARATOR, $includes));

require_once 'Zend/Controller/Front.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/Session.php';
require_once 'Zend/Layout.php';
require_once 'Zend/Debug.php';

error_reporting(E_ALL);

Zend_Session::start();

/* Initialize the front controller. */
$front = Zend_Controller_Front::getInstance();
$front->addModuleDirectory('../application');
$front->throwExceptions(true);
$front->setBaseUrl('/moss/');

$layout = Zend_Layout::startMvc();
$layout->setLayoutPath('../application/layouts/');

try {
	$front->dispatch();
} catch (Exception $e) {
	Zend_Debug::dump($e);
}

