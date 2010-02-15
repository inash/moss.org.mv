<?php

/**
 * Abstract Default Controller.
 * 
 * This is the default website abstract controller which wraps some basic
 * functionality in the predispatch function.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon May 25, 2009 05:58 AM
 */

abstract class Pub_Controller_Action extends Zend_Controller_Action
{
    /**
     * Main application configuration.
     *
     * @var Zend_Config
     */
    protected $config;
    
    /**
     * Default Zend_Db database adapter resource.
     * 
     * @var Zend_Db
     */
    protected $db;
    
    /**
     * Holds a reference to the Logs model. Which allows to quickly insert
     * log entries to the logs database table.
     * 
     * @var Zend_Db_Table_Abstract
     */
    protected $log;
    
    /**
     * Holds a reference to the user session namespace if the user is
     * authenticated.
     * 
     * Application protected property which holds basic information of the
     * authenticated user. This could be used to easily access those information
     * from within any sub class of DefaultController.
     * 
     * @var array
     */
    protected $user = array(
        'authenticated' => false,
        'userId'        => null,
        'email'         => null,
        'name'          => null,
        'class'         => null);

    /**
     * Holds the Zend_Acl object. This is only initialized if the user is
     * authenticated.
     * 
     * @var Zend_Acl
     */
    protected $acl;
    
    public function preDispatch()
    {
        $this->view->baseUrl = $this->_request->getBaseUrl();
        
        /* Set config to local protected $config. */
//        $this->config = Zend_Registry::get('config');
        
        /* Set the default database adapter to the local protected db. */
        $this->db = $this->getInvokeArg('bootstrap')->getResource('db');
        
        /* Set logs protected property with an instance of the Logs model. */
        $this->log = new Default_Model_DbTable_Logs();
        
        /* Assign user session namespace as an array to the protected
         * DefaultController property $user, which is described at this class's
         * property declaration area . */
        $userns = new Zend_Session_Namespace('user');
        
        if ($userns->authenticated == true) {
            
            /* Fetch menus for the current user's groups assignment. Sarey's
             * magnificent query for retrieving a set from a tertiary
             * relationship table. */
            $query = $this->db->query(
                "SELECT me.*, mg.title as menuGroupTitle, mo.title as moduleTitle, "
              . "mo.description "
              . "FROM menus me "
              . "INNER JOIN modules mo on mo.name = me.moduleName "
              . "INNER JOIN menu_groups mg on mg.name = me.menuGroup "
              . "WHERE me.userGroup in ("
              .     "SELECT `group` FROM users_groups WHERE userId='{$userns->userId}') "
              . "OR me.userGroup='{$userns->primaryGroup}' "
              . "ORDER BY me.menuGroup, me.`order`");

            /* Filter and normalize menu entries, removing any subsequent
             * duplicates by going through each item. */
            $menus = array();
            $adminMenus = array();
            
            while ($row = $query->fetch()) {
                if (!array_key_exists(strtolower($row['moduleName']), $menus)) {
                    $row['moduleName'] = strtolower($row['moduleName']);
    
                    /* Separate admin menus and normal menus. */
                    if ($row['menuGroup'] == 'admin') {
                        $adminMenus[] = $row;
                    } else {
                        $menus[] = $row;
                    }
                }
            }
            
            /* Assign menus to view. */
            $menus = array_merge($adminMenus, $menus);
            $this->view->menus = $menus;
            
            $this->user['authenticated'] = true;
            $this->user['userId']        = $userns->userId;
            $this->user['email']         = $userns->email;
            $this->user['name']          = $userns->name;
            $this->user['memberType']    = $userns->memberType;
            $this->user['primaryGroup']  = $userns->primaryGroup;
            $this->user['website']       = $userns->website;
            $this->user['company']       = $userns->company;
            $this->user['location']      = $userns->location;
            $this->user['groups']        = $userns->groups;

            /* Unserialize acl if authenticated and set in the session. */
            $aclns = new Zend_Session_Namespace('acl');
            if (isset($aclns->acl)) {
                $this->acl = unserialize($aclns->acl);
                $this->view->acl = $this->acl;
            }
        }
        
        /* Assign user array to the layout view as well. */
        $this->view->user = $this->user;
        
        /* Check if the flash messenger has any messages. Process them. */
        if ($this->_helper->flashMessenger->hasMessages()) {
            $this->view->messages = $this->_helper->flashMessenger->getMessages();
        }
    }
    
    public function postDispatch()
    {
        /* Set referer into user session namespace. */
        $userns = new Zend_Session_Namespace('user');
        $userns->requestUri = $this->_request->getRequestUri();
    }
}
