<?php
// Forum Organizer: managages statistics and updates forum infos

class ForumOrganizer
{
    private $db;
    private $thread_user_id = 0;

    // Construct
    public function __construct(&$db)
    {
        $this->db = &$db;
    }

    // Verify Forum
    final private function verifyForum($forum_id)
    {
        // Database
        $db = &$this->db;

        // Initialize Vars
        $statement_method = 'ForumOrganizer->verifyForum()';

        // Check
        if ($forum_id < 1)
        {
            error('Dev error: $forum_id is not set for ForumOrganizer->verifyForum()');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Forum Information
        $sql = 'SELECT id 
            FROM forums
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$forum_id);
        if (!$stmt->execute())
        {
            statement_error('get forum information',$statement_method);
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Verify
        $forum_id   = isset($row['id']) ? (int) $row['id'] : 0;
        if ($forum_id < 1)
        {
            error('Dev error: could not find forum for ForumOrganizer->verifyForum()');
        }
    }

    // Verify Thread
    final private function verifyThread($thread_id)
    {
        // Database
        $db = &$this->db;

        // Initialize Vars
        $statement_method = 'ForumOrganizer->verifyThread()';

        // Check Thread ID
        if ($thread_id < 1)
        {
            error('Dev error: $thread_id is not set for ForumOrganizer->verifyThread');
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Check if the thread exists
        $sql = 'SELECT id, user_id
            FROM forum_threads
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$thread_id);
        if (!$stmt->execute())
        {
            statement_error('check if thread exists',$statement_method);
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Verify
        $thread_id  = isset($row['id']) ? (int) $row['id'] : 0;
        if ($thread_id < 1)
        {
            error('Dev error: thread does not exist for ForumOrganizer->verifyThread');
        }

        // Set Thread User ID
        $this->thread_user_id = (int) $row['user_id'];
    }

    // Thread: Count Total Replies
    final public function threadTotalReplies($thread_id)
    {
        // Initialize Objects
        $db = &$this->db;

        // Initiailize Vars
        $statement_method = 'ForumOrganizer->threadTotalReplies()';

        // Make sure thread ID is set
        if ($thread_id < 1)
        {
            error('Dev error: $thread_id is not set for ForumOrganizer->threadTotalReplies');
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
        if (!$stmt->execute())
        {
            statement_error('update total replies',$statement_method);
        }
        $stmt->close();
    }

    // Forum: Adds 1 for total posts
    final public function forumTotalPostsAddOne($forum_id)
    {
        // Initiialize Objects and Vars
        $db                 = &$this->db;
        $statement_method   = 'ForumOrganizer->forumTotalPostsAddOne()';

        // Verify
        if ($forum_id < 1)
        {
            error('Dev error: $forum_id is not set for ForumOrganizer->forumTotalPostsAddOne()');
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
        if (!$stmt->execute())
        {
            statement_error('update forum',$statement_method);
        }
        $stmt->close();
    }

    // Thread: Update Info
    final public function threadUpdateInfo($thread_id)
    {
        // Initialize Object
        $db = &$this->db;

        // Initialize Vars
        $statement_method   = 'ForumOrganizer->threadUpdateInfo()';
        $comment_user_id    = 0;

        // Make sure thread id is set
        if ($thread_id < 1)
        {
            error('Dev error: $thread_id is not set for ForumOrganizer->threadUpdateInfo');
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
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i',$comment_id);
            if (!$stmt->execute())
            {
                statement_error('get comment info',$statement_method);
            }
            $result         = $stmt->get_result();
            $comment_row    = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);
            $stmt->close();

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
        if (!$stmt->execute())
        {
            statement_error('update thread info',$statement_method);
        }
        $stmt->close();
    }

    // Update Forum Information
    final public function forumUpdateInfo($forum_id)
    {
        // Initialize Object
        $db = &$this->db;

        // Initialize Vars
        $statement_method   = 'ForumOrganizer->forumUpdateInfo()';
        $last_user_id       = 0;
        $last_thread_id     = 0;

        // Forum ID
        if ($forum_id < 1)
        {
            error('Dev error: $forum_id is not set for ForumOrganizer->forumUpdateInfo()');
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
            ORDER BY date_bumped
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
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i',$thread_id);
            if (!$stmt->execute())
            {
                statement_error('get thread info',$statement_method);
            }
            $result = $stmt->get_result();
            $row    = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);
            $stmt->close();

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
        if (!$stmt->execute())
        {
            statement_error('update forum info',$statement_method);
        }
        $stmt->close();
    }

    // Add One Post to Category
    final public function categoryTotalPostsAddOne($category_id)
    {
        // Initialize Object
        $db = &$this->db;

        // Initialize Vars
        $statement_method   = 'ForumOrganizer->categoryTotalPostsAddOne()';

        // Check
        if ($category_id < 1)
        {
            error('Dev error: $category_id is not set for ForumOrganizer->categoryTotalPostsAddOne');
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
        if (!$stmt->execute())
        {
            statement_error('update category',$statement_method);
        }
        $stmt->close();
    }

    // Thread: Count Unique Comments
    final public function threadUniqueComments($thread_id)
    {
        // Initialize Objects and Vars
        $db                 = &$this->db;
        $statement_method   = 'ForumOrganizer->threadUniqueComments()';

        // Make sure thread ID is set
        if ($thread_id < 1)
        {
            error('Dev error: $thread_id is not set for '.$statement_method);
        }

        // Verify Thread
        $this->verifyThread($thread_id);

        // Set Vars
        $thread_user_id     = $this->thread_user_id;
        if ($thread_user_id < 1)
        {
            error('Dev error: $thread_user_id is not set for '.$statement_method);
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
        if (!$stmt->execute())
        {
            statement_error('update thread',$statement_method);
        }
        $stmt->close();
    }
}