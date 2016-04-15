<?php

class ComposeNoteSubmit
{
    private $r_username = '';
    private $r_user_id = 0;
    private $user_id = 0;

    private $title = '';
    private $title_code = '';
    private $message = '';
    private $message_code = '';

    // Construct
    public function __construct()
    {
        // Classes and Functions
        sbc_class('UserTimer');
        sbc_class('Message');
        sbc_class('TextareaSettings');
        sbc_class('BlockCheck');
        sbc_function('get_username');

        // Globals
        global $db,$User;

        // Username
        $username           = '';
        $username           = get_username($_POST['username']);
        $this->r_username   = $username;

        // Note Title
        $titleObject        = new Message(array
        (
            'name'          => 'title',
            'min'           => 3,
            'column_max'    => 250,
            'nl2br'         => 0,
            'basic'         => 0,
            'ajax'          => 0,
            'images'        => 0,
            'videos'        => 0,
        ));
        $titleObject->insert($_POST['title']);

        // Set Vars
        $this->title        = $titleObject->getMessage();
        $this->title_code   = $titleObject->getMessageCode();

        // Textarea Settings
        $TextareaSettings   = new TextareaSettings('composenote');
        $message_settings   = $TextareaSettings->getSettings();

        // Message
        $messageObject      = new Message($message_settings);
        $messageObject->insert($_POST['message']);

        // Set vars
        $this->message      = $messageObject->getMessage();
        $this->message_code = $messageObject->getMessageCode();

        // =================================== DOTHISLAST

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $user_id = $User->getUserId();
        $this->user_id = $user_id;

        // User Timer
        $UserTimer = new UserTimer(array
        (
            'user_id'   => $user_id, 
        ));
        $UserTimer->setColumn('compose_note');
        $UserTimer->checkTimer($db);

        // Get User Information and Check
        $this->getOtherUser($db);

        // Set Vars
        $r_user_id = $this->r_user_id;

        // Check if they're blocking each other
        $BlockCheck = new BlockCheck(array
        (
            'user_id'       => $user_id,
            'r_user_id'     => $r_user_id,
        ));
        $BlockCheck->check($db);

        error('hokay lets do more \'cause title is '.$this->title.' and message is '.$this->message);


        // =================================== BOTTOM OF BEEP

        // User Timer
        $UserTimer->update($db);

        // Close Connection
        $db->close();
    }

    // Get Other User Information
    final private function getOtherUser(&$db)
    {
        // Initialize Vars
        $user_id    = $this->user_id;
        $r_user_id  = 0;
        $r_username = $this->r_username;
        if (empty($r_username))
        {
            error('Dev error: $r_username is not set for ComposeNoteSubmit->getOtherUser()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get User's Information
        $sql = 'SELECT id
            FROM users
            WHERE username=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('s',$r_username);
        if (!$stmt->execute())
        {
            error('Could not execute statement (get user information) for ComposeNoteSubmit->getOtherUser()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Check if the user exists
        $r_user_id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($r_user_id < 1)
        {
            error('Could not find user in database');
        }

        // Make sure we can't send messages to ourselves
        if ($r_user_id == $user_id)
        {
            error('Sorry, you cannot send mail to yourself');
        }

        // Set vars
        $this->r_user_id = $r_user_id;
    }




}
