<?php

/**
 * News form.
 *
 * @author  Inash Zubair <inash@leptone.com>
 * @created Sat Jan 30, 2010 05:03 AM
 */

class Admin_Form_News extends Zend_Form
{
    public function init()
    {
        /* Add newsId element. */
        $this->addElement('hidden', 'newsId');

        /* Add date element. */
        $this->addElement('text', 'date', array(
            'label'    => 'Date',
            'required' => true));
        
        /* Add news type element. */
        $this->addElement('select', 'type', array(
            'label'    => 'Type',
            'required' => true));
        $this->type->addMultiOptions(array(
            'News' => 'News',
            'Announcement' => 'Announcement'));

        /* Add name element. */
        $this->addElement('text', 'name', array(
            'label'    => 'Name',
            'required' => true,
            'size'     => 50));

        /* Add name element. */
        $this->addElement('text', 'title', array(
            'label'    => 'Title',
            'required' => true,
            'size'     => 50));

        /* Add content element. */
        $this->addElement('textarea', 'n_content', array(
            'label'    => 'Content',
            'required' => true,
            'rows'     => 10));

        /* Add submit button. */
        $this->addElement('submit', 'update', array(
            'label'  => 'Update',
            'ignore' => true));
    }
}
