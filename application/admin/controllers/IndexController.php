<?php

/**
 * Administrative Index Controller. This is the base action controller for
 * the admin module. It provides basic functionality and views for 
 * administrators.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Tue Jun 09, 2009 12:05 AM
 */

require_once 'AdminController.php';

class Admin_IndexController extends AdminController
{
	public function indexAction() {}
	
	public function sidebarAction()
	{
		$this->_helper->viewRenderer->setResponseSegment('sidebar');
	}
}
