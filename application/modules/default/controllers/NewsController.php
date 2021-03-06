<?php

/**
 * Default news controller.
 *
 * @author  Inash Zubair <inash@leptone.com>
 * @created Fri Jan 22, 2010 05:41 AM
 */

class NewsController extends Pub_Controller_Action
{
    public function indexAction()
    {
        $type = $this->_request->getParam('type', 'news');
        $page = $this->_request->getParam('page', 1);
        
        $typeParam = (ucfirst($type) == 'Announcements') ? 'Announcement' : 'News';
        $select = $this->db->select(array(
            'userId', 'date', 'type', 'featured',
            'name', 'title', 'excerpt'))
            ->from(array('mn' => 'moss_news'))
            ->joinLeft(array('u' => 'users'), 'u.userId=mn.userId', array('uname' => 'u.name'))
            ->where("type='{$typeParam}'")
            ->order('date DESC');
        
        $adapter   = new Zend_Paginator_Adapter_DbSelect($select);
        $paginator = new Zend_Paginator($adapter);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($page);

        $this->view->type = $type;
        $this->view->paginator = $paginator;
    }

    public function viewAction()
    {
        $year  = $this->_request->getParam('year');
        $month = $this->_request->getParam('month');
        $name  = $this->_request->getParam('name');
        $type  = $this->_request->getParam('type');
        $id    = $this->_request->getParam('id');

        $select = $this->db->select();
        $select->from(array('mn' => 'moss_news'))
            ->joinLeft(array('u' => 'users'), 'u.userId=mn.userId', array('uname' => 'u.name'));
        
        switch ($type) {
            case 'announcement':
                $select->where("type='Announcement'")
                    ->where('newsId=?');
                $stmt = $select->query(Zend_Db::FETCH_ASSOC, array($id));
                break;

            case 'news':
                $select->where("type='News'")
                    ->where("LEFT(`date`, 4)=?")
                    ->where("MID(`date`, 6, 2)=?")
                    ->where("mn.name=?");
                $stmt = $select->query(Zend_Db::FETCH_ASSOC, array($year, $month, $name));
        }
        
        /* Redirect to error404 if the news item does not exist. */
        if ($stmt->rowCount() == 0) {
            $this->_forward('error404', 'index', 'default');
            return true;
        }

        $ndbt = new Default_Model_DbTable_News();
        $news = $stmt->fetch();
        $news['link'] = $ndbt->getLink($news);
        $this->view->news = $news;

        /* Search flickr tags for related pictures. */
        $flickr = new Zend_Service_Flickr('');
        $tag = "mossorgmv{$type}{$news['newsId']}";
        $this->view->flickrTag = $tag;
        $results = $flickr->tagSearch($tag, array('per_page' => 14));

        if ($results->totalResultsAvailable > 0) {
            $this->view->flickr = $results;
        }
    }
}
