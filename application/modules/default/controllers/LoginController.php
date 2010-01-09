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
            ->where("u.email='{$email}'");
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

        /* If account is inactive. */
        if ($firstRow['active'] != 'Y') {
            $form->markAsError();
            $this->view->form = $form;
            $this->view->message = 'Your account is inactive.';
            $this->render('index');
            return false;
        }

        /* If account is disabled. */
        if ($firstRow['disabled'] == 'Y') {
            $form->markAsError();
            $this->view->form = $form;
            $this->view->message = 'Your account is disabled.';
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

        /* Check if user is required to reset password. */
        if ($firstRow['reset'] == 'Y') {
            $userns->authenticated = false;
            $userns->reset = true;
            $this->_redirect('/change');
        }
        
        /* Redirect to auth referer and remove from session. */
        $authReferer = $userns->auth_referer;
        $authReferer = ltrim($authReferer, $this->_request->getBaseUrl());
        if ($authReferer == 'login' || $authReferer == 'register') $authReferer = 'Front';
        unset($userns->auth_referer);
        $this->_redirect($authReferer);
    }

    public function resetAction()
    {
        $form = new Default_Form_Reset();
        if (!$this->_request->isPost()) {
            $this->view->form = $form;
            return true;
        }

        $params['email'] = $this->_request->getPost('email');
        $params['csrf']  = $this->_request->getPost('csrf');
        $form->populate($params);

        if (!$form->isValid($params)) {
            $this->view->form = $form;
            return false;
        }

        /* Process form. */
        $usersDbTable = new Default_Model_DbTable_Users();
        $user = $usersDbTable->fetchRow(array("email='{$form->getValue('email')}'"));

        /* If user does not exist, proceed to displaying the result page,
         * without revealing whether the user exists or not. */
        if (!$user) {
            $this->render('reset-result');
            return false;
        }

        /* Make sure not to proceed if the reset flag is already set for the
         * user record or if the record is disabled. */
        if ($user->reset == 'Y' || $user->disabled == 'Y') {
            $this->render('reset-result');
            return false;
        }

        /* Create temporary password. */
        $password  = rand(0, 10000);
        $password .= microtime();
        $password  = md5($password);
        $password  = substr($password, 6, 12);

        /* Update user record. */
        $user->reset = 'Y';
        $user->password = md5($password);
        $user->save();

        /* Prepare mail body. */
        $this->view->user = $user;
        $this->view->password = $password;
        $body = $this->view->render('login/mail.reset.phtml');

        /* Send email. */
        $mail = new Zend_Mail();
        $mail->addTo($user->email, $user->name);
        $mail->setSubject("MOSS forgot password: Reset your account password");
        $mail->setFrom("noreply@moss.org.mv", "MOSS");
        $mail->setBodyText($body);
        
        /* Use smtp if environment is not production. */
        if (APP_ENV != 'production') {
            $server = $this->getInvokeArg('bootstrap')->getApplication()->getOption('mail');
            $server = $server['server'];
            $mail->send(new Zend_Mail_Transport_Smtp($server));
        } else {
            $mail->send();
        }

        $this->render('reset-result');
    }

    public function changeAction()
    {
        /* Redirect to login page if reset flag has not been set. */
        $userns = new Zend_Session_Namespace('user');
        if (!isset($userns->reset) || $userns->reset != true) {
            $this->_redirect('/login');
            return false;
        }

        /* Create form and assign it to the view. */
        $form = new Default_Form_Change();
        $this->view->form = $form;

        if (!$this->_request->isPost()) return true;

        /* Validate form. */
        $params['opassword'] = $this->_request->getPost('opassword');
        $params['npassword'] = $this->_request->getPost('npassword');
        $params['cpassword'] = $this->_request->getPost('cpassword');
        $params['csrf']      = $this->_request->getPost('csrf');

        /* Check password policy. */
        if (strlen($params['opassword']) < 8 ||
            strlen($params['npassword']) < 8 ||
            strlen($params['cpassword']) < 8
        ) {
            $form->markAsError();
            $this->view->message = 'The minimum Password lengths has to be 8 characters.';
            return false;
        }

        if (!$form->isValid($params)) {
            return false;
        }

        $userns = new Zend_Session_Namespace('user');
        $user = new Default_Model_Users();
        $user->find($userns->userId);

        if ($user->getUserId() == '') {
            echo 'Invalid user.';
            exit;
        }

        /* Compare if old password matches. */
        If (md5($params['opassword']) != $user->getPassword()) {
            $form->markAsError();
            $this->view->message = 'Invalid Password.';
            return false;
        }
        
        /* Check if new passwords match. */
        if ($params['npassword'] != $params['cpassword']) {
            $form->markAsError();
            $this->view->message = 'New Passwords do not match.';
            return false;
        }

        /* Check if old and new passwords are the same. */
        if ($params['opassword'] == $params['npassword']) {
            $form->markAsError();
            $this->view->message = 'New Password cannot be the same as the old one.';
            return false;
        }

        /* If all's well, proceed changing the password */
        $user->setPassword(md5($params['npassword']));
        $user->setReset('N');
        $user->save();
        $userns->authenticated = true;
        unset($userns->reset);

        /* Redirect to auth referer and remove from session. */
        $authReferer = $userns->auth_referer;
        $authReferer = ltrim($authReferer, $this->_request->getBaseUrl());
        if ($authReferer == 'login' || $authReferer == 'register') $authReferer = 'Front';
        unset($userns->auth_referer);
        $this->_redirect($authReferer);
    }
}
