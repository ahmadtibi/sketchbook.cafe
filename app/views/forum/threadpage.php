<?php
use SketchbookCafe\PollDisplay\PollDisplay as PollDisplay;

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
$PollForm           = &$data['PollForm'];
$ForumAdmin         = &$data['ForumAdmin'];
$poll_row           = &$data['poll_row'];
$SubscribeForm      = &$data['SubscribeForm'];
$challenge_row      = &$data['challenge_row'];
$ChallengeForm      = &$data['ChallengeForm'];

// Page Numbers
$pagenumbers        = &$data['pagenumbers'];
$pages_min          = &$data['pages_min'];
$pages_max          = &$data['pages_max'];
$pages_total        = &$data['pages_total'];

// Other Vars
$user_id            = $User->getUserId();
?>

<link href="https://www.sketchbook.cafe/css/font-awesome/css/font-awesome.css" rel="stylesheet" type="text/css">



<style type="text/css">
.challenge_wrap {
    overflow: hidden;

    padding: 15px;
    background-color: #FFFFFF;
    border: 1px solid #151515;
}
.challenge_title_right {
    float: right;
    min-width: 300px;
    overflow: hidden;

    font-size: 12px;
    text-align: right;

    color: #767676;
}
.challenge_title {
    overflow: hidden;

    padding: 5px;

    font-family: Georgia, serif;
    font-size: 28px;

    margin-bottom: 6px;
}
.challenge_description {
    padding: 5px;
    line-height: 21px;
}
.challenge_requirements {
    padding: 5px;
    line-height: 21px;
}
.challenge_points {
    padding: 5px;
    line-height: 21px;
}
.challenge_submit_wrap {
    padding: 5px;
    margin-top: 15px;
}
</style>
<?php
// Challenge?
if ($challenge_row['id'] > 0)
{
?>
<div class="challenge_wrap">
    <div class="challenge_title">
        <div class="challenge_title_right">
            User Difficulty: 2.0 out of 10 (24 votes)
        </div>
        <?php echo $challenge_row['name'];?>
    </div>
    <div class="challenge_description">
        <div class="">
            <b>Description</b>
        </div>
        <?php echo $challenge_row['description'];?>
    </div>
    <div class="challenge_requirements">
        <div>
            <b>Requirements</b>
        </div>
        <?php echo $challenge_row['requirements'];?>
    </div>
    <div class="challenge_points">
        <div>
            <b>Points</b>
        </div>
        <?php echo $challenge_row['points'];?> Sketch Points
    </div>
<?php
if ($User->loggedIn())
{
    // Start Form
    echo $ChallengeForm->start();
    echo $ChallengeForm->field['challenge_id'];
?>
    <div class="challenge_submit_wrap">
        <div>
            <b>Submit Entry</b>
        </div>
        <div>
            File:
<?php
    echo $ChallengeForm->field['imagefile'];
?>
        </div>
        <div>
<?php
    echo $ChallengeForm->field['message'];
?>
        </div>
        <div>
<?php
    echo $ChallengeForm->field['submit'];
?>
        </div>
    </div>
<?php
    // End Form
    echo $ChallengeForm->end();
}
?>
</div>
<?php
}
?>


<style type="text/css">
.threadLastEdited {
    margin-top: 15px;

    font-family: Georgia, serif;
    font-size: 11px;
    font-style: italic;

    color: #989898;
}

.threadTitleRight {
    float: right;
    overflow: hidden;
    width: 200px;
    text-align: right;
    font-size: 14px;
    border: 1px solid #151515;
}
</style>

<div class="threadTitleWrap">
    <div class="threadTitle">
<?php
// If User
if ($User->loggedIn())
{
    // Start Form
    echo $SubscribeForm->start();
    echo $SubscribeForm->field['thread_id'];
?>
        <div class="threadTitleRight">
<?php
    echo $SubscribeForm->field['submit'];
?>
        </div>
<?php
    // End Form
    echo $SubscribeForm->end();
}

// Sticky
if ($thread_row['is_sticky'] == 1)
{
    echo '<b>[STICKY]</b>';
}

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
    <a href="https://www.sketchbook.cafe/forum/"><?php echo $category_row['name'];?></a>
    <span class="breadCrumbSeparator">></span>
    <a href="https://www.sketchbook.cafe/forum/<?php echo $forum_row['id'];?>/"><?php echo $forum_row['name'];?></a>
    <span class="breadCrumbSeparator">></span>
    <a href="https://www.sketchbook.cafe/forum/thread/<?php echo $thread_row['id'];?>/"><?php echo $thread_row['title'];?></a>
</div>

