<?php

/**
 * Projects module enables to create an maintain a list of on going projects and
 * manage them in a convenient way. By allowing members to participate and
 * manage actions for a project, the stake holders are able to monitor, manage
 * and complete projects in an efficient manner.
 *
 * @author  Inash Zubair <inash@leptone.com>
 * @created Mon Feb 15, 2010 09:57 PM
 */

class ProjectsController extends Pub_Controller_ApplicationAction
{
    /**
     * Fetches and displays all the public projects by status.
     * Private and restricted projects are collectively displayed within the
     * same view based on the user's relation to a project.
     *
     * @staticvar string $permission
     */
    public function indexAction()
    {
        static $permission = 'view';

        /* Prepare select. */
        $select = $this->db->select()
            ->from(
                array('p' => 'projects'),
                array('projectId', 'name', 'title', 'dateCreated', 'dateEnd',
                      'createdBy', 'status'))
            ->joinLeft(array('u' => 'users'), 'u.userId=p.createdBy', array('uname' => 'u.name'))
            ->where("p.active='Y'")
            ->order('p.dateEnd ASC');
        $result = $this->db->query($select)->fetchAll();
        $this->view->projects = $result;
    }

    public function viewAction()
    {
        static $permission = 'view';

        $name = $this->_request->getParam('name');
        $pdbt = new Default_Model_DbTable_Projects();

        /* Check whether the project exists. */
        $project = $pdbt->fetchRow("name='{$name}'");
        if (!$project) {
            $this->_forward('error404', 'index', 'default', array('page' => $name));
        }

        $this->view->project = $project;
    }
}
