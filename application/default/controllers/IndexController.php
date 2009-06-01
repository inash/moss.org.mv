<?php

/**
 * Index Controller
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon May 25, 2009 05:09 AM
 */

require_once 'DefaultController.php';
require_once 'models/Pages.php';

class IndexController extends DefaultController
{
	/**
     * Static page handler. If a requested controller doesn't exist, the
     * application will route all the requests to the default controller's
     * index action, which renders wiki pages.
     */
    public function indexAction()
    {
        /* Get page name. Format and clean title. */
        $name = trim($this->_request->getPathInfo(), '/');
        
        /* If the request is for root, redirecto to /Front. */
        if ($name == '') $this->_redirect('/Front');
        
        /* Explode the name parameter and extract operational fields values. */
        $nameParts = explode('/', $name);
        $operation = false;
        if (count($nameParts) > 1) {
            $operation = array_pop($nameParts);
        }
        
        /* Set $pageName. */
        $pageName = $nameParts[0];
        $pageName = ucwords($pageName);

        /* Search the pages table for the page. */
        $pagesModel = new Pages();
        $page = $pagesModel->fetchRow("title='{$pageName}'");
        
        /* If page does not exist, show page does not exist view and get a list
         * of probable page matches. This page should display a link to create
         * the page. Pages that does not exist should be anchored in red or
         * brown. This is given that $operation = false. */
        if (!$page && $operation == false) {
            $this->view->name = $pageName;
            $this->render('non-existent');
        }

        /* If the page did not exist and there is an operation specified, such
         * as `new`: to create the non-existent page. */
        if (!$page && $operation != false) {
            $this->_forward('new', 'wiki', 'default', array('page' => $pageName));
        }
        
        /* Display the page below. */
        $this->view->page = $page;
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
     * Catch operational functionality for static pages here and forward them
     * to their respective ApplicationController actions.
     */
    public function __call($method, $params)
    {
        /* Disable view renderer. */
        $this->_helper->viewRenderer->setNoRender();
        
        /* If the requested operation is to create a new Wiki Page. */
        if ($method == 'newAction') {
            $pageName = trim($this->_request->getPathInfo(), 'new');
            $pageName = trim($pageName, '/');
            
            /* Forward request to wiki/new. */
            $this->_forward('new', 'wiki', 'default', array('page' => $pageName));
        }
    }
}
