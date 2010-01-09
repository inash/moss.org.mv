<?php

/**
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun Jan 10, 2010 1:23 AM
 */

class Default_Form_Change extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        
        /* Add current password element. */
        $this->addElement('password', 'opassword', array(
            'label'         => 'Current Password',
            'size'          => 30,
            'required'      => true,
            'filters'       => array('StringTrim'),
            'validators'    => array('NotEmpty'),
            'errorMessages' => array('Current Password has errors')));

        /* Add new password element. */
        $this->addElement('password', 'npassword', array(
            'label'         => 'New Password',
            'size'          => 30,
            'required'      => true,
            'filters'       => array('StringTrim'),
            'validators'    => array('NotEmpty'),
            'errorMessages' => array('New Password has errors')));

        /* Add confirm new password element. */
        $this->addElement('password', 'cpassword', array(
            'label'         => 'Confirm New Password',
            'size'          => 30,
            'required'      => true,
            'filters'       => array('StringTrim'),
            'validators'    => array('NotEmpty'),
            'errorMessages' => array('Confirm New Password has errors')));

        /* Add the submit button. */
        $submit = $this->addElement('submit', 'change', array(
            'ignore' => true,
            'label'  => 'Change',
            'class'  => 'required'));
        
        /* Add CSRF protection. */
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
            'errorMessages' => array('Invalid form. Please click on Login')));
    }
}
