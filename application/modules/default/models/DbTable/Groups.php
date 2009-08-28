<?php

/**
 * Groups Table Gateway Model.
 * 
 * This is the Active Record model for the groups table in the database.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Thu Aug 27, 2009 04:48 AM
 */

class Default_Model_DbTable_Groups extends Zend_Db_Table_Abstract
{
    protected $_name       = 'groups';
    protected $_primaryKey = 'name';
    
    protected $_dependentTables = array('Default_Model_DbTable_Users');
}
