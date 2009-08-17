<?php

/**
 * Profile Controller. Shows user profile and enables authenticated user to
 * manage their profile settings.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun Jun 7, 2009 07:28 AM
 */

require_once 'ApplicationController.php';
require_once 'Zend/Validate/Alnum.php';
require_once 'Zend/Validate/EmailAddress.php';
require_once 'Zend/Validate/StringLength.php';

class ProfileController extends DefaultController
{
    public function indexAction()
    {
        $userId = $this->_request->getParam('controller');
        $usersModel = new Default_Model_DbTable_Users();
        $profile = $usersModel->find($userId)->current();
        
        if (!$profile) {
            echo "Invalid user profile!";
            exit;
        }
        
        $profile = $profile->toArray();
        unset($profile['password']);
        $this->view->profile = $profile;
    }
    
    /**
     * Displays the account modification screen. Allows the user to change
     * his/her details, change password, etc.
     * 
     * Works with both methods get and post. If the request method plain get,
     * display the modification form view. If the request is post, process
     * the request for modification. If errors exist, display the modification
     * screen view with errors.
     */
    public function settingsAction()
    {
        /* Check if user authenticated. Otherwise redirect to login. */
        $userNs = new Zend_Session_Namespace('user');
        if (!$userNs->authenticated) {
            $this->_redirect('/login');
            return false;
        }
        
        /* Get the currently logged in user's record and assign it to the view.
         * This is required for both operations, displaying and processing. */
        $usersModel = new Users();
        $user = $usersModel->find($this->user['userId'])->current();
        $this->view->profile = $user->toArray();
        
        /* Return without doing post processing logic if the request method is
         * GET. Which assumes that it is the default settings modification
         * view. */
        if ($this->_request->getMethod() == 'GET') return;
        
        /* Get all parameters and remote unnecessary fields. */
        $params = $this->_request->getPost();
        unset($params['update']);
        
        /* Get current user record to compare modifications with. */
        $changes = array();
        if ($params['userId'] != $user->userId) $changes[] = 'userId';
        if ($params['name']   != $user->name)   $changes[] = 'name';
        if ($params['email']  != $user->email)  $changes[] = 'email';
        
        /* Check if password modifications exist. */
        $md5nPassword  = md5($params['nPassword']);
        $md5cnPassword = md5($params['cnPassword']);
        $md5Password   = md5($params['password']);
        
        if ($params['nPassword'] != '') {
            $changes[] = 'password';
        }
        
        /* If no changes recorded, don't process any further, display a notice
         * indicating that no changes were applied. */
        if (count($changes) == 0) {
            $this->_helper->flashMessenger->addMessage(
                "No Profile modifications recorded!");
            $this->_redirect($this->user['userId']);
        }
        
        /* Proceed with applying modifications but first check if the password
         * authentication is successful. Otherwise redisplay page with error
         * message indication authentication failure. */
        if ($md5Password != $user->password) {
            $messages['password'] = 'Invalid current password to authenticate changes!';
            $this->view->profile = $params;
            $this->view->rmessages = $messages;
            $this->render();
            return false;
        }
        
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
        if (!isset($messages['userId']) && $params['userId'] != $user->userId) {
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
        if (!isset($messages['email']) && $params['email'] != $user->email) {
            $query = $db->query(
                "SELECT COUNT(email) AS cnt FROM users WHERE email='{$params['email']}'");
            $row = $query->fetch();
            if ($row['cnt'] > 0) $messages['email'] = 'Email is already taken!';
        }
        
        /* Do Password validation. */
        if ($params['nPassword'] != '') {
            if (!$vAlnum->isValid($params['nPassword'])) {
                $messages['nPassword'] = 'Invalid Password!';
            }
            
            /* Validate password length. */
            $vStringLength->setMin(8);
            $vStringLength->setMax(50);
            if (!$vStringLength->isValid($params['nPassword'])) {
                $messages['nPassword'] = 'Password needs to be more than 8 characters.';
            }
            
            /* Check passwords mismatch. */
            if ($params['cnPassword'] != $params['nPassword']) {
                $messages['cnPassword'] = 'Passwords do not match!';
            }
        }
        
        /* If error messages exist, render the registration form view along
         * with the error messages. */
        if (isset($messages) && count($messages) > 0) {
            unset($params['password'], $params['passwordc']);
            $this->view->profile = $params;
            $this->view->rmessages = $messages;
            $this->render();
            return false;
        }
        
        /* If chanegs are all good, apply them to the database. */
        try {
            $db->beginTransaction();
            $user->userId   = $params['userId'];
            $user->name     = $params['name'];
            $user->email    = $params['email'];
            
            if (in_array('password', $changes)) {
                $user->password = $md5nPassword;
            }
            
            $user->save();
            
            $this->log->insert(array(
                'entity'    => 'users',
                'entityId'  => $params['userId'],
                'timestamp' => date('Y-m-d H:i:s'),
                'code'      => 'edit',
                'message'   => "profile modified. fields: ".join(', ', $changes),
                'userId'    => $params['userId']));
            
            /* TODO: Update userId where required if the userId was changed. */
            
            $db->commit();
            $userNs = new Zend_Session_Namespace('user');
            $userNs->userId = $params['userId'];
            
            $this->_helper->flashMessenger->addMessage(
                "Profile settings changed successfully!");
            $this->_redirect($user->userId);
        } catch (Exception $e) {
            Zend_Debug::dump($e);
            $db->rollback();
        }
    }
    
    public function feedAction()
    {
        //
    }
}
