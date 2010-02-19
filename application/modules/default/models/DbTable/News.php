<?php

/**
 * DbTable for the news table.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Fri Jan 22, 2010 05:10 AM
 */

class Default_Model_DbTable_News extends Zend_Db_Table_Abstract
{
    protected $_name       = 'moss_news';
    protected $_primaryKey = 'newsId';
    protected $_referenceMap = array(
        'User' => array(
            'columns'       => array('userId'),
            'refTableClass' => 'Default_Model_DbTable_Users',
            'refColumns'    => array('userId')
        ));

    /**
     * Generates url as understood by the news/view action.
     *
     * @param array $item
     * @return string
     */
    public function getLink($item)
    {
        if (empty($item)) return false;
        $type  = strtolower($item['type']);
        $time  = strtotime($item['date']);
        $year  = date('Y', $time);
        $month = date('m', $time);
        
        switch ($type) {
            case 'announcement':
                $link = "announcement/{$item['newsId']}";
                break;
            case 'news':
                $link = "news/{$year}/{$month}/{$item['name']}";
                break;
        }
        
        return $link;
    }

    /**
     * Override abstract _fetch function to set an additional link field to the
     * rows.
     *
     * @param Zend_Db_Table_Select $select
     * @return array
     */
    public function _fetch($select)
    {
        $result = parent::_fetch($select);
        foreach ($result as $key => $item) {
            $result[$key]['link'] = $this->getLink($item);
        }
        return $result;
    }
}
