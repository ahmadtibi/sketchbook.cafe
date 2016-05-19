<?php
// @author          Kameloh
// @lastUpdated     2016-05-04

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Form\Form as Form;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;

class AdminChallengesPage
{
    public $Form = '';
    public $categories_result;
    public $categories_rownum = 0;
    public $challenges_result;
    public $challenges_rownum = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AdminChallengesPage->__construct()';

        // Initialize
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Open Connection
        $db->open();

        // Admin Required + Process Data
        $User->setFrontpage();
        $User->admin($db);
        $User->requireAdminFlag('challenges');

        // Get Challenges
        $this->getChallenges($db);

        // Get Challenge Categories
        $this->getCategories($db);

        // Process All User Data Last!
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();

        // Settings
        $TS                 = new TextareaSettings('challenge_description');
        $TS->setValue('');
        $message_settings   = $TS->getSettings();
        unset($TS);
        $TS                 = new TextareaSettings('challenge_requirements');
        $TS->setValue('');
        $message_settings2  = $TS->getSettings();

        // Form
        $Form   = new Form(array
        (
            'name'      => 'newchallengeform',
            'action'    => 'https://www.sketchbook.cafe/admin/new_challenge/',
            'method'    => 'POST',
        ));

        // Description
        $Form->field['description'] = $Form->textarea($message_settings);

        // Requirements
        $Form->field['requirements'] = $Form->textarea($message_settings2);

        // Submit
        $Form->field['submit'] = $Form->submit(array
        (
            'name'  => 'submit',
            'css'   => '',
        ));

        // Name
        $Form->field['name']    = $Form->input(array
        (
            'name'          => 'name',
            'type'          => 'text',
            'max'           => 150,
            'value'         => '',
            'placeholder'   => 'challenge name',
            'css'           => 'input500',
        ));

        // Points
        $Form->field['points']  = $Form->input(array
        (
            'name'          => 'points',
            'type'          => 'text',
            'max'           => 3,
            'value'         => '0',
            'placeholder'   => '',
            'css'           => 'input300',
        ));

        // User (creator of the challenge)
        $Form->field['username'] = $Form->input(array
        (
            'name'          => 'username',
            'type'          => 'text',
            'max'           => 30,
            'value'         => '',
            'placeholder'   => 'username',
            'css'           => 'input300',
        ));

        // Categories
        $list       = [];
        $list[' ']  = 0;
        $input      = array('name'=>'category_id',);
        $value      = 0;
        if ($this->categories_rownum > 0)
        {
            // Loop
            while ($trow = mysqli_fetch_assoc($this->categories_result))
            {
                $temp_name          = $trow['name'];
                $list[$temp_name]   = $trow['id'];
            }
            mysqli_data_seek($this->categories_result,0);
        }
        $Form->field['category'] = $Form->dropdown($input,$list,$value);

        // Thread ID
        $Form->field['thread']  = $Form->input(array
        (
            'name'          => 'thread_id',
            'type'          => 'text',
            'max'           => 10,
            'value'         => '0',
            'placeholder'   => 'thread ID',
            'css'           => 'input300',
        ));

        // Set
        $this->Form = $Form;
    }

    // Get Challenge Categories
    final private function getCategories(&$db)
    {
        $method = 'AdminChallengesPage->getCategories()';

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Categories
        $sql = 'SELECT id, name
            FROM challenge_categories
            WHERE isdeleted=0
            ORDER BY name
            ASC';
        $this->categories_result = $db->sql_query($sql);
        $this->categories_rownum = $db->sql_numrows($this->categories_result);
    }

    // Get Challenges
    final private function getChallenges(&$db)
    {
        $method = 'AdminChallengesPage->getChallenges()';

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Challenges
        $sql = 'SELECT id, category_id, name
            FROM challenges
            WHERE isdeleted=0
            ORDER BY id
            DESC
            LIMIT 20';
        $this->challenges_result    = $db->sql_query($sql);
        $this->challenges_rownum    = $db->sql_numrows($this->challenges_result);
    }
}