<?php

/**
 * Default news controller.
 *
 * @author  Inash Zubair <inash@leptone.com>
 * @created Fri Jan 22, 2010 05:41 AM
 */

class NewsController extends Pub_Controller_Action
{
    public function viewAction()
    {
        $year  = $this->_request->getParam('year');
        $month = $this->_request->getParam('month');
        $name  = $this->_request->getParam('name');

        $select = $this->db->select();
        $select->from(array('mn' => 'moss_news'))
            ->joinLeft(array('u' => 'users'), 'u.userId=mn.userId', array('uname' => 'u.name'))
            ->where("LEFT(`date`, 4)=?")
            ->where("MID(`date`, 6, 2)=?")
            ->where("mn.name=?");

        $stmt = $select->query(Zend_Db::FETCH_ASSOC, array($year, $month, $name));
        
        /* Redirect to error404 if the news item does not exist. */
        if ($stmt->rowCount() == 0) {
            $this->_forward('error404', 'index', 'default');
            return true;
        }

        $ndbt = new Default_Model_DbTable_News();
        $news = $stmt->fetch();
        $news['link'] = $ndbt->getLink($news);
        $this->view->news = $news;
    }
}
