<?php
// @author          Kameloh
// @lastUpdated     2016-05-05

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\UserTimer\UserTimer as UserTimer;
use SketchbookCafe\ImageFile\ImageFile as ImageFile;
use SketchbookCafe\Message\Message as Message;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\ThreadReply\ThreadReply as ThreadReply;
use SketchbookCafe\ChallengeOrganizer\ChallengeOrganizer as ChallengeOrganizer;
use SketchbookCafe\ForumOrganizer\ForumOrganizer as ForumOrganizer;
use SketchbookCafe\StatsOrganizer\StatsOrganizer as StatsOrganizer;

class ChallengeEntrySubmit
{
    private $user_id = 0;
    private $ip_address = '';
    private $rd = 0;
    private $time = 0;
    private $challenge_id = 0;
    private $thread_id = 0;
    private $forum_id = 0;

    // Generated
    private $entry_id = 0;
    private $image_id = 0;
    private $comment_id = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ChallengeEntrySubmit->__construct()';

        // Initialize
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];
        $this->time         = SBC::getTime();
        $this->ip_address   = SBC::getIpAddress();
        $this->rd           = SBC::rd();

        // Challenge ID
        $this->challenge_id = isset($_POST['challenge_id']) ? (int) $_POST['challenge_id'] : 0;
        if ($this->challenge_id < 1)
        {
            SBC::devError('$challenge_id is not set',$method);
        }

        // Image File
        $ImageFile  = new ImageFile(array
        (
            'name'          => 'imagefile',
            'max_filesize'  => 4194304, // 4 mb
            'required'      => 1,
            'allow_gif'     => 1,
            'allow_png'     => 1,
            'allow_jpg'     => 1,
            'allow_apng'    => 0,
            'width_min'     => 10,
            'width_max'     => 10000,
            'height_min'    => 10,
            'height_max'    => 10000,
        ));
        $ImageFile->sendFile();

        // Textarea Settings
        $TextareaSettings   = new TextareaSettings('forum_reply');
        $message_settings   = $TextareaSettings->getSettings();

        // Message
        $messageObject      = new Message($message_settings);
        $messageObject->insert($_POST['message']);

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id = $User->getUserId();

        // Check?
        if (!$ImageFile->hasFile())
        {
            SBC::devError('No file selected',$method);
        }

        // User Timer
        $UserTimer  = new UserTimer(array
        (
            'user_id'   => $this->user_id,
        ));
        $UserTimer->setColumn('forum_reply');
        $UserTimer->checkTimer($db);

        // Get Challenge Info
        $this->getChallengeInfo($db);

        // Get Thread Info
        $this->getThreadInfo($db);

        // Create Image
        $this->createImage($db,$ImageFile);

        // Create Thread Reply
        $this->createReply($db,$messageObject);

        // Create Challenge Entry... to store comment info and image info
        $this->createChallengeEntry($db);

        // Insert Comment Into Thread
        $this->insertComment($db);

        // Insert Comment into Challenge Table
        $this->insertIntoChallengeTable($db);

        // Update Comment Info
        $this->updateComment($db);

        // User Timer
        $UserTimer->update($db);

        // Forum Organizer
        $ForumOrganizer = new ForumOrganizer($db);
        $ForumOrganizer->threadUniqueComments($this->thread_id);
        $ForumOrganizer->threadTotalReplies($this->thread_id);
        $ForumOrganizer->threadUpdateInfo($this->thread_id);
        $ForumOrganizer->threadUpdateBumpDate($this->thread_id);
        $ForumOrganizer->forumTotalPostsAddOne($this->forum_id);
        $ForumOrganizer->forumUpdateInfo($this->forum_id);

        $total_comments = $ForumOrganizer->threadGetTotalComments($this->thread_id);

        // StatsOrganizer
        $StatsOrganizer = new StatsOrganizer($db);

        // Add Total Posts for User
        $StatsOrganizer->userForumPostAdd($this->user_id);

        // Close Connection
        $db->close();

        // Calculate Page
        $ppage  = 10;
        $total_comments -= 1; // subtract one since the forumorganizer adds +1 for the forum thread's post
        if ($total_comments < 1)
        {
            $total_comments = 0;
        }
        $pageno = SBC::currentPage($ppage,$total_comments);

        // Header
        header('Location: https://www.sketchbook.cafe/forum/thread/'.$this->thread_id.'/'.$pageno.'/#recent');
        exit;
    }

    // Create Image
    final private function createImage(&$db,&$ImageFile)
    {
        $method = 'ChallengeEntrySubmit->createImage()';

        // Get File Information
        $file_info  = $ImageFile->getInfo();

        // Create New Image
        $ImageFile->setUserId($this->user_id);
        $ImageFile->createImage($db);
        $this->image_id = $ImageFile->getImageId();
    }

    // Get Challenge Info
    final private function getChallengeInfo(&$db)
    {
        $method = 'ChallengeEntrySubmit->getChallengeInfo()';

        // Initialize
        $challenge_id   = SBC::checkNumber($this->challenge_id,'$this->challenge_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Challenge Info
        $sql = 'SELECT id, thread_id, isdeleted
            FROM challenges
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$challenge_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $challenge_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($challenge_id < 1)
        {
            SBC::devError('Could not find challenge in database',$method);
        }

        // Deleted?
        if ($row['isdeleted'] == 1)
        {
            SBC::devError('Challenge no longer exists');
        }

        // Set
        $this->thread_id = $row['thread_id'];
    }

    // Get Thread Info
    final private function getThreadInfo(&$db)
    {
        $method = 'ChallengeEntrySubmit->getThreadInfo()';

        // Initialize
        $thread_id  = SBC::checkNumber($this->thread_id,'$this->thread_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread Info
        $sql = 'SELECT id, forum_id
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $thread_id  = isset($row['id']) ? (int) $row['id'] : 0;
        if ($thread_id < 1)
        {
            SBC::devError('Could not find thread in database',$method);
        }

        // Set
        $this->forum_id = $row['forum_id'];
        if ($this->forum_id < 1)
        {
            SBC::devError('Thread does not have a forum set',$method);
        }
    }

    // Create Reply
    final private function createReply(&$db,&$messageObject)
    {
        $method = 'ChallengeEntrySubmit->createReply()';

        // Initialize
        $thread_id  = SBC::checkNumber($this->thread_id,'$this->thread_id');
        $user_id    = SBC::checkNumber($this->user_id,'$this->user_id');
        $time       = $this->time;
        $ip_address = $this->ip_address;

        // Create New Message
        $messageObject->setUserId($user_id);
        $messageObject->setType('forum_thread_reply');
        $messageObject->createMessage($db);
        $messageObject->setParentId($thread_id);
        $messageObject->updateParentId($db);

        // Set
        $this->comment_id = $messageObject->getCommentId();
    }

    // Insert Comment
    final private function insertComment(&$db)
    {
        $method = 'ChallengeEntrySubmit->insertComment()';

        // Initialize
        $thread_id  = SBC::checkNumber($this->thread_id,'$this->thread_id');
        $comment_id = SBC::checkNumber($this->comment_id,'$this->comment_id');

        // New Thread Reply
        $ThreadReply = new ThreadReply($db);
        $ThreadReply->setThreadId($thread_id);
        $ThreadReply->insertCommentId($comment_id);
    }

    // Insert Comment Into Challenge Table
    final private function insertIntoChallengeTable(&$db)
    {
        $method = 'ChallengeEntrySubmit->insertIntoChallengeTable()';

        // Initialize
        $comment_id     = SBC::checkNumber($this->comment_id,'$this->comment_id');
        $challenge_id   = SBC::checkNumber($this->challenge_id,'$this->challenge_id');

        // Switch
        $db->sql_switch('sketchbookcafe_challenges');

        // Insert into table
        $table_name = 'fc'.$challenge_id.'l';
        $sql = 'INSERT INTO '.$table_name.'
            SET cid=?,
            ispending=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$comment_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Create Challenge Entry
    final private function createChallengeEntry(&$db)
    {
        $method = 'ChallengeEntrySubmit->createChallengeEntry()';

        // Initialize
        $challenge_id   = SBC::checkNumber($this->challenge_id,'$this->challenge_id');
        $comment_id     = SBC::checkNumber($this->comment_id,'$this->comment_id');
        $image_id       = SBC::checkNumber($this->image_id,'$this->image_id');
        $user_id        = SBC::checkNumber($this->user_id,'$this->user_id');

        // Get Entry ID
        $ChallengeOrganizer = new ChallengeOrganizer($db);
        $this->entry_id = $ChallengeOrganizer->getNewEntry($challenge_id,$comment_id,$image_id,$user_id);
    }

    // Update Comment Info
    final private function updateComment(&$db)
    {
        $method = 'ChallengeEntrySubmit->updateComment()';

        // Initialize
        $comment_id     = SBC::checkNumber($this->comment_id,'$this->comment_id');
        $image_id       = SBC::checkNumber($this->image_id,'$this->image_id');
        $entry_id       = SBC::checkNumber($this->entry_id,'$this->entry_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update comment
        $sql = 'UPDATE sbc_comments
            SET image_id=?,
            entry_id=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii',$image_id,$entry_id,$comment_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}