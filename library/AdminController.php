<?php

/**
 * Abstract Admin Controller.
 * 
 * This is the abstract administration controller which extends the abstract
 * Application Controller and provides functionality for website/application
 * administrators for management purposes.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon May 25, 2009 06:11 AM
 */

require_once 'ApplicationController.php';

abstract class AdminController extends ApplicationController
{
	public function preDispatch()
	{
    	parent::preDispatch();
    	
    	/* Check if the user is of class 'Administrator'. If not show
    	 * unauthorized view. */
    	if ($this->user['class'] != 'Administrator') {
    		$this->render('unauthorized');
    	}
	}
}
