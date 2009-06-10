<?php

/**
 * Abstract Application Controller.
 * 
 * This is the abstract application controller which extends the abstract
 * default controller and enables authenticated and application level
 * functionality for the users/members.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon May 25, 2009 06:11 AM
 * @version $Id$
 */

require_once 'DefaultController.php';

abstract class ApplicationController extends DefaultController
{
    public function preDispatch()
    {
        parent::preDispatch();
        
        /* Make sure the user is authenticated, if not, redirect to login
         * controller, authenticate user and return user back to where he/she
         * was heading.
         * 
         * Bypass auth check for certain module/actions. */
        $bypass = array(
          'wiki' => array('history', 'revision'));
        
        if (!$this->user['authenticated'] &&
        $this->_request->getControllerName() != 'login'
        && !(array_key_exists($this->_request->getControllerName(), $bypass)
            && in_array($this->_request->getActionName(), $bypass[$this->_request->getControllerName()]))) {
            $this->_redirect('/login');
            return false;
        }
    }
}
