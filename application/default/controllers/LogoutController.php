<?php

/**
 * Logout Controller.
 * 
 * Destroys session and redirects the user to the home page.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun May 31, 2009 06:59 AM
 */

require_once 'DefaultController.php';

class LogoutController extends DefaultController
{
	public function preDispatch() {}
	
    public function indexAction()
    {
    	Zend_Session::destroy(true);
        session_destroy();
        $this->_redirect('/');
    }
}
