<?php

/**
 * Index Controller
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon May 25, 2009 05:09 AM
 */

require_once 'models/Pages.php';

class IndexController extends DefaultController
{
    public function indexAction()
    {
        /* Router to static action if the module is default but the controller
         * is not defined, supposedly a static page name, etc. */
        if ($this->_request->getParam('module') == 'default' &&
        $this->_request->getParam('controller') != 'index') {
        	$this->_forward('static');
        	return false;
        }
        
        /* Else if the request is for the default action, meaning the Index
         * pages. */
        else {
        	$this->_redirect('/Index');
        }
    }
    
    /**
     * Renders the default sidebar.
     * 
     * This method is intelligent when you have a file same as the name of the
     * requested page or action in the format: sidebar.pagename.phtml.
     */
    public function sidebarAction()
    {
    	echo $this->render();
    }
    
    /**
     * Static page handler. If a requested controller doesn't exist, the
     * application will route all the requests to the default controller's
     * index action, which in return checks if the request is explicitly for
     * the index controller. If not forward the request to this static action
     * handler.
     */
    public function staticAction()
    {
    	/* Get page name. Format and clean title. */
    	$name = trim($this->_request->getPathInfo(), '/');
    	
    	/* Search the pages table for the page. */
    	$pagesModel = new Pages();
    	$page = $pagesModel->fetchRow(array('title' => $name));
    	
    	/* If page does not exist, show page does not exist view and get a list
    	 * of probable page matches. This page should display a link to create
    	 * the page. Pages that does not exist should be anchored in red or
    	 * brown. */
    	if (!$page) {
    		$this->view->name = $name;
    		$this->render('non-existent');
    	}
    }
}
