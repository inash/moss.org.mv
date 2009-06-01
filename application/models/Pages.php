<?php

/**
 * Pages Table Gateway Model.
 * 
 * This is the Active Record model for the pages table in the database.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun May 31, 2009 03:51 AM
 */

require_once 'PageRevisions.php';

class Pages extends Zend_Db_Table_Abstract
{
    protected $_name       = 'pages';
    protected $_primaryKey = 'pageId';
    protected $_dependentTables = array('PageRevisions');
    
    protected $_referenceMap    = array(
        'CreatedBy' => array(
            'columns'       => array('createdBy'),
            'refTableClass' => 'Users',
            'refColumns'    => array('userId')
        ),
        'ModifiedBy' => array(
            'columns'       => array('modifiedBy'),
            'refTableClass' => 'Users',
            'refColumns'    => array('userId')
        ),
        'Revision' => array(
            'columns'       => array('pageRevisionId'),
            'refTableClass' => 'PageRevisions',
            'refColumns'    => array('pageRevisionId')
        )
    );
}
