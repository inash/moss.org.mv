<?php

/**
 * Users Model.
 * 
 * This is the Active Record model for the users table in the database.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun May 31, 2009 06:44 AM
 */

class Default_Model_DbTable_Users extends Zend_Db_Table_Abstract
{
    protected $_name       = 'users';
    protected $_primaryKey = 'userId';
    protected $_dependentTables = array('Pages', 'PageRevisions');
}
