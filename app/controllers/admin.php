<?php
// Admin

class Admin extends Controller
{
    protected $obj_array = '';

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
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