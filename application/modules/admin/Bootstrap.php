<?php

/**
 * The admin modules bootstrap file. This tells Zend_Application that here
 * lies the admin module and all it's sub components.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Thu Aug 27, 2009 05:22 AM
 */

class Admin_Bootstrap extends Zend_Application_Module_Bootstrap
{
    protected function _initAutoloader()
    {
        $moduleloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Admin',
            'basePath'  => APPLICATION_PATH.'modules/admin'));
        
        return $moduleloader;
    }
}
