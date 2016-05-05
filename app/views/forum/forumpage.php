<?php
// Initialize Objects and Vars
$User           = &$data['User'];
$Form           = &$data['Form'];
$category_row   = &$data['category_row'];
$forum_row      = &$data['forum_row'];
$threads_result = &$data['threads_result'];
$threads_rownum = &$data['threads_rownum'];
$Member         = &$data['Member'];
$Comment        = &$data['Comment'];

// Forum Admins
$forum_admin_result = &$data['forum_admin_result'];
$forum_admin_rownum = &$data['forum_admin_rownum'];

// Page Numbers
$pagenumbers    = &$data['pagenumbers'];
$pages_min      = &$data['pages_min'];
$pages_max      = &$data['pages_max'];
$pages_total    = &$data['pages_total'];

// Thread Timers
$view_time      = &$data['view_time'];

// Generated
$user_id        = $User->getUserId();
?>
<style type="text/css">
.fpWrap {
    margin-left: 15px;
    margin-right: 15px;
    margin-top: 9px;
    margin-bottom: 9px;
    background-color: #FFFFFF;

    -webkit-box-shadow: 0px 2px 2px 0px rgba(135,135,135,1);
    -moz-box-shadow: 0px 2px 2px 0px rgba(135,135,135,1);
    box-shadow: 0px 2px 2px 0px rgba(135,135,135,1);

}
.fpTable {
    width: 100%;
}
.fpTd {
    display: table-cell;
    padding: 12px;

}
.fpTdLeft {

}
.fpTdMiddle {
    text-align: center;
    width: 50px;
}
.fpTdRight {
    width: 215px;
    text-align: right;
}
.fpTitle {
    margin-left: 15px;
    margin-top: 15px;
    height: 40px;
    font-size: 30px;
    font-family: Georgia, serif;
}
.fpTitle a:link, .fpTitle a:active, .fpTitle a:visited {
    color: #151515;
}
.fpTitle a:hover {
    text-decoration: underline;
}
.breadCrumbs {
    margin-left: 15px;
}
.fpTopWrap {
    overflow: hidden;
}
.fpTopRight {
    margin-right: 15px;
    float: right;
    text-align: right;
}
.fpNewThreadButton {
    width: 100px;
    text-align: center;
    font-size: 12px;
    font-family: Georgia, serif;
    padding: 6px;

    cursor: pointer;

    -moz-border-radius: 2px 2px 2px 2px;
    border-radius: 2px 2px 2px 2px;

    color: #151515;
    background-color: #ACACAC;
}
.fpNewThreadButton:hover {
    background-color: #757575;
}
.fpNewThreadDiv {
    margin: 15px;
    padding: 15px;
    background-color: #FFFFFF;
}
.fpInputTitle {
    border: 1px solid #D4D4D4;
}
.fpInputPoll {
    border: 1px solid #D4D4D4;
    width: 500px;
}
.fpCreatePollLink {
    font-size: 12px;
}
.createpolldiv {
    margin-bottom: 25px;
}
</style>
<div class="fpTitle">
    <a href="https://www.sketchbook.cafe/forum/<?php echo $forum_row['id'];?>"><?php echo $forum_row['name'];?></a>
</div>

<div class="fpTopWrap">
    <div class="fpTopRight">
<?php
// Users Only
if ($User->loggedIn())
{
?>
        <div id="fpNewThreadButton" class="fpNewThreadButton">
            New Thread
        </div>
<?php
}
?>
    </div>
    <div class="breadCrumbs">
        <a href="https://www.sketchbook.cafe/forum/"><?php echo $category_row['name'];?></a>
        <span class="breadCrumbSeparator">></span>
        <a href="https://www.sketchbook.cafe/forum/<?php echo $forum_row['id'];?>"><?php echo $forum_row['name'];?></a>
    </div>
