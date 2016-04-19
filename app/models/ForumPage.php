<?php

class ForumPage
{
    public $Form = '';
    private $forum_id = 0;
    public $forum_row = [];

    // Construct
    public function __construct()
    {

    }

    // Set Forum ID
    final public function setForumId($forum_id)
    {
        $this->forum_id = isset($forum_id) ? (int) $forum_id : 0;
        if ($this->forum_id < 1)
        {
            error('Dev error: $forum_id is not set for ForumPage->setForumId()');
        }
    }

    // Process
    final public function process(&$obj_array)
    {
        // Initialize Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Initialize Vars
        $forum_id   = $this->forum_id;
        if ($forum_id < 1)
        {
            error('Dev error: $forum_id is not set for ForumPage->process()');
        }

        // Classes and Functions
        sbc_class('Form');
        sbc_class('TextareaSettings');

        // Open Connection
        $db->open();

        // Admin Required + Process Data
        $User->setFrontpage();
        $User->optional($db);

        // Process all data
        $ProcessAllData = new ProcessAllData();

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Forum Information
        $sql = 'SELECT id, parent_id, name, description, isforum, isdeleted
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get forum information) for ForumPage->process()');
        }
        $result     = $stmt->get_result();
        $forum_row  = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();


        // Check
        $forum_id = isset($forum_row['id']) ? (int) $forum_row['id'] : 0;
        if ($forum_id < 1)
        {
            error('Forum not found');
        }

        // Make sure it's a forum and not a category
        if ($forum_row['isforum'] != 1)
        {
            error('Invalid forum');
        }

        // Check if deleted
        if ($forum_row['isdeleted'] == 1)
        {
            error('Forum no longer exists');
        }

        // Get Parent Category
        $parent_id = $forum_row['parent_id'];
        if ($parent_id < 1)
        {
            error('Dev error: $parent_id is not set for Forum('.$forum_id.') in ForumPage->process()');
        }

        // Parent
        $sql = 'SELECT id, name
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$parent_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get parent information) for ForumPage->process()');
        }
        $result         = $stmt->get_result();
        $category_row   = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Set
        $this->forum_row    = $forum_row;
        $this->category_row = $category_row;

        // Close Connection
        $db->close();

        // Form
        $Form   = new Form(array
        (
            'name'      => 'newforumthread',
            'action'    => 'https://www.sketchbook.cafe/forum/new_thread/',
            'method'    => 'POST',
        ));

        // Forum ID
        $Form->field['forum_id'] = $Form->hidden(array
        (
            'name'      => 'forum_id',
            'value'     => $forum_id,
        ));

        // Title
        $Form->field['name']     = $Form->input(array
        (
            'name'          => 'name',
            'type'          => 'text',
            'max'           => 100,
            'value'         => '',
            'placeholder'   => 'title',
            'css'           => 'input500 fpInputTitle',
        ));

        // Textarea Settings
        $TextareaSettings = new TextareaSettings('forum_thread');
        $TextareaSettings->setValue('');
        $message_settings   = $TextareaSettings->getSettings();

        // Textarea
        $Form->field['message'] = $Form->textarea($message_settings);

        // Set 
        $this->Form = $Form;
    }
}