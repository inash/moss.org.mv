<?php

/**
 * Main moss.org.mv application bootstrap file. This initializes the
 * Zend_Application environment and sets the necessary resources by reading
 * from the configuration file.
 * 
 * The environment is specified through APP_ENV and the resources set through
 * config Front Controller, Layout and Database.
 */

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initAutoload()
    {
    	$autoloader = Zend_Loader_Autoloader::getInstance();
    	$autoloader->suppressNotFoundWarnings(false);
    	
        $moduleloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Default',
            'basePath'  => dirname(__FILE__).'/modules/default'));
        return $moduleloader;
    }
    
    protected function _initDoctype()
    {
    	$this->bootstrap('view');
    	$view = $this->getResource('view');
    	$view->doctype('XHTML1_STRICT');
    }
    
    protected function _initRoutes()
    {
    	$this->bootstrap('FrontController');
    	$front = $this->getResource('FrontController');
    	$router = $front->getRouter();
    }
}
