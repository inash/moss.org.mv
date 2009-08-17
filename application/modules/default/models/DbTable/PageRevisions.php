<?php

/**
 * Page Revisions Model.
 * 
 * This is the Active Record model for the page_revisions table in the database.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon Jun 1, 2009 03:23 AM
 */

class Default_Model_DbTable_PageRevisions extends Zend_Db_Table_Abstract
{
    protected $_name       = 'page_revisions';
    protected $_primaryKey = 'pageRevisionId';
    
    protected $_referenceMap    = array(
        'Page' => array(
            'columns'       => array('pageId'),
            'refTableClass' => 'Default_Model_DbTable_Pages',
            'refColumns'    => array('pageId')
        ),
        'User' => array(
            'columns'       => array('userId'),
            'refTableClass' => 'Default_Model_DbTable_Users',
            'refColumns'    => array('userId')
        )
    );
}
