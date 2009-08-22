<?php

/**
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sat Aug 22, 2009 09:15 PM
 */

class Pub_Form_Decorator_Composite extends Zend_Form_Decorator_Abstract
{
    public function buildLabel()
    {
        $element = $this->getElement();
        $label   = $element->getLabel();
        if ($translator = $element->getTranslator()) {
            $label = $translator->translate($label);
        }
        
        /* Return label as it is if helper is formSubmit. */
        if ($element->helper == 'formSubmit') return $label;
        
        /* Return nothing if helper is formHidden. */
        if ($element->helper == 'formHidden') return;
        
        $label .= ':';
        return $element->getView()
            ->formLabel($element->getName(), $label);
    }
    
    public function buildInput()
    {
        $element = $this->getElement();
        $helper  = $element->helper;
        
        if ($helper == 'formSubmit') {
        	return $element->getView()->$helper(
                $element->getName(),
                $element->getLabel(),
                $element->getValue(),
                $element->getAttribs(),
                $element->options);
        }
        
//        if ($element->getType() == 'Zend_Form_Element_Captcha') {
//        	$output = $element->getView()->formHidden(
//                $element->getName().'[id]',
//                $element->getValue());
//            $output .= $element->getView()->formText(
//                $element->getName().'[input]');
//            return $output;
//        }
        
        return $element->getView()->$helper(
            $element->getName(),
            $element->getValue(),
            $element->getAttribs(),
            $element->options);
    }
    
    public function buildErrors()
    {
    	$element  = $this->getElement();
    	$messages = $element->getMessages();
    	if (empty($messages)) {
    		return '';
    	}
    	return '<div class="errors">'
            . $element->getView()->formErrors($messages)
            . '</div>';
    }
    
    public function buildDescription()
    {
    	$element  = $this->getElement();
    	$desc     = $element->getDescription();
    	if (empty($desc)) return '';
    	return PHP_EOL . '<span class="description">' . $desc . '</span>';
    }
    
    public function render($content)
    {
        $element = $this->getElement();
        if (!$element instanceof Zend_Form_Element) {
            return $content;
        }
        if (null === $element->getView()) {
            return $content;
        }
        
        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        $label     = $this->buildLabel();
        $input     = $this->buildInput();
        $errors    = $this->buildErrors();
        $desc      = $this->buildDescription();
        
        if ($element->helper == 'formSubmit') {
        	$output = '<tr class="buttons"><td>&nbsp;</td><td>' . $input . '</td></tr>';
        } elseif ($element->helper == 'formHidden') {
        	$output = $input;
        } elseif ($element->getType() == 'Zend_Form_Element_Captcha') { 
        	//$view    = $element->getView();
            //$captcha = $element->getCaptcha();
            //$markup  = $captcha->render($view, $element);
            $output  = '<tr><th class="required">' . $label . '</td>'
                . '<td>' . $content . $errors . '</td></tr>';
            return $output;
        } else {
        	if ($element->isRequired()) {
        		$output = '<tr><th class="required">';
        	} else {
                $output = '<tr><th>';
        	}
        	
        	$output .= $label . '</th>'
	            . '<td>' . $input . $desc . $errors . '</td>'
	            . '</tr>';
        }
        
        switch ($placement) {
            case 'PREPEND':
                return $output . $separator . $content;
                break;
            case 'APPEND':
            default:
                return $content . $separator . $output;
        }
    }
}
