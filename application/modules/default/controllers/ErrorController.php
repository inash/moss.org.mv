<?php

/**
 * Error Controller. Handles exceptions.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon May 25, 2009 05:14 AM
 */

class ErrorController extends Pub_Controller_Action
{
	public function errorAction()
	{
		$errors = $this->_getParam('error_handler');
		$this->view->errors = $errors;
		
		/* Email to the administrator or webmaster. */
        $mail = new Zend_Mail();
        $mail->addTo('inash@leptone.com', 'Inash Zubair');
        $mail->setSubject('MOSS Exception');
        $mail->setFrom('noreply@moss.org.mv', 'MOSS');
        
        /* Prepare body. */
        $body  = print_r($errors, true);
        $body .= print_r($_SERVER, true);
        $mail->setBodyText($body);
        
        /* Use smtp if environment is not production. */
        if (APP_ENV != 'production') {
            $server = $this->getInvokeArg('bootstrap')->getOption('mail');
            $server = $server['server'];
            $mail->send(new Zend_Mail_Transport_Smtp($server));
        } else {
            $mail->send();
        }
		
		switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER :
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION :
                // 404 error -- controller or action not found                
                $this->getResponse()->setRawHeader ('HTTP/1.1 404 Not Found') ;
                // ... get some output to display...
                break ;
            default :
                // application error; display error page, but don't change                
                // status code                
                break ;
		}
	}
}
