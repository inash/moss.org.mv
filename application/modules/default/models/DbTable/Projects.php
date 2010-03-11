<?php

/**
 * Projects db table.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Thu Mar 11, 2010 05:38 AM
 */

class Default_Model_DbTable_Projects extends Zend_Db_Table_Abstract
{
    protected $_name       = 'projects';
    protected $_primaryKey = 'projectId';
    
    protected $_referenceMap    = array(
        'CreatedBy' => array(
            'columns'       => array('createdBy'),
            'refTableClass' => 'Default_Model_DbTable_Users',
            'refColumns'    => array('userId')));

    public function getMembership($userId)
    {
        //
    }

    public function getPermission($userId)
    {
        //
    }
}
