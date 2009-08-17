<?php

/**
 * Default Logs DbTable class. Encapsulates the database table logs within the
 * Zend_Db_Table_Gateway.
 * 
 * This is the Active Record model for the logs table in the database.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun Jun 7, 2009 06:28 AM
 */

class Default_Model_DbTable_Logs extends Zend_Db_Table_Abstract
{
    protected $_name       = 'logs';
    protected $_primaryKey = 'logId';
    protected $_referenceMap = array(
        'User' => array(
            'columns'       => array('userId'),
            'refTableClass' => 'Users',
            'refColumns'    => array('userId')));
}
