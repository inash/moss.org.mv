<?php

/**
 * @author  Inash Zubair <inash@leptone.com>
 * @created Fri Aug 14, 2009 08:49 AM
 */

class Default_Form_Contact extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        
        /* Add name element. */
        $this->addElement('text', 'name', array(
            'label'      => 'Name:',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty')));
        
        /* Add email element. */
        $this->addElement('text', 'email', array(
            'label'      => 'Email:',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array('EmailAddress')));
        
        /* Add the comment element. */
        $this->addElement('textarea', 'comment', array(
            'label'    => 'Comment:',
            'required' => true));
        
        /* Add a captcha. */
        $this->addElement('captcha', 'captcha', array(
            'label'    => 'Verify:',
            'required' => true,
            'captcha'  => array(
                'captcha' => 'Image',
                'name'    => 'captchaId',
                'font'    => PUB_PATH.'DejaVuSans.ttf',
                'imgDir'  => PUB_PATH.'captcha',
                'imgUrl'  => 'captcha/')));
        
        /* Add the submit button. */
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label'  => 'Submit'));
        
        /* Add the reset button. */
        $this->addElement('reset', 'reset', array(
            'label' => 'Reset'));
        
        /* Add CSRF protection. */
        $this->addElement('hash', 'csrf', array(
            'ignore' => true));
    }
}
