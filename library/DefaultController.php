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

abstract class DefaultController extends Zend_Controller_Action
{
	public function preDispatch()
	{
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->view->headTitle()->setSeparator(' - ');
		$this->view->headTitle('MOSS', 'SET');
		
		/* Assign user session namespace to the layout view. */
		$userns = new Zend_Session_Namespace('user');
		$this->view->userns = $userns;
		
		/* Generate sidebar. */
        $this->_helper->layout()->sidebar = $this->view->render('index/sidebar.phtml');
	}
	
	public function postDispatch()
	{
		/* Set referer into user session namespace. */
        $userns = new Zend_Session_Namespace('user');
        $userns->requestUri = $this->_request->getRequestUri();
	}
}
