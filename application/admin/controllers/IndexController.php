<?php

/**
 * Administrative Index Controller. This is the base action controller for
 * the admin module. It provides basic functionality and views for 
 * administrators.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Tue Jun 09, 2009 12:05 AM
 */

require_once 'AdminController.php';
require_once 'Zend/Db/Select.php';
require_once 'Zend/Paginator.php';

class Admin_IndexController extends AdminController
{
    public function indexAction() {}
    
    public function sidebarAction()
    {
        $this->_helper->viewRenderer->setResponseSegment('sidebar');
    }
    
    public function activityAction()
    {
        $dbSelect = new Zend_Db_Select(Zend_Registry::get('db'));
        $dbSelect->from('logs')
            ->order('timestamp DESC');
        $paginator = Zend_Paginator::factory($dbSelect, 'DbSelect');
        $paginator->setCurrentPageNumber(1);
        $paginator->setItemCountPerPage(10);
        $this->view->paginator = $paginator;
    }
}
