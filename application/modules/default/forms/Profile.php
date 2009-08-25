<?php

/**
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon Aug 24, 2009 04:46 AM
 */

class Default_Form_Profile extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->setOptions(array('disableLoadDefaultDecorators' => true));
        $this->clearDecorators();
        $this->addDecorator('FormElements')
            ->addDecorator('HtmlTag', array(
                'tag'         => '<table>',
                'border'      => 0,
                'cellspacing' => 0,
                'cellpadding' => 4))
            ->addDecorator('Form');
            
        $this->addElementPrefixPath('Pub_Form_Decorator',
            'Pub/Form/Decorator/',
            'decorator');
        
        $this->setElementDecorators(array('Composite'));
        
        /* Add userId element. */
        $this->addElement('text', 'userId', array(
            'label'         => 'Username',
            'size'          => 30,
            'maxLength'     => 50,
            'required'      => true,
            'filters'       => array('StringTrim', 'StringToLower'),
            'validators'    => array('Alnum',
                array('StringLength', false, array(1, 50))),
            'errorMessages' => array('Username is required')));
        
        /* Add name element. */
        $this->addElement('text', 'name', array(
            'label'         => 'Name',
            'size'          => 30,
            'maxLength'     => 100,
            'required'      => true,
            'filters'       => array('StringTrim'),
            'validators'    => array('NotEmpty'),
            'errorMessages' => array('Name is required')));
        
        /* Add email element. */
        $this->addElement('text', 'email', array(
            'label'         => 'Email',
            'size'          => 30,
            'maxLength'     => 100,
            'required'      => true,
            'filters'       => array('StringTrim'),
            'validators'    => array('EmailAddress'),
            'errorMessages' => array('Email is required')));
        
        /* Add password element. */
        $this->addElement('password', 'password', array(
            'label'         => 'Password',
            'required'      => true,
            'filters'       => array('StringTrim'),
            'validators'    => array(array('StringLength', false, array(8, 50))),
            'description'   => 'Minimum 8 characters',
            'errorMessages'      => array(
                'stringLengthTooLong'  => 'Maximum 50 characters',
                'stringLengthTooShort' => 'Minimum 8 characters')));
        
        /* Add confirm password element. */
        $this->addElement('password', 'cpassword', array(
            'label'         => 'Confirm Pasword',
            'required'      => true,
            'filters'       => array('StringTrim'),
            'validators'    => array(array('StringLength', false, array(8, 50))),
            'errorMessages' => array(
                'stringLengthTooLong'  => 'Maximum 50 characters',
                'stringLengthTooShort' => 'Minimum 8 characters')));
        
        /* Add a captcha. */
        $this->addElement('captcha', 'captcha', array(
            'label'    => 'Verify',
            'required' => true,
            'captcha'  => array(
                'captcha' => 'Image',
                'name'    => 'captchaId',
                'font'    => PUB_PATH.'DejaVuSans.ttf',
                'imgDir'  => PUB_PATH.'captcha',
                'imgUrl'  => 'captcha/')));
        
        /* Add the submit button. */
        $submit = $this->addElement('submit', 'register', array(
            'ignore'     => true,
            'label'      => 'Register',
            'class'      => 'required'));
        
        /* Add CSRF protection. */
        $this->addElement('hash', 'csrf', array(
            'ignore' => true));
    }
}
