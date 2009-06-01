<?php

/**
 * Index Controller
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
    
    /**
     * Shows create new Wiki Page.
     */
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
            
            /* Put empty file in /tmp to initially diff new file. */
            $fileEmpty = '/tmp/'.Zend_Session::getId().'_empty';
            file_put_contents($fileEmpty, '');
            
            /* Put current new page's content to /tmp. */
            $filePage = '/tmp/'.Zend_Session::getId().'_wiki_'.$title;
            file_put_contents($filePage, $body);
            
            /* Get a diff between the new file and the current new page. */
            $diff = shell_exec("diff --normal -N {$fileEmpty} {$filePage}");
            
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
                'diff'      => $diff));
            
            /* If success, redirect to Static page view action. Set flash
             * message to appear stating that the insertion succeeded. */
            $this->_helper->flashMessenger->addMessage(
                "New Page <b>{$title}</b> Successfully Created!");
            
            $this->_redirect("/{$title}");
        }
    }
}
