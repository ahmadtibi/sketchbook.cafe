<?php
// Initialize Vars
$Mail           = &$data['Mail'];
$Comment        = &$data['Comment'];
$Member         = &$data['Member'];
$User           = &$data['User'];
$DeleteForm     = &$data['DeleteForm'];
$Form           = &$data['Form'];
$result         = &$data['result'];
$rownum         = &$data['rownum'];
$pagenumbers    = &$data['pagenumbers'];
$pages_min      = &$data['pages_min'];
$pages_max      = &$data['pages_max'];
$pages_total    = &$data['pages_total'];

// Set Vars
$mail_id        = $Mail['id'];
$user_id        = $User->getUserId();
$r_user_id      = $Mail['r_user_id'];
$main_user_id   = $Mail['user_id'];
$main_id        = $Mail['comment_id'];

// Is removed
$is_removed     = 0;
if ($Mail['removed_user_id'] != 0 || $Mail['removed_r_user_id'] != 0)
{
    $is_removed = 1;
}

// Other ID
$other_id       = 0;
if ($user_id == $Mail['user_id'])
{
    $other_id   = $Mail['r_user_id'];
}
else
{
    $other_id   = $Mail['user_id'];
}
?>
<div class="threadTitleWrap">
    <div class="threadTitleRight">
        <div id="deletethreadlink" class="inboxDeleteButton">
            x
        </div>
    </div>
    <div class="threadTitle">
        <a href="https://www.sketchbook.cafe/mailbox/note/<?php echo $mail_id;?>/" class="<?php if ($is_removed == 1) { echo 'strike'; } ?>"><?php echo $Mail['title'];?></a>
    </div>
</div>
<div class="breadCrumbs">
    <a href="https://www.sketchbook.cafe/mailbox/">Mailbox</a>
    <span class="breadCrumbSeparator">></span>
    <a href="https://www.sketchbook.cafe/mailbox/note/<?php echo $mail_id;?>/"><?php echo $Mail['title'];?></a>
</div>
<?php
// Delete Form
echo $DeleteForm->start();
echo $DeleteForm->field['mail_id'];
?>
<div id="deletethread" class="noteDeleteDiv" style="display: none;">
    <div class="noteDeleteTitle">
        Delete Note?
    </div>
    <div class="noteDeleteOptions">
<?php
echo $DeleteForm->field['submit'];
?>
        /
        <span id="deletethreadcancel" class="noteDeleteCancelSpan">
            No
        </span>
    </div>
</div>
<?php
// End Delete Form
echo $DeleteForm->end();
?>

<!-- Start Main Post -->
<div class="commentWrap">

    <div class="commentLeft">

        <div class="commentAvatarDiv">
            <script>sbc_avatar(<?php echo $main_user_id;?>);</script>
        </div>
        <div class="commentUsername">
            <script>sbc_username(<?php echo $main_user_id;?>);</script>
        </div>
        <div class="commentUserTitle">
            <?php echo $Member->displayTitle($main_user_id);?>
        </div>

    </div>

    <div class="commentRight">

        <div class="commentTopWrap">
            <div class="commentTopRight">
                #<?php echo $Mail['comment_id'];?>
            </div>
            <div class="commentDate">
                <?php echo $User->mytz($Comment->getDate($Mail['comment_id']),'F jS, Y - g:iA');?>
            </div>
        </div>

        <div class="commentMessage">
            <?php echo $Comment->displayComment($Mail['comment_id']);?>
        </div>
<?php
// Signature?
if ($Member->notEmpty($main_user_id,'forumsignature'))
{
?>
        <div class="commentSignature">
            <?php echo $Member->displayForumSignature($main_user_id);?>
        </div>
<?php
}
?>
    </div>
</div>
<!-- End Main Post -->

<?php
// Page Numbers
if ($rownum > 0)
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


<!-- Start Comments -->
<?php
// Comments?
if ($rownum > 0)
{
    // Loop
    $i = 0;
    while ($trow = mysqli_fetch_assoc($result))
    {
        // Recent Anchor
        if ($i >= ($rownum - 1))
        {
            echo '<a name="recent"></a>';
        }

        // Add
        $i++;

        // Comment ID
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

    </div>

    <div class="commentRight">

        <div class="commentTopWrap">
            <div class="commentTopRight">
                #<?php echo $comment_id;?>
            </div>
            <div class="commentDate">
                <?php echo $User->mytz($Comment->getDate($comment_id),'F jS, Y - g:iA');?>
            </div>
        </div>

        <div class="commentMessage">
            <?php echo $Comment->displayComment($comment_id);?>
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
    mysqli_data_seek($result,0);
}
?>

<!-- End Comments -->

<?php
// Page Numbers
if ($rownum > 0)
{
?>
<div class="pageNumbersWrap">
    <div class="pageNumbersLeft">
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
if ($is_removed == 1)
{
?>

<div class="commentWrap">

    <div class="commentLeft">
        <div class="commentAvatarDiv">
            &nbsp;
        </div>
        <div class="commentUsername">
            &nbsp;
        </div>
        <div class="commentUserTitle">
            &nbsp;
        </div>
    </div>
    <div class="commentRight">
        <span class="fi">
            <script>sbc_username(<?php echo $other_id;?>,'');</script>
            has removed this from their mailbox and it cannot be replied to.
        </span>
    </div>
</div>

<?php
}
else
{
    // Start Form
    echo $Form->start();
    echo $Form->field['id']
?>
<!-- Start Reply -->
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
</div>
<?php
    // End Form
    echo $Form->end();
}
?>
<!-- End Reply -->
