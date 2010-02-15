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
        
        /* If the request is not for root, redirect to wiki page. */
        if ($name != '') {
            $this->_forward('view', 'wiki', 'default', array('name' => $name));
            return true;
        }

        /* Get featured announcement item. */
        $ndbt = new Default_Model_DbTable_News();
        $select = $this->db->select()
                ->from(array('mn' => 'moss_news'), array('newsId', 'userId', 'date', 'type', 'name', 'title', 'excerpt'))
                ->joinLeft(array('u' => 'users'), 'u.userId=mn.userId', array('uname' => 'u.name'))
                ->where("mn.featured='Y'")
                ->where("type='Announcement'")
                ->order('date DESC')
                ->limit(1);
        $featured = $this->db->query($select)->fetch();
        if (!empty($featured)) $featured['link'] = $ndbt->getLink($featured);
        $this->view->featuredAnnouncement = $featured;

        /* Get featured news item. */
        $select = $this->db->select()
                ->from(array('mn' => 'moss_news'), array('newsId', 'userId', 'date', 'type', 'name', 'title', 'excerpt'))
                ->joinLeft(array('u' => 'users'), 'u.userId=mn.userId', array('uname' => 'u.name'))
                ->where("mn.featured='Y'")
                ->where("type='News'")
                ->order('date DESC')
                ->limit(1);
        $featured = $this->db->query($select)->fetch();
        if (!empty($featured)) $featured['link'] = $ndbt->getLink($featured);
        $this->view->featuredNews = $featured;

        /* Display announcements under announcements tab in home screen. */
        $announcements = $ndbt->fetchAll(array("type='Announcement'"), 'date DESC', 10);
        $this->view->announcements = $announcements;
        
        /* Display news items under news tab in home screen. */
        $news = $ndbt->fetchAll(array("type='News'"), 'date DESC', 10);
        $this->view->news = $news;
    }
    
    public function error404Action()
    {
        $page = $this->_request->getParam('page');
        $this->view->name = $page;
        
        /* Get fulltext entry matches based on the page parameter requested. */
        $pagesModel = new Default_Model_Pages();
        $entries = $pagesModel->fetchFullTextMatches($page);
        $this->view->fullTextMatches = $entries;
    }
    
    public function nonExistentAction()
    {
        $page = $this->_request->getParam('page');
        $this->view->name = $page;
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
        
        /* Render index side bar with recent stuff. */
        if ($this->_request->getParam('action') == 'index' &&
            $this->_request->getParam('controller') == 'index' &&
            $this->_request->getParam('module') == 'default'
        ) {
            $this->_forward('sidebar-front');
            return true;
        }

        /* Render request specific sidebar. */
        $page = strtolower($this->_request->getParam('controller'));
        $path = $this->view->getScriptPaths();
        $path = $path[0];
        $fileName = "index/sidebar-{$page}.phtml";
        $file = $path.$fileName;
        if (file_exists($file)) $this->_forward("sidebar-{$page}");
    }
    
    public function sidebarUserAction()
    {
        $paidMemberTypes = array('Individual', 'Business', 'Privileged');
        if (in_array($this->user['memberType'], $paidMemberTypes)) {
            $user = new Default_Model_Users();
            $user->find($this->user['userId']);
            $this->view->isFeePending = $user->isFeePending();
        }
    }
    
    public function sidebarFrontAction()
    {
        /* Recently joined. */
        $query = $this->db->query(
            "SELECT userId, name, email FROM users "
          . "WHERE active='Y' "
          . "ORDER BY dateRegistered DESC "
          . "LIMIT 5");
        $this->view->recentlyJoined = $query->fetchAll();

        /* Recently updated pages. */
        $query = $this->db->query(
            "SELECT * FROM pages "
          . "GROUP BY pageId "
          . "ORDER BY dateModified DESC "
          . "LIMIT 5");
        $this->view->recentlyUpdatedPages = $query->fetchAll();
        $this->_helper->viewRenderer->setResponseSegment('sidebar');
    }
    
    public function deniedAction() {}
    
    public function debugAction()
    {
        Zend_Debug::dump($_SESSION);
        exit;
    }
}
