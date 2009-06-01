<?php

/**
 * User Registration Controller.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun May 31, 2009 10:18 AM
 */

require_once 'DefaultController.php';
require_once 'Zend/Validate/Alnum.php';
require_once 'Zend/Validate/EmailAddress.php';
require_once 'Zend/Validate/Identical.php';
require_once 'Zend/Validate/StringLength.php';
require_once 'Zend/Validate/NotEmpty.php';
require_once 'Zend/Filter/Alnum.php';
require_once 'Zend/Filter/Input.php';

class RegisterController extends DefaultController
{
    public function indexAction()
    {
        /* If a user is already authenticated redirect to referer. */
        $userns = new Zend_Session_Namespace('user');
        if ($userns->authenticated == true) {
            $this->_redirect(trim($userns->requestUri, $this->_request->getBaseUrl()));
            return false;
        }
        
        /* If request method is post, assume the user has submitted the
         * registration form and proceed with registering the user with the
         * processAction. */
        if ($this->_request->isPost()) {
            $this->_forward('process');
        }
    }
    
    public function processAction()
    {
    	/* Get all parameters and remote unnecessary fields. */
        $params = $this->_request->getPost();
        unset($params['register']);
        
        /* Do validation and filteration. */
        $vAlnum = new Zend_Validate_Alnum();
        $vEmail = new Zend_Validate_EmailAddress();
        $vStringLength = new Zend_Validate_StringLength();
        $vIdentical    = new Zend_Validate_Identical();
        $vNotEmpty  = new Zend_Validate_NotEmpty();
        
        /* Do userId validation. */
        if (!$vAlnum->isValid($params['userId'])) {
        	$messages['userId'] = 'Username must only contain alpha-numeric characters!';
        }
        
        $vStringLength->setMin(1);
        $vStringLength->setMax(50);
        if (!$vStringLength->isValid($params['userId'])) {
        	$messages['userId'] = 'Username cannot be longer than 50 characters!';
        }
        
        /* Do name validation. */
        if ($params['name'] == '') {
        	$messages['name'] = 'Please provide a Name!';
        }
        
        /* Do Email validation. */
        if (!$vEmail->isValid($params['email'])) {
        	$messages['email'] = 'Invalid Email address!';
        }
        
        /* Do Password validation. */
        if (!$vAlnum->isValid($params['password'])) {
        	$messages['password'] = 'Invalid Password!';
        }
        
        /* Validate password length. */
        $vStringLength->setMin(8);
        $vStringLength->setMax(50);
        if (!$vStringLength->isValid($params['password'])) {
        	$messages['password'] = 'Password needs to be more than 8 characters.';
        }
        
        /* Check passwords mismatch. */
        if ($params['passwordc'] != $params['password']) {
        	$messages['passwordc'] = 'Passwords do not match!';
        }
        
        /* If error messages exist, render the registration form view along
         * with the error messages. */
        if (isset($messages) && count($messages) > 0) {
        	unset($params['password'], $params['passwordc']);
        	$this->view->user = $params;
        	$this->view->messages = $messages;
        	$this->render('index');
        	return false;
        }
        
        /* If registration completed, redirect to registered view. */
        $this->_redirect('/register/complete');
    }
    
    public function completeAction() {}
}
