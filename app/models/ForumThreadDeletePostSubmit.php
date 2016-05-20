<?php
// @author          Kameloh
// @lastUpdated     2016-05-20

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\ForumAdmin\ForumAdmin as ForumAdmin;

class ForumThreadDeletePostSubmit
{
    private $comment_id = 0;

    private $time = 0;
    private $ip_address = '';

    private $user_id = 0;

    public function __construct(&$obj_array)
    {
        $method = 'ForumThreadDeletePostSubmit->__construct()';

        // Initialize
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];
        $this->time         = SBC::getTime();
        $this->ip_address   = SBC::getIpAddress();
        $this->comment_id   = isset($_POST['comment_id']) ? (int) $_POST['comment_id'] : 0;
        $action             = isset($_POST['action']) ? (int) $_POST['action'] : 0;
        $confirm            = isset($_POST['confirm']) ? (int) $_POST['confirm'] : 0;
        if ($this->comment_id < 1)
        {
            SBC::devError('Comment ID is not set',$method);
        }
        if ($action != 1)
        {
            SBC::userError('Please select an action');
        }
        if ($confirm != 1)
        {
            SBC::userError('You must confirm action to continue');
        }

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id = $User->getUserId();

        // Forum Admin
        $ForumAdmin = new ForumAdmin($obj_array);
        $ForumAdmin->setUserId($this->user_id);
        $ForumAdmin->getForumInfoByCommentId($this->comment_id);
        $ForumAdmin->process();
        $ForumAdmin->requireFlag('delete_post');
        $comment_type = $ForumAdmin->getCommentType();
        $thread_id = $ForumAdmin->getThreadId();

        // Comment Replies Only - not thread post
        if ($comment_type != 3)
        {
            SBC::userError('Sorry, this form only works for forum replies');
        }

        // Mark comment as deleted
        $this->updateComment($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/forum/thread/'.$thread_id.'/');
        exit;
    }

    // Mark Comment as Deleted
    final private function updateComment(&$db)
    {
        $method = 'ForumThreadDeletePostSubmit->updateComment()';

        // Initialize
        $comment_id = $this->comment_id;
        if ($comment_id < 1)
        {
            SBC::devError('Comment ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update comment
        $sql = 'UPDATE sbc_comments
            SET isdeleted=1
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$comment_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

}