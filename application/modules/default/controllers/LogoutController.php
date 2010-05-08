<?php

/**
 * Logout Controller.
 * 
 * Destroys session and redirects the user to the home page.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun May 31, 2009 06:59 AM
 */

class LogoutController extends Pub_Controller_Action
{
    public function indexAction()
    {
    	Zend_Session::destroy(true);
        $this->_redirect('/');
    }
}
