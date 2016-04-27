<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;

class BlockUserEdit
{
    private $user_id = 0;
    public $form = [];
    public $result = [];
    public $rownum = 0;

    private $obj_array;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'BlockUserEdit->__construct()';

        // Initialize Objects
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];
        $this->obj_array    = &$obj_array;

        // Open Connection
        $db->open();

        // Required User + Process Data
        $User->setFrontpage();
        $User->required($db);
        $user_id        = $User->getUserId();
        $this->user_id  = $user_id;

        // Get Blocked Users
        $this->getBlockedUsers($db);

        // Process all data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // New Form
        $Form = new Form(array
        (
            'name'      => 'blockuserform',
            'action'    => 'https://www.sketchbook.cafe/settings/blockuser_submit/',
            'method'    => 'POST',
        ));

        // Username
        $Form->field['username'] = $Form->input(array
        (
            'name'          => 'username',
            'type'          => 'text',
            'max'           => 30,
            'value'         => '',
            'placeholder'   => 'username',
            'css'           => '',
        ));

        // Submit
        $Form->field['submit'] = $Form->submit(array
        (
            'name'      => 'submit',
            'css'       => '',
        ));

        // Set vars
        $this->form = $Form;
    }

    // Get Blocked Users
    private function getBlockedUsers(&$db)
    {
        $method = 'BlockUserEdit->getBlockedUsers()';

        // Initialize Objects
        $Member = &$this->obj_array['Member'];

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Initialize Vars
        $user_id    = $this->user_id;
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }   
        $table_name = 'u'.$user_id.'c';
        $type       = 1; // 1 blocked users

        // Get Users
        $sql = 'SELECT cid
            FROM '.$table_name.'
            WHERE type='.$type.'
            LIMIT 500'; // add a limit just in case <_>;
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        // Add Members to Global Members Objcet
        $Member->idAddRows($result,'cid');

        // Link
        $this->result   = $result;
        $this->rownum   = $rownum;
    }
}