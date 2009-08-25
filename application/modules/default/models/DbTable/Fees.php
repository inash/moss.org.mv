<?php

/**
 * Fees Table Gateway Model.
 * 
 * This is the Active Record model for the fees table in the database.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Tue Aug 25, 2009 08:15 AM
 */

class Default_Model_DbTable_Fees extends Zend_Db_Table_Abstract
{
    protected $_name       = 'fees';
    protected $_primaryKey = 'feeId';
    
    protected $_referenceMap    = array(
        'User' => array(
            'columns'       => array('userId'),
            'refTableClass' => 'Default_Model_DbTable_Users',
            'refColumns'    => array('userId')
        ));
}
