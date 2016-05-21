<?php
use SketchbookCafe\PollDisplay\PollDisplay as PollDisplay;
use SketchbookCafe\DisplayComment\DisplayComment as DisplayComment;

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
$user_entry_id      = &$data['user_entry_id'];
$entries_result     = &$data['entries_result'];
$entries_rownum     = &$data['entries_rownum'];

// Object Array
$obj_array = array
(
    'User'      => &$User,
    'Comment'   => &$Comment,
    'Member'    => &$Member,
);

// Display Comment
$DisplayComment = new DisplayComment($obj_array);

// Challenges
$entry              = &$data['entry'];
$challenge_row      = &$data['challenge_row'];

// Page Numbers
$PageNumbers        = &$data['PageNumbers'];

// Other Vars
$user_id            = $User->getUserId();

// Set Title
$thread_title = '';

// Sticky
if ($thread_row['is_sticky'] == 1)
{
    $thread_title .= '<b>[Sticky]</b> ';
}

// Locked
if ($thread_row['is_locked'] == 1)
{
    $thread_title .= '<b>[Locked]</b> ';
}

// Set Title
$thread_title .= $thread_row['title'];

// Poll
$PollDisplay = '';
if ($thread_row['poll_id'] > 0)
{
    $poll_object_array = array
    (
        'thread_row'    => $thread_row,
        'poll_row'      => $poll_row,
        'PollForm'      => $PollForm,
        'User'          => $User,
    );

    $PollObject     = new PollDisplay($poll_object_array);
    $PollDisplay    = $PollObject->getPoll();
}
?>

<link href="https://www.sketchbook.cafe/css/font-awesome/css/font-awesome.css" rel="stylesheet" type="text/css">


<?php
// Challenge?
if ($thread_row['challenge_id'] > 0)
{
    // Set
    $difficulty = 0;
    if ($challenge_row['difficulty_total'] > 0)
    {
        // Calculate
        $difficulty = number_format(($challenge_row['difficulty_total'] / $challenge_row['difficulty_max']) * 10,1);
    }
?>
<div class="challenge_wrap">

    <!-- Start Left -->
    <div class="challenge_left">

        <div class="challenge_title">
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
                <b>Reward</b>
            </div>
            <?php echo $challenge_row['points'];?> Points
        </div>
        <div class="challenge_minitext">
            <div>
                Average User Difficulty: <b><?php echo $difficulty;?></b> out of 10 (<?php echo $challenge_row['difficulty_votes'];?> votes)
            </div>
        </div>
        <div class="challenge_bottom_links">
            <a href="https://www.sketchbook.cafe/challenges/<?php echo $thread_row['challenge_id'];?>/1/0/">View Gallery</a>
<?php
    // User
    if ($User->loggedIn())
    {
?>
            <span class="challenge_bottom_spacer">
                &#8226;
            </span>
            <a href="#submit_entry" onClick="hideshow('challenge_submit_entry');">Submit Entry</a>
<?php
    }

    // Admins only
    if ($User->isAdmin())
    {
?>
            <div>
                <a href="https://www.sketchbook.cafe/challenges/<?php echo $thread_row['challenge_id'];?>/2/0/"><?php echo $challenge_row['total_pending'];?> Pending</a>
            </div>
<?php
    }
?>
        </div>

    <!-- End Left -->
    </div>

    <!-- Start Right -->
    <div class="challenge_right">


<?php
    // Challenge Gallery
    if ($entries_rownum > 0)
    {
?>
        <div class="challenge_gallery_wrap">
            <div class="challenge_gallery_thumb_wrap">
<?php
        // Create gallery based off entries
        while ($trow = mysqli_fetch_assoc($entries_result))
        {
?>
                <div class="challenge_thumbnail_div">
                    <span class="helper"></span>
                    <a href="https://www.sketchbook.cafe/entry/<?php echo $trow['id'];?>/">
                        <script>sbc_challenge_thumbnail(<?php echo $trow['image_id'];?>);</script>
                    </a>
                </div>
<?php
        }
        mysqli_data_seek($entries_result,0);
?>
            </div>
        </div>

<?php
    }
?>
    </div>
</div>
<?php
}
?>

