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
$challenge_row  = &$data['challenge_row'];

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
<!-- Start Forum Wrap -->
<div class="forum_main_wrap">
    <div class="forum_main_title sbc_font_main forum_title_link">
        <a href="https://www.sketchbook.cafe/forum/<?php echo $forum_row['id'];?>"><?php echo $forum_row['name'];?></a>
    </div>
    <div class="forum_main_top_wrap">

<?php
// Users Only
if ($User->loggedIn())
{
?>
        <div class="forum_main_top_right sbc_font">
            <div id="forum_main_new_thread_button" class="forum_main_new_thread_button sbc_font">
                New Thread
            </div>
        </div>
<?php
}
?>

        <div class="forum_main_breadcrumbs breadCrumbs">
            <a href="https://www.sketchbook.cafe/"><?php echo $category_row['name'];?></a>
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
    <div id="forum_main_new_thread_div" class="forum_main_new_thread_div">
        <div class="fpCreateLink">
            <b>New Forum Thread</b> 
            <span class="fpCreatePollLink">
                <a href="" onClick="hideshow('createpoll');return false;">Create Poll</a>
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
                <div class="innerRight fpCreateLink">
<?php
echo $Form->field['poll'.$i];
?>
<?php
    // More Options
    if ($i == 5)
    {
?>
                    <span id="createpoll_more_link" style="display:;">
                        <a href="" onClick="hideshow('createpoll_more'); hideshow('createpoll_more_link'); return false;">add more options</a>
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


    <!-- Page Numbers -->
    <div class="forum_main_pagenumbers_div">
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
    </div>

    <!-- Forum Table with Threads -->
   <div class="table fp_table">
        <div class="tr">
            <div class="fpTd fpTdLeft fpTdMainTop sbc_font">
                Topic
            </div>
            <div class="fpTd fpTdMiddle fpTdMainTop sbc_font">
                Users
            </div>
            <div class="fpTd fpTdMiddle fpTdMainTop sbc_font">
                Posts
            </div>
            <div class="fpTd fpTdMiddle fpTdMainTop sbc_font">
                Views
            </div>
            <div class="fpTd fpTdRight fpTdMainTop sbc_font">
                Freshness
            </div>
        </div>
<?php
// Threads
if ($threads_rownum > 0)
{
    // Loop
    while ($trow = mysqli_fetch_assoc($threads_result))
    {
        // Challenge?
        $pending_entries = 0;
        if ($trow['challenge_id'] > 0)
        {
            $pending_entries = $challenge_row[$trow['challenge_id']]['total_pending'];
        }

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
                <div class="fpTdTitle sbc_font">
<?php
        // Challenge
        if ($trow['challenge_id'] > 0)
        {
?>
                    <span class="fb">[Challenge]</span>
<?php
        }

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
<?php
        // Pending
        if ($pending_entries > 0)
        {
?>
                    <span class="fpPendingEntries fb">
                        (<?php echo $pending_entries;?> Pending)
                    </span>
<?php
        }
?>
                </div>
                <div class="fpTdTitleUser sbc_font">
                    <script>sbc_avatar(<?php echo $trow['user_id'];?>,'fpAvatar');</script>
                    <span>
                        <script>sbc_username(<?php echo $trow['user_id'];?>,' ');</script> 
                        on <?php echo $User->mytz($trow['date_created'],'F jS, Y - g:iA');?>
                    </span>
                </div>
            </div>
            <div class="fpTd fpTdMain fpPosts sbc_font">
                <?php echo $trow['total_users'];?>
            </div>
            <div class="fpTd fpTdMain fpPosts sbc_font">
                <?php echo $trow['total_comments'];?>
            </div>
            <div class="fpTd fpTdMain fpPosts sbc_font">
                <?php echo $trow['total_views'];?>
            </div>
            <div class="fpTd fpTdMain fpFreshness sbc_font">
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
    <!-- End Forum Table -->
    </div>


<!-- End Forum Wrap -->
</div>


<!-- Use this CSS instead -->
<style type="text/css">
.fp_table {
    width: 100%;
    background-color: #FFFFFF;
}
</style>


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
/*

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
*/
?>
