<?php
// @author          Kameloh
// @lastUpdated     2016-05-12

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Message\Message as Message;
use SketchbookCafe\UserTimer\UserTimer as UserTimer;
use SketchbookCafe\TextareaSettings\TextareaSettings as TextareaSettings;
use SketchbookCafe\ThreadReply\ThreadReply as ThreadReply;
use SketchbookCafe\BlockCheck\BlockCheck as BlockCheck;
use SketchbookCafe\ThreadOrganizer\ThreadOrganizer as ThreadOrganizer;
use SketchbookCafe\ForumOrganizer\ForumOrganizer as ForumOrganizer;
use SketchbookCafe\UserOrganizer\UserOrganizer as UserOrganizer;
use SketchbookCafe\ImageFile\ImageFile as ImageFile;
use SketchbookCafe\ChallengeOrganizer\ChallengeOrganizer as ChallengeOrganizer;

class ForumThreadReply
{
    // User Info + Required
    private $user_id = 0;
    private $ip_address = 0;
    private $time = 0;
    private $rd = 0;

    // Thread Info
    private $forum_id = 0;
    private $thread_user_id = 0;

    // Comment Info
    private $comment_id = 0;

    // Challenge Info
    private $challenge_difficulty = 0;
    private $challenge_id = 0;
    private $image_id = 0;
    private $entry_id = 0;

    // Page Numbers
    private $ppage = 10;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'ForumThreadReply->__construct()';

        // Initialize
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Initialize
        $this->ip_address   = SBC::getIpAddress();
        $this->time         = SBC::getTime();
        $this->rd           = SBC::rd();
        $this->challenge_id = isset($_POST['challenge_id']) ? (int) $_POST['challenge_id'] : 0;
        $this->thread_id    = isset($_POST['thread_id']) ? (int) $_POST['thread_id'] : 0;
        $TextareaSettings   = new TextareaSettings('forum_reply');