<style type="text/css">
.thread_wrap {
    overflow: hidden;
    margin-left: 9px;
    margin-right: 9px;
    margin-top: 25px;
    margin-bottom: 25px;
    // border: 1px solid #151515;


}
.thread_title_wrap {
    overflow: hidden;
}
.thread_title_right {
    float: right;
    overflow: hidden;
    min-width: 225px;
    text-align: right;
}
.thread_title_left {
    overflow: hidden;
}
.thread_title {
    font-size: 24px;
    font-family: 'Alegreya SC', serif;

    color: #303030;
}
.thread_title a:link, .thread_title a:visited, .thread_title a:active {
    color: #303030;
}
.thread_title a:hover {
    text-decoration: underline;
}
.thread_breadcrumbs_wrap {
}
.thread_comment_wrap {
    overflow: hidden;
}
.thread_upload_div {
    padding: 15px;
    background-color: #F5F5F5;

}
.thread_upload_link a:link, .thread_upload_link a:visited, .thread_upload_link a:active {
    color: red;
}
.thread_upload_link a:hover {
    text-decoration: underline;
}
.thread_challenge_inner_title {
    font-size: 13px;
    font-weight: bold;
    padding: 2px;
}
.thread_challenge_inner_spacer {
    height: 5px;
}
.thread_last_edited {
    margin-top: 6px;

    font-size: 12px;
    font-style: italic;
    font-family: 'Ek Mukta', sans-serif;

    color: #A1A1A1;
}
.thread_subscribe_submit {
    font-size: 13px;
    font-family: 'Ek Mukta', sans-serif;

    -moz-border-radius: 5px 5px 5px 5px;
    border-radius: 5px 5px 5px 5px;
}
.thread_reply_title {
    font-size: 16px;
    font-family: 'Ek Mukta', sans-serif;
}
</style>

<div class="thread_wrap">
    <div class="thread_title_wrap">

<?php
// Subscription Form
if ($User->loggedIn())
{
    echo '<div class="thread_title_right">';
    // Start Form
    echo $SubscribeForm->start();
    echo $SubscribeForm->field['thread_id'];
    echo $SubscribeForm->field['submit'];

    // End Form
    echo $SubscribeForm->end();
    echo '</div>';
}
?>
        <div class="thread_title_left">
            <div class="thread_title">
                <a href=""><?php echo $thread_title;?></a>
            </div>
        </div>
    </div>

    <div class="thread_breadcrumbs_wrap">
        <div class="breadCrumbs">
            <a href="https://www.sketchbook.cafe/"><?php echo $category_row['name'];?></a>
            <span class="breadCrumbsSeperator">></span>
            <a href="https://www.sketchbook.cafe/forum/<?php echo $forum_row['id'];?>/"><?php echo $forum_row['name'];?></a>
            <span class="breadCrumbsSeperator">></span>
            <a href="https://www.sketchbook.cafe/forum/thread/<?php echo $thread_row['id'];?>/"><?php echo $thread_row['title'];?></a>
        </div>
    </div>

    <div class="thread_comment_wrap">
<?php
// Thread Comment
echo $DisplayComment->process(array
(
    'comment_id'    => $thread_row['comment_id'],
    'thread_locked' => $thread_row['is_locked'],
    'thread_sticky' => $thread_row['is_sticky'],
    'thread_id'     => $thread_row['id'],
    'PollDisplay'   => $PollDisplay,
));

?>
    </div>

<?php
// Page Numbers
if ($comments_rownum > 0)
{
?>
    <div class="thread_pagenumbers_wrap">
        <div class="pageNumbersWrap">
            <div class="pageNumbersLeft">
                Viewing <?php echo $PageNumbers['pages_min'];?>-<?php echo $PageNumbers['pages_max'];?> posts (<?php echo $PageNumbers['pages_total'];?> total). 
            </div>
            <div class="pageNumbersRight">
<?php
    echo $PageNumbers['pagenumbers'];
?>
            </div>
        </div>
    </div>
<?php
}
?>

    <!-- Start Comments -->
