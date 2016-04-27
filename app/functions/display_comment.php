<?php
// Display Comment
function display_comment($comment_id)
{
    // Globals
    global $Comment,$User,$Member;

    // Current User Id
    $current_user_id = $User->getUserId();

    // Does the comment exist?
    if (!isset($Comment->comment[$comment_id]['id']))
    {
        return null;
    }

    // Comment Vars
    $date_created   = $Comment->comment[$comment_id]['date_created'];
    $date_updated   = $Comment->comment[$comment_id]['date_updated'];
    $message        = $Comment->comment[$comment_id]['message'];
    $user_id        = $Comment->comment[$comment_id]['user_id'];

    // User Vars
    $posts          = $Member->displayPosts($user_id);
    $user_title     = $Member->displayTitle($user_id);
    $signature      = $Member->displayForumSignature($user_id);
    $display_date   = $User->mytz($date_created,'F jS, Y - g:iA');

    // Signature Link
    $signature_link = '';
    if (!empty($signature))
    {
        $signature_link = '
        <div class="commentSignature">
            '.$signature.'
        </div>';
    }

    // Edit Link
    $edit_link = '';
    if ($current_user_id == $user_id)
    {
        $edit_link  = '<a href="#" onClick="sbc_edit_comment_form('.$comment_id.'); return false;">edit</a>';
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
                '.$message.'
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