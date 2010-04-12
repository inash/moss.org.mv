<?php

/**
 * User Registration Controller.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun May 31, 2009 10:18 AM
 */

class RegisterController extends Pub_Controller_Action
{
    public function indexAction()
    {
        /* If a user is already authenticated redirect to referer. */
        $userns = new Zend_Session_Namespace('user');
        if ($userns->authenticated == true) {
        	if ($userns->requestUri != $this->_request->getBaseUrl.'login') {
                $this->_redirect(trim($userns->requestUri, $this->_request->getBaseUrl()));
        	} else {
        		$this->_redirect('/');
        	}
            return false;
        }
        
        /* If request method is post, assume the user has submitted the
         * registration form and proceed with registering the user with the
         * processAction. */
        if ($this->_request->isPost()) {
            $this->_forward('process');
        }
        
        /* Set the Register form to the view. */
        $form = new Default_Form_Register();
        $this->view->form = new Default_Form_Register();
    }
    
    public function processAction()
    {
        /* Process post form. */
    	$form = new Default_Form_Register();
    	if (!$form->isValid($this->_request->getPost())) {
    		$this->view->form = $form;
    		$this->render('index');
    		return false;
    	}
    	
    	/* Check if cpassword and password matches. */
    	if ($form->getValue('password') != $form->getValue('cpassword')) {
    		$cpassword = $form->getElement('cpassword');
    		$cpassword->setErrors(array('Does not match with Password'));
    		$form->markAsError();
    	}
    	
    	/* Get form values to further custom validate fields. */
        $params = $form->getValues();
        unset($params['captcha'], $params['cpassword']);
        
        /* Check if userId already exists. */
        $usersModel = new Default_Model_DbTable_Users();
        $userExist  = $usersModel->find($params['userId'])->current();
        if ($userExist) {
        	$userId = $form->getElement('userId');
        	$userId->setErrors(array('Username is already taken'));
        	$form->markAsError();
        }
        
        /* Check if email already exists. */
        $emailExist = $usersModel->fetchRow("email='{$params['email']}'");
        if ($emailExist) {
        	$email = $form->getElement('email');
        	$email->setErrors(array('Email is already taken'));
        	$form->markAsError();
        }
        
        /* If error messages exist, render the registration form view along
         * with the error messages. */
        if ($form->isErrors()) {
            $this->view->form = $form;
            $this->render('index');
            return false;
        }
        
        /* hash passwords. */
        $params['password'] = md5($params['password']);
        
        /* If registration completed, redirect to registered view. */
        $userId = $usersModel->insert(array(
            'userId'         => $params['userId'],
            'name'           => $params['name'],
            'email'          => $params['email'],
            'password'       => $params['password'],
            'dateRegistered' => date('Y-m-d H:i:s')));
        
        /* Add log entry indicating registration of new user. */
        $this->log->insert(array(
            'entity'    => 'users',
            'entityId'  => $userId,
            'timestamp' => date('Y-m-d H:i:s'),
            'code'      => 'register',
            'message'   => "new user registered [{$userId}] {$params['name']}",
            'userId'    => $userId));
        
        /* Calculate hash for new user. */
        $hash = $params['userId'].$params['name'].$params['email'].date('Y-m-d H:i:s');
        $hash = md5($hash);
        
        /* Add new user entry. */
        $newUsersModel = new Default_Model_DbTable_NewUsers();
        $unId = $newUsersModel->insert(array(
            'userId'    => $userId,
            'timestamp' => date('Y-m-d H:i:s'),
            'hash'      => $hash));
        
        /* Send email. */
        $mail = new Zend_Mail();
        $mail->addTo($params['email'], $params['name']);
        $mail->setSubject("MOSS registration: Activate your account");
        $mail->setFrom("noreply@moss.org.mv", "MOSS");
        
        /* Prepare mail body. */
        $params['hash'] = $hash;
        $this->view->params = $params;
        $body = $this->view->render('register/mail.register.phtml');
        $mail->setBodyText($body);
        
        /* Use smtp if environment is not production. */ 
        if (APP_ENV != 'production') {
            $server = $this->getInvokeArg('bootstrap')->getApplication()->getOption('mail');
            $server = $server['server'];
            $mail->send(new Zend_Mail_Transport_Smtp($server));
        } else {
            $mail->send();
        }
        
        $this->_redirect('/register/complete');
    }
    
    public function completeAction() {}
    
    public function activateAction()
    {
        $hash = trim($this->_request->getParam('hash'));
        
        /* Check if hash exists and get it. */
        $newUsersModel = new Default_Model_DbTable_NewUsers();
        $record = $newUsersModel->fetchRow("hash='{$hash}'");
        
        /* If records doesn't exist, assume that the request is invalid and
         * redirect the user to the homepage and set a message indicating that
         * the activation process failed. */
        if (!$record) {
            $this->_helper->flashMessenger->addMessage(
                "The Activation process failed.");
            $this->_redirect('/Front');
            return false;
        }
        
        /* Activate record. */
        $user = $record->findParentRow('Default_Model_DbTable_Users');
        $user->active = 'Y';
        $user->save();
        
        /* Add log entry indicating activation of user account. */
        $this->log->insert(array(
            'entity'    => 'users',
            'entityId'  => $user->userId,
            'timestamp' => date('Y-m-d H:i:s'),
            'code'      => 'activate',
            'message'   => "user account activated [{$user->userId}] {$user->name}",
            'userId'    => $user->userId));
        
        /* Delete new user record from users_new table. */
        $record->delete();
        $this->view->user = $user;
    }
}
