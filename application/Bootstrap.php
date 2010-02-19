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
    	$view->headTitle('MOSS, Maldives Open Source Society');
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

        /* Add reset password route. */
        $router->addRoute('reset',
            new Zend_Controller_Router_Route_Static('reset', array(
                'module'     => 'default',
                'controller' => 'login',
                'action'     => 'reset')));

        /* Add forced change password route. */
        $router->addRoute('change',
            new Zend_Controller_Router_Route_Static('change', array(
                'module'     => 'default',
                'controller' => 'login',
                'action'     => 'change')));

        /* Add route to view announcement items. */
        $router->addRoute('announcementView',
            new Zend_Controller_Router_Route('announcement/:id', array(
                'module'     => 'default',
                'controller' => 'news',
                'action'     => 'view',
                'type'       => 'announcement')));
        
        /* Add route to view news items. */
        $router->addRoute('newsView',
            new Zend_Controller_Router_Route('news/:year/:month/:name', array(
                'module'     => 'default',
                'controller' => 'news',
                'action'     => 'view',
                'type'       => 'news')));

        /* More announcements listings. */
        $router->addRoute('announcements', new Zend_Controller_Router_Route(
            'announcements/:page',
            array(
                'module'     => 'default',
                'controller' => 'news',
                'action'     => 'index',
                'type'       => 'announcements',
                'page'       => 1)));

        /* More news listings. */
        $router->addRoute('news', new Zend_Controller_Router_Route(
            'news/:page',
            array(
                'module'     => 'default',
                'controller' => 'news',
                'action'     => 'index',
                'type'       => 'news',
                'page'       => 1)));
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
