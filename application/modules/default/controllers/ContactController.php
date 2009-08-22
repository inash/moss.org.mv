<?php

/**
 * Contact page controller. Basically a formmail controller script.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon Jul 27, 2009 12:54 AM
 */

class ContactController extends Pub_Controller_Action
{
    public function indexAction()
    {
    	$form = new Default_Form_Contact();
    	$this->view->form = $form;
    	
    	/* Forward to processAction if request method is post. */
        if ($this->_request->isPost()) {
        	$this->_forward('process');
        }
    }
    
    public function processAction()
    {
    	$form = new Default_Form_Contact();
    	if (!$form->isValid($this->_request->getPost())) {
    		$this->view->form = $form;
    		$this->render('index');
    		return false;
    	}
    	
    	/* Prepare fields. */
    	$params = $this->_request->getPost();
    	unset($params['captcha'], $params['submit'], $params['csrf']);
    	
    	/* Get mail server configuration. */
    	$mail = $this->getInvokeArg('bootstrap')->getApplication()->getOption('mail');
    	
    	$transport = new Zend_Mail_Transport_Smtp($mail['server']);
    	Zend_Mail::setDefaultTransport($transport);
    	
    	$mail = new Zend_Mail();
    	$mail->setBodyText($params['comment'])
    	   ->setFrom($params['email'], $params['name'])
    	   ->addTo('info@moss.org.mv')
    	   ->setSubject('Online Inquiry')
    	   ->send();
    }
}
