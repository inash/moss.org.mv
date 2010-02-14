<?php

/**
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sat Aug 22, 2009 09:17 AM
 */

class Admin_ActivityController extends Pub_Controller_Action
{
    public function indexAction()
    {
    	$page = $this->_request->getParam('page');
    	if (null === $page) $page = 1;
    	
        $bootstrap = $this->getInvokeArg('bootstrap');
        $db = $bootstrap->getResource('db');
        $dbSelect = $db->select()
            ->from(array('l' => 'logs'))
            ->joinLeft(array('u' => 'users'), 'u.userId=l.userId', array('uname' => 'u.name'))
            ->order('timestamp DESC');
        
        $paginator = Zend_Paginator::factory($dbSelect, 'DbSelect');
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(20);
        $this->view->paginator = $paginator;
    }
}
