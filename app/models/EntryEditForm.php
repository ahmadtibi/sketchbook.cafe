<?php
// @author          Kameloh
// @lastUpdated     2016-05-09

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Entry\Entry as Entry;
use SketchbookCafe\Form\Form as Form;

class EntryEditForm
{
    public $Form;

    private $entry_id = 0;
    private $user_id = 0;

    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Set ID
    final public function setEntryId($entry_id)
    {
        $method = 'EntryEditForm->setEntryId()';

        $this->entry_id = isset($entry_id) ? (int) $entry_id : 0;
        if ($this->entry_id < 1)
        {
            SBC::devError('Entry ID is not set',$method);
        }
    }

    // Process
    final public function process()
    {
        $method = 'EntryEditForm->process()';

        // Initialize
        $db         = &$this->obj_array['db'];
        $User       = &$this->obj_array['User'];

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id = $User->getUserId();

        // Get Entry Info
        $this->getEntryInfo($db);

        // Close Connection
        $db->close();

        // Create Forms
        $this->createEntryForm();
    }

    // Get Entry Info
    final private function getEntryInfo(&$db)
    {
        $method = 'EntryEditForm->getEntryInfo()';

        // Initialize
        $user_id    = $this->user_id;
        $entry_id   = $this->entry_id;
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }
        if ($entry_id < 1)
        {
            SBC::devError('$entry_id is not set',$method);
        }

        // Get Entry
        $Entry      = new Entry($db);
        $entry_row  = $Entry->getEntryRow($entry_id);

        // Do they own the entry?
        if ($user_id != $entry_row['user_id'])
        {
            SBC::userError('Sorry, you may only edit entries that belong to you');
        }
    }

    // Create Entry Form
    final public function createEntryForm()
    {
        $method = 'EntryEditForm->createEntryForm()';

        // New Form
        $Form = new Form(array
        (
            'name'      => 'editentryform',
            'action'    => 'https://www.sketchbook.cafe/entry/edit/',
            'method'    => 'POST',
        ));

        // Entry ID
        $Form->field['entry_id'] = $Form->hidden(array
        (
            'name'      => 'entry_id',
            'value'     => $this->entry_id,
        ));

        // Submit
        $Form->field['submit'] = $Form->submit(array
        (
            'name'      => 'Submit',
            'css'       => '',
        ));

        // Set
        $this->Form = $Form;
    }
}