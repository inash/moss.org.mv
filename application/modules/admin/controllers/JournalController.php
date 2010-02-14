<?php

/**
 * Journal entry management controller to manage a general accounting journal.
 * The purpose of this module is to administer general financial transactions
 * and record them as a journal entry.
 *
 * @author  Inash Zubair <inash@leptone.com>
 * @created Tue Jan 19, 2010 05:43 PM
 */

class Admin_JournalController extends Pub_Controller_ApplicationAction
{
    public function headerAction()
    {
        static $permission = 'view';
    }

    public function indexAction()
    {
        static $permission = 'view';

        $start = $this->_request->getParam('start', 0);
        $limit = $start + 10;
        $select = $this->db->select()
            ->from(array('mj' => 'moss_journal'))
            ->joinLeft(array('u' => 'users'), 'u.userId=mj.userId', array('uname' => 'u.name'))
            ->where("mj.sequence BETWEEN {$start} AND {$limit}")
            ->order('dateEntry DESC')
            ->order('index');

        $stmt = $this->db->query($select);
        $records = $stmt->fetchAll();
        $this->view->records = $records;
    }

    public function newAction()
    {
        static $permission = 'add';
        
        /* Display form entry screen if request method is not post. */
        if (!$this->_request->isPost()) return false;

        /* Get posted fields. */
        $params = $this->_request->getPost();
        unset($params['submit']);

        /* Validate fields. */
        $params['dateEntry'] = date('Y-m-d H:i:s', strtotime($params['date']));
        $vd = new Zend_Validate_Date();
        if (!$vd->isValid($params['date'])) $invalid[] = 'date';

        /* Validate debit account name and amount fields. */
        foreach ($params['debit'] as $key => $debit) {

            /* If a debit account name is empty or invalid. */
            if ($debit == '') {
                $invalid[] = 'debit';
                break;
            }

            /* If a debit amount is empty or invalid. */
            if ($params['drAmt'][$key] == '') {
                $invalid[] = 'debit';
                break;
            }
        }

        /* Validate credit account name and amount fields. */
        foreach ($params['credit'] as $key => $credit) {

            /* If a credit account name is empty or invalid. */
            if ($credit == '') {
                $invalid[] = 'credit';
                break;
            }

            /* If a credit amount is empty or invalid. */
            if ($params['crAmt'][$key] == '') {
                $invalid[] = 'credit';
                break;
            }
        }

        /* Check if total of debits and credits match. */
        $drTotal = 0;
        $crTotal = 0;
        foreach ($params['drAmt'] as $val) $drTotal += $val;
        foreach ($params['crAmt'] as $val) $crTotal += $val;
        if ($drTotal != $crTotal) $invalid[] = 'total';

        /* Set internal entry created date. */
        $params['dateCreated'] = date('Y-m-d H:i:s');

        /* If all's well, insert the records to the database. */
        $journalDbTable = new Default_Model_DbTable_Journal();

        /* Get last sequence number from settings table. */
        $sequence = $this->db->query("SELECT mj_sequence FROM moss_settings")->fetch();
        $sequence = $sequence['mj_sequence'];
        $sequence++;
        
        /* Insert the debit side of the entry. */
        $totalAccounts = count($params['debit']) + count($params['credit']);
        $index = 1;
        $logData = null;
        foreach ($params['debit'] as $key => $debit) {
            $record = array(
                'userId'      => $this->user['userId'],
                'sequence'    => $sequence,
                'dateCreated' => $params['dateCreated'],
                'dateEntry'   => $params['dateEntry'],
                'index'       => ($index++.'/'.$totalAccounts),
                'accountName' => $debit,
                'side'        => 'Debit',
                'amount'      => $params['drAmt'][$key]);

            /* Only set the description field for the first debit record
             * for the entry. */
            if ($key == 0) $record['description'] = $params['description'];
            $journalDbTable->insert($record);

            /* Prepare log message. */
            $logData['debit'][] = $record;
        }

        /* Insert the credit side of the entry. */
        foreach ($params['credit'] as $key => $credit) {
            $record = array(
                'userId'      => $this->user['userId'],
                'sequence'    => $sequence,
                'dateCreated' => $params['dateCreated'],
                'dateEntry'   => $params['dateEntry'],
                'index'       => ($index++.'/'.$totalAccounts),
                'accountName' => $credit,
                'side'        => 'Credit',
                'amount'      => $params['crAmt'][$key]);

            $journalDbTable->insert($record);

            /* Prepare log message. */
            $logData['credit'][] = $record;
        }

        /* Update sequence settings. */
        $this->db->query("UPDATE moss_settings SET mj_sequence='{$sequence}'");

        /* Insert log entry. */
        $this->log->insert(array(
            'entity'    => 'admin_journal',
            'entityId'  => "sequence:{$sequence}",
            'code'      => 'entry',
            'message'   => "added new journal entry with sequence: {$sequence}",
            'timestamp' => $params['dateCreated'],
            'userId'    => $this->user['userId'],
            'data'      => gzdeflate(Zend_Json::encode($logData), 9)));

        /* If successful, redirect to index screen. */
        $this->_redirect('/admin/journal');
    }
}
