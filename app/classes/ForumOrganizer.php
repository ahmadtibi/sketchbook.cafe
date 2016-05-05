<?php
// @author          Kameloh
// @lastUpdated     2016-05-01
// Forum Organizer: managages statistics and updates forum infos
namespace SketchbookCafe\ForumOrganizer;

use SketchbookCafe\SBC\SBC as SBC;

class ForumOrganizer
{
    private $db;
    private $thread_user_id = 0;
    private $thread_date_updated = 0;

    // Construct
    public function __construct(&$db)
    {
        $this->db = &$db;
    }

    // Verify Forum
    final private function verifyForum($forum_id)
    {
        $method = 'ForumOrganizer->verifyForum()';

        // Database
        $db = &$this->db;

        // Check
        if ($forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Forum Information
        $sql = 'SELECT id 
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Verify
        $forum_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($forum_id < 1)
        {
            SBC::devError('could not find forum',$method);
        }
    }

    // Verify Thread
    final private function verifyThread($thread_id)
    {
        $method = 'ForumOrganizer->verifyThread()';

        // Database
        $db = &$this->db;

        // Check Thread ID
        if ($thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Check if the thread exists
        $sql = 'SELECT id, user_id, date_updated
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
            SBC::devError('thread does not exist',$method);
        }

        // Set Thread Vars
        $this->thread_user_id       = (int) $row['user_id'];
        $this->thread_date_updated  = (int) $row['date_updated'];
    }

    // Thread: Count Total Replies
    final public function threadTotalReplies($thread_id)
    {
        $method = 'ForumOrganizer->threadTotalReplies()';

        // Initialize Objects
        $db = &$this->db;

        // Make sure thread ID is set
        if ($thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Verify Thread
        $this->verifyThread($thread_id);

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Table
        $table_name = 't'.$thread_id.'d';

        // Count
        $sql = 'SELECT COUNT(*)
            FROM '.$table_name;
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        // Total
        $total = isset($row[0]) ? (int) $row[0] : 0;
        if ($total < 1)
        {
            $total = 0;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Total Replies
        $sql = 'UPDATE forum_threads
            SET total_comments=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$total,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Forum: Adds 1 for total posts
    final public function forumTotalPostsAddOne($forum_id)
    {
        $method = 'ForumOrganizer->forumTotalPostsAddOne()';

        // Initiialize Objects and Vars
        $db     = &$this->db;

        // Verify
        if ($forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Forum
        $sql = 'UPDATE forums
            SET total_posts=(total_posts + 1)
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Thread: Update Bump Info
    // Updates bump time based off thread info
    final public function threadUpdateBumpDate($thread_id)
    {
        $method = 'ForumOrganizer->threadUpdateBumpDate';

        // Initialize
        $db         = &$this->db;

        // Make sure thread ID is set
        if ($thread_id < 1)
        {
            SBC::devError('thread ID is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread Info
        $sql = 'SELECT id, forum_id, date_bumped
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
            SBC::devError('Cannot find thread in database',$method);
        }

        // Set
        $forum_id       = $row['forum_id'];
        $date_bumped    = $row['date_bumped'];

        // Verify Forum
        $this->verifyForum($forum_id);

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Table
        $table = 'forum'.$forum_id.'x';

        // Update
        $sql = 'UPDATE '.$table.'
            SET date_bumped=?
            WHERE thread_id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$date_bumped,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Thread: Update Info
    final public function threadUpdateInfo($thread_id)
    {
        $method = 'ForumOrganizer->threadUpdateInfo()';

        // Initialize
        $db                 = &$this->db;
        $comment_user_id    = 0;

        // Make sure thread id is set
        if ($thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Verify Thread
        $this->verifyThread($thread_id);

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Table
        $table_name = 't'.$thread_id.'d';

        // Get Last Comment ID
        $sql = 'SELECT cid
            FROM '.$table_name.'
            ORDER BY id
            DESC
            LIMIT 1';
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        // Check Comment ID
        $comment_id = isset($row['cid']) ? (int) $row['cid'] : 0;
        if ($comment_id < 1)
        {
            $comment_id = 0;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Only if a comment exists!
        if ($comment_id > 0)
        {
            // Get Comment Info
            $sql = 'SELECT id, user_id
                FROM sbc_comments
                WHERE id=?
                LIMIT 1';
            $stmt           = $db->prepare($sql);
            $stmt->bind_param('i',$comment_id);
            $comment_row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

            // Comment Info
            $comment_id = isset($comment_row['id']) ? (int) $comment_row['id'] : 0;
            if ($comment_id > 0)
            {
                // Set Values
                $comment_user_id    = $comment_row['user_id'];
            }
        }

        // Update Forum Thread
        $sql = 'UPDATE forum_threads
            SET last_user_id=?,
            last_comment_id=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii',$comment_user_id,$comment_id,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Update Forum Information
    final public function forumUpdateInfo($forum_id)
    {
        $method = 'ForumOrganizer->forumUpdateInfo()';

        // Initialize
        $db                 = &$this->db;
        $last_user_id       = 0;
        $last_thread_id     = 0;

        // Forum ID
        if ($forum_id < 1)
        {
            SBC::devError('$forum_id is not set',$method);
        }

        // Verify Forum
        $this->verifyForum($forum_id);

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Table name
        $table_name = 'forum'.$forum_id.'x';

        // Get Last Thread
        $sql = 'SELECT thread_id
            FROM '.$table_name.'
            WHERE is_sticky=0
            ORDER BY date_updated
            DESC
            LIMIT 1';
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        // Check
        $thread_id  = isset($row['thread_id']) ? (int) $row['thread_id'] : 0;
        if ($thread_id < 1)
        {
            $thread_id = 0;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Thread Information
        if ($thread_id > 0)
        {
            // Get it!
            $sql = 'SELECT id, user_id
                FROM forum_threads
                WHERE id=?
                LIMIT 1';
            $stmt   = $db->prepare($sql);
            $stmt->bind_param('i',$thread_id);
            $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

            // Verify
            $thread_id  = isset($row['id']) ? (int) $row['id'] : 0;
            if ($thread_id > 0)
            {
                // Set Vars
                $last_user_id   = $row['user_id'];
                $last_thread_id = $row['id'];
            }
        }

        // Update Forum Info
        $sql = 'UPDATE forums
            SET last_user_id=?,
            last_thread_id=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii',$last_user_id,$last_thread_id,$forum_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Forum: Count Total Threads
    final public function forumCountTotalThreads($forum_id)
    {
        $method = 'ForumOrganizer->forumCountTotalThreads()';

        // Initialize
        $db = &$this->db;

        // Verify Forum
        $this->verifyForum($forum_id);

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Table
        $table_name = 'forum'.$forum_id.'x';

        // Count Threads
        $sql = 'SELECT COUNT(*) 
            FROM '.$table_name;
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        // Total
        $total = isset($row[0]) ? (int) $row[0] : 0;
        if ($total < 1)
        {
            $total = 0;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Forum Stats
        $sql = 'UPDATE forums
            SET total_threads=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$total,$forum_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Add One Post to Category
    final public function categoryTotalPostsAddOne($category_id)
    {
        $method = 'ForumOrganizer->categoryTotalPostsAddOne()';

        // Initialize
        $db = &$this->db;

        // Check
        if ($category_id < 1)
        {
            SBC::devError('$category_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update category
        $sql = 'UPDATE forums
            SET total_posts=(total_posts + 1)
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$category_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Thread: Count Unique Comments
    final public function threadUniqueComments($thread_id)
    {
        $method = 'ForumOrganizer->threadUniqueComments()';

        // Initialize
        $db = &$this->db;

        // Make sure thread ID is set
        if ($thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Verify Thread
        $this->verifyThread($thread_id);

        // Set Vars
        $thread_user_id     = $this->thread_user_id;
        if ($thread_user_id < 1)
        {
            SBC::devError('$thread_user_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe_forums');

        // Table
        $table_name = 't'.$thread_id.'d';

        // Count Unique Users
        $sql = 'SELECT DISTINCT(uid)
            FROM '.$table_name.'
            WHERE uid!='.$thread_user_id;
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);
        $db->sql_freeresult($result);

        // Total
        $total = isset($rownum) ? (int) $rownum : 0;
        $total += 1; // add thread owner

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Update Thread
        $sql = 'UPDATE forum_threads
            SET total_users=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$total,$thread_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }

    // Thread: Get Total Comments
    final public function threadGetTotalComments($thread_id)
    {
        $method = 'ForumOrganizer->threadGetTotalComments()';

        // Initialize
        $db = &$this->db;

        // Make sure thread ID is set
        if ($thread_id < 1)
        {
            SBC::devError('$thread_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Total Comments
        $sql = 'SELECT total_comments
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Total
        $total = isset($row['total_comments']) ? (int) $row['total_comments'] : 0;
        if ($total < 1)
        {
            $total = 0;
        }

        // Return
        return $total;
    }

    // User: Viewed Thread
    final public function userViewedThread($thread_id,$user_id)
    {
        $method = 'ForumOrganizer->userViewedThread()';

        // Initialize
        $db     = &$this->db;
        $time   = SBC::getTime();

        // User?
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }

        // Verify Thread
        $this->verifyThread($thread_id);

        // Set Vars after Verify
        $date_updated   = $this->thread_date_updated;

        // Switch
        $db->sql_switch('sketchbookcafe_users');

        // Table
        $table_name = 'u'.$user_id.'vt';

        // Check if the thread already exists in the user's table
        $sql = 'SELECT id
            FROM '.$table_name.'
            WHERE cid=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $id = isset($row['id']) ? (int) $row['id'] : 0;

        // Empty?
        if ($id < 1)
        {
            // Add
            $sql = 'INSERT INTO '.$table_name.'
                SET cid=?,
                lda=?,
                pda=?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('iii',$thread_id,$date_updated,$time);
            SBC::statementExecute($stmt,$db,$sql,$method);
        }
        else
        {
            // Update (use key instead of thread_id)
            $sql = 'UPDATE '.$table_name.'
                SET lda=?,
                pda=?
                WHERE id=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('iii',$date_updated,$time,$id);
            SBC::statementExecute($stmt,$db,$sql,$method);
        }
    }

    // Forum: Update Admin Array
    final public function forumAdminUpdateArray($forum_id)
    {
        $method = 'ForumOrganizer->forumAdminUpdateArray()';

        // Initialize Vars and Objects
        $db = &$this->db;

        // Verify Forum
        $this->verifyForum($forum_id);

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Administrators
        $sql = 'SELECT user_id
            FROM forum_admins
            WHERE forum_id=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        if (!$stmt->execute())
        {
            SBC::devError('cannot get admins',$method);
        }
        $result = $stmt->get_result();
        $rownum = $db->sql_numrows($result);
        $stmt->close();

        // Results?
        $array_admins = '';
        if ($rownum > 0)
        {
            // Loop
            while ($trow = mysqli_fetch_assoc($result))
            {
                if ($trow['user_id'] > 0)
                {
                    // Add to List
                    $array_admins .= $trow['user_id'].' ';
                }
            }
            $db->sql_freeresult($result);
        }

        // Update Forum
        $sql = 'UPDATE forums
            SET array_admins=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('si',$array_admins,$forum_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}