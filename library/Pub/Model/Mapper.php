<?php

/**
 * Abstract mapper class for Pub.
 * 
 * This mapper class abstracts out mapping application specific models with
 * their DbTables. If a different source is used for mapping, the mapper class
 * needs to be modified to derive data retrieval functionality from the new
 * data source.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun Aug 23, 2009 08:12 PM
 */

class Pub_Model_Mapper
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
     * Lazy loads Default_Model_DbTable_* if no instance registered.
     * 
     * @return Zend_Db_Table_Abstract
     */
    public function getDbTable()
    {
    	$dbTableClassName = get_class($this);
    	$dbTableClassName = str_replace('Default_Model_', '', $dbTableClassName);
    	$dbTableClassName = str_replace('Mapper', '', $dbTableClassName);
        if (null === $this->_dbTable) {
            $this->setDbTable('Default_Model_DbTable_' . $dbTableClassName);
        }
        return $this->_dbTable;
    }
}