<?php
// Comments
if ($comments_rownum > 0)
{
    // Loop
    $i = 0;
    while ($trow = mysqli_fetch_assoc($comments_result))
    {
?>
    <div class="thread_comment_wrap">
<?php
        // Recent Anchor
        if ($i >= ($comments_rownum - 1))
        {
            echo '<a name="recent"></a>';
        }

        // Add
        $i++;

        // Pending
        $image_script   = '';
        $entry_id       = $Comment->comment[$trow['cid']]['entry_id'];
        $image_id       = $Comment->comment[$trow['cid']]['image_id'];

        // Entry
        if (isset($entry[$entry_id]) && $image_id > 0)
        {
            if ($entry[$entry_id]['ispending'] == 1 || !$User->loggedIn())
            {
                $image_script = '<div class="thumb_div"><script>sbc_thumbnail('.$image_id.',\'\');</script></div>';
            }
            else
            {
                $image_script = '<div class="thumb_div"><script>sbc_image('.$image_id.',\'\');</script></div>';
            }
        }

/*
        if ($image_id > 0)
        {
            $image_script = '<div class="thumb_div"><script>sbc_thumbnail('.$image_id.',\'\');</script></div>';
        }
*/

        // Entry
        if (isset($entry[$entry_id]))
        {

            // FIXME: Decide whether to allow full images as posts even if the entry is pending
/*
            if ($entry[$entry_id]['ispending'] == 1)
            {
                $image_script = '
<div class="pending_div">
    <div id="pending'.$image_id.'_2" class="pending_image" onclick="hideshow(\'pending'.$image_id.'_2\'); hideshow(\'pending'.$image_id.'\'); return false;" style="display:;">Pending Entry</div>
    <div id="pending'.$image_id.'" style="display:none;"><script>sbc_thumbnail('.$image_id.',\'\');</script></div>
</div>';
            }
*/
        }

        // Display Comment
        echo $DisplayComment->process(array
        (
            'comment_id'    => $trow['cid'],
            'image_script'  => $image_script,
        ));
?>
    </div>
<?php
    }
    mysqli_data_seek($comments_result,0);
}
?>
    <!-- End Comments -->



<?php
// Page Numbers
if ($comments_rownum > 0)
{
?>
    <div class="thread_pagenumbers_wrap">
        <div class="pageNumbersWrap">
            <div class="pageNumbersLeft">
                &nbsp;
            </div>
            <div class="pageNumbersRight">
<?php
    echo $PageNumbers['pagenumbers'];
?>
            </div>
        </div>
    </div>
<?php
}
?>

<style type="text/css">
.rating {
    overflow: hidden;
    display: inline-block;
    font-size: 0;
    position: relative;
}
.rating-input {
    float: right;
    width: 16px;
    height: 16px;
    padding: 0;
    margin: 0 0 0 -16px;
    opacity: 0;
}
.rating:hover .rating-star:hover, .rating:hover .rating-star:hover ~ .rating-star, .rating-input:checked ~ .rating-star {
    background-position: 0 0;
    cursor: pointer;
}
.rating-star,
.rating:hover .rating-star {
    position: relative;
    float: right;
    display: block;
    width: 16px;
    height: 16px;
    background: url('https://www.sketchbook.cafe/images/star.png') 0 -16px;
}


</style>



    <!-- Start Reply Form -->
<a name="submit_entry"></a>
<?php
$allow_challenge_submission = 0;
if ($User->loggedIn())
{
    if ($user_entry_id < 1 && $thread_row['challenge_id'] > 0)
    {
        $allow_challenge_submission = 1;
    }

    // FIXME: temporary
    if ($thread_row['challenge_id'] > 0)
    {
        $allow_challenge_submission = 1;
    }
?>
    <div class="thread_comment_wrap">
        <div class="commentWrap">
<?php
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
                <b>Thread is locked.</b>
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
                <div class="fb thread_reply_title">
                    Reply
<?php
        if ($allow_challenge_submission > 0)
        {
?>
            or
            <span class="thread_upload_link">
                <a href="" onClick="hideshow('challenge_submit_entry');return false;">Submit Entry</a>
            </span>
<?php
        }
?>
                </div>
<?php
        // Challenge?
        if ($allow_challenge_submission > 0)
        {
?>
                <div id="challenge_submit_entry" class="thread_upload_div" style="display:none;">
                    <div class="thread_challenge_inner_title sbc_font">
                        Difficulty:
                    </div>
                    <div>
                        <span class="rating">
<?php
// Loop
$i = 10;
while ($i > 0)
{
?>
                            <input type="radio" class="rating-input" id="rating-input-1-<?php echo $i;?>" name="challenge_difficulty" value="<?php echo $i;?>"/>
                            <label for="rating-input-1-<?php echo $i;?>" class="rating-star"></label>
<?php

    $i--;
}
?>
                        </span>
                    </div>
                    <div class="thread_challenge_inner_spacer">
                    </div>
                    <div class="thread_challenge_inner_title sbc_font">
                        Select a File:
                    </div>
<?php
            echo $Form->field['challenge_id'];
            echo $Form->field['imagefile'];
?>
                </div>
<?php
        }
?>
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
    </div>
<?php
}
?>
    <!-- End Reply Form -->
</div>

