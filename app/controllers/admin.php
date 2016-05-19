<?php
// @author          Kameloh
// @lastUpdated     2016-05-17

use SketchbookCafe\SBC\SBC as SBC;

class Admin extends Controller
{
    protected $obj_array = '';

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Challenge Applications
    public function challenge_applications()
    {
        $method = 'admin->challenges_applications()';

        // Model
        $Page = $this->model('AdminChallengeApplicationsPage',$this->obj_array);
        $Page->process();
        $result = $Page->getResult();
        $rownum = $Page->getRownum();

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/admin_top', 
        [
            'User'              => &$this->obj_array['User'],
            'current_page'      => 'challenges_applications',
        ]);
        $this->view('admin/challenge_applications',
        [
            'result'    => &$result,
            'rownum'    => &$rownum,
        ]);
        $this->view('sketchbookcafe/admin_bottom');
        $this->view('sketchbookcafe/footer');
    }

    // Pending Entries
    public function pending_entries()
    {
        $method = 'admin->pending_entries()';

        // Model
        $PageObj        = $this->model('AdminPendingEntriesPage',$this->obj_array);
        $challenge_row  = $PageObj->getChallengeRow();
        $entries_result = $PageObj->getEntriesResult();
        $entries_rownum = $PageObj->getEntriesRownum();

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/admin_top', 
        [
            'User'              => &$this->obj_array['User'],
            'current_page'      => 'huh',
        ]);
        $this->view('admin/pending_entries',
        [
            'Member'            => &$this->obj_array['Member'],
            'Images'            => &$this->obj_array['Images'],
            'challenge_row'     => $challenge_row,
            'entries_result'    => &$entries_result,
            'entries_rownum'    => &$entries_rownum,
        ]);
        $this->view('sketchbookcafe/admin_bottom');
        $this->view('sketchbookcafe/footer');
    }

    // Fix Challenge Table
    public function fix_challenge_table($challenge_id = 0)
    {
        $method = 'admin->fix_challenge_table()';

        // Check
        $challenge_id = isset($challenge_id) ? (int) $challenge_id : 0;
        if ($challenge_id < 1)
        {
            SBC::devError('Challenge ID not set',$method);
        }

        // Model
        $ChallengeObj = $this->model('AdminChallengeFixtable',$this->obj_array);
        $ChallengeObj->setId($challenge_id);
        $ChallengeObj->process();
    }

    // Create new Challenge
    public function new_challenge()
    {
        $this->model('AdminChallengeSubmit',$this->obj_array);
    }

    // Manage Challenges
    public function manage_challenges()
    {
        $method = 'admin->manage_challenges()';

        $User           = &$this->obj_array['User'];
        $current_page   = 'challenges';

        // Model
        $PageObj            = $this->model('AdminChallengesPage',$this->obj_array);
        $Form               = $PageObj->Form;

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/admin_top', 
        [
            'User'          => $User,
            'current_page'  => $current_page,
        ]);
        $this->view('admin/challenges',
        [
            'Form'              => $Form,
            'categories_result' => &$PageObj->categories_result,
            'categories_rownum' => &$PageObj->categories_rownum,
            'challenges_result' => &$PageObj->challenges_result,
            'challenges_rownum' => &$PageObj->challenges_rownum,
        ]);
        $this->view('sketchbookcafe/admin_bottom');
        $this->view('sketchbookcafe/footer');
    }

    // Create Challenge Category
    public function create_challenge_category()
    {
        $this->model('AdminChallengeCategoriesSubmit',$this->obj_array);
    }

    // Challenge Categories
    public function challenge_categories()
    {
        $method = 'admin->challenge_categories()';

        // Objects
        $User   = &$this->obj_array['User'];

        // Model
        $PageObj            = $this->model('AdminChallengeCategoriesPage',$this->obj_array);
        $Form               = $PageObj->Form;
        $categories_result  = $PageObj->categories_result;
        $categories_rownum  = $PageObj->categories_rownum;

        $current_page   = 'challenge_categories';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/admin_top', 
        [
            'User'          => $User,
            'current_page'  => $current_page,
        ]);
        $this->view('admin/managechallengecategories',
        [
            'Form'              => $Form,
            'categories_result' => $categories_result,
            'categories_rownum' => $categories_rownum,
        ]);
        $this->view('sketchbookcafe/admin_bottom');
        $this->view('sketchbookcafe/footer');
    }

    // Edit Forum Admin
    public function edit_forum_admin($id = 0)
    {
        $method = 'admin->edit_forum_admin()';

        // Objects
        $User   = &$this->obj_array['User'];
        $Member = &$this->obj_array['Member'];

        // ID of Admin
        $id     = isset($id) ? (int) $id : 0;
        if ($id < 1)
        {
            SBC::devError('Admin ID not set',$method);
        }

        // Edit Forum Admin
        $EditObject = $this->model('AdminEditForumAdminPage', $id);
        $EditObject->process($this->obj_array);
        $Form       = $EditObject->Form;
        $forum_row  = $EditObject->forum_row;

        // Current Page
        $current_page = 'manage_forum_admins';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/admin_top', 
        [
            'User'          => $User,
            'current_page'  => $current_page,
        ]);
        $this->view('admin/editforumadmin',
        [
            'Form'      => $Form,
            'Member'    => $Member,
            'forum_row' => $forum_row,
            'id'        => $id,
        ]);
        $this->view('sketchbookcafe/admin_bottom');
        $this->view('sketchbookcafe/footer');
    }
    public function edit_forum_admin_submit()
    {
        // Model
        $this->model('AdminEditForumAdminSubmit', $this->obj_array);
    }

    // Manage Forum Admins
    public function manage_forum_admins()
    {
        // Objects
        $User   = &$this->obj_array['User'];

        // Model
        $ManageObject       = $this->model('AdminForumAdminsPage',$this->obj_array);
        $forums_result      = $ManageObject->forums_result;
        $forums_rownum      = $ManageObject->forums_rownum;
        $f_admin_result     = $ManageObject->f_admin_result;
        $f_admin_rownum     = $ManageObject->f_admin_rownum;
        $Form               = $ManageObject->Form;

        // Vars
        $current_page       = 'manage_forum_admins';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/admin_top', 
        [
            'User'          => $User,
            'current_page'  => $current_page,
        ]);
        $this->view('admin/manageforumadmins',
        [
            'forums_result'     => $forums_result,
            'forums_rownum'     => $forums_rownum,
            'f_admin_result'    => $f_admin_result,
            'f_admin_rownum'    => $f_admin_rownum,
            'Form'              => $Form,
        ]);
        $this->view('sketchbookcafe/admin_bottom');
        $this->view('sketchbookcafe/footer');
    }
    public function manage_forum_admins_submit()
    {
        // Model
        $this->model('AdminForumAdminsSubmit',$this->obj_array);
    }

    // Manage Forum
    public function manage_forum()
    {
        // User
        $User   = &$this->obj_array['User'];

        // Model
        $ManageObject       = $this->model('AdminManageForumPage',$this->obj_array);
        $categories_result  = $ManageObject->categories_result;
        $categories_rownum  = $ManageObject->categories_rownum;
        $forums_result      = $ManageObject->forums_result;
        $forums_rownum      = $ManageObject->forums_rownum;
        $f_admin_result     = $ManageObject->f_admin_result;
        $f_admin_rownum     = $ManageObject->f_admin_rownum;

        // Vars
        $current_page   = 'manage_forum';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/admin_top', 
        [
            'User'          => $User,
            'current_page'  => $current_page,
        ]);
        $this->view('admin/manageforum',
        [
            'categories_result' => $categories_result,
            'categories_rownum' => $categories_rownum,
            'forums_result'     => $forums_result,
            'forums_rownum'     => $forums_rownum,
            'f_admin_result'    => $f_admin_result,
            'f_admin_rownum'    => $f_admin_rownum,
        ]);
        $this->view('sketchbookcafe/admin_bottom');
        $this->view('sketchbookcafe/footer');
    }

    // Fix Forum Table
    public function fix_forum_table($updated = 0)
    {
        // Objects
        $User   = $this->obj_array['User'];

        // Updated
        $updated    = isset($updated) ? (int) $updated : 0;
        if ($updated != 1)
        {
            $updated = 0;
        }

        // Model
        $FixObject  = $this->model('AdminFixForumTablePage',$this->obj_array);
        $Form       = $FixObject->Form;

        // Current Page
        $current_page = 'fix_forum_table';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/admin_top', 
        [
            'User'          => $User,
            'current_page'  => $current_page,
        ]);
        $this->view('admin/fixforumtablepage', 
        [
            'Form'          => $Form,
            'updated'       => $updated,
        ]);
        $this->view('sketchbookcafe/admin_bottom');
        $this->view('sketchbookcafe/footer');
    }
    public function fix_forum_table_submit()
    {
        // Model
        $this->model('AdminFixForumTableSubmit',$this->obj_array);
    }

    // Fix User Table
    public function fix_user_table($updated = 0)
    {
        // Objects
        $User   = $this->obj_array['User'];

        // Updated
        $updated    = isset($updated) ? (int) $updated : 0;
        if ($updated != 1)
        {
            $updated = 0;
        }

        // Model
        $FixObject  = $this->model('AdminFixUserTablePage',$this->obj_array);
        $Form       = $FixObject->Form;

        // Current Page
        $current_page = 'fix_user_table';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/admin_top', 
        [
            'User'          => $User,
            'current_page'  => $current_page,
        ]);
        $this->view('admin/fixusertablepage', 
        [
            'User'          => $User,
            'Form'          => $Form,
            'updated'       => $updated,
        ]);
        $this->view('sketchbookcafe/admin_bottom');
        $this->view('sketchbookcafe/footer');
    }
    public function fix_user_table_submit()
    {
        // Model
        $this->model('AdminFixUserTableSubmit',$this->obj_array);
    }

    // Edit Forum
    public function forum_forums_edit($id = 0)
    {
        // Objects
        $User   = $this->obj_array['User'];

        // Initialize Vars
        $id = isset($id) ? (int) $id : 0;
        if ($id < 1)
        {
            error('Forum ID is not set');
        }

        // Model
        $ForumObject    = $this->model('AdminForumForumEditPage');
        $ForumObject->setId($id);
        $ForumObject->process($this->obj_array);
        $Form           = $ForumObject->Form;

        // Vars
        $current_page   = 'forumforums';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/admin_top', 
        [
            'User'          => $User,
            'current_page'  => $current_page,
        ]);
        $this->view('admin/forumforumsedit', 
        [
            'User'          => $User,
            'Form'          => $Form,
        ]);
        $this->view('sketchbookcafe/admin_bottom');
        $this->view('sketchbookcafe/footer');
    }
    public function forum_forums_edit_submit()
    {
        // Model
        $this->model('AdminForumForumEditSubmit',$this->obj_array);
    }

    // Forum Forums
    public function forum_forums()
    {
        // Objects
        $User   = $this->obj_array['User'];

        // Model
        $ForumsObject       = $this->model('AdminForumForumsPage',$this->obj_array);
        $Form               = $ForumsObject->Form;
        $categories_result  = $ForumsObject->categories_result;
        $categories_rownum  = $ForumsObject->categories_rownum;
        $forums_result      = $ForumsObject->forums_result;
        $forums_rownum      = $ForumsObject->forums_rownum;

        // Vars
        $current_page = 'forumforums';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/admin_top', 
        [
            'User'          => $User,
            'current_page'  => $current_page,
        ]);
        $this->view('admin/forumforums', 
        [
            'User'              => $User,
            'Form'              => $Form,
            'categories_result' => $categories_result,
            'categories_rownum' => $categories_rownum,
            'forums_result'     => $forums_result,
            'forums_rownum'     => $forums_rownum,
        ]);
        $this->view('sketchbookcafe/admin_bottom');
        $this->view('sketchbookcafe/footer');
    }
    public function forum_forums_submit()
    {
        // Model
        $this->model('AdminForumForumsSubmit',$this->obj_array);
    }

    // Edit Forum Category
    public function forum_categories_edit($id = 0)
    {
        // Objects
        $User   = $this->obj_array['User'];

        // Initialize Vars
        $id = isset($id) ? (int) $id : 0;
        if ($id < 1)
        {
            error('Category ID is not set');
        }

        // Model
        $CategoriesObject   = $this->model('AdminForumCategoriesEditPage');
        $CategoriesObject->setId($id);
        $CategoriesObject->process($this->obj_array);
        $Form               = $CategoriesObject->Form;

        // Vars
        $current_page   = 'forumcategories';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/admin_top', 
        [
            'User'          => $User,
            'current_page'  => $current_page,
        ]);
        $this->view('admin/forumcategoriesedit', 
        [
            'User'          => $User,
            'Form'          => $Form,
        ]);
        $this->view('sketchbookcafe/admin_bottom');
        $this->view('sketchbookcafe/footer');
    }
    public function forum_categories_edit_submit()
    {
        // Model
        $this->model('AdminForumCategoriesEditSubmit',$this->obj_array);
    }

    // Forum Categories
    public function forum_categories()
    {
        // Objects
        $User   = $this->obj_array['User'];

        // Model
        $CategoriesObject   = $this->model('AdminForumCategoriesPage',$this->obj_array);
        $Form               = $CategoriesObject->Form;
        $result             = $CategoriesObject->result;
        $rownum             = $CategoriesObject->rownum;

        // Vars
        $current_page   = 'forumcategories';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/admin_top', 
        [
            'User'          => $User,
            'current_page'  => $current_page,
        ]);
        $this->view('admin/forumcategories', 
        [
            'User'      => $User,
            'Form'      => $Form,
            'result'    => $result,
            'rownum'    => $rownum,
        ]);
        $this->view('sketchbookcafe/admin_bottom');
        $this->view('sketchbookcafe/footer');
    }
    public function forum_categories_submit()
    {
        // Model
        $this->model('AdminForumCategoriesSubmit',$this->obj_array);
    }

    // Main Page
    public function index()
    {
        // Objects
        $User   = $this->obj_array['User'];

        // Model
        $this->model('AdminPage',$this->obj_array);

        // Vars
        $current_page = '';

        // View
        $this->view('sketchbookcafe/header');
        $this->view('sketchbookcafe/admin_top', 
        [
            'User'          => $User,
            'current_page'  => $current_page,
        ]);
        $this->view('admin/index', 
        [
            'User'  => $User,
        ]);
        $this->view('sketchbookcafe/admin_bottom');
        $this->view('sketchbookcafe/footer');
    }
}