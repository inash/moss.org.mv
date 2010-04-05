<?php

/**
 * Settings Db Table.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon Apr 5, 2010 05:33 AM
 */

class Default_Model_DbTable_Settings extends Zend_Db_Table_Abstract
{
    protected $_name       = 'moss_settings';
    protected $_primaryKey = 'settingId';
}
