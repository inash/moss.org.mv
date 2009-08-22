<?php

/**
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sat Aug 22, 2009 07:25 PM
 */

class Default_Form_Login extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        
        /* Add email element. */
        $this->addElement('text', 'email', array(
            'label'         => 'Email:',
            'maxLength'     => 100,
            'required'      => true,
            'filters'       => array('StringTrim'),
            'validators'    => array('EmailAddress'),
            'errorMessages' => array('Email is required and can\'t be empty')));
        
        /* Add password element. */
        $this->addElement('password', 'password', array(
            'label'         => 'Password:',
            'required'      => true,
            'filters'       => array('StringTrim'),
            'validators'    => array('NotEmpty'),
            'errorMessages' => array('Password is required and can\'t be empty')));
        
        /* Add the submit button. */
        $submit = $this->addElement('submit', 'login', array(
            'ignore' => true,
            'label'  => 'Login',
            'class'  => 'required'));
        
        /* Add CSRF protection. */
        $this->addElement('hash', 'csrf', array(
            'ignore' => true));
    }
}
