<?php

/**
 * Abstract Default Controller.
 * 
 * This is the default website abstract controller which wraps some basic
 * functionality in the predispatch function.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon May 25, 2009 05:58 AM
 * @version $Id$
 */

abstract class DefaultController extends Zend_Controller_Action
{
	public function preDispatch()
	{
		$this->view->baseUrl = $this->_request->getBaseUrl();
	}
}
