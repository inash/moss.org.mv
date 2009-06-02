<?php

/**
 * New Users Model.
 * 
 * This is the Active Record model for the users_new table in the database.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Tue Jun 02, 2009 04:41 AM
 */

require_once 'Users.php';

class NewUsers extends Zend_Db_Table_Abstract
{
    protected $_name       = 'users_new';
    protected $_primaryKey = 'unId';
    protected $_referenceMap = array(
        'User' => array(
            'columns'       => array('userId'),
            'refTableClass' => 'Users',
            'refColumns'    => array('userId')));
}
