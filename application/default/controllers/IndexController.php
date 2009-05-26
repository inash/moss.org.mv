<?php

/**
 * Index Controller
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon May 25, 2009 05:09 AM
 */

class IndexController extends DefaultController
{
    public function indexAction()
    {
        /* Router to static action if the module is default but the controller
         * is not defined, supposedly a static page name, etc. */
        if ($this->_request->getParam('module') == 'default' &&
        $this->_request->getParam('controller') != 'index') {
        	$this->_forward('static');
        	return false;
        }
    }
    
    /**
     * Static page handler. If a requested controller doesn't exist, the
     * application will route all the requests to the default controller's
     * index action, which in return checks if the request is explicitly for
     * the index controller. If not forward the request to this static action
     * handler.
     */
    public function staticAction()
    {
    	echo 'static';
        Zend_Debug::dump($this->_request);exit;
    }
}
