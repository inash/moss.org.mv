<?php

/**
 * Users administration controller.
 * 
 * This allows application administrators to administrate user related
 * functionality.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon Aug 24, 2009 05:11 AM
 */

class Admin_UsersController extends Pub_Controller_ApplicationAction
{
    public function preDispatch()
    {
        parent::preDispatch();
    }
    
    public function headerAction()
    {
        static $permission = 'view';
        
    	/* Get request variables. */
    	$params['filter']   = $this->_request->getParam('filter', null);
    	$params['criteria'] = $this->_request->getParam('criteria', null);
    	$params['state']    = $this->_request->getParam('state', null);
    	
    	/* Assign them to the view. */
    	$this->view->params = $params;
    	
    	/* Set filter array. */
        $filters = array(
            'userId' => 'Username',
            'name'   => 'Name',
            'email'  => 'Email');
        $this->view->filters = $filters;
        
        /* Set states array. */
        $states = array(
            'all'      => 'All',
            'inactive' => 'Inactive',
            'disabled' => 'Disabled',
            'fee'      => 'Fee Pending');
        $this->view->states = $states;
    }
    
    public function indexAction()
    {
        static $permission = 'view';
        
        /* Get and set default pagination params. */
        $filter   = $this->_request->getParam('filter', null);
        $criteria = $this->_request->getParam('criteria', null);
        $state    = $this->_request->getParam('state', 'all');
        $page     = $this->_request->getParam('page', 1);
        
        /* Get db resource. */
        $db = $this->getInvokeArg('bootstrap')->getResource('db');
        
        /* Build query based on the selected filter/state. */
        $select = $db->select()
            ->from(array('u' => 'users'),
                array('u.userId', 'u.name', 'u.dateRegistered'))
            ->order('name ASC');
        if ($criteria != '') $select->where("{$filter} LIKE '%{$criteria}%'");
        
        switch ($state) {
        	case 'fee':
        		$select->where(
                        "u.userId NOT IN ("
                      .     "SELECT userId FROM fees "
                      .     "WHERE forTheYear = year(curdate()) "
                      .     "GROUP BY userId)");
        		break;
        	case 'inactive':
        		$select->where("active='N'");
        		break;
        		
        	case 'disabled':
        		$select->where("disabled='Y'");
        		break;
        }
        
        /* Prepare paginator. */
        $paginator = Zend_Paginator::factory($select, 'DbSelect');
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(10);
        $this->view->paginator = $paginator;
        
        /* Prepare custom paginator parameters. */
        $params = array(
            'filter'   => $filter,
            'criteria' => $criteria,
            'state'    => $state,
            'action'   => null);
        $this->view->params = $params;
    }
    
    public function viewAction()
    {
    	$userId = $this->_request->getParam('userId');
    	$usersModel = new Default_Model_Users();
    	$user = $usersModel->find($userId);
    	$this->view->user = $user;
    }
}
