<?php

/**
 * @author  Inash Zubair <inash@leptone.com>
 * @created Thu Aug 27, 2009 04:43 AM
 */

class Admin_Form_User extends Pub_Form
{
    public function init()
    {
        parent::init();
        
        /* Add userId element. */
        $this->addElement('text', 'userId', array(
            'label'         => 'Username',
            'size'          => 30,
            'maxLength'     => 50,
            'required'      => true,
            'filters'       => array('StringTrim', 'StringToLower'),
            'validators'    => array('Alnum',
                array('StringLength', false, array(1, 50))),
            'errorMessages' => array('Username is required')));
        
        /* Add name element. */
        $this->addElement('text', 'name', array(
            'label'         => 'Name',
            'size'          => 30,
            'maxLength'     => 100,
            'required'      => true,
            'filters'       => array('StringTrim'),
            'validators'    => array('NotEmpty'),
            'errorMessages' => array('Name is required')));
        
        /* Add email element. */
        $this->addElement('text', 'email', array(
            'label'         => 'Email',
            'size'          => 30,
            'maxLength'     => 100,
            'required'      => true,
            'filters'       => array('StringTrim'),
            'validators'    => array('EmailAddress'),
            'errorMessages' => array('Email is required')));
        
        /* Get member types from member type db table. */
        $memberTypesDbTable = new Default_Model_DbTable_MemberTypes();
        $memberTypes = $memberTypesDbTable->fetchAll(null, 'title ASC')->toArray();
        foreach ($memberTypes as $val) $vmt[$val['title']] = $val['title'];
        
        /* Add member type element. */
        $this->addElement('select', 'memberType', array(
            'label'    => 'Member Type',
            'required' => true));
        $this->memberType->addMultiOptions($vmt);
        
        /* Get system groups from the groups db table. */
        $groupsDbTable = new Default_Model_DbTable_Groups();
        $groups = $groupsDbTable->fetchAll(null, 'name ASC')->toArray();
        foreach ($groups as $group) $vg[$group['name']] = $group['title'];
        
        /* Add primaryGroup element. */
        $this->addElement('select', 'primaryGroup', array(
            'label'    => 'Primary Group',
            'required' => true));
        $this->primaryGroup->addMultiOptions($vg);
        
        /* Add website element. */
        $this->addElement('text', 'website', array(
            'label'     => 'Website',
            'size'      => 30,
            'maxLength' => 100,
            'filters'   => array('StringTrim')));
        
        /* Add company element. */
        $this->addElement('text', 'company', array(
            'label'     => 'Company',
            'size'      => 30,
            'maxLength' => 100,
            'filters'   => array('StringTrim')));
        
        /* Add location element. */
        $this->addElement('text', 'location', array(
            'label'     => 'Location',
            'size'      => 30,
            'maxLength' => 50,
            'filters'   => array('StringTrim')));
        
        /* Add the submit button. */
        $submit = $this->addElement('submit', 'update', array(
            'ignore' => true,
            'label'  => 'Update',
            'class'  => 'required'));
    }
}
