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
require_once 'Zend/Validate/StringLength.php';
require_once 'Zend/Mail.php';
require_once 'Zend/Mail/Transport/Sendmail.php';
require_once 'Zend/Mail/Transport/Smtp.php';
require_once 'Users.php';
require_once 'NewUsers.php';

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
        $params['userId'] = strtolower($params['userId']);
        unset($params['register']);
        
        /* Do validation and filteration. */
        $vAlnum = new Zend_Validate_Alnum();
        $vEmail = new Zend_Validate_EmailAddress();
        $vStringLength = new Zend_Validate_StringLength();
        
        /* Do userId validation. */
        if (!$vAlnum->isValid($params['userId'])) {
            $messages['userId'] = 'Username must only contain alpha-numeric characters!';
        }
        
        $vStringLength->setMin(1);
        $vStringLength->setMax(50);
        if (!$vStringLength->isValid($params['userId'])) {
            $messages['userId'] = 'Username cannot be longer than 50 characters!';
        }
        
        /* Check if userId already exists. */
        $db = Zend_Registry::get('db');
        if (!isset($messages['userId'])) {
            $query = $db->query(
                "SELECT COUNT(userId) AS cnt FROM users WHERE userId='{$params['userId']}'");
            $row = $query->fetch();
            if ($row['cnt'] > 0) $messages['userId'] = "Username is already taken!";
        }
        
        /* Do name validation. */
        if ($params['name'] == '') {
            $messages['name'] = 'Please provide a Name!';
        }
        
        /* Do Email validation. */
        if (!$vEmail->isValid($params['email'])) {
            $messages['email'] = 'Invalid Email address!';
        }
        
        /* Check if email already exists. */
        if (!isset($messages['email'])) {
            $query = $db->query(
                "SELECT COUNT(email) AS cnt FROM users WHERE email='{$params['email']}'");
            $row = $query->fetch();
            if ($row['cnt'] > 0) $messages['email'] = 'Email is already taken!';
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
            $this->view->rmessages = $messages;
            $this->render('index');
            return false;
        }
        
        /* hash passwords. */
        $params['password']  = md5($params['password']);
        $params['passwordc'] = md5($params['passwordc']);
        
        /* If registration completed, redirect to registered view. */
        $usersModel = new Users();
        $userId = $usersModel->insert(array(
            'userId'         => $params['userId'],
            'name'           => $params['name'],
            'email'          => $params['email'],
            'password'       => $params['password'],
            'dateRegistered' => date('Y-m-d H:i:s')));
        
        /* If an error occured, forward to error controller. */
        if (!$userId) {
            echo "error registering user.";
            exit;
        }
        
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
        $newUsersModel = new NewUsers();
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
        $mail->send(new Zend_Mail_Transport_Smtp($this->config->mail->server));
        
        $this->_redirect('/register/complete');
    }
    
    public function completeAction() {}
    
    public function activateAction()
    {
        $hash = trim($this->_request->getParam('hash'));
        
        /* Check if hash exists and get it. */
        $newUsersModel = new NewUsers();
        $record = $newUsersModel->fetchRow("hash='{$hash}'");
        
        /* If records doesn't exist, assume that the request is invalid and
         * redirect the user to the homepage and set a message indicating that
         * the activation process failed. */
        if (!$record) {
            $this->_helper->flashMessenger->addMessage(
                "The Activation process failed.!");
            $this->_redirect('/Front');
            return false;
        }
        
        /* Activate record. */
        $user = $record->findParentRow('Users');
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
