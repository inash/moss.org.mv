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
    private $udbt;
    
    public function init()
    {
        $this->udbt = new Default_Model_DbTable_Users();
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
            'email'  => 'Email',
            'primaryGroup' => 'Primary Group');
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
                array('u.userId', 'u.name', 'u.dateLastLogin'))
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
        $paginator->setItemCountPerPage(20);
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
        static $permission = 'view';
        
        /* Get userId. */
        $userId = $this->_request->getParam('userId');
        $usersModel = new Default_Model_Users();

        /* Find user and set to the view. */
        $user = $usersModel->find($userId);
        $this->view->user = $user;
    }
    
    public function editAction()
    {
        static $permission = 'edit';
        
        /* Get userId parameter. */
        $userId = $this->_request->getParam('userId');
        $usersModel = new Default_Model_Users();
        $user = $usersModel->find($userId);
        $this->view->user = $user;
        
        /* Throw exception if the user does not exist. */
        if (!$user) {
            throw new Exception("The requested user to edit does not exist.");
            return false;
        }
        
        /* Initialize the User form, populate with the user information and
         * set it to the view if the request method is not post. */
        $form = new Admin_Form_User();
        
        /* Remove workflow action required fields from general edit. */
        $form->removeElement('memberType');
        
        if (!$this->_request->isPost()) {
            $form->populate($user->toArray());
            $this->view->form = $form;
            return true;
        }
        
        /* Get post variables. */
        $params = $this->_request->getPost();
        unset($params['update']);
        
        /* Validate the form using the posted variables. */
        if (!$form->isValid($params)) {
            $this->view->form = $form;
            $this->render('edit');
            return false;
        }
        
        /* Prepare an array of modified fields. */
        $changed = array();
        foreach ($params as $key => $val) {
            $method = 'get'.ucfirst($key);
            if ($user->$method() != $val) $changed[] = $key;
        }
        
        /* If there are no field changes, return to the view action displaying
         * a message that the user record was not modified. */
        if (count($changed) == 0) {
            $this->_helper->flashMessenger->addMessage("No changes recorded.");
            $this->_redirect("admin/users/view/userId/{$userId}");
        }
        
        /* If all's well, set the new variables to the user record and save
         * them. */
        $user->setOptions($params);
        $user->save();
        
        /* Enter log entry about the edit. */
        $this->log->insert(array(
            'entity'    => 'users',
            'entityId'  => $user->getUserId(),
            'code'      => 'edit',
            'message'   => "modified fields: " . join(', ', $changed),
            'timestamp' => date('Y-m-d H:i:s'),
            'userId'    => $this->user['userId']));
        
        /* Add message about the change and redirect to the view action. */
        $this->_helper->flashMessenger->addMessage("User record modified successfully.");
        $this->_redirect("admin/users/view/userId/{$userId}");
    }
    
    public function subscriptionAction()
    {
        static $permission = 'subscription';
        
        /* Get userId parameter. */
        $userId = $this->_request->getParam('userId');
        $usersModel = new Default_Model_Users();
        $user = $usersModel->find($userId);
        $this->view->user = $user;
        
        /* Get form and set to the view. */
        $form = new Admin_Form_Subscription();
        $form->getElement('userId')->setValue($user->getUserId());
        
        /* Display the memberType field in the form if the user's memberType
         * is User/Student. */
        if (!in_array($user->getMemberType(), array('User', 'Student'))) {
            $form->removeElement('memberType');
        }
        
        /* Return and render the feePayment view if request method is get. */
        if (!$this->_request->isPost()) {
            $this->view->form = $form;
            return true;
        }
        
        /* Process the post request. */
        $params = $this->_request->getPost();
        unset($params['add']);
        
        /* Check if the user has paid for the current year. If so, display
         * message and redirect to viewing user. */
        if (!$user->isFeePending()) {
            $this->_helper->flashMessenger->addMessage(
                "The user has already paid Fee for the current year.");
            $this->_redirect("admin/users/view/userId/{$userId}");
        }
        
        /* If memerbType is set and different from the user's memberType, then
         * change the user's subscription memberType and enter fee. */
        if (isset($params['memberType']) && $user->getMemberType() != $params['memberType']) {
            $oldMemberType = $user->getMemberType();
            $user->setMemberType($params['memberType']);
            $user->save();
            
            /* Add log entry. */
            $this->log->insert(array(
                'entity'    => 'users',
                'entityId'  => $userId,
                'code'      => 'subscription change',
                'message'   => "memberType changed from {$oldMemberType} to {$params['memberType']}",
                'timestamp' => date('Y-m-d H:i:s'),
                'userId'    => $this->user['userId']));
        }
        
        /* Get member type. */
        $memberTypesDbTable = new Default_Model_DbTable_MemberTypes();
        $memberType = $memberTypesDbTable->find($user->getMemberType())->current();
        
        /* Enter fee information and save. */
        $feesDbTable = new Default_Model_DbTable_Fees();
        $feeId = $feesDbTable->insert(array(
            'timestamp'   => date('Y-m-d H:i:s'),
            'userId'      => $userId,
            'forTheYear'  => $params['forTheYear'],
            'amount'      => $memberType->fee,
            'enteredBy'   => $this->user['userId'],
            'entryMethod' => 'Admin'));
        
        /* Enter log entry for the fee entry. */
        $this->log->insert(array(
            'entity'    => 'users',
            'entityId'  => $userId,
            'code'      => 'fee',
            'message'   => "fee payment for memberType {$user->getMemberType()} of MRF {$memberType->fee}",
            'timestamp' => date('Y-m-d H:i:s'),
            'userId'    => $this->user['userId']));
        
        /* Add log message and redirect to viewing user. */
        $this->_helper->flashMessenger->addMessage(
            "Subscription Fee entered successfully!");
        $this->_redirect("admin/users/view/userId/{$userId}");
    }

    public function deleteAction()
    {
        static $permission = 'delete';

        /* Find user. */
        $userId = $this->_request->getParam('userId');
        $user = $this->udbt->find($userId)->current();
        $name = $user->name;

        /* Delete user. */
        $user->delete();

        /* Add log message. */
        $this->log->insert(array(
            'entity'    => 'admin_users',
            'entityId'  => $userId,
            'code'      => 'delete',
            'message'   => "user {$name} deleted.",
            'timestamp' => date('Y-m-d H:i:s'),
            'userId'    => $this->user['userId']));

        /* Set session flash message and redirect to user listing index page. */
        $this->_helper->flashMessenger->addMessage("User {$name} deleted.");
        $this->_redirect('/admin/users');
    }
}
