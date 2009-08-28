<?php

/**
 * Member Types Table Gateway Model.
 * 
 * This is the Active Record model for the member_types table in the database.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Thu Aug 27, 2009 04:39 AM
 */

class Default_Model_DbTable_MemberTypes extends Zend_Db_Table_Abstract
{
    protected $_name       = 'member_types';
    protected $_primaryKey = 'title';
}
