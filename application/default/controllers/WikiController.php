<?php

/**
 * Wiki Controller.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun May 31, 05:51 AM
 */

require_once 'ApplicationController.php';
require_once 'Pages.php';
require_once 'PageRevisions.php';

class WikiController extends ApplicationController
{
    public function indexAction()
    {
        
    }
    
    public function newAction()
    {
        /* Get the page name. */
        $pageName = $this->_request->getParam('page');
        
        /* Verify that the page does not already exists in the database. If
         * the page does exist, then show an error message indicating that the
         * page already exists and whether the user would like to view or
         * modify the page instead. */
        $pagesModel = new Pages();
        $page = $pagesModel->fetchRow(array('title' => $pageName));
        
        /* If page does not exist and the request method is not POST. */
        if (!$page && !$this->_request->isPost()) {
            $this->view->name = $pageName;
            return false;
        }
        
        /* If the request method is POST, process the input and create a new
         * page. */
        if ($this->_request->isPost()) {
            $params = $this->_request->getPost();
            
            /* Unset unnecessary fields. */
            unset($params['save']);
            
            /* Filter and CamelCase titles. */
            $title = trim(Zend_Filter::get($params['title'], 'Word_UnderscoreToCamelCase'));
            $body  = trim($params['body']);
            
            $userns = new Zend_Session_Namespace('user');
            
            /* Add page. */
            $pageId = $pagesModel->insert(array(
                'title'          => $title,
                'dateCreated'    => date('Y-m-d H:i:s'),
                'dateModified'   => date('Y-m-d H:i:s'),
                'pageRevisionId' => 0,
                'createdBy'      => $this->user['userId'],
                'modifiedBy'     => $this->user['userId'],
                'body'           => $body,
                'published'      => 'Published'));
            
            /* Add page revision. */
            $pageRevModel = new PageRevisions();
            $pageRevisionId = $pageRevModel->insert(array(
                'pageId'    => $pageId,
                'userId'    => $this->user['userId'],
                'timestamp' => date('Y-m-d H:i:s'),
                'summary'   => 'new',
                'body'      => $body));
            
            /* If success, redirect to Static page view action. Set flash
             * message to appear stating that the insertion succeeded. */
            $this->_helper->flashMessenger->addMessage(
                "New Page <b>{$title}</b> Successfully Created!");
            
            $this->_redirect("/{$title}");
        }
    }
    
    public function editAction()
    {
        $pageName = $this->_request->getParam('page');
        
        /* Get the page. */
        $pagesModel = new Pages();
        $page = $pagesModel->fetchRow("title='{$pageName}'");
        
        /* If the page does not exist, render non-existent page. */
        if (!$page) {
            $this->view->page = $pageName;
            $this->render('index/non-existent');
            return false;
        }
        
        $this->view->page = $page;
    }
    
    public function updateAction()
    {
        if (!$this->_request->isPost()) {
            echo 'invalid request';
            exit;
        }
        
        /* Get and prepare variables. */
        $params = $this->_request->getPost();
        $params['title']   = trim(Zend_Filter::get($params['title'], 'Word_UnderscoreToCamelCase'));
        $params['body']    = trim($params['body']);
        $params['summary'] = trim($params['summary']);
        unset($params['save']);
        
        /* Get page and verify if the page exists. */
        $pagesModel = new Pages();
        $page = $pagesModel->fetchRow("title='{$params['title']}'");
        
        /* If page does not exist, forward to non-existent view. */
        if (!$page) {
            $this->view->page = $params['title'];
            $this->render('index/non-existent');
            return false;
        }
        
        /* If there's no diff, alert and fail back to viewing the page. */
        if ($params['body'] == $page->body) {
            $this->_helper->flashMessenger->addMessage("No Change. Page Unmodified.");
            $this->_redirect("/{$page->title}");
            return true;
        }
        
        /* TODO: distinguish edit operation by title. eg: +rename, etc. */

        /* Update page revision by inserting a new record with the new diff. */
        $pageRevModel = new PageRevisions();
        $pageRevId    = $pageRevModel->insert(array(
            'pageId'    => $page->pageId,
            'userId'    => $this->user['userId'],
            'timestamp' => date('Y-m-d H:i:s'),
            'summary'   => $params['summary'],
            'body'      => $params['body']));
        
        /* Update page record with new data. */
        $page->pageRevisionId = $pageRevId;
        $page->dateModified   = date('Y-m-d H:i:s');
        $page->modifiedBy     = $this->user['userId'];
        $page->body = $params['body'];
        $page->save();
        
        /* Add flash message and redirect to view page. */
        $this->_helper->flashMessenger->addMessage("Page Successfully Updated!");
        $this->_redirect("/{$page->title}");
    }
    
    public function historyAction()
    {
        $pageName = $this->_request->getParam('page');
        
        /* Get the page and check if it exists or not. */
        $pagesModel = new Pages();
        $page = $pagesModel->fetchRow("title='{$pageName}'");
        
        if (!$page) {
            $this->view->page = $pageName;
            $this->render('index/non-existent');
            return false;
        }
        
        /* Assign page record to view. */
        $this->view->page = $page;
        
        /* Get history for the page. */
        $db = Zend_Registry::get('db');
        $query = $db->query(
            "SELECT pr.pageRevisionId, pr.userId, pr.timestamp, pr.summary, "
          . "u.name FROM page_revisions pr, users u "
          . "WHERE u.userId=pr.userId "
          . "AND pr.pageId='{$page->pageId}' "
          . "ORDER BY timestamp DESC");
        
        /* Assign history rows if any. */
        if ($query->rowCount() > 0) {
        	$this->view->history = $query->fetchAll();
        }
    }
    
    public function revisionAction()
    {
    	$pageName = $this->_request->getParam('page');
    	$pageRevisionId = $this->_request->getParam('pageRevisionId');
    	
    	/* Check if the page exists, otherwise render non-existent page */
    	$pagesModel = new Pages();
    	$page = $pagesModel->fetchRow("title='{$pageName}'");
    	
    	if (!$page) {
    		$this->view->page = $pageName;
    		$this->render('index/non-existent');
    		return false;
    	}
    	
    	/* Get revision and it's previous history. */
    	$db = Zend_Registry::get('db');
    	$query = $db->query(
            "SELECT * FROM page_revisions "
          . "WHERE pageRevisionId='{$pageRevisionId}'");
        $pageRevision = $query->fetch();

        $query = $db->query(
            "SELECT * FROM page_revisions "
          . "WHERE pageId='{$page->pageId}' "
          . "AND timestamp < '{$pageRevision['timestamp']}' "
          . "ORDER BY timestamp DESC "
          . "LIMIT 1");
        $prevRevision = $query->fetch();
        
        $this->view->page = $page;
        $this->view->pageRevision = $pageRevision;
        $this->view->prevRevision = $prevRevision;
    }
}
