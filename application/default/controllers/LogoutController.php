<?php

/**
 * Logout Controller.
 * 
 * Destroys session and redirects the user to the home page.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun May 31, 2009 06:59 AM
 */

require_once 'ApplicationController.php';

class LogoutController extends ApplicationController
{
    public function indexAction()
    {
        session_destroy();
        $this->_redirect('/');
    }
}
