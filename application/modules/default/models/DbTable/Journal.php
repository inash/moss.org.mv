<?php

/**
 * Default Journal DbTable class.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon Jan 25, 2010 05:51 PM
 */

class Default_Model_DbTable_Journal extends Zend_Db_Table_Abstract
{
    protected $_name       = 'moss_journal';
    protected $_primaryKey = 'mjId';
    protected $_referenceMap = array(
        'User' => array(
            'columns'       => array('userId'),
            'refTableClass' => 'Users',
            'refColumns'    => array('userId')));
}
