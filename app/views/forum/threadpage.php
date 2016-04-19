<?php
// Initialize Objects and Vars
$User           = &$data['User'];
$thread_row     = &$data['thread_row'];
$forum_row      = &$data['forum_row'];
$category_row   = &$data['category_row'];
$Comment        = &$data['Comment'];
$Member         = &$data['Member'];
$Form           = &$data['Form'];
$user_id        = $User->getUserId();
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

        </div>
        <div class="commentRight">

            <div class="commentTopWrap">
                <div class="commentTopRight">
                    #<?php echo $thread_row['comment_id'];?>
                </div>
                <div class="commentDate">
                    <?php echo $User->mytz($Comment->getDate($thread_row['comment_id']),'F jS, Y - g:iA');?>
                </div>
            </div>

            <div class="commentMessage">
                <?php echo $Comment->displayComment($thread_row['comment_id']);?>
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