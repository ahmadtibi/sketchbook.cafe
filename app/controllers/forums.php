<?php
// @author          Kameloh
// @lastUpdated     2016-05-02
// Forums Controller
// Main Forums Page!

class Forums extends Controller
{
    protected $obj_array = '';

    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Main Page
    public function index()
    {
        // Header
        header('Location: https://www.sketchbook.cafe/forum/');
        exit;
/*
        // Objects
        $User   = $this->obj_array['User'];

        // Model
        $MainPageObject = $this->model('ForumMain',$this->obj_array);
        $categories_result  = $MainPageObject->categories_result;
        $categories_rownum  = $MainPageObject->categories_rownum;
        $forums_result      = $MainPageObject->forums_result;
        $forums_rownum      = $MainPageObject->forums_rownum;

        // View
        $this->view('sketchbookcafe/header');
        $this->view('forum/index',
        [
            'User'              => $User,
            'categories_result' => $categories_result,
            'categories_rownum' => $categories_rownum,
            'forums_result'     => $forums_result,
            'forums_rownum'     => $forums_rownum,
        ]);
        $this->view('sketchbookcafe/footer');
*/
    }
}