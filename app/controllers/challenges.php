<?php
// @author          Kameloh
// @lastUpdated     2016-05-19

use SketchbookCafe\SBC\SBC as SBC;

class Challenges extends Controller
{
    protected $obj_array = '';

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Admin Pending
    public function pending_admin()
    {
        $method = 'challenges->pending_admin()';

        $this->model('ChallengesPendingAdminSubmit',$this->obj_array);
    }

    // Edit Pending
    public function pending_edit()
    {
        $method = 'challenges->pending_edit()';

        $this->model('ChallengesPendingEditSubmit',$this->obj_array);
    }

    // View Pending Application
    public function pending($app_id = 0)
    {
        $method = 'challenges->pending()';

        if ($app_id < 1)
        {
            SBC::userError('App ID is not set');
        }

        // Model
        $Page       = $this->model('ChallengesPendingPage',$this->obj_array);
        $Page->setApplicationId($app_id);
        $Page->process();
        $ChallengeForm  = $Page->getEditForm();
        $AdminForm      = $Page->getAdminForm();
        $app_row        = $Page->getAppRow();

        // View
        $this->view('sketchbookcafe/header');
        $this->view('challenges/pending_application',
        [
            'app_row'       => &$app_row,
            'ChallengeForm' => &$ChallengeForm,
            'AdminForm'     => &$AdminForm,
            'User'          => &$this->obj_array['User'],
        ]);
        $this->view('sketchbookcafe/footer');
    }

    // Apply
    public function apply()
    {
        $method = 'challenges->apply()';

        $this->model('ChallengesApplySubmit',$this->obj_array);
    }

    // View Entry
    public function entry($entry_id = 0)
    {
        $method = 'challenges->entry()';

        $entry_id = isset($entry_id) ? (int) $entry_id : 0;
        if ($entry_id < 1)
        {
            // Redirect
            header('Location: https://www.sketchbook.cafe/challenges/');
            exit;
        }

        // Model
        $PageObj = $this->model('ChallengeEntryPage',$this->obj_array);
        $PageObj->setEntryId($entry_id);
        $PageObj->process();

        // View
        $this->view('sketchbookcafe/header');
        $this->view('challenges/view_entry');
        $this->view('sketchbookcafe/footer');
    }

    // Main Page
    public function index($challenge_id = 0, $area = 0, $pageno = 0)
    {
        $method = 'challenges->index()';

        // Initialize
        $challenge_id   = isset($challenge_id) ? (int) $challenge_id : 0;
        $area           = isset($area) ? (int) $area : 0;
        $pageno         = isset($pageno) ? (int) $pageno : 0;
        if ($challenge_id < 1)
        {
            $challenge_id = 0;
        }

        // Gallery
        if ($challenge_id > 0 && ($area == 1 || $area == 2))
        {
            // Model
            $PageObj= $this->model('ChallengeGalleryPage',$this->obj_array);
            $PageObj->setChallengeId($challenge_id);
            $PageObj->setPageNumber($pageno);
            $PageObj->setArea($area);
            $PageObj->process();

            // Vars
            $entries_result = $PageObj->getResult();
            $entries_rownum = $PageObj->getRownum();
            $challenge_row  = $PageObj->getChallengeRow();

            // View
            $this->view('sketchbookcafe/header');
            $this->view('challenges/gallery',
            [
                'entries_result'    => &$entries_result,
                'entries_rownum'    => &$entries_rownum,
                'PageNumbers'       => &$PageObj->PageNumbers,
                'challenge_row'     => &$challenge_row,
            ]);
            $this->view('sketchbookcafe/footer');
        }

        // Index Page
        if ($challenge_id < 1)
        {
            // Model
            $PageObj = $this->model('ChallengesPage',$this->obj_array);
            $PageObj->process();

            // Set vars
            $result         = $PageObj->getResult();
            $rownum         = $PageObj->getRownum();
            $ChallengeForm  = $PageObj->getChallengeForm();
            $app_result     = $PageObj->getApplicationsResult();
            $app_rownum     = $PageObj->getApplicationsRownum();
            $Member         = &$this->obj_array['Member'];
            $User           = &$this->obj_array['User'];
            $app_id         = $PageObj->getAppId();

            // View
            $this->view('sketchbookcafe/header');
            $this->view('challenges/index',
            [
                'result'        => &$result,
                'rownum'        => &$rownum,
                'app_result'    => &$app_result,
                'app_rownum'    => &$app_rownum,
                'Member'        => &$Member,
                'User'          => &$User,
                'ChallengeForm' => &$ChallengeForm,
                'app_id'        => &$app_id,
            ]);
            $this->view('sketchbookcafe/footer');
        }
    }

}