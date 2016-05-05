<?php
// @author          Kameloh
// @lastUpdated     2016-05-02

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\ForumAdmin\ForumAdmin as ForumAdmin;
use SketchbookCafe\Message\Message as Message;

class ForumThreadEditTitleSubmit
{
    private $thread_id = 0;
    private $forum_id = 0;
    private $user_id = 0;
    private $ip_address = '';
    private $time = 0;

    private $title = '';
    private $title_code = '';

    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ForumThreadEditTitleSubmit->__construct()';

        // Initialize
        $this->obj_array    = &$obj_array;
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];
        $this->time         = SBC::getTime();
        $this->ip_address   = SBC::getIpAddress();

        // Thread ID
        $this->thread_id    = isset($_POST['thread_id']) ? (int) $_POST['thread_id'] : 0;
        if ($this->thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Title
        $titleObj   = new Message(array
        (
            'name'          => 'name',
            'min'           => 1,
            'column_max'    => 250,
            'nl2br'         => 0,
            'basic'         => 0,
            'ajax'          => 0,
            'images'        => 0,
            'videos'        => 0,
        ));
        $titleObj->insert($_POST['name']);

        // Set 
        $this->title        = $titleObj->getMessage();
        $this->title_code   = $titleObj->getMessageCode();

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id = $User->getUserId();

        // Forum Admin
        $ForumAdmin = new ForumAdmin($this->obj_array);
        $ForumAdmin->setUserId($this->user_id);
        $ForumAdmin->getForumInfoByThreadId($this->thread_id);
        $ForumAdmin->process();
        $ForumAdmin->requireFlag('edit_thread');
        $this->forum_id = $ForumAdmin->getForumId();

        // Update Thread since we've already verified thread and forum admin
        $this->updateThread($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/forum/thread/'.$this->thread_id.'/');
        exit;
    }

    // Update Thread
    final private function updateThread(&$db)
    {
        $method = 'ForumThreadEditTitleSubmit->updateThread()';

        // Initialize
        $user_id    = SBC::checkNumber($this->user_id,'$this->user_id');
        $thread_id  = SBC::checkNumber($this->thread_id,'$this->thread_id');
        $title      = SBC::checkEmpty($this->title,'$this->title');
        $title_code = SBC::checkEmpty($this->title_code,'$this->title_code');
        $ip_address = $this->ip_address;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Thread
        $sql = 'UPDATE forum_threads
            SET ip_updated=?,
            title=?,
            title_code=?,
            last_user_id=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('sssii',$ip_address,$title,$title_code,$user_id,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}