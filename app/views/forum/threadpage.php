<?php
// Initialize Objects and Vars
$comments_result    = &$data['comments_result'];
$comments_rownum    = &$data['comments_rownum'];
$User               = &$data['User'];
$thread_row         = &$data['thread_row'];
$forum_row          = &$data['forum_row'];
$category_row       = &$data['category_row'];
$Comment            = &$data['Comment'];
$Member             = &$data['Member'];
$Form               = &$data['Form'];
$user_id            = $User->getUserId();

// Page Numbers
$pagenumbers        = &$data['pagenumbers'];
$pages_min          = &$data['pages_min'];
$pages_max          = &$data['pages_max'];
$pages_total        = &$data['pages_total'];
?>

<div class="threadTitleWrap">
    <div class="threadTitle">
        <a href=""><?php echo $thread_row['title'];?></a>
    </div>
</div>
<div class="breadCrumbs">
    <a href="https://www.sketchbook.cafe/forums/"><?php echo $category_row['name'];?></a>
    <span class="breadCrumbSeparator">></span>
    <a href="https://www.sketchbook.cafe/forum/<?php echo $forum_row['id'];?>/"><?php echo $forum_row['name'];?></a>
    <span class="breadCrumbSeparator">></span>
    <a href="https://www.sketchbook.cafe/forum/thread/<?php echo $thread_row['id'];?>/"><?php echo $thread_row['title'];?></a>
</div>

<div style="overflow: hidden;">

    <!-- Start Main Post -->
    <div class="commentWrap">

        <div class="commentLeft">

            <div class="commentAvatarDiv">
                <script>sbc_avatar(<?php echo $thread_row['user_id'];?>);</script>
            </div>
            <div class="commentUsername">
                <script>sbc_username(<?php echo $thread_row['user_id'];?>);</script>
            </div>
            <div class="commentUserTitle">
                <?php echo $Member->displayTitle($thread_row['user_id']);?>
            </div>
            <div class="commentPosts">
                <script>sbc_number_display(<?php echo $Member->displayPosts($thread_row['user_id']);?>,'Post','Posts');</script>
            </div>

        </div>
        <div class="commentRight">

            <div class="commentTopWrap">
                <div class="commentTopRight">
<?php
// Owner Only
if ($user_id == $thread_row['user_id'])
{
?>
                    <a href="#" onClick="sbc_edit_comment_form(<?php echo $thread_row['comment_id'];?>); return false;">edit</a>
<?php
}
?>
                    #<?php echo $thread_row['comment_id'];?>
                </div>
                <div class="commentDate">
                    <?php echo $User->mytz($Comment->getDate($thread_row['comment_id']),'F jS, Y - g:iA');?>
                </div>
            </div>
            <div class="commentMessage">
                <span id="edit_comment_window<?php echo $thread_row['comment_id'];?>">
                    <?php echo $Comment->displayComment($thread_row['comment_id']);?>
                </span>
            </div>
<?php
// Signature?
if ($Member->notEmpty($thread_row['user_id'],'forumsignature'))
{
?>
            <div class="commentSignature">
                <?php echo $Member->displayForumSignature($thread_row['user_id']);?>
            </div>
<?php
}
?>
        </div>
    </div>
    <!-- End Main Post -->

<?php
// Page Numbers
if ($comments_rownum > 0)
{
?>
<div class="pageNumbersWrap">
    <div class="pageNumbersLeft">
        Viewing <?php echo $pages_min;?>-<?php echo $pages_max;?> posts (<?php echo $pages_total;?> total). 
    </div>
    <div class="pageNumbersRight">

<?php
echo $pagenumbers;
?>

    </div>
</div>
<?php
}
?>

<?php
// Comments
if ($comments_rownum > 0)
{
    // Loop
    $i = 0;
    while ($trow = mysqli_fetch_assoc($comments_result))
    {
        // Recent Anchor
        if ($i >= ($comments_rownum - 1))
        {
            echo '<a name="recent"></a>';
        }

        // Add
        $i++;

        // comment ID
        $comment_id         = $trow['cid'];
        $comment_user_id    = $Comment->comment[$comment_id]['user_id'];
?>

<div class="commentWrap">

    <div class="commentLeft">

        <div class="commentAvatarDiv">
            <script>sbc_avatar(<?php echo $comment_user_id;?>);</script>
        </div>
        <div class="commentUsername">
            <script>sbc_username(<?php echo $comment_user_id;?>);</script>
        </div>
        <div class="commentUserTitle">
            <?php echo $Member->displayTitle($comment_user_id);?>
        </div>
        <div class="commentPosts">
            <script>sbc_number_display(<?php echo $Member->displayPosts($comment_user_id);?>,'Post','Posts');</script>
        </div>

    </div>

    <div class="commentRight">

        <div class="commentTopWrap">
            <div class="commentTopRight">
<?php
// Comment Owner Only
if ($user_id == $comment_user_id)
{
?>
                <a href="#" onClick="sbc_edit_comment_form(<?php echo $comment_id;?>); return false;">edit</a>
<?php
}
?>
                #<?php echo $comment_id;?>
            </div>
            <div class="commentDate">
                <?php echo $User->mytz($Comment->getDate($comment_id),'F jS, Y - g:iA');?>
            </div>
        </div>

        <div class="commentMessage">
            <span id="edit_comment_window<?php echo $comment_id;?>">
                <?php echo $Comment->displayComment($comment_id);?>
            </span>
        </div>
<?php
// Signature?
if ($Member->notEmpty($comment_user_id,'forumsignature'))
{
?>
        <div class="commentSignature">
            <?php echo $Member->displayForumSignature($comment_user_id);?>
        </div>
<?php
}
?>
    </div>
</div>

<?php
    }
    mysqli_data_seek($comments_result,0);
}
?>

<?php
// Page Numbers
if ($comments_rownum > 0)
{
?>
<div class="pageNumbersWrap">
    <div class="pageNumbersLeft">
        &nbsp;
    </div>
    <div class="pageNumbersRight">

<?php
echo $pagenumbers;
?>

    </div>
</div>
<?php
}
?>

<?php
// Users Only
if ($User->loggedIn())
{
    // Start Form
    echo $Form->start();
    echo $Form->field['thread_id'];
?>

<div class="commentWrap">

    <div class="commentLeft">
        <div class="commentAvatarDiv">
            <script>sbc_avatar(<?php echo $user_id;?>);</script>
        </div>
        <div class="commentUsername">
            <script>sbc_username(<?php echo $user_id;?>);</script>
        </div>
        <div class="commentUserTitle">
            <?php echo $Member->displayTitle($user_id);?>
        </div>
    </div>
    <div class="commentRight">
        <div class="fb">
            Reply
        </div>
        <div>
<?php
    // Message
    echo $Form->field['message'];
?>
        </div>
    </div>

<?php
    // End Form
    echo $Form->end();
}
?>


</div>