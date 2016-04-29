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
$ForumAdmin         = &$data['ForumAdmin'];

// Page Numbers
$pagenumbers        = &$data['pagenumbers'];
$pages_min          = &$data['pages_min'];
$pages_max          = &$data['pages_max'];
$pages_total        = &$data['pages_total'];
?>

<div class="threadTitleWrap">
    <div class="threadTitle">
<?php
// Locked
if ($thread_row['is_locked'] == 1)
{
    echo '<b>[LOCKED]</b>';
}
?>
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

<?php
// Thread Comment
echo display_comment($thread_row['comment_id']);

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

        // Display Comment
        echo display_comment($trow['cid']);
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

<!-- User Reply Start -->
<div class="commentWrap">
<?php
// Users Only
if ($User->loggedIn())
{
    // Thread Locked
    if ($thread_row['is_locked'] == 1)
    {
?>
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
        <b>Forum Thread is locked.</b>
    </div>
<?php
    }
    else
    {
        // Start Form
        echo $Form->start();
        echo $Form->field['thread_id'];
?>
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
}
?>
</div>
<!-- User Reply End -->