</div>
<?php
// Users Only
if ($User->loggedIn())
{
    // Form Start
    echo $Form->start();
    echo $Form->field['forum_id'];
?>
<div id="fpNewThreadDiv" class="fpNewThreadDiv" style="display: none;">
    <div>
        <b>New Forum Thread</b> 
        <span class="fpCreatePollLink">
            <a href="" onClick="hideshow('createpoll');return false;">[Create Poll]</a>
        </span>
    </div>

    <!-- Start Polls -->
    <div id="createpoll" class="createpolldiv" style="display:none;">

    <div class="innerWrap">
        <div class="innerLeft">
            &nbsp;
        </div>
        <div class="innerRight">
            <b>Create a Poll</b> (note: answers cannot be changed after thread is created)
        </div>
    </div>

<?php
// Polls
$i = 1;
while ($i < 11)
{
    // If 5, hide the rest
    if ($i == 6)
    {
?>
    <span id="createpoll_more" style="display: none;">
<?php
    }
?>

    <div class="innerWrap">
        <div class="innerLeft">
            Poll <?php echo $i;?>:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['poll'.$i];
?>
<?php
    // More Options
    if ($i == 5)
    {
?>
            <span id="createpoll_more_link" style="display:;">
                <a href="" onClick="hideshow('createpoll_more'); hideshow('createpoll_more_link'); return false;">[More Options]</a>
            </span>
<?php
    }
?>
        </div>
    </div>

<?php
    // If 10, hide the rest
    if ($i == 10)
    {
?>
    </span>
<?php
    }

    $i++;
}
?>

    </div>
    <!-- End Polls -->

    <div class="innerWrap">
        <div class="innerLeft">
            Title:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['name'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Message:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['message'];
?>
        </div>
    </div>


</div>
<?php

    // Form End
    echo $Form->end();
}
?>

<div class="pageNumbersWrap">
    <div class="pageNumbersLeft">
        Viewing <?php echo $pages_min;?>-<?php echo $pages_max;?> threads (<?php echo $pages_total;?> total). 
    </div>
    <div class="pageNumbersRight">

<?php
echo $pagenumbers;
?>

    </div>
</div>

<div class="fpWrap">
    <div class="table fpTable">

        <div class="tr">
            <div class="fpTd fpTdLeft fpTdMainTop">
                Topic
            </div>
            <div class="fpTd fpTdMiddle fpTdMainTop">
                Users
            </div>
            <div class="fpTd fpTdMiddle fpTdMainTop">
                Posts
            </div>
            <div class="fpTd fpTdMiddle fpTdMainTop">
                Views
            </div>
            <div class="fpTd fpTdRight fpTdMainTop">
                Freshness
            </div>
        </div>

<style type="text/css">
.fpTdTitle {
    overflow: hidden;
    max-width: 800px;
    font-size: 16px;
    font-family: Georgia, serif;
}
.fpTdTitle a:link, .fpTdTitle a:active, .fpTdTitle a:visited {
    color: red;
}
.fpTdTitle a:hover {
    text-decoration: underline;
}
.fpTdTitleUser {
    margin-top: 6px;
    font-size: 12px;
    font-family: Georgia, serif;

    color: #828282;
}
.fpTdTitleUser a:link, .fpTdTitleUser a:active, .fpTdTitleUser a:visited {
    color: #828282;
    text-decoration: underline;
}
.fpTdTitleUser a:hover {
    text-decoration: underline;
}
.fpTdMainTop {
    font-size: 12px;
    font-family: Georgia, serif;
    font-weight: bold;

    color: #3C3C3C;
    background-color: #C1C1C1;
    border-bottom: 1px solid #CCCCCC;
}
.fpTdMain {
    border-bottom: 1px solid #E1E1E1;
}
.fpPosts {
    font-family: Georgia, serif;
    font-size: 13px;
    line-height: 20px;
    text-align: center;
}
.fpFreshness {
    text-align: right;
    font-family: Georgia, serif;
    font-size: 13px;

    color: #828282;
}
.fpFreshness a:active, .fpFreshness a:link, .fpFreshness a:visited {
    color: #828282;
    text-decoration: underline;
}
.fpFreshness a:hover {
    color: #828282;
    text-decoration: underline;
}
.fpPageNumbers {
    padding-left: 12px;
    font-size: 13px;
}
.fpAvatar {
    padding-left: 2px;
    padding-right: 2px;
    max-width: 20px;
    max-height: 20px;

    vertical-align: middle;

    -moz-border-radius: 2px 2px 2px 2px;
    border-radius: 2px 2px 2px 2px;
}
.fpTdSticky {
    background-color: #EBF4F4;
}
</style>


<?php
// Threads
if ($threads_rownum > 0)
{
    // Loop
    while ($trow = mysqli_fetch_assoc($threads_result))
    {
        // Initialize Vars
        $isupdated  = '';

        // User?
        if ($user_id > 0)
        {
            if (isset($view_time[$trow['id']]))
            {
                // Date Updated greater than Date Viewed?
                if ($view_time[$trow['id']]['date_updated'] > $view_time[$trow['id']]['date_viewed'])
                {
                    $isupdated = ' fb ';
                }
            }
        }
?>
        <div class="tr <?php if ($trow['is_sticky'] == 1) { ?> fpTdSticky<?php } ?>">
            <div class="fpTd fpTdMain">
                <div class="fpTdTitle">
<?php
        // Sticky Thread
        if ($trow['is_sticky'] == 1)
        {
?>
                    <span class="fb">[Sticky]</span>
<?php
        }

        // Locked Thread
        if ($trow['is_locked'] == 1)
        {
?>
                    <span class="fb">[Locked]</span>
<?php
        }

        // Poll
        if ($trow['poll_id'] > 0)
        {
?>
                    <span class="fb">[Poll]</span>
<?php
        }
?>
                    <a href="https://www.sketchbook.cafe/forum/thread/<?php echo $trow['id'];?>" class="<?php echo $isupdated;?>"><?php echo $trow['title'];?></a>
                    <span class="fpPageNumbers">
                        <script>sbc_numbered_links('https://www.sketchbook.cafe/forum/thread/<?php echo $trow['id'];?>/',10,<?php echo $trow['total_comments']-1;?>,'sbc_pagenumber');</script>
                    </span>
                </div>
                <div class="fpTdTitleUser">
                    <script>sbc_avatar(<?php echo $trow['user_id'];?>,'fpAvatar');</script>
                    <span>
                        <script>sbc_username(<?php echo $trow['user_id'];?>,' ');</script> 
                        on <?php echo $User->mytz($trow['date_created'],'F jS, Y - g:iA');?>
                    </span>
                </div>
            </div>
            <div class="fpTd fpTdMain fpPosts">
                <?php echo $trow['total_users'];?>
            </div>
            <div class="fpTd fpTdMain fpPosts">
                <?php echo $trow['total_comments'];?>
            </div>
            <div class="fpTd fpTdMain fpPosts">
                <?php echo $trow['total_views'];?>
            </div>
            <div class="fpTd fpTdMain fpFreshness">
                <script>sbc_dateago(<?php echo time();?>, <?php echo $trow['date_updated'];?>);</script>
                by 
                <script>sbc_avatar(<?php echo $trow['last_user_id'];?>,'fpAvatar');</script>
                <span style="">
                    <script>sbc_username(<?php echo $trow['last_user_id'];?>,' ');</script>
                </span>
            </div>
        </div>

<?php
    }
    mysqli_data_seek($threads_result,0);
}
?>

    </div>

</div>

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


<style type="text/css">
.forum_admins_wrap {
    margin-left: 15px;
    margin-right: 15px;
    margin-bottom: 20px;
    overflow: hidden;
    border 1px solid #151515;

    background-color: #C1C1C1;

    -moz-border-radius: 100px 100px 100px 100px;
    border-radius: 100px 100px 100px 100px;
}

.forum_admins_title {
    font-size: 13px;
    font-family: Georgia, serif;
}
.forum_admins_inner {
    padding: 6px;
    font-size: 0px;
    overflow: hidden;

    text-align: center;
}
.forum_admins_admin {
    display: inline-block;
    overflow: hidden;
    width: 85px;
    height: 85px;
    margin: 2px;

    -moz-border-radius: 85px 85px 85px 85px;
    border-radius: 85px 85px 85px 85px;

    border: 2px solid #FFFFFF;
}
.forum_admins_avatar {
    max-width: 85px;

}
</style>

<?php
// Forum Admins
if ($forum_admin_rownum > 0)
{
?>
<div class="forum_admins_wrap">

    <div class="forum_admins_inner">
<?php
    // Loop Admins
    while ($trow = mysqli_fetch_assoc($forum_admin_result))
    {
?>
        <div class="forum_admins_admin">
            <script>sbc_avatar(<?php echo $trow['user_id'];?>,'forum_admins_avatar');</script>
        </div>
<?php
    }
    mysqli_data_seek($forum_admin_result,0);
?>
    </div>
</div>

<?php
}
?>
