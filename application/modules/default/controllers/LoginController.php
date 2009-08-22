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
        if (!isset($userns->auth_referer)) {
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

        /* Get user record. Actual authentication action. */
        $usersModel = new Default_Model_DbTable_Users();
        $user = $usersModel->fetchRow(
            "email='{$email}' "
          . "AND password='{$password}' "
          . "AND active='Y'");

        /* If authentication failed, redisplay login page with error message. */
        if (!$user) {
        	$form->markAsError();
        	$this->view->form = $form;
        	$this->render('index');
        	return false;
        }
        
        /* Update user session namespace with authentication information. */
        $userns = new Zend_Session_Namespace('user');
        $userns->authenticated = true;
        $userns->userId        = $user->userId;
        $userns->email         = $email;
        $userns->name          = $user->name;
        $userns->primaryGroup  = $user->primaryGroup;
        
        /* Update login information on user record. */
        $user->dateLastLogin = date('Y-m-d H:i:s');
        $user->save();
        
        /* Redirect to auth referer and remove from session. */
        $authReferer = $userns->auth_referer; 
        $authReferer = trim($authReferer, $this->_request->getBaseUrl());
        if ($authReferer == 'login' || $authReferer == 'register') $authReferer = 'Front';
        unset($userns->auth_referer);
        $this->_redirect($authReferer);
    }
}
