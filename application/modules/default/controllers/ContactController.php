<?php

/**
 * Contact page controller. Basically a formmail controller script.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon Jul 27, 2009 12:54 AM
 */

require_once 'DefaultController.php';
require_once 'Zend/Captcha/Image.php';

class ContactController extends DefaultController
{
    private $captcha;
    
    public function init()
    {
//        $this->captcha = new Zend_Captcha_ReCaptcha(array(
//            'privKey' => '6LevhgcAAAAAAIsexIh1SoA1SlxtmEefn011nmF5',
//            'pubKey'  => '6LevhgcAAAAAAHiJKW2PWBXg1GQRI-ksGSUO9jDz'));
        $this->captcha = new Zend_Captcha_Image();
        $this->captcha->setFont(PUB_PATH.'DejaVuSans.ttf');
        $this->captcha->setImgDir(PUB_PATH.'captcha/');
        $this->captcha->setImgUrl('captcha/');
        $this->captcha->setName('captchaId');
    }
    
    public function indexAction()
    {
    	$form = new Default_Form_Contact();
    	$this->view->form = $form;
    	
    	/* Forward to processAction if request method is post. */
        if ($this->_request->isPost()) {
        	$this->_forward('process');
        	return true;
        }
        
        /* Generate and set captcha to the view. */
        $this->captcha->generate();
        $this->view->captcha = $this->captcha;
    }
    
    public function processAction()
    {
    	/* Get the request variables. */
    	$name  = $this->_request->getPost('name');
    	$email = $this->_request->getPost('email');
    	
    	$captchaId = $this->_request->getPost('captchaId');
    	$captcha   = $this->_request->getPost('captcha');
        $captchaSession = new Zend_Session_Namespace("Zend_Form_Captcha_{$captchaId}");
        
        /* Validate input data. */
        if ($name == '') $error['name'] = 'Please provide your name.';
        $emailVal = new Zend_Validate_EmailAddress();
        if (!$emailVal->isValid($email)) $error['email'] = 'Invalid email address!';
        if ($comments == '') $error['comments'] = 'Please provide your comment.';
        if ($captcha == '') $error['captcha'] = 'Empty captcha value!';
        
        /* If there are errors and messages has been set, redisplay the contact
         * index page with the error messages for correction. */
        if (isset($error) && is_array($error)) {
        }
        
        if ($this->captcha->isValid($_POST)) {
            echo 'yeah';
        } else {
            echo 'nay';
        }
        exit;
    }
    
    public function postDispatch()
    {
        $this->_helper->actionStack('sidebar', 'index', 'default');
    }
}
