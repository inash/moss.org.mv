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
    	$autoloader->registerNamespace('Pub_');
    	
    	$moduleloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Default',
            'basePath'  => APPLICATION_PATH.'modules/default'));
        
        return $moduleloader;
    }
    
    protected function _initDoctype()
    {
    	$this->bootstrap('view');
    	$this->bootstrap('frontController');
    	$view = $this->getResource('view');
    	$frontController = $this->getResource('frontController');
    	$view->doctype('XHTML1_STRICT');
    	$view->headScript()->appendFile($frontController->getBaseUrl().'/scripts/prototype.js');
    	$view->headScript()->appendScript("var baseUrl = '{$frontController->getBaseUrl()}';");
    }
    
    protected function _initHeadTitle()
    {
    	$view = $this->getResource('view');
    	$view->headTitle()->setSeparator(' - ');
    	$view->headTitle('MOSS, Maldivess Open Source Society');
    }
    
    protected function _initRoutes()
    {
    	$this->bootstrap('frontController');
    	$front  = $this->getResource('frontController');
    	$router = $front->getRouter();
    	
    	/* Add the default registration activation route. */
        $router->addRoute('activate',
            new Zend_Controller_Router_Route('activate/:hash', array(
                'module'     => 'default',
                'controller' => 'register',
                'action'     => 'activate')));
    }
    
    protected function _initSessionUser()
    {
        $userns = new Zend_Session_Namespace('user');
        return $userns;
    }
    
    protected function _initSidebar()
    {
        $fc = Zend_Controller_Front::getInstance();
        $fc->registerPlugin(new Zend_Controller_Plugin_ActionStack());
        $as = $fc->getPlugin('Zend_Controller_Plugin_ActionStack');
        $as->pushStack(new Zend_Controller_Request_Simple('sidebar', 'index', 'default'));
    }
}