        // Challenge
        if ($this->challenge_id > 0)
        {
            // Challenge Difficulty
            $this->challenge_difficulty = isset($_POST['challenge_difficulty']) ? (int) $_POST['challenge_difficulty'] : 0;
            if ($this->challenge_difficulty < 1 || $this->challenge_difficulty > 10)
            {
                // Set it to 0 since we still allow normal replies
                $this->challenge_difficulty = 0;
            }

            // Image File
            $ImageFile  = new ImageFile(array
            (
                'name'          => 'imagefile',
                'max_filesize'  => 4194304, // 4 mb
                'required'      => 0,
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

            // If we have an image file then require a difficulty
            if ($ImageFile->hasFile())
            {
                if ($this->challenge_difficulty < 1)
                {
                    SBC::userError('Please rate difficulty for challenge');
                }
            }
        }

        // Check Thread ID
        if ($this->thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Message
        $MessageObj = new Message($TextareaSettings->getSettings());
        $MessageObj->insert($_POST['message']);

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $this->user_id = $User->getUserId();

        // User Timer Check
        $UserTimer = new UserTimer(array('user_id'=>$this->user_id,));
        $UserTimer->setColumn('forum_reply');
        $UserTimer->checkTimer($db);

        // Thread Reply
        $ThreadReply = new ThreadReply($db);
        $ThreadReply->setThreadId($this->thread_id);
        $ThreadReply->checkThread();

        // Set Vars
        $this->thread_user_id   = $ThreadReply->getThreadUserId();
        $this->challenge_id     = $ThreadReply->getChallengeId();

        // Block Check
        $this->checkBlocked($db);

        // Challenge
        if ($this->challenge_id > 0 && $ImageFile->hasFile())
        {
            // Get Info
            $this->getChallengeInfo($db);

            // Check if the user already submitted an entry
            // TEMPORARY FIXME: $this->checkUserEntry($db);

            // Create Image
            $this->createImage($db,$ImageFile);
        }

        // Create Reply
        $this->createReply($db,$MessageObj);

        // Challenge
        if ($this->challenge_id > 0 && $ImageFile->hasFile())
        {
            // Create Challenge Organizer
            $ChallengeOrganizer = new ChallengeOrganizer($db);
            $this->entry_id = $ChallengeOrganizer->getNewEntry(array
            (
                'challenge_id'          => $this->challenge_id,
                'comment_id'            => $this->comment_id,
                'image_id'              => $this->image_id,
                'user_id'               => $this->user_id,
                'challenge_difficulty'  => $this->challenge_difficulty,
            ));

            // Insert into the challenge table
            $this->insertIntoChallengeTable($db);

            // Update Comment
            $this->updateCommentInfo($db);
        }

        // Insert into thread's table
        $ThreadReply->insertCommentId($this->comment_id);

        // Thread Organizer
        $ThreadOrganizer = new ThreadOrganizer($db);
        $ThreadOrganizer->countUniqueUsers($this->thread_id);
        $ThreadOrganizer->countTotalReplies($this->thread_id);
        $ThreadOrganizer->updateLastPostInfo($this->thread_id);
        $ThreadOrganizer->updateBumpDate($this->thread_id);

        // Set Vars
        $this->forum_id = $ThreadOrganizer->getForumId($this->thread_id);

        // Forum Organizer
        $ForumOrganizer = new ForumOrganizer($db);
        $ForumOrganizer->addOnePostCount($this->forum_id);
        $ForumOrganizer->updateLastPostInfo($this->forum_id);

        // User Organizer
        $UserOrganizer = new UserOrganizer($db);
        $UserOrganizer->addPostCount($this->user_id);

        // Challenge
        if ($this->challenge_id > 0 && $ImageFile->hasFile())
        {
            $ChallengeOrganizer->updateLastImages($this->challenge_id);
            $ChallengeOrganizer->updateTotalEntries($this->challenge_id);
        }

        // Vars
        $total_comments = $ThreadOrganizer->getTotalComments($this->thread_id);
        if ($total_comments < 1)
        {
            $total_comments = 0;
        }

        // User Timer Update
        $UserTimer->update($db);

        // Close Connection
        $db->close();

        // Calculate Page
        $pageno = SBC::currentPage($this->ppage,$total_comments);

        // Header
        header('Location: https://www.sketchbook.cafe/forum/thread/'.$this->thread_id.'/'.$pageno.'/#recent');
        exit;    
    }

    // Check Blocked
    final private function checkBlocked(&$db)
    {
        $method = 'ForumThreadReply->checkBlocked()';

        // Block Check
        $BlockCheck = new BlockCheck(array
        (
            'user_id'   => $this->user_id,
            'r_user_id' => $this->thread_user_id,
        ));
        $BlockCheck->check($db);
    }

    // Create Reply
    final private function createReply(&$db,&$MessageObj)
    {
        $method = 'ForumThreadReply->createReply()';

        // Initialize
        $thread_id  = SBC::checkNumber($this->thread_id,'thread_id');
        $user_id    = SBC::checkNumber($this->user_id,'user_id');

        // Create New Message
        $MessageObj->setUserId($user_id);
        $MessageObj->setType('forum_thread_reply');
        $MessageObj->createMessage($db);
        $MessageObj->setParentId($thread_id);
        $MessageObj->updateParentId($db);
        $this->comment_id = $MessageObj->getCommentId();

        // Check
        if ($this->comment_id < 1)
        {
            SBC::devError('Could not insert new comment into database',$method);
        }
    }

    // Get Challenge Info
    final private function getChallengeInfo(&$db)
    {
        $method = 'ForumThreadReply->getChallengeInfo()';

        // Challenge ID
        $challenge_id   = $this->challenge_id;
        if ($challenge_id < 1)
        {
            SBC::devError('$challenge_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Challenge Info
        $sql = 'SELECT id, isdeleted
            FROM challenges
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$challenge_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
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
    }

    // Create Image
    final private function createImage(&$db,&$ImageFile)
    {
        $method = 'ForumThreadReply->createImage()';

        // Create New Image
        $ImageFile->setUserId($this->user_id);
        $ImageFile->createImage($db);
        $this->image_id = $ImageFile->getImageId();
    }

    // Insert into Challenge Table
    final private function insertIntoChallengeTable(&$db)
    {
        $method = 'ForumThreadReply->insertIntoChallengeTable()';

        // Initialize
        $comment_id     = SBC::checkNumber($this->comment_id,'$this->comment_id');
        $challenge_id   = SBC::checkNumber($this->challenge_id,'$this->challenge_id');
        $difficulty     = SBC::checkNumber($this->challenge_difficulty,'$this->challenge_difficulty');
        $user_id        = SBC::checkNumber($this->user_id,'$this->user_id');
        $entry_id       = SBC::checkNumber($this->entry_id,'$this->entry_id');
        $table_name     = 'fc'.$challenge_id.'l';

        // Switch
        $db->sql_switch('sketchbookcafe_challenges');

        // Insert into challenge table
        $sql = 'INSERT INTO '.$table_name.'
            SET cid=?,
            difficulty=?,
            ispending=1,
            uid=?,
            entry_id=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiii',$comment_id,$difficulty,$user_id,$entry_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Update Comment Info for Challenges
    final private function updateCommentInfo(&$db)
    {
        $method = 'ForumThread->updateCommentInfo()';

        // Initialize
        $comment_id = SBC::checkNumber($this->comment_id,'$this->comment_id');
        $image_id   = SBC::checkNumber($this->image_id,'$this->image_id');
        $entry_id   = SBC::checkNumber($this->entry_id,'$this->entry_id');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Comment
        $sql = 'UPDATE sbc_comments
            SET image_id=?,
            entry_id=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii',$image_id,$entry_id,$comment_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Check User Entry
    final private function checkUserEntry(&$db)
    {
        $method = 'ForumThreadReply->checkUserEntry()';

        // Initialize
        $challenge_id   = $this->challenge_id;
        $user_id        = $this->user_id;
        $table_name     = 'fc'.$challenge_id.'l';
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }
        if ($challenge_id < 1)
        {
            SBC::devError('$challenge_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe_challenges');

        // Check
        $sql = 'SELECT id
            FROM '.$table_name.'
            WHERE uid=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Entry?
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id > 0)
        {
            SBC::userError('Sorry, you may only submit one entry for this challenge');
        }
    }
}