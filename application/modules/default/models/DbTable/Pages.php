<?php

/**
 * Pages Table Gateway Model.
 * 
 * This is the Active Record model for the pages table in the database.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun May 31, 2009 03:51 AM
 */

class Default_Model_DbTable_Pages extends Zend_Db_Table_Abstract
{
    protected $_name       = 'pages';
    protected $_primaryKey = 'pageId';
    protected $_dependentTables = array('Default_Model_DbTable_PageRevisions');
    
    protected $_referenceMap    = array(
        'CreatedBy' => array(
            'columns'       => array('createdBy'),
            'refTableClass' => 'Default_Model_DbTable_Users',
            'refColumns'    => array('userId')
        ),
        'ModifiedBy' => array(
            'columns'       => array('modifiedBy'),
            'refTableClass' => 'Default_Model_DbTable_Users',
            'refColumns'    => array('userId')
        ),
        'Revision' => array(
            'columns'       => array('pageRevisionId'),
            'refTableClass' => 'Default_Model_DbTable_PageRevisions',
            'refColumns'    => array('pageRevisionId')
        )
    );
}
