<?php

/**
 * PagesMapper class.
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sun Aug 23, 2009 08:14 PM
 */

class Default_Model_PagesMapper extends Pub_Model_Mapper
{
    /**
     * Save an entry.
     * 
     * @param Default_Model_Pages $page
     * @return void
     */
    public function save(Default_Model_Pages $page)
    {
        $data = array(
            'pageId'         => $page->getPageId(),
            'name'           => $page->getName(),
            'title'          => $page->getTitle(),
            'dateCreated'    => $page->getDateCreated(),
            'dateModified'   => $page->getDateModified(),
            'pageRevisionId' => $page->getPageRevisionId(),
            'createdBy'      => $page->getCreatedBy(),
            'modifiedBy'     => $page->getModifiedBy(),
            'body'           => $page->getBody(),
            'table'          => $page->getTable(),
            'published'      => $page->getPublished());
        
        if (null === ($dateRegistered = $page->getDateCreated())) {
            $data['dateCreated'] = date('Y-m-d H:i:s');
            $this->getDbTable()->insert($data);
        } else {
            $this->getDbTable()->update($data, array('pageId=?', $page->getPageId()));
        }
    }
    
    /**
     * Find an entry by pageId.
     * 
     * @param string $pageId
     * @param Default_Model_Pages $page
     * @return void
     */
    public function find($pageId, Default_Model_Pages $page)
    {
        $result = $this->getDbTable()->find($pageId);
        if (0 == count($result)) return;
        $row = $result->current();
        $page->setOptions($row->toArray());
    }
    
    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Default_Model_Pages();
            $entry->setOptions($row->toArray());
            $entries[] = $entry;
        }
        return $entries;
    }
    
    public function fetchFullTextMatches($string)
    {
        $dbTable = $this->getDbTable();
        $db = $dbTable->getAdapter();
        $select = $db->select()
            ->from(array('p' => 'pages'), array('p.pageId', 'p.name', 'p.title'))
            ->where('MATCH (title, body) AGAINST (? IN BOOLEAN MODE)')
            ->query(Zend_DB::FETCH_ASSOC, array($string));

        $result = $select->fetchAll();
        return $result;
    }
}
