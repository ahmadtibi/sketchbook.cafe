<?php
// @author          Kameloh
// @lastUpdated     2016-05-20
namespace SketchbookCafe\DisplayComment;

use SketchbookCafe\SBC\SBC as SBC;

class DisplayComment
{
    private $user_id = 0;
    private $obj_array = [];
    private $edit_link = '';

    private $css_comment = ' sbc_font sbc_font_size sbc_font_height sbc_font_link ';

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'DisplayComment->__construct()';

        $this->obj_array = &$obj_array;

        // Set user vars for later
        $User           = &$this->obj_array['User'];
        $this->user_id  = $User->getUserId();
    }

    // Add Spacing
    final private function addSpacing($edit)
    {
        // Vars
        $start  = '<span class="comment_edit_span">';
        $end    = '</span><span class="comment_edit_bullet">&#8226;</span>';

        // Set
        $this->edit_link .= $start.$edit.$end;
    }

    // Process
    final public function process($input)
    {
        // Initialize
        $User           = &$this->obj_array['User'];
        $Comment        = &$this->obj_array['Comment'];
        $Member         = &$this->obj_array['Member'];
        $comment_id     = isset($input['comment_id']) ? $input['comment_id'] : 0;
        $thread_locked  = isset($input['thread_locked']) ? $input['thread_locked'] : 0;
        $thread_sticky  = isset($input['thread_sticky']) ? $input['thread_sticky'] : 0;
        $thread_id      = isset($input['thread_id']) ? $input['thread_id'] : 0;
        $image_script   = isset($input['image_script']) ? $input['image_script'] : '';
        $PollDisplay    = isset($input['PollDisplay']) ? $input['PollDisplay'] : '';

        // Reset Edit Link for New Comment
        $this->edit_link = '';

        // Does the comment exist?
        if ($comment_id < 1)
        {
            return null;
        }
        if (!isset($Comment->comment[$comment_id]['id']))
        {
            return null;
        }

        // User Vars
        $current_user_id    = $this->user_id;

        // Comment Vars
        $entry_id       = $Comment->comment[$comment_id]['entry_id'];
        $type           = $Comment->comment[$comment_id]['type'];
        $date_created   = $Comment->comment[$comment_id]['date_created'];
        $date_updated   = $Comment->comment[$comment_id]['date_updated'];
        $message        = $Comment->comment[$comment_id]['message'];
        $user_id        = $Comment->comment[$comment_id]['user_id'];
        $is_locked      = $Comment->comment[$comment_id]['is_locked'];
        $last_user_id   = $Comment->comment[$comment_id]['last_user_id'];
        $isdeleted      = $Comment->comment[$comment_id]['isdeleted'];
        $last_username  = '';

        // Message Deleted?
        if ($isdeleted == 1)
        {
            $value = '
<div class="commentWrap">
    <div class="commentDeletedDiv">
        deleted
    </div>
</div>
';
            return $value;
        }

        // User Vars
        $posts          = $Member->displayPosts($user_id);
        $sketch_points  = $Member->displaySketchPoints($user_id);
        $user_title     = $Member->displayTitle($user_id);
        $signature      = $Member->displayForumSignature($user_id);
        $display_date   = $User->mytz($date_created,'F jS, Y - g:iA');
        $last_date      = $User->mytz($date_updated,'F jS, Y - g:iA');

        // Edited
        $last_edited = '';
        if ($last_user_id > 0)
        {
            // Set Vars
            $last_username = $Member->displayUsername($last_user_id);

            // Mark as last edited
            $last_edited = '<div class="thread_last_edited">Last Edited by <b>'.$last_username.'</b> on '.$last_date.'</div>';
        }

        // Signature Link
        $signature_link = '';
        if (!empty($signature))
        {
            $signature_link = '
            <div class="commentSignature">
                '.$signature.'
            </div>';
        }

        // Forum Admin
        $forum_admin = 0;
        if ($User->isForumAdmin())
        {
            $forum_admin = 1;
        }

        // Admin Edit (check all)
        $forum_admin_flag = $User->getForumAdminFlagArray();

        // Admin Links
        $admin_links = '';
        if ($forum_admin == 1)
        {
            // Type 2: Thread
            if ($type == 2)
            {
                // Edit Thread (Title)
                if ($forum_admin_flag['edit_thread'] == 1)
                {
                    $admin_links = '<a href="" onClick="sbc_thread_edittitle('.$thread_id.','.$comment_id.'); return false;">edit title</a>';
                    $this->addSpacing($admin_links);
                }

                // Lock Thread
                if ($forum_admin_flag['lock_thread'] == 1)
                {
                    $admin_links = '<a href="https://www.sketchbook.cafe/forum/thread_lock/'.$comment_id.'/">';
                    if ($thread_locked == 1)
                    {
                        $admin_links .= 'unlock';
                    }
                    else
                    {
                        $admin_links .= 'lock';
                    }
                    $admin_links .= '</a>';

                    $this->addSpacing($admin_links);
                }

                // Sticky Thread
                if ($forum_admin_flag['sticky_thread'] == 1)
                {
                    $admin_links = '<a href="https://www.sketchbook.cafe/forum/thread_sticky/'.$comment_id.'/">';
                    if ($thread_sticky == 1)
                    {
                        $admin_links .= 'unsticky';
                    }
                    else
                    {
                        $admin_links .= 'sticky';
                    }
                    $admin_links .= '</a>';

                    $this->addSpacing($admin_links);
                }

                // Bump Thread
                if ($forum_admin_flag['bump_thread'] == 1)
                {
                    $admin_links = '<a href="https://www.sketchbook.cafe/forum/thread_bump/'.$comment_id.'/">bump thread</a>';
                    $this->addSpacing($admin_links);
                }

                // Delete Thread
                if ($forum_admin_flag['delete_thread'] == 1)
                {
                    $admin_links = '<a href="" onClick="sbc_thread_deletethread('.$thread_id.','.$comment_id.'); return false;">delete thread</a>';
                    $this->addSpacing($admin_links);
                }
            }

            // Edit Post
            if ($forum_admin_flag['edit_post'] == 1)
            {
                $admin_links = '<a href="#" onClick="sbc_edit_comment_form('.$comment_id.'); return false;">admin edit</a>';
                $this->addSpacing($admin_links);
            }

            // Delete Post (type 3 comments only)
            if ($forum_admin_flag['delete_post'] == 1 && $type == 3)
            {
                $admin_links = '<a href="#" onClick="sbc_delete_comment_form('.$comment_id.'); return false;">delete post</a>';

                $this->addSpacing($admin_links);
            }

            // Lock Post
            if ($forum_admin_flag['lock_post'] == 1)
            {
                $admin_links = '<span id="lockcomment'.$comment_id.'">';

                $admin_links .= '<a href="#" onClick="sbc_thread_lockpost('.$comment_id.'); return false;">';
                $admin_links .= $is_locked ? 'unlock post' : 'lock post';
                $admin_links .= '</a>';

                $admin_links .= '</span>';

                $this->addSpacing($admin_links);
/*
                $admin_links = '<a href="https://www.sketchbook.cafe/forum/lock_post/'.$comment_id.'/">';
                $admin_links .= $is_locked ? 'unlock post' : 'lock post';
                $admin_links .= '</a>';

                $this->addSpacing($admin_links);
*/
            }
        }

        // Edit Link
        $edit_link = '';
        if ($current_user_id == $user_id && $type > 1)
        {
            $this->addSpacing('<a href="#" onClick="sbc_edit_comment_form('.$comment_id.'); return false;">edit</a>');

/*
            // Entry
            if ($entry_id > 0)
            {
                $this->addSpacing('<a href="#" onClick="sbc_thread_editentry_form('.$entry_id.','.$comment_id.'); return false;">edit entry</a>');
            }
*/
        }

        $value = '
<!-- Start Comment('.$comment_id.') -->
<div class="commentWrap">

    <div class="commentLeft">

        <div class="commentAvatarDiv">
            <script>sbc_avatar('.$user_id.');</script>
        </div>
        <div class="commentUsername">
            <script>sbc_username('.$user_id.');</script>
        </div>
        <div class="commentUserTitle">
            '.$user_title.'
        </div>
        <div class="commentPosts">
            <script>sbc_number_display('.$posts.',\'Post\',\'Posts\');</script>
        </div>
        <div class="commentPosts">
            <script>sbc_number_display('.$sketch_points.',\'Sketch Point\',\'Sketch Points\');</script>
        </div>
    </div>

    <div class="commentRight">

        <div class="commentTopWrap">
            <div class="commentTopRight">
                '.$this->edit_link.'
                #'.$comment_id.'
            </div>
            <div class="commentDate">
                '.$display_date.'
            </div>
        </div>

        <div class="commentMessage '.$this->css_comment.'">
                '.$image_script.'
            <span id="edit_entry_window'.$entry_id.'"></span>
            <span id="delete_comment_window'.$comment_id.'"></span>
            <span id="edit_comment_window'.$comment_id.'">
                '.$PollDisplay.'
                '.$message.'
                '.$last_edited.'
            </span>
        </div>
        '.$signature_link.'
    </div>
</div>

<!-- End Comment('.$comment_id.') -->
';

        // Return
        return $value;
    }
}