<?php

/**
 * User model mapper class.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Wed Aug 19, 2009 01:50 AM
 */

class Default_Model_UsersMapper
{
	/**
	 * @var Zend_Db_Table_Abstract
	 */
    protected $_dbTable;
    
    /**
     * Specify Zend_Db_Table instance to use for data operations.
     * 
     * @param Zend_Db_Table_Abstract $dbTable
     * @return Default_Model_Users
     */
    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided!');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }
    
    /**
     * Get registered Zend_Db_Table instance.
     * 
     * Lazy loads Default_Model_DbTable_Users if no instance registered.
     * 
     * @return Zend_Db_Table_Abstract
     */
    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Default_Model_DbTable_Users');
        }
        return $this->_dbTable;
    }
    
    /**
     * Save a user entry.
     * 
     * @param Default_Model_Users $user
     * @return void
     */
    public function save(Default_Model_Users $user)
    {
        $data = array(
            'userId'         => $user->getUserId(),
            'name'           => $user->getName(),
            'email'          => $user->getEmail(),
            'password'       => $user->getPassword(),
            'primaryGroup'   => $user->getPrimaryGroup(),
            'website'        => $user->getWebsite(),
            'company'        => $user->getCompany(),
            'location'       => $user->getLocation(),
            'dateRegistered' => $user->getDateRegistered(),
            'dateLastLogin'  => $user->getDateLastLogin(),
            'active'         => $user->getActive(),
            'reset'          => $user->getReset());
        
        if (null === ($dateRegistered = $user->getDateRegistered())) {
        	$data['dateRegistered'] = date('Y-m-d H:i:s');
        	$this->getDbTable()->insert($data);
        } else {
        	$this->getDbTable()->update($data, array('userId=?', $user->getUserId()));
        }
    }
    
    /**
     * Find a user by userId.
     * 
     * @param string $userId
     * @param Default_Model_Users $user
     * @return void
     */
    public function find($userId, Default_Model_Users $user)
    {
    	$result = $this->getDbTable()->find($userId);
    	if (0 == count($result)) return;
        $row = $result->current();
        $user->setOptions($row->toArray());
    }
    
    /**
     * Fetch all user entries.
     * 
     * @return array
     */
    public function fetchAll()
    {
    	$resultSet = $this->getDbTable()->fetchAll();
    	$entries = array();
    	foreach ($resultSet as $row) {
    		$entry = new Default_Model_Users();
    		$entry->setOptions($row->toArray());
    		$entries[] = $entry;
    	}
    	return $entries;
    }
}
