<?php

/**
 * Login Controller. Enables user authentication and authorization.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun May 31, 2009 06:11 AM
 */

class LoginController extends Pub_Controller_Action
{
    public function indexAction()
    {
        /* If already authenticated, redirect to referer otherwise home page. */
        $userns  = new Zend_Session_Namespace('user');
        if ($userns->authenticated) {
            $referer = $_SERVER['HTTP_REFERER'];
            $this->_redirect(trim($userns->requestUri, $this->_request->getBaseUrl()));
            return false;
        }
        
        /* If the request method is post, forward to the login action to
         * perform actual authentication logic. */
        if ($this->_request->isPost()) {
            $this->_forward('login');
            return true;
        }
        
        /* Set auth referer from previous request. So that the application
         * redirect to that location when ever the authentication process
         * succeeds. */
        $auth_referer = $userns->auth_referer;
        if (!$auth_referer != '') {
            $userns->auth_referer = $userns->requestUri;
        }
        
        /* Set the Login form to the view. */
        $form = new Default_Form_Login();
        $this->view->form = $form;
    }
    
    public function loginAction()
    {
        /* Disable view rendering and layout as they are not required for
         * this action. */
        $this->_helper->viewRenderer->setNoRender();
        
        /* Validate, if fails, show index view with errors. */
        $form = new Default_Form_Login();
        if (!$form->isValid($this->_request->getPost())) {
        	$this->view->form = $form;
        	$this->render('index');
        	return false;
        }
        
        /* Get auth parameters and filter them. */
        $fields   = $form->getValues();
        $email    = $fields['email'];
        $password = md5($fields['password']);

        /* Get user record along with related groups from the users_groups db
         * table. This allows to identify if a user exist prior to
         * authenticating. */
        $db = $this->getInvokeArg('bootstrap')->getResource('db');
        $select = $db->select()
            ->from(array('u' => 'users'))
            ->joinLeft(array('ug' => 'users_groups'), 'ug.userId=u.userId', array('ug.group'))
            ->where("u.email='{$email}'")
            ->where("u.active='Y'")
            ->where("u.reset='N'")
            ->where("u.disabled='N'");
        $query = $select->query();
        
        /* If no user record is found, invalidate form and redisplay the login
         * form view. */
        if (count($query) == 0) {
            $form->markAsError();
            $this->view->form = $form;
            $this->render('index');
            return false;
        }
        
        /* Get user's query results and the first row. */
        $results  = $query->fetchAll();
        $firstRow = array_shift($results);
        
        /* If password mismatch. */
        if ($password != $firstRow['password']) {
            $form->markAsError();
            $this->view->form = $form;
            $this->render('index');
            return false;
        }
        
        /* Get user's other groups. */
        $xGroups = array();
        if ($firstRow['group'] != '') $xGroups[] = $firstRow['group'];
        if (!in_array($firstRow['primaryGroup'], $xGroups)) $xGroups[] = $firstRow['primaryGroup'];
        foreach ($results as $row) {
            if ($row['group'] != '' && !in_array($row['group'], $xGroups)) $xGroups[] = $row['group'];
        }
        
        /* Prepare array for permissions retrieval. */
        $sqlga = array();
        foreach ($xGroups as $xg) $sqlga[] = "role='{$xg}'";
        $sqlgs = implode(' OR ', $sqlga);
        
        /* Query and get all permissions belonging to the user's groups. */
        $select = $db->select()
            ->from(array('p' => 'permissions'))
            ->where("entityType='Group'")
            ->where($sqlgs);
        $query  = $select->query();
        $result = $query->fetchAll();
        $permGroups = array();
        $exgl = array();
        
        /* Filter and remove permissions if 'all' permission is set for a
         * certain module. */
        foreach ($result as $perm) {
            if (in_array($perm['resource'], $exgl)) continue;
            if ($perm['permission'] == 'all') {
                $exgl[] = $perm['resource'];
            }
            $permGroups[] = $perm;
        }
        
        /* Query and get all permissions belonging to the user. */
        $select = $db->select()
            ->from(array('p' => 'permissions'))
            ->where("entityType='User'")
            ->where("role='{$firstRow['userId']}'");
        $query  = $select->query();
        $result = $query->fetchAll();
        $permUser = array();
        
        /* Filter and remove permissions if 'all' permission is set for a
         * certain module for the user. */
        foreach ($result as $perm) {
            if (in_array($perm['resource'], $exgl)) continue;
            if ($perm['permission'] == 'all') {
                $exgl[] = $perm['resource'];
            }
            $permUser[] = $perm;
        }
        
        /* Create the ACL object and load all the permissions to it. */
        $acl = new Zend_Acl();
        $acl->addRole(new Zend_Acl_Role($firstRow['userId']));
        
        /* Add all the group based permissions. */
        foreach ($permGroups as $perm) {
            if (!$acl->has($perm['resource'])) $acl->add(new Zend_Acl_Resource($perm['resource']));
            if ($perm['permission'] == 'all') {
                $acl->allow($firstRow['userId'], $perm['resource']);
            } else {
                $acl->allow($firstRow['userId'], $perm['resource'], $perm['permission']);
            }
        }
        
        /* Add all the user based permissions. */
        foreach ($permUser as $perm) {
            if (!$acl->has($perm['resource'])) $acl->add(new Zend_Acl_Resource($perm['resource']));
            if ($perm['permission'] == 'all') {
                $acl->allow($firstRow['userId'], $perm['resource']);
            } else {
                $acl->allow($firstRow['userId'], $perm['resource'], $perm['permission']);
            }
        }
        
        /* Store permissions in the session for consecutive later usage. */
        $aclns = new Zend_Session_Namespace('acl');
        $aclns->acl = serialize($acl);
        
        /* Update user session namespace with authentication information. */
        $userns = new Zend_Session_Namespace('user');
        $userns->authenticated = true;
        $userns->userId        = $firstRow['userId'];
        $userns->email         = $firstRow['email'];
        $userns->name          = $firstRow['name'];
        $userns->memberType    = $firstRow['memberType'];
        $userns->primaryGroup  = $firstRow['primaryGroup'];
        $userns->website       = $firstRow['website'];
        $userns->company       = $firstRow['company'];
        $userns->location      = $firstRow['location'];
        $userns->groups        = $xGroups;
        
        /* Set administrator flag if user is in administrator group. */
        $userns->isAdministrator = false;
        if (in_array('administrator', $xGroups)) $userns->isAdministrator = true;
        
        /* Set expiration duration. */
        // $namespace->setExpirationSeconds(60*60*24);
        
        /* Update login information on user record. */
        $usersModel = new Default_Model_DbTable_Users();
        $user = $usersModel->find($firstRow['userId'])->current();
        $user->dateLastLogin = date('Y-m-d H:i:s');
        $user->save();
        
        /* Redirect to auth referer and remove from session. */
        $authReferer = $userns->auth_referer;
        $authReferer = ltrim($authReferer, $this->_request->getBaseUrl());
        if ($authReferer == 'login' || $authReferer == 'register') $authReferer = 'Front';
        unset($userns->auth_referer);
        $this->_redirect($authReferer);
    }
}
