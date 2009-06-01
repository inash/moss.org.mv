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
	protected $user;
	
	public function preDispatch()
	{
		parent::preDispatch();
		
		/* Make sure the user is authenticated, if not, redirect to login
		 * controller, authenticate user and return user back to where he/she
		 * was heading. */
		$userns  = new Zend_Session_Namespace('user');
		if (!$userns->authenticated &&
		$this->_request->getControllerName() != 'login') {
			$this->_redirect('/login');
			return false;
		}
		
		/* Do no process below if user is not authenticated. */
		if ($userns->authenticated == false) return false;
		
		/* Set ApplicationController protected environment variables. */
		$this->user['userId'] = $userns->userId;
		$this->user['email']  = $userns->email;
		$this->user['name']   = $userns->name;
	}
}
