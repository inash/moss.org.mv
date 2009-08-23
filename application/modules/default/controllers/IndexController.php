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

class IndexController extends Pub_Controller_Action
{
    /**
     * Static page handler. If a requested controller doesn't exist, the
     * application will route all the requests to the default controller's
     * index action, which renders wiki pages.
     * 
     * First check if a user exists with the provided parameter. If the user
     * does not exist, proceed with fetching the wiki page. If the user exists,
     * display the user profile page view.
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
        $paramsN = $nameParts;
        unset($paramsN[0], $paramsN[1]);
        
        /* Reindex $params which is used later below. */
        foreach ($paramsN as $val) $params[] = $val;
        
        /* Check if a user profile with the id that matches the provided
         * parameter exists in the users table. If the user does not exist, 
         * proceed in displaying the wiki page below. */
        $usersModel = new Default_Model_DbTable_Users();
        $user = $usersModel->find($nameParts[0])->current();
        if ($user) {
            if (!$operation) $operation = 'index';
            $this->_forward($operation, 'profile', 'default');
            return true;
        }
        
        /* Set $pageName. */
        $pageName = $nameParts[0];
        $pageName = ucwords($pageName);

        /* Search the pages table for the page. */
        $pagesModel = new Default_Model_DbTable_Pages();
        $page = $pagesModel->fetchRow("name='{$pageName}'");
        
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
                    $this->_forward('non-existent');
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
                        'pageRevisionId' => $params[0]));
                    break;
                
                /* Generic viewing of the page. */
                default:
                    $this->view->page = $page;
                    
                    /* Render page specific sidebar. */
                    $paths = $this->view->getScriptPaths();
                    $path = $paths[0];
                    $file = strtolower($pageName);
            }
        }
        
        //$this->_helper->actionStack('sidebar', 'index', 'default');
    }
    
    public function nonExistentAction()
    {
        //
    }
    
    /**
     * Renders the default sidebar.
     * 
     * This method is intelligent when you have a file same as the name of the
     * requested page or action in the format: sidebar-pagename.phtml.
     */
    public function sidebarAction()
    {
        $this->_helper->viewRenderer->setResponseSegment('sidebar');
        // TODO: complete auto sidebar feature.
        
        /* Render request specific sidebar. */
        $page = strtolower($this->_request->getParam('controller'));
        $path = $this->view->getScriptPaths();
        $path = $path[0];
        $fileName = "index/sidebar-{$page}.phtml";
        $file = $path.$fileName;
        if (file_exists($file)) $this->_forward("sidebar-{$page}");
    }
    
    public function sidebarFrontAction()
    {
//    	$this->_helper->viewRenderer->setNoRender();
        $query = $this->db->query(
            "SELECT userId, name, email FROM users "
          . "WHERE active='Y' "
          . "ORDER BY dateRegistered DESC "
          . "LIMIT 5");
        $this->view->recentlyJoined = $query->fetchAll();
        $this->_helper->viewRenderer->setResponseSegment('sidebar');
    }
    
    public function debugAction()
    {
        Zend_Debug::dump($_SESSION);
        exit;
    }
}
