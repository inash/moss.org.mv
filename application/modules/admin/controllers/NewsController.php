<?php

/**
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Fri 25 Dec, 2009 12:09 PM
 */

class Admin_NewsController extends Pub_Controller_Action
{
    public function headerAction()
    {
        static $permission = 'view';
        
        /* Get request variables. */
        $params['filter']   = $this->_request->getParam('filter', null);
        $params['criteria'] = $this->_request->getParam('criteria', null);
        
        /* Assign them to the view. */
        $this->view->params = $params;
        
        /* Set filter type array. */
        $filters = array(
            'News' => 'News',
            'Announcement' => 'Announcement');
        $this->view->filters = $filters;
    }
    
    public function indexAction()
    {
        /* Prepare header params. */
        $params = array(
            'filter'   => $this->_request->getParam('filter', 'News'),
            'criteria' => $this->_request->getParam('criteria'));
        $this->view->params = $params;

        /* Get page. */
        $page = $this->_request->getParam('page');

        /* Get recent news based on type and criteria. */
        $select = $this->db->select();
        $select->from('moss_news')
            ->where("type='{$params['filter']}'")
            ->order('date DESC');

        /* Set criteria. */
        if ($params['criteria'] != '') {
            $select->where("name LIKE '%{$params['criteria']}%'")
                ->orWhere("title LIKE '%{$params['criteria']}%'")
                ->orWhere("content LIKE '%{$params['criteria']}%'");
        }
        
        /* Prepare paginator. */
        $adapter = new Zend_Paginator_Adapter_DbSelect($select);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setCurrentPageNumber($page);
        $this->view->paginator = $paginator;
    }

    public function editAction()
    {
        static $permission = 'edit';

        $newsId = $this->_request->getParam('newsId');

        /* Get the requested news item. */
        $newsDbTable = new Default_Model_DbTable_News();
        $news = $newsDbTable->find($newsId)->current();
        
        /* Redirect to non-existent if the news item does not exist. */
        if (!$news) {
            $this->_forward('error404', 'index', 'default');
            return false;
        }

        /* Prepare form and set it in the view. */
        $form = new Admin_Form_News();
        $newsArr = $news->toArray();
        $newsArr['n_content'] = $newsArr['content'];
        unset($newsArr['content']);
        $form->populate($newsArr);
        $this->view->form = $form;
    }
}
