<?php

/**
 * 
 * @author  Inash Zubair <inash@leptone.com>
 * @created Thu Aug 27, 2009 07:00 AM
 */

class Admin_Form_Subscription extends Pub_Form
{
    public function init()
    {
        parent::init();
        
        /* Add userId element. */ 
        $this->addElement('hidden', 'userId');
        
        /* Get member types from member type db table. */
        $memberTypesDbTable = new Default_Model_DbTable_MemberTypes();
        $memberTypes = $memberTypesDbTable->fetchAll(null, 'title ASC')->toArray();
        foreach ($memberTypes as $val) $vmt[$val['title']] = $val['title'];
        
        /* Add member type element. */
        $this->addElement('select', 'memberType', array(
            'label'    => 'Member Type',
            'required' => true));
        $this->memberType->addMultiOptions($vmt);
        
        /* Prepare forTheYear field values. */
        $year  = date('Y');
        $years = array();
        for ($i=1; $i<6; $i++) {
            $date = date('Y', mktime(0, 0, 0, 0, 1, $year+$i));
            $years[$date] = $date;
        }
        
        /* Add forTheYear field. A selection of years starting from the current
         * year. */
        $this->addElement('select', 'forTheYear', array(
            'label'    => 'For The Year',
            'required' => true));
        $this->forTheYear->addMultiOptions($years);
        
        /* Add submit button. */
        $this->addElement('submit', 'add', array(
            'label'  => 'Add',
            'ignore' => true));
    }
}
