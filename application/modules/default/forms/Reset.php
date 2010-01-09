<?php

/**
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sat Jan 9, 2009 12:19 PM
 */

class Default_Form_Reset extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        
        /* Add email element. */
        $this->addElement('text', 'email', array(
            'label'         => 'Email',
            'size'          => 30,
            'maxLength'     => 100,
            'required'      => true,
            'filters'       => array('StringTrim'),
            'validators'    => array('EmailAddress'),
            'errorMessages' => array('Email is required and can\'t be empty')));

        /* Add CSRF protection. */
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
            'errorMessages' => array('Invalid form data. Click Reset to proceed.')));
        
        /* Add the submit button. */
        $submit = $this->addElement('submit', 'login', array(
            'ignore' => true,
            'label'  => 'Login',
            'class'  => 'required'));
    }
}
