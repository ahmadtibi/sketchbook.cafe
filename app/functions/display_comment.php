<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-05-08
// Display Comment
function display_comment($input)
{
    // Globals (sorry, I know I shouldn't use this...)
    global $Comment,$User,$Member;

    // Initialize
    $comment_id     = isset($input['comment_id']) ? $input['comment_id'] : 0;
    $thread_locked  = isset($input['thread_locked']) ? $input['thread_locked'] : 0;
    $thread_sticky  = isset($input['thread_sticky']) ? $input['thread_sticky'] : 0;
    $thread_id      = isset($input['thread_id']) ? $input['thread_id'] : 0;
    if ($comment_id < 1)
    {
        return null;
    }

    // Image
    $image_script   = isset($input['image_script']) ? $input['image_script'] : '';

    // Current User Id
    $current_user_id = $User->getUserId();

    // Does the comment exist?
    if (!isset($Comment->comment[$comment_id]['id']))
    {
        return null;
    }

    // Comment Vars
    $type           = $Comment->comment[$comment_id]['type'];
    $date_created   = $Comment->comment[$comment_id]['date_created'];
    $date_updated   = $Comment->comment[$comment_id]['date_updated'];
    $message        = $Comment->comment[$comment_id]['message'];
    $user_id        = $Comment->comment[$comment_id]['user_id'];
    $last_user_id   = $Comment->comment[$comment_id]['last_user_id'];
    $last_username  = '';
    $is_locked      = $Comment->comment[$comment_id]['is_locked'];

    // User Vars
    $posts          = $Member->displayPosts($user_id);
    $user_title     = $Member->displayTitle($user_id);
    $signature      = $Member->displayForumSignature($user_id);
    $display_date   = $User->mytz($date_created,'F jS, Y - g:iA');
    $last_date      = $User->mytz($date_updated,'F jS, Y - g:iA');

    // Poll
    $PollDisplay    = isset($input['PollDisplay']) ? $input['PollDisplay'] : '';

    // Edited
    $last_edited = '';
    if ($last_user_id > 0)
    {
        // Set Vars
        $last_username = $Member->displayUsername($last_user_id);

        // Mark as last edited
        $last_edited = '<div class="threadLastEdited">Last Edited by <b>'.$last_username.'</b> on '.$last_date.'</div>';
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


    // Thread Commands First
    // Posts Commands Second since it's repeated
    // Lock Thread      T -
    // Sticky Thread    T -
    // Bump Thread      T -
    // Delete Thread    T Form
    // Edit Post        - immediate
    // Delete Post      - form?
    // Lock Post        - immediate... does NOT lock the thread

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
                $admin_links .= '<a href="" onClick="sbc_thread_edittitle('.$thread_id.','.$comment_id.'); return false;">[Edit Title]</a>';
            }

            // Lock Thread
            if ($forum_admin_flag['lock_thread'] == 1)
            {
                $admin_links .= '<a href="https://www.sketchbook.cafe/forum/thread_lock/'.$comment_id.'/">';
                if ($thread_locked == 1)
                {
                    $admin_links .= '[Unlock Thread]';
                }
                else
                {
                    $admin_links .= '[Lock Thread]';
                }
                $admin_links .= '</a>';
            }

            // Sticky Thread
            if ($forum_admin_flag['sticky_thread'] == 1)
            {
                $admin_links .= '<a href="https://www.sketchbook.cafe/forum/thread_sticky/'.$comment_id.'/">';
                if ($thread_sticky == 1)
                {
                    $admin_links .= '[Unsticky Thread]';
                }
                else
                {
                    $admin_links .= '[Sticky Thread]';
                }
                $admin_links .= '</a>';
            }

            // Bump Thread
            if ($forum_admin_flag['bump_thread'] == 1)
            {
                $admin_links .= '<a href="https://www.sketchbook.cafe/forum/thread_bump/'.$comment_id.'/">[Bump Thread]</a>';
            }

            // Delete Thread
            if ($forum_admin_flag['delete_thread'] == 1)
            {
                $admin_links .= '<a href="" onClick="sbc_thread_deletethread('.$thread_id.','.$comment_id.'); return false;">[Delete Thread]</a>';
            }
        }

        // Edit Post
        if ($forum_admin_flag['edit_post'] == 1)
        {
            $admin_links .= '<a href="#" onClick="sbc_edit_comment_form('.$comment_id.'); return false;">[Edit]</a>';
        }

        // Delete Post
        if ($forum_admin_flag['delete_post'] == 1)
        {
            $admin_links .= '[Delete Post]';
        }

        // Lock Post
        if ($forum_admin_flag['lock_post'] == 1)
        {
            $admin_links .= '<a href="https://www.sketchbook.cafe/forum/lock_post/'.$comment_id.'/">[';
            $admin_links .= $is_locked ? 'Unlock Post' : 'Lock Post';
            $admin_links .= ']</a>';
        }
    }

    // Edit Link
    $edit_link = '';
    if ($forum_admin == 1)
    {
        $edit_link .= $admin_links;
    }
    if ($current_user_id == $user_id)
    {
        $edit_link  .= '<a href="#" onClick="sbc_edit_comment_form('.$comment_id.'); return false;">[Edit]</a>';
    }

    // Value
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

    </div>

    <div class="commentRight">

        <div class="commentTopWrap">
            <div class="commentTopRight">
                '.$edit_link.'
                #'.$comment_id.'
            </div>
            <div class="commentDate">
                '.$display_date.'
            </div>
        </div>

        <div class="commentMessage">
            <span id="edit_comment_window'.$comment_id.'">
                '.$image_script.'
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