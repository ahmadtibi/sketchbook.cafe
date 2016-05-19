<?php
// @author          Kameloh
// @lastUpdated     2016-05-16

use SketchbookCafe\SBC\SBC as SBC;

class Entry extends Controller
{
    protected $obj_array = '';

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    public function entry_pending()
    {
        $this->model('EntryPendingSubmit',$this->obj_array);
    }

    public function index($entry_id = 0)
    {
        $method = 'Entry->index()';

        $entry_id = isset($entry_id) ? (int) $entry_id : 0;
        if ($entry_id < 1)
        {
            $entry_id = 0;
        }

        // View Entry
        if ($entry_id > 0)
        {
            $Page = $this->model('EntryViewPage',$this->obj_array);
            $Page->setEntryId($entry_id);
            $Page->process();
            $entry_row      = $Page->getEntryRow();
            $challenge_row  = $Page->getChallengeRow();
            $AdminForm      = $Page->getAdminForm();

            $this->view('sketchbookcafe/header');
            $this->view('entry/viewentry',
            [
                'User'          => &$this->obj_array['User'],
                'Comment'       => &$this->obj_array['Comment'],
                'Member'        => &$this->obj_array['Member'],
                'entry_row'     => &$entry_row,
                'challenge_row' => &$challenge_row,
                'AdminForm'     => &$AdminForm,
            ]);
            $this->view('sketchbookcafe/footer');
        }
        // Index Page
        else
        {
            $Page = $this->model('EntryIndexPage',$this->obj_array);
            $this->view('sketchbookcafe/header');
            $this->view('entry/index');
            $this->view('sketchbookcafe/footer');
        }
    }

}