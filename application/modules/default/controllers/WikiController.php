<?php

/**
 * Wiki Controller.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun May 31, 05:51 AM
 */

class WikiController extends Pub_Controller_Action
{
    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->_request->getActionName();
        $restricted = array('new', 'edit', 'update');
        $userns = new Zend_Session_Namespace('user');
        if (in_array($action, $restricted) && $userns->authenticated == false) {
            $this->_redirect('/login');
        }
    }

    public function viewAction()
    {
        /* Explode the name parameter and extract operational fields values. */
        $nameParts = explode('/', $this->_request->getParam('name'));
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

                    /* Display 404 page for unauthorised users and the default
                     * non-existent page for authorised users who has 'add'
                     * permission for the wiki module. */
                    if ($this->user['authenticated'] && $this->user['primaryGroup'] == 'Administrator') {
                        $this->_forward('non-existent', 'index', 'default', array('page' => $pageName));
                    } else {
                        $this->_forward('error404', 'index', 'default', array('page' => $pageName));
                    }
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
    }
    
    public function newAction()
    {
        /* Get the page name. */
        $pageName = $this->_request->getParam('page');
        
        /* Verify that the page does not already exists in the database. If
         * the page does exist, then show an error message indicating that the
         * page already exists and whether the user would like to view or
         * modify the page instead. */
        $pagesModel = new Default_Model_DbTable_Pages();
        $page = $pagesModel->fetchRow(array('name' => $pageName));
        
        /* If page does not exist and the request method is not POST. */
        if (!$page && !$this->_request->isPost()) {
            $this->view->name = $pageName;
            return false;
        }
        
        /* If the request method is POST, process the input and create a new
         * page. Otherwise return. */
        if (!$this->_request->isPost()) {
            return true;
        }
        
        /* Determing action, either to preview or continue saving the new page. */
        $action = $this->_request->getPost('preview');
        if ($action == 'Preview') {
            // TODO: show preview and return true.
        }
        
        /* Begin transaction. */
        $db = $pagesModel->getAdapter();
        $db->beginTransaction();
        
        try {
            $params = $this->_request->getPost();
            
            /* Unset unnecessary fields. */
            unset($params['preview'], $params['save']);
            
            /* Filter and CamelCase titles. */
            $name  = trim(Zend_Filter::get($params['name'], 'Word_UnderscoreToCamelCase'));
            $title = trim($params['title']);
            $body  = trim($params['body']);
            
            $userns = new Zend_Session_Namespace('user');
            
            /* Add page. */
            $pageId = $pagesModel->insert(array(
                'name'           => $name,
                'title'          => $title,
                'dateCreated'    => date('Y-m-d H:i:s'),
                'dateModified'   => date('Y-m-d H:i:s'),
                'pageRevisionId' => 0,
                'createdBy'      => $this->user['userId'],
                'modifiedBy'     => $this->user['userId'],
                'body'           => $body,
                'published'      => 'Published'));
            
            /* Add page revision. */
            /* disabled temporarily. */
            $pageRevModel = new Default_Model_DbTable_PageRevisions();
            $pageRevisionId = $pageRevModel->insert(array(
                'pageId'    => $pageId,
                'userId'    => $this->user['userId'],
                'timestamp' => date('Y-m-d H:i:s'),
                'summary'   => 'new',
                'body'      => $body));
            
            /* If success, redirect to Static page view action. Set flash
             * message to appear stating that the insertion succeeded. */
            $this->_helper->flashMessenger->addMessage(
                "New Page <b>{$name}</b> Successfully Created!");
            
            $db->commit();
            $this->_redirect("/{$name}");
            
        } catch (Exception $e) {
            $db->rollBack();
            throw new Exception($e->getMessage());
        }
    }
    
    public function editAction()
    {
        $pageName = $this->_request->getParam('page');
        
        /* Get the page. */
        $pagesModel = new Default_Model_DbTable_Pages();
        $page = $pagesModel->fetchRow("name='{$pageName}'");
        
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
        $params['oldName'] = trim(Zend_Filter::get($params['oldName'], 'Word_UnderscoreToCamelCase'));
        $params['name']    = trim(Zend_Filter::get($params['name'], 'Word_UnderscoreToCamelCase'));
        $params['title']   = trim($params['title']);
        $params['body']    = trim($params['body']);
        $params['summary'] = trim($params['summary']);
        unset($params['preview'], $params['save']);
        
        /* Get page and verify if the page exists. */
        $pagesModel = new Default_Model_DbTable_Pages();
        $page = $pagesModel->fetchRow("name='{$params['oldName']}'");
        
        /* If page does not exist, forward to non-existent view. */
        if (!$page) $this->_forward("/{$params['name']}");
        
        /* If there's no diff, alert and fail back to viewing the page. */
        if ($params['body'] == $page->body &&
        $params['title'] == $page->title &&
        $params['name'] == $page->name) {
            $this->_helper->flashMessenger->addMessage("No Change. Page Unmodified.");
            $this->_redirect("/{$page->name}");
            return true;
        }
        
        /* TODO: distinguish edit operation by title. eg: +rename, etc. */

        /* Begin transaction. */
        $db = $pagesModel->getAdapter();
        $db->beginTransaction();
        
        try {
            /* Update page revision by inserting a new record with the new diff. */
            /* disabled temporarily. */
            $pageRevModel = new Default_Model_DbTable_PageRevisions();
            $pageRevId    = $pageRevModel->insert(array(
                'pageId'    => $page->pageId,
                'userId'    => $this->user['userId'],
                'timestamp' => date('Y-m-d H:i:s'),
                'summary'   => $params['summary'],
                'body'      => $params['body']));

            /* Update page record with new data. */
            $page->name           = $params['name'];
            $page->title          = $params['title'];
            $page->body           = $params['body'];
            $page->pageRevisionId = $pageRevId;
            $page->dateModified   = date('Y-m-d H:i:s');
            $page->modifiedBy     = $this->user['userId'];
            $page->save();

            /* Add log entry regarding new page revision. */
            $this->log->insert(array(
                'entity'    => 'page_revisions',
                'entityId'  => $page->pageId,
                'timestamp' => date('Y-m-d H:i:s'),
                'code'      => 'new',
                'message'   => "new page revision created for page [{$page->pageId}] {$page->name}",
                'userId'    => $this->user['userId']));

            /* Notify moderators. */
            if (APP_ENV == 'production') {
                $emailBody = "http://{$_SERVER['SERVER_NAME']}{$this->view->baseUrl}/{$page->name}";
                   $mail = new Zend_Mail();
                   $mail->addTo('inash@leptone.com');
                   $mail->setSubject('Wiki Update Notification.');
                   $mail->setBodyText($emailBody);
                   $mail->setFrom('noreply@moss.org.mv', 'MOSS');
                   $mail->send();
            }

            /* Add flash message and redirect to view page. */
            $this->_helper->flashMessenger->addMessage("Page Successfully Updated!");
            $db->commit();
            $this->_redirect("/{$page->name}");

        } catch (Exception $e) {
            $db->rollBack();
            throw new Exception($e->getMessage());
        }
    }
    
    public function historyAction()
    {
        $pageName = $this->_request->getParam('page');
        
        /* Get the page and check if it exists or not. */
        $pagesModel = new Default_Model_DbTable_Pages();
        $page = $pagesModel->fetchRow("name='{$pageName}'");
        
        if (!$page) {
            $this->view->page = $pageName;
            $this->render('index/non-existent');
            return false;
        }
        
        /* Assign page record to view. */
        $this->view->page = $page;
        
        /* Get history for the page. */
        $db = $pagesModel->getAdapter();
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
        $pagesModel = new Default_Model_DbTable_Pages();
        $page = $pagesModel->fetchRow("name='{$pageName}'");

        if (!$page) {
            $this->view->page = $pageName;
            $this->render('index/non-existent');
            return false;
        }

        /* Get revision and it's previous history. */
        $db = $pagesModel->getAdapter();
        $query = $db->query(
            "SELECT * FROM page_revisions "
          . "WHERE pageRevisionId='{$pageRevisionId}' "
          . "AND pageId='{$page->pageId}'");
        $pageRevision = $query->fetch();

        // TODO: Assert if pageRevision does not exist for the page.

        /* If request method is post, it means to revert to the requested
         * revision. Revert to the requested revision and display the history
         * page. */
        if ($this->_request->isPost()) {
            $page->pageRevisionId = $pageRevision['pageRevisionId'];
            $page->body = $pageRevision['body'];
            $page->modifiedBy = $pageRevision['userId'];
            $page->save();
            $this->_redirect("/{$page->name}/history");
            return true;
        }

        $query = $db->query(
            "SELECT * FROM page_revisions "
          . "WHERE pageId='{$page->pageId}' "
          . "AND timestamp < '{$pageRevision['timestamp']}' "
          . "ORDER BY timestamp DESC "
          . "LIMIT 1");
        $prevRevision = $query->fetch();
        
        $this->view->page = $page;

        require_once 'simplediff.php';
        $diff = htmlDiff($prevRevision['body'], $pageRevision['body']);
        $diff = str_replace("\n", "<br />", $diff);
        $this->view->diff = $diff;
    }
}
