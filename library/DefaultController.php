<?php

/**
 * Abstract Default Controller.
 * 
 * This is the default website abstract controller which wraps some basic
 * functionality in the predispatch function.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon May 25, 2009 05:58 AM
 */

require_once 'Logs.php';

abstract class DefaultController extends Zend_Controller_Action
{
	protected $config;
	
	/**
	 * Holds a reference to the Logs model. Which allows to quickly insert
	 * log entries to the logs database table.
	 * 
	 * @var Zend_Db_Table_Abstract
	 */
	protected $log;
	
	/**
	 * Holds a reference to the user session namespace if the user is
	 * authenticated.
	 * 
	 * Application protected property which holds basic information of the
     * authenticated user. This could be used to easily access those information
     * from within any sub class of DefaultController.
	 * 
	 * @var Zend_Session_Namespace
	 */
	 protected $user;
	
	public function preDispatch()
	{
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->view->headTitle()->setSeparator(' - ');
		$this->view->headTitle('MOSS', 'SET');
		
		/* Set config to local protected $config. */
		$this->config = Zend_Registry::get('config');
		
		/* Set logs protected property with an instance of the Logs model. */
		$this->log = new Logs();
		
		/* Assign user session namespace as an array to the protected
		 * DefaultController property $user, which is described at this class's
		 * property declaration area . */
		$userns = new Zend_Session_Namespace('user');
		$this->user['authenticated'] = false;
		
		if ($userns->authenticated == true) {
			$this->user['authenticated'] = true;
			$this->user['userId'] = $userns->userId;
			$this->user['email']  = $userns->email;
			$this->user['name']   = $userns->name;
			$this->user['class']  = $userns->class;
		}
		
		/* Assign user array to the layout view as well. */
		$this->view->user = $this->user;
		
        /* Check if the flash messenger has any messages. Process them. */
        if ($this->_helper->flashMessenger->hasMessages()) {
        	$this->view->messages = $this->_helper->flashMessenger->getMessages();
        }
	}
	
	public function postDispatch()
	{
		/* Set referer into user session namespace. */
        $userns = new Zend_Session_Namespace('user');
        $userns->requestUri = $this->_request->getRequestUri();
	}
}
