<?php
// @author          Kameloh
// @lastUpdated     2016-05-16

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\SBCChallenges\SBCChallenges as SBCChallenges;
use SketchbookCafe\Form\Form as Form;

class EntryViewPage
{
    private $entry_id = 0;
    private $entry_row = [];
    private $challenge_row = [];

    private $AdminForm = '';

    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Set Entry ID
    final public function setEntryId($entry_id)
    {
        $method = 'EntryViewPage->setEntryId()';

        $this->entry_id = isset($entry_id) ? (int) $entry_id : 0;
        if ($this->entry_id < 1)
        {
            SBC::devError('Entry ID is not set',$method);
        }
    }

    // Process
    final public function process()
    {
        $method = 'EntryViewPage->process()';

        // Initialize
        $db     = &$this->obj_array['db'];
        $User   = &$this->obj_array['User'];

        // Open Connection
        $db->open();

        // User Optional
        $User->setFrontpage();
        $User->optional($db);

        // Get Entry Info
        $this->getEntry($db);

        // Challenge
        $SBCChallenges = new SBCChallenges($this->obj_array);
        $SBCChallenges->idAddOne($this->entry_row['challenge_id']);
        $SBCChallenges->process();
        $this->challenge_row = $SBCChallenges->getChallengeRow();

        // Process All Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // Create Admin Form
        if ($this->entry_row['ispending'] == 1 && $User->isAdmin())
        {
            $this->createAdminForm();
        }
    }

    // Get Entry
    final private function getEntry(&$db)
    {
        $method = 'EntryViewPage->getEntry()';

        // Initialize
        $Comment    = &$this->obj_array['Comment'];
        $Images     = &$this->obj_array['Images'];
        $Member     = &$this->obj_array['Member'];
        $entry_id   = $this->entry_id;
        if ($entry_id < 1)
        {
            SBC::devError('Entry ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Entry Info
        $sql = 'SELECT id, difficulty, challenge_id, comment_id, image_id, user_id, date_created,
            ispending, isdeleted
            FROM challenge_entries
            WHERE id=?
            LIMIT 1';
        $stmt               = $db->prepare($sql);
        $stmt->bind_param('i',$entry_id);
        $this->entry_row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $entry_id   = isset($this->entry_row['id']) ? (int) $this->entry_row['id'] : 0;
        if ($entry_id < 1)
        {
            SBC::userError('Could not find entry in database');
        }

        // Deleted?
        if ($this->entry_row['isdeleted'] == 1)
        {
            SBC::userError('Entry no longer exists');
        }

        // Add IDs
        $Comment->idAddOne($this->entry_row['comment_id']);
        $Images->idAddOne($this->entry_row['image_id']);
        $Member->idAddOne($this->entry_row['user_id']);
    }

    // Get Entry Row
    final public function getEntryRow()
    {
        return $this->entry_row;
    }

    // Get Challenge Row
    final public function getChallengeRow()
    {
        return $this->challenge_row;
    }

    // Create Form
    final private function createAdminForm()
    {
        $method = 'EntryViewPage->createAdminForm()';

        // New Form
        $AdminForm  = new Form(array
        (
            'name'      => 'adminform',
            'action'    => 'https://www.sketchbook.cafe/entry/entry_pending/',
            'method'    => 'POST',
        ));

        // Entry ID
        $AdminForm->field['entry_id'] = $AdminForm->hidden(array
        (
            'name'      => 'entry_id',
            'value'     => $this->entry_id,
        ));

        // Submit
        $AdminForm->field['submit'] = $AdminForm->submit(array
        (
            'name'      => 'Submit',
            'css'       => '',
        ));

        // Select
        $list[' ']          = 0;
        $list['Approve']    = 1;
        $list['Delete']     = 2;
        $input = array('name'=>'action');
        $value = 0;
        $AdminForm->field['action'] = $AdminForm->dropdown($input,$list,$value);

        // Confirm
        $AdminForm->field['confirm'] = $AdminForm->checkbox(array
        (
            'name'      => 'confirm',
            'value'     => 1,
            'checked'   => 0,
            'css'       => '',
        ));

        // Set
        $this->AdminForm = $AdminForm;
    }

    // Get Admin Form
    final public function getAdminForm()
    {
        return $this->AdminForm;
    }
}