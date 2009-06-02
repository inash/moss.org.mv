<?php

/**
 * Index Controller.
 * 
 * This controller acts as a default entry point for the website and
 * application. It does this by trying to determine unrouted requests to it's
 * indexAction and then acting as the centerpoint for the WikiController.
 * 
 * All unrouted requests are assumed to be a Wiki page, be it whether the page
 * exists or not. And provides functionality to view/edit/create Wiki Pages.
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
            $operation = $nameParts[1];
        }
        
        /* Prepare extra parameters. */
        $params = $nameParts;
        unset($params[0], $params[1]);
        
        /* Set $pageName. */
        $pageName = $nameParts[0];
        $pageName = ucwords($pageName);

        /* Search the pages table for the page. */
        $pagesModel = new Pages();
        $page = $pagesModel->fetchRow("title='{$pageName}'");
        
        /* If the page does not exist, do one of the following depending on the
         * 1 option available.
         * 
         * Either show the non-existent page (which is done by default and if a
         * valid operation is not specified for this scenario) or forward to
         * creating the page anew. */
        if (!$page) {
            switch ($operation) {
                case 'new':
                    $this->_forward('new', 'wiki', 'default', array('page' => $pageName));
                    break;
                
                default:
                    $this->view->name = $pageName;
                    $this->render('non-existent');
            }
            
        /* If the page does exist, do so below. By default it just displays the
         * page. If the valid operations (edit) are provided, those are
         * forwarded to their respective action handlers in the WikiController. */
        } else {
            switch ($operation) {
                case 'edit':
                    $this->_forward('edit', 'wiki', 'default', array('page' => $pageName));
                    break;
                
                case 'history':
                    $this->_forward('history', 'wiki', 'default', array('page' => $pageName));
                    break;
                    
                case 'revision':
                    $this->_forward('revision', 'wiki', 'default', array(
                        'page' => $pageName,
                        'pageRevisionId' => $params[2]));
                    break;
                
                /* Generic viewing of the page. */
                default:
                    $this->view->page = $page;
            }
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
    	// TODO: complete auto sidebar feature.
        echo $this->render();
    }
}
