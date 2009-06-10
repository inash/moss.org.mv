<?php

/**
 * Logs Model.
 * 
 * This is the Active Record model for the logs table in the database.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun Jun 7, 2009 06:28 AM
 */

class Logs extends Zend_Db_Table_Abstract
{
    protected $_name       = 'logs';
    protected $_primaryKey = 'logId';
    protected $_referenceMap = array(
        'User' => array(
            'columns'       => array('userId'),
            'refTableClass' => 'Users',
            'refColumns'    => array('userId')));
}
