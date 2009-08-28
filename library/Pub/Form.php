<?php

/**
 * Abstract Pub Form.
 * 
 * This component is intended to be an abstract class for application form
 * classes to inherit from. It basically sets the new composite pub form
 * decorator to the components.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Fri Aug 28, 2009 03:59 AM
 */

abstract class Pub_Form extends Zend_Form
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
    }
}
