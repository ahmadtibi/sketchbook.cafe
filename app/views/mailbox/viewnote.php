<?php
// Initialize Vars
$Mail           = &$data['Mail'];
$Comment        = &$data['Comment'];
$Member         = &$data['Member'];
$User           = &$data['User'];
$Form           = &$data['Form'];
$result         = &$data['result'];
$rownum         = &$data['rownum'];
$pagenumbers    = &$data['pagenumbers'];

// Set Vars
$mail_id        = $Mail['id'];
$user_id        = $User->getUserId();
$r_user_id      = $Mail['r_user_id'];
$main_user_id   = $Mail['user_id'];
$main_id        = $Mail['comment_id'];

// Start Form
echo $Form->start();

// Hidden ID
echo $Form->field['id']
?>
<div class="threadTitle">
    <?php echo $Mail['title'];?>
</div>
<div class="breadCrumbs">
    <a href="https://www.sketchbook.cafe/">Home</a>
    <span class="breadCrumbSeparator">></span>
    <a href="https://www.sketchbook.cafe/mailbox/">Mailbox</a>
    <span class="breadCrumbSeparator">></span>
    <a href=""><?php echo $Mail['title'];?></a>
</div>

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
                #1
            </div>
            <div class="commentDate">
                <?php echo $User->mytz($Comment->getDate($Mail['comment_id']),'F jS, Y g:iA');?>
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

<style type="text/css">
.pageNumbersWrap {
    overflow: hidden;
    margin-top: 9px;
    margin-bottom: 9px;
    margin-left: 9px;
    margin-right: 9px;
    font-size: 11px;
    font-family: Georgia, serif;

    color: #6E6E6E;
}
.pageNumbersLeft {
    width: 50%;
    float: left;
    overflow: hidden;
    line-height: 25px;
    font-size: 12px;
}
.pageNumbersRight {
    overflow: hidden;
    text-align: right;
}
.pageNumbersItem {
    overflow: hidden;
    display: inline-block;
    padding-left: 6px;
    padding-right: 6px;
    margin-right: 2px;

    font-size: 14px;
    line-height: 25px;
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;

    color: #484848;
}
.pageNumbersItem:hover {
    background-color: #848484;
}
.pageNumbersItemSelected {
    color: #151515;
    background-color: #848484;
}
.pageNumbersItemUnselected {
    background-color: #ACACAC;
}
.pageNumbersItemUnselected:hover {
    color: #151515;
}
</style>

<div class="pageNumbersWrap">
    <div class="pageNumbersLeft">
        Viewing 1-3 posts (3 total). 
        Last updated 2 months ago by <u>Kameloh</u>
    </div>
    <div class="pageNumbersRight">

<?php
echo $pagenumbers;
?>

    </div>
</div>


<!-- Start Comments -->
<?php
// Comments?
if ($rownum > 0)
{
    // Loop
    while ($trow = mysqli_fetch_assoc($result))
    {
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
                #1
            </div>
            <div class="commentDate">
                <?php echo $User->mytz($Comment->getDate($comment_id),'F jS, Y g:iA');?>
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

<div class="pageNumbersWrap">
    <div class="pageNumbersLeft">
    </div>
    <div class="pageNumbersRight">
<?php
echo $pagenumbers;
?>
    </div>
</div>


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
<!-- End Reply -->


<?php
// End Form
echo $Form->end();
?>