<style type="text/css">
.pollWrap {
    width: 67%;
    margin-bottom: 15px;
    overflow: hidden;



}
.pollMessageWrap {
}
.pollMessage {
    padding: 6px;
    font-size: 12px;
    font-family: Georgia, serif;
}
.pollBarOuter {
    min-height: 15px;
    background-color: #BFBFBF;

    -moz-border-radius: 15px 15px 15px 15px;
    border-radius: 15px 15px 15px 15px;
}
.pollBarInner {
    font-size: 12px;
    font-family: Georgia, serif;

    line-height: 15px;
    min-height: 15px;
    color: #636363;
    background-color: #7FF1F2;

    -moz-border-radius: 15px 15px 15px 15px;
    border-radius: 15px 15px 15px 15px;
}
.pollTitle {
    overflow: hidden;
    font-size: 13px;
    font-weight: bold;
    font-family: Georgia, serif;
}
.pollTitleRight {
    float: right;
    min-width: 20%;
    font-size: 12px;
    text-align: right;
}
.pollRadio {

}
.pollBarInnerText {
    width: 400px;
    height: 13px;
    line-height: 18px;
    display: inline-block;
    overflow: hidden;
    padding-left: 6px;
}

input[type="radio"],
input[type="checkbox"] { outline: none; }

.checkbox-custom, .radio-custom {
    opacity: 0;
    position: absolute;   
}
.checkbox-custom, .checkbox-custom-label, .radio-custom, .radio-custom-label {
    display: inline-block;
    vertical-align: middle;
    margin-top: 6px;
    margin-right: 6px;
    cursor: pointer;
}
.checkbox-custom-label, .radio-custom-label {
    position: relative;
}

.checkbox-custom + .checkbox-custom-label:before, .radio-custom + .radio-custom-label:before {
    content: '';
    background: #fff;
    border: 2px solid #ddd;
    display: inline-block;
    vertical-align: middle;
    width: 15px;
    height: 15px;
    padding: 2px;
 
    text-align: center;
}

.checkbox-custom:checked + .checkbox-custom-label:before {
    content: "\f00c";
    font-family: 'FontAwesome';
    background: rebeccapurple;
    color: #fff;
}

.radio-custom + .radio-custom-label:before {
    border-radius: 50%;
}

.radio-custom:checked + .radio-custom-label:before {
    content: "\f00c";
    font-family: 'FontAwesome';
    color: #868686;
}

.checkbox-custom:focus + .checkbox-custom-label, .radio-custom:focus + .radio-custom-label {
  outline: 1px solid #ddd; /* focus style */
}


.pollInnerWrap {
    overflow: hidden;

    margin-bottom: 6px;
}
.pollInnerLeft {
    overflow: hidden;
    float: left;
    padding-top: 6px;
}

.pollInnerRight {
    overflow: hidden;

    min-height: 30px;
}
.pollvotebutton {
    margin-top: 6px;

    -moz-border-radius: 2px 2px 2px 2px;
    border-radius: 2px 2px 2px 2px;
    border: 2px solid #DDDDDD;
}
</style>
<?php
// Poll
$poll_object_array = array
(
    'thread_row'    => $thread_row,
    'poll_row'      => $poll_row,
    'PollForm'      => $PollForm,
    'User'          => $User,
);

$PollObject     = new PollDisplay($poll_object_array);
$PollDisplay    = $PollObject->getPoll();
?>

<?php
// Thread Comment
echo display_comment(array
(
    'comment_id'    => $thread_row['comment_id'],
    'thread_locked' => $thread_row['is_locked'],
    'thread_sticky' => $thread_row['is_sticky'],
    'thread_id'     => $thread_row['id'],
    'PollDisplay'   => $PollDisplay,
));


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

<script>
// Master Thumbnail
function sbc_thumbnail(f_id,f_class)
{
    var output      = '';
    var i_url       = '';
    var t_url       = '';

    // Is there an image?
    if (image_id[f_id] > 0)
    {
        // Set Vars
        i_url = image_url[f_id];
        t_url = image_thumb[f_id];

        // Full Image
        output = '<div><img src="https://www.sketchbook.cafe/' + t_url + '" class="' + f_class + '"></img></div>';
    }

    document.write(output);
}
</script>

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

        // Comment Image?
        $image_script   = '';
        $image_id       = $Comment->comment[$trow['cid']]['image_id'];
        if ($image_id > 0)
        {
            $image_script = '<script>sbc_thumbnail('.$image_id.',\'\');</script>';
        }

        // Display Comment
        echo display_comment(array
        (
            'comment_id'    => $trow['cid'],
            'image_script'  => $image_script,
        ));
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