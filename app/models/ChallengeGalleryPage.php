<?php
// @author          Kameloh
// @lastUpdated     2016-05-11

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\PageNumbers\PageNumbers as PageNumbers;

class ChallengeGalleryPage
{
    private $area = 0;
    private $challenge_id = 0;
    private $obj_array = [];

    private $challenge_row = [];
    private $entries_result = [];
    private $entries_rownum = 0;
    private $total_entries = 0;

    // Page Numbers
    public $PageNumbers = [];
    private $pageno = 0;
    private $ppage = 30;

    // Construct
    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Set Challenge ID
    final public function setChallengeId($challenge_id)
    {
        $method = 'ChallengeGalleryPage->setChallengeId()';

        $this->challenge_id = isset($challenge_id) ? (int) $challenge_id : 0;
        if ($this->challenge_id < 1)
        {
            SBC::devError('Challenge ID is not set',$method);
        }
    }

    // Set Page Number
    final public function setPageNumber($pageno)
    {
        $method = 'ChallengeGalleryPage->setPageNumber()';

        $this->pageno = isset($pageno) ? (int) $pageno : 0;
    }

    // Process
    final public function process()
    {
        $mehod = 'ChallengeGalleryPage->process()';

        // Initialize
        $db             = &$this->obj_array['db'];
        $User           = &$this->obj_array['User'];
        $challenge_id   = $this->challenge_id;
        if ($challenge_id < 1)
        {
            SBC::devError('Challenge ID is not set',$method);
        }

        // Open Connection
        $db->open();

        // User Optional + Frontpage
        $User->setFrontpage();
        $User->optional($db);

        // Get Challenge Info
        $this->getChallengeInfo($db);

        // Get Gallery
        $this->getGallery($db);

        // Process Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // Create Page Numbers
        $this->createPageNumbers();
    }

    // Get Challenge Info
    final private function getChallengeInfo(&$db)
    {
        $method = 'ChallengeGalleryPage->getChallengeInfo()';

        // Initialize
        $challenge_id   = $this->challenge_id;
        if ($challenge_id < 1)
        {
            SBC::devError('Challenge ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Challenge Info
        $sql = 'SELECT id, thread_id, points, name, description, requirements, total_entries, isdeleted
            FROM challenges
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$challenge_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $challenge_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($challenge_id < 1)
        {
            SBC::userError('Could not find challenge in database');
        }

        // Deleted?
        if ($row['isdeleted'] == 1)
        {
            SBC::userError('Sorry, this challenge no longer exists');
        }

        // Set Vars
        $this->challenge_row    = $row;
        $this->total_entries    = $row['total_entries'];
    }

    // Get Gallery
    final private function getGallery(&$db)
    {
        $method = 'ChallengeGalleryPage->getGallery()';

        // Initialize
        $ppage          = $this->ppage;
        $pageno         = $this->pageno;
        $offset         = $pageno * $ppage;
        $Member         = &$this->obj_array['Member'];
        $Images         = &$this->obj_array['Images'];
        $challenge_id   = $this->challenge_id;
        $table_name     = 'fc'.$challenge_id.'l';
        $entry_list     = '';
        if ($challenge_id < 1)
        {
            SBC::devError('Challenge ID is not set',$method);
        }

        // Main or Pending?
        if ($this->area == 1)
        {
            $ispending = 0;
        }
        else
        {
            $ispending = 1;
        }

        // Switch
        $db->sql_switch('sketchbookcafe_challenges');

        // Get entries
        $sql = 'SELECT entry_id
            FROM '.$table_name.'
            WHERE ispending='.$ispending.'
            ORDER BY id
            DESC
            LIMIT '.$offset.', '.$ppage;
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        // Got a gallery?
        if ($rownum > 0)
        {
            // Loop
            while ($trow = mysqli_fetch_assoc($result))
            {
                if ($trow['entry_id'] > 0)
                {
                    $entry_list .= $trow['entry_id'].' ';
                }
            }
            mysqli_data_seek($result,0);
        }

        // Clean
        $entry_list = SBC::idClean($entry_list);

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Do we have a list?
        if (!empty($entry_list))
        {
            // Get entries
            $sql = 'SELECT id, user_id, image_id
                FROM challenge_entries
                WHERE id IN('.$entry_list.')';
            $this->entries_result = $db->sql_query($sql);
            $this->entries_rownum = $db->sql_numrows($this->entries_result);

            // Add ids by result
            $Member->idAddRows($this->entries_result,'user_id');
            $Images->idAddRows($this->entries_result,'image_id');
        }
    }

    // Get Result
    final public function getResult()
    {
        return $this->entries_result;
    }

    // Get Rownum
    final public function getRownum()
    {
        return $this->entries_rownum;
    }

    // Get Total Entries
    final public function getTotalEntries()
    {
        return $this->total_entries;
    }

    // Create Page Numbers
    final private function createPageNumbers()
    {
        // Create Page Numbers
        $PageNumbersObj = new PageNumbers(array
        (
            'name'          => 'pagenumbers',
            'first'         => 0, // current page
            'current'       => $this->pageno, // page number
            'posts'         => $this->total_entries, // count(*) value
            'ppage'         => $this->ppage, // max number of posts per page
            'display'       => 4, // numbers of pages to display as links per side
            'link'          => 'https://www.sketchbook.cafe/challenges/'.$this->challenge_id.'/'.$this->area.'/{page_link}/', 
            'css_overlay'   => '',
            'css_inactive'  => 'pageNumbersItem pageNumbersItemUnselected',
            'css_active'    => 'pageNumbersItem pageNumbersItemSelected',
        ));
        $this->PageNumbers = array
        (
            'pagenumbers'   => $PageNumbersObj->getPageNumbers(),
            'pages_min'     => $PageNumbersObj->pages_min,
            'pages_max'     => $PageNumbersObj->pages_max,
            'pages_total'   => $PageNumbersObj->pages_total,
        );
    }

    // Get Challenge Row
    final public function getChallengeRow()
    {
        return $this->challenge_row;
    }

    // Set Area
    final public function setArea($area)
    {
        $method = 'ChallengeGalleryPage->setArea()';

        $this->area = isset($area) ? (int) $area : 0;
        if ($this->area < 1 || $this->area > 2)
        {
            SBC::devError('Invalid Area',$method);
        }
    }
}