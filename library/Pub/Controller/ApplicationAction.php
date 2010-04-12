<?php

/**
 * Application specific controller action which encapsulates authentication
 * and some of the bootstrapping for the view.
 * 
 * @author  inash
 * @created Tue Aug 25, 2009 12:41 PM
 */
class Pub_Controller_ApplicationAction extends Pub_Controller_Action
{
    /**
     * Main ACL object.
     * @var Zend_Acl
     */
    protected $acl;

    public function preDispatch()
    {
        parent::preDispatch();

        /* Check if user is logged in: required. Otherwise redirect to login
         * screen. */
        $userns = new Zend_Session_Namespace('user');
        if ((!isset($userns->authenticated) || $userns->authenticated != true)
        && $this->_request->getControllerName() != 'login') {
            parent::postDispatch();
            $this->_forward('denied', 'index', 'default');
        }
        
        /* determine http request context and disable layout and viewRenderer
         * for the requested action */
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
        }

        /* check authentication and load user information from session. */
        if ($userns->authenticated == true) {
            
            /* load and initialize access control lists and permissions for the
             * current module and controller. authority is asserted by requested
             * controller's action. */
            $this->checkAcl();
        }
    }

    private function checkAcl()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        $options = $bootstrap->getOptions();
        $moduleDirectory = $options['resources']['frontController']['moduleDirectory'];

        /* Process for ACL checks. */
        $controllerFile = $moduleDirectory . '/'
            . $this->_request->getModuleName()
            . '/controllers/' . ucfirst($this->_request->getControllerName())
            . 'Controller.php';
        $controllerClass = ucfirst($this->_request->getControllerName()) . 'Controller';
        if ($this->_request->getModuleName() != 'default') {
            $controllerClass = ucfirst($this->_request->getModuleName())
                . '_' . $controllerClass;
        }
        $method = $this->_request->getActionName() . 'Action';
        $module = $this->_request->getModuleName() . '_'
            . $this->_request->getControllerName();
            
        require_once $controllerFile;
        
        $ref = new ReflectionClass($controllerClass);
        $met = $ref->getMethod($method);
        $sta = $met->getStaticVariables();

        /* If the permission static variable is not set in the request action
         * then throw and exception stating so. */
        if (!isset($sta['permission'])) {
            $permission = 'view';
            // TODO log to debug
            throw new Exception("Permission variable not set in {$module}::{$method}");
        }
        $permission = $sta['permission'];
        
        try {
            if (is_array($permission) && count($permission) >= 1) {
                foreach ($permission as $perm) {
                    if ($this->acl->isAllowed($this->user['userId'], $module, $perm)) {
                        return;
                    }
                }
                $request = $this->getRequest();
                $request->setActionName('denied')
                        ->setControllerName('index')
                        ->setModuleName('default')
                        ->setDispatched(false);
            } else {
                if (!$this->acl->isAllowed($this->user['userId'], $module, $permission)) {
                    $request = $this->getRequest();
                    $request->setActionName('denied')
                            ->setControllerName('index')
                            ->setModuleName('default')
                            ->setDispatched(false);
                }
            }
        } catch (Zend_Acl_Exception $e) {
            $request = $this->getRequest();
            $request->setActionName('denied')
                    ->setControllerName('index')
                    ->setModuleName('default')
                    ->setDispatched(false);
            // TODO log this to debug logger
        }
    }
}
