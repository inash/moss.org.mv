<?php

/**
 * Profile Controller. Shows user profile and enables authenticated user to
 * manage their profile settings.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun Jun 7, 2009 07:28 AM
 */

require_once 'ApplicationController.php';
require_once 'Users.php';

class ProfileController extends DefaultController
{
    public function indexAction()
    {
        $params = $this->_request->getParams();
        $this->view->profile = $params;
    }
    
    public function feedAction()
    {
    	//
    }
}
