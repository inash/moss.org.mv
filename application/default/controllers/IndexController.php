<?php

/**
 * Index Controller
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon May 25, 2009 05:09 AM
 * @version $Id$
 */

class IndexController extends DefaultController
{
	public function indexAction()
	{
		echo 'hi';
		$this->_helper->viewRenderer->setNoRender();
		$this->view->headTitle('Hello');
	}
}
