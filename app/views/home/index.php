<?php
// Set Arrays
$online_data['rownum'] = 0;
$entries_data['rownum'] = 0;
$stream_data['streamer_id'] = 0;
$top_data['rownum'] = 0;

// Initialize
$top_data       = &$data['top_data'];
$stream_data    = &$data['stream_data'];
$User           = &$data['User'];
$entries_data   = &$data['entries_data'];
$online_data    = &$data['online_data'];
$forum_data     = &$data['forum_data'];
$thread         = &$forum_data['thread'];
$twitch_json    = &$data['twitch_json'];
$twitch_array   = [];
$twitch_array['streams'] = []; // initialize
if (!empty($twitch_json))
{
    $twitch_array = json_decode($twitch_json, true);
}
$total_streams = count($twitch_array['streams']);
?>
<style type="text/css">
.streamers_wrap {
    overflow: hidden;

    -webkit-box-shadow: 0px 2px 5px 0px rgba(148,148,148,1);
    -moz-box-shadow: 0px 2px 5px 0px rgba(148,148,148,1);
    box-shadow: 0px 2px 5px 0px rgba(148,148,148,1);
}
.streamers_top_title {
    font-size: 12px;
    padding-left: 15px;
    padding-top: 5px;
    color: #585858;
    background-color: #FFFFFF;
}
.streamers_bottom_wrap {
    padding-top: 5px;
    padding-bottom: 5px;
    padding-left: 15px;
    padding-right: 15px;

    overflow: hidden;
    text-align: left;
    font-size: 0px;

    background-color: #FFFFFF;
}
.streamers_item_wrap {
    margin-left: 5px;
    margin-right: 5px;
    margin-bottom: 10px;
    display: inline-block;
    overflow: hidden;
    border: 1px solid #151515;

    -webkit-box-shadow: 0px 1px 5px 0px rgba(168,168,168,1);
    -moz-box-shadow: 0px 1px 5px 0px rgba(168,168,168,1);
    box-shadow: 0px 1px 5px 0px rgba(168,168,168,1);
}
.streamers_item_wrap img {
    max-width: 160px;
}
.streamers_item_displayname {
    padding-left: 3px;
    font-size: 13px;
    text-align: left;
    overflow: hidden;
}
.streamers_item_viewers {
    padding-right: 3px;
    float: right;
    min-width: 25px;
    overflow: hidden;
    text-align: right;
    font-size: 12px;
}
.streamers_item_bottom_wrap {
    overflow: hidden;

    background-color: #151515;
    color: #FFFFFF;
}
.streamers_add {

    font-size: 11px;
    padding-left: 6px;
}
.streamers_add a:active, .streamers_add a:link, .streamers_add a:visited {
    color: #585858;
}
.streamers_add a:hover {
    text-decoration: underline;
}
.streamers_add_div {

}
</style>
<?php
if ($total_streams > 0)
{
?>
<div class="streamers_wrap">
    <div class="streamers_top_title">
        <b>Streaming Live</b>
<?php
    // Users Only
    if ($User->loggedIn())
    {
        if ($stream_data['streamer_id'] < 1 && $stream_data['sketch_points'] > 200)
        {
?>
        <span id="streamers_add_button" class="streamers_add">
            <a href="" onClick="return false;">
                (add your stream)
            </a>
        </span>
<?php
            // Start Form
            $StreamForm = &$stream_data['StreamForm'];
            echo $StreamForm->start();
?>
        <div id="streamers_add_div" class="streamers_add_div" style="display: none;">
<?php
            echo $StreamForm->field['username'];
            echo $StreamForm->field['submit'];
?>
        </div>
<?php
            // End Form
            echo $StreamForm->end();
        }
        else
        {
?>
        <span class="streamers_add">
            <a href="https://www.sketchbook.cafe/settings/stream/">
                (edit)
            </a>
        </span>
<?php
        }
    }
?>
    </div>
    <div class="streamers_bottom_wrap">
<?php
    // List the streams
    foreach ($twitch_array['streams'] as $stream)
    {
        // Make sure the streamer is on creative
        if ($stream['game'] == 'Creative')
        {
?>
        <a href="https://www.twitch.tv/<?php echo $stream['channel']['name'];?>/">
            <div class="streamers_item_wrap">
                <img src="<?php echo $stream['preview']['medium'];?>">
                <div class="streamers_item_bottom_wrap">
                    <div class="streamers_item_viewers">
                        <?php echo $stream['viewers'];?> V
                    </div>
                    <div class="streamers_item_displayname">
                        <?php echo $stream['channel']['display_name'];?>
                    </div>
                </div>
            </div>
        </a>
<?php
        }
    }
?>
    </div>
</div>
<?php
}
?>




<style type="text/css">
.forum_avatar_mini {
    padding-left: 2px;
    padding-right: 2px;
    max-width: 20px;
    max-height: 20px;

    vertical-align: middle;

    -moz-border-radius: 4px 4px 4px 4px;
    border-radius: 4px 4px 4px 4px;
}

.forum_wrap {
    overflow: hidden;
}
.forum_category_wrap {
    overflow: hidden;

    background-color: #C1C1C1;

<?php
/*
    -webkit-box-shadow: 0px 2px 2px 0px rgba(135,135,135,1);
    -moz-box-shadow: 0px 2px 2px 0px rgba(135,135,135,1);
    box-shadow: 0px 2px 2px 0px rgba(135,135,135,1);
*/
?>
}
.forum_category_name {
    margin-top: 6px;
    margin-left: 20px;

    font-size: 24px;
    font-family: 'Alegreya SC', serif;

    color: #151515;
}

.forum_category_description {
    margin-left: 20px;
    margin-bottom: 6px;

    font-size: 14px;
    line-height: 21px;
    font-family: 'Ek Mukta', sans-serif;

    color: #151515;
}

.forum_forum_wrap {
    overflow: hidden;
    padding-left: 20px;
    padding-right: 20px;

    background-color: #FFFFFF;
}
.forum_forum_name {
    padding-left: 20px;

    font-weight: bold;
    font-size: 17px;
    font-family: 'Alegreya SC', serif;
}

.forum_forum_name a:link, .forum_forum_name a:visited, .forum_forum_name a:active {
    color: #C13A3A;
}
.forum_forum_name a:hover {
    text-decoration: underline;
}

.forum_forum_description {
    padding-left: 20px;
    padding-bottom: 5px;
    font-size: 13px;
    font-family: 'Ek Mukta', sans-serif;
}


.forum_table {
    width: 100%;
}
.forum_table_forum_top {
    padding-left: 20px;
    padding-top: 15px;
    padding-bottom: 5px;
    letter-spacing: 0.1em;
    font-weight: bold;
    font-size: 12px;
    font-family: 'Ek Mukta', sans-serif;

    color: #151515;
}
.forum_table_threads_top {
    width: 100px;
    letter-spacing: 0.1em;
    text-align: center;
    padding-top: 15px;
    padding-bottom: 5px;

    font-weight: bold;
    font-size: 12px;
    font-family: 'Ek Mukta', sans-serif;
}
.forum_table_posts_top {
    width: 100px;
    letter-spacing: 0.1em;
    text-align: center;
    padding-top: 15px;
    padding-bottom: 5px;

    font-weight: bold;
    font-size: 12px;
    font-family: 'Ek Mukta', sans-serif;
}
.forum_table_freshness_top {
    width: 400px;
    letter-spacing: 0.1em;
    text-align: right;
    padding-right: 20px;
    padding-top: 15px;
    padding-bottom: 5px;

    font-weight: bold;
    font-size: 12px;
    font-family: 'Ek Mukta', sans-serif;
}

.forum_table_forum_bottom {
    padding-top: 15px;
    padding-bottom: 15px;
}
.forum_table_threads_bottom {
    width: 100px;
    text-align: center;
    font-size: 14px;
    font-family: 'Ek Mukta', sans-serif;
    height: 20px;
    padding-top: 15px;
    overflow: hidden;

}
.forum_table_posts_bottom {
    width: 100px;
    text-align: center;
    font-size: 14px;
    font-family: 'Ek Mukta', sans-serif;
    height: 20px;
    padding-top: 15px;
    overflow: hidden;

}
.forum_table_freshness_bottom {
    width: 400px;
    padding-right: 20px;
    text-align: right;


}


.forum_freshness_thread {
    width: 100%;
    overflow: hidden;
    font-weight: bold;
    font-size: 14px;
    font-family: 'Ek Mukta', sans-serif;
}
.forum_freshness_userdiv {
    width: 100%;
    padding-top: 3px;
    font-size: 14px;
    font-family: 'Ek Mukta', sans-serif;
}

.forum_table_freshness_bottom a:link, .forum_table_freshness_bottom a:visited, .forum_table_freshness_bottom a:active {
    color: #C13A3A;
}
.forum_table_freshness_bottom a:hover {
    text-decoration: underline;
}
</style>


<style type="text/css">
.home_wrap {
    margin-left: 10px;
    margin-right: 10px;
    margin-top: 15px;
    margin-bottom: 15px;

    padding: 5px;

    background-color: #FFFFFF;

    -moz-border-radius: 2px 2px 2px 2px;
    border-radius: 2px 2px 2px 2px;

    -webkit-box-shadow: 0px 1px 5px 0px rgba(168,168,168,1);
    -moz-box-shadow: 0px 1px 5px 0px rgba(168,168,168,1);
    box-shadow: 0px 1px 5px 0px rgba(168,168,168,1);
}
</style>

<!-- Start Home -->
<div class="home_wrap">



<!-- Start Forum -->
<div class="forum_wrap">
<?php
// Categories
while ($crow = mysqli_fetch_assoc($forum_data['categories_result']))
{
    $category_id    = $crow['id'];
?>
    <div class="forum_category_wrap">
        <div class="forum_category_name">
            <?php echo $crow['name'];?>
        </div>
        <div class="forum_category_description">
            <?php echo $crow['description'];?>
        </div>

        <!-- Start Table -->
        <div class="table forum_table">
            <div class="tr forum_forum_wrap">
                <div class="td forum_table_forum_top">
                    Forum Name
                </div>
                <div class="td forum_table_threads_top">
                    Threads
                </div>
                <div class="td forum_table_posts_top">
                    Posts
                </div>
                <div class="td forum_table_freshness_top">
                    Last Post
                </div>
            </div>
<?php
    // Forums
    while ($frow = mysqli_fetch_assoc($forum_data['forums_result']))
    {
        // Parent?
        if ($category_id == $frow['parent_id'])
        {
?>
            <div class="tr forum_forum_wrap">
                <div class="td forum_table_forum_bottom">


                    <div class="forum_forum_name">
                        <a href="https://www.sketchbook.cafe/forum/<?php echo $frow['id'];?>/"><?php echo $frow['name'];?></a>
                    </div>
                    <div class="forum_forum_description">
                        <?php echo $frow['description'];?>
                    </div>


                </div>
                <div class="td">
                    <div class="forum_table_threads_bottom">
                        <?php echo number_format($frow['total_threads']);?>
                    </div>
                </div>
                <div class="td">
                    <div class="forum_table_posts_bottom">
                        <?php echo number_format($frow['total_posts']);?>
                    </div>
                </div>
                <div class="td forum_table_freshness_bottom">
<?php
            // Last Thread
            if ($frow['last_thread_id'] > 0)
            {
?>
                    <div class="forum_freshness_thread">
                        <a href="https://www.sketchbook.cafe/forum/thread/<?php echo $frow['last_thread_id'];?>/"><?php echo $thread[$frow['last_thread_id']]['title'];?></a>
                    </div>
                    <div class="forum_freshness_userdiv">
                        <script>sbc_dateago(<?php echo time();?>,<?php echo $thread[$frow['last_thread_id']]['date_updated'];?>);</script>
                        by 
                        <script>sbc_avatar(<?php echo $thread[$frow['last_thread_id']]['last_user_id'];?>,'forum_avatar_mini');</script>
                        <script>sbc_username(<?php echo $thread[$frow['last_thread_id']]['last_user_id'];?>,'');</script>
                    </div>
<?php
            }
            else
            {
?>
                    <div class="forum_freshness_userdiv">
                        No Threads
                    </div>
<?php
            }
?>
                </div>
            </div>
<?php
        }
    }
?>
        <!-- End Table -->
        </div>
<?php
    mysqli_data_seek($forum_data['forums_result'],0);
?>
    </div>
<?php
}
mysqli_data_seek($forum_data['categories_result'],0);
?>
<!-- End Forum -->
</div>



<style type="text/css">
.recent_entries_wrap {
    background-color: #FFFFFF;
}
.recent_entries_title {
    padding-left: 20px;
    padding-top: 5px;
    font-weight: bold;
    font-size: 16px;
    color: #787878;
}
.recent_entries_gallery_wrap {
    padding-left: 10px;
    padding-right: 10px;
    text-align: center;
    overflow: hidden;
    font-size: 0px;
    max-height: 360px;
}
</style>

<!-- Start Entries -->
<?php
if ($entries_data['rownum'] > 0)
{
?>
<div class="recent_entries_wrap">
    <div class="recent_entries_title">
        Recent Entries
    </div>
    <div class="recent_entries_gallery_wrap">
<?php
    while ($trow = mysqli_fetch_assoc($entries_data['result']))
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
?>
    </div>
</div>
<?php
}
?>
<!-- End Entries -->



<style type="text/css">
.onlinelist_wrap {
    overflow: hidden;

    background-color: #FFFFFF;

<?php
/*
    -webkit-box-shadow: 0px 0px 4px 0px rgba(125,125,125,1);
    -moz-box-shadow: 0px 0px 4px 0px rgba(125,125,125,1);
    box-shadow: 0px 0px 4px 0px rgba(125,125,125,1);
*/
?>
}

.onlinelist_title {
    margin-top: 12px;
    margin-left: 15px;

    font-size: 17px;
    font-family: 'Alegreya SC', serif;

    color: #151515;
}
.onlinelist_inner_wrap {
    overflow: hidden;
    margin-left: 16px;
    margin-bottom: 12px;
    margin-top: 4px;
}
.onlinelist_userdiv {
    float: left;
    display: inline-block;
    overflow: hidden;
    min-width: 50px;
    height: 20px;
    margin: 3px;
    border: 1px solid #ECECEC;
    border-bottom: 3px solid #60E0DA;

    background-color: #ECECEC;

    -moz-border-radius: 3px 3px 3px 3px;
    border-radius: 3px 3px 3px 3px;
}
.onlinelist_avatardiv {
    float: left;
    display: inline-block;
    overflow: hidden;
    width: 20px;
    text-align: center;
    background-color: #FFFFFF;
}
.onlinelist_user {
    font-size: 13px;
    letter-spacing: 0.1em;
    font-family: 'Ek Mukta', sans-serif;
    line-height: 20px;
    padding-left: 32px;
    padding-right: 8px;

    color: #151515;

}
.onlinelist_user a:link, .onlinelist_user a:visited, .onlinelist_user a:active {
    color: #151515;
}
.onlinelist_user a:hover {
    text-decoration: underline;
}
.onlinelist_avatar {
    max-width: 20px;
    max-height: 20px;
}
.onlinelist_last {
    padding-left: 9px;
    font-size: 16px;
    color: #787878;
}
</style>

<!-- Who's Online -->
<div class="onlinelist_wrap">
    <div class="onlinelist_title">
        Who's Online 
        <span class="onlinelist_last">
            (last 24 hours)
        </span>
    </div>
    <div class="onlinelist_inner_wrap">
<?php
// Online?
if ($online_data['rownum'] > 0)
{
    // Loop Users
    while ($trow = mysqli_fetch_assoc($online_data['result']))
    {
?>
        <div class="onlinelist_userdiv">
            <div class="onlinelist_avatardiv">
                <script>sbc_avatar(<?php echo $trow['id'];?>,'onlinelist_avatar');</script>
            </div>
            <div class="onlinelist_user">
                <script>sbc_username(<?php echo $trow['id'];?>,'');</script>
            </div>
        </div>
<?php
    }
    mysqli_data_seek($online_data['result'],0);
}
?>
    </div>
</div>
<!-- End Who's Online -->


<style type="text/css">
.top_sp_wrap {
    overflow: hidden;
    margin-left: 15px;
    margin-right: 15px;
}
.top_sp_title {
    font-size: 15px;
}
.top_sp_bottom {
    padding: 5px;
    font-size: 0px;
    overflow: hidden;
}
.top_sp_item {
    margin-left: 3px;
    margin-right: 3px;
    overflow: hidden;
    display: inline-block;

    -webkit-box-shadow: 0px 0px 5px 0px rgba(230,230,230,1);
    -moz-box-shadow: 0px 0px 5px 0px rgba(230,230,230,1);
    box-shadow: 0px 0px 5px 0px rgba(230,230,230,1);
}
.top_sp_avatardiv {
    display: inline-block;
    overflow: hidden;

    vertical-align: middle;

    white-space: nowrap;
    text-align: center;

    height: 138px;
    max-width: 138px;
}
.top_sp_avatardiv img {
    vertical-align: middle;

}
.top_sp_points {
    overflow: hidden;
    font-size: 12px;
    text-align: center;

    color: #585858;
}
.top_sp_points a:link, .top_sp_points a:active, .top_sp_points a:visited {
    color: red;
}
.top_sp_points a:hover {
    text-decoration: underline;
}
</style>
<?php
// Top Sketch Points?
if ($top_data['rownum'] > 0)
{
?>
<!-- Start Top Sketch Points -->
<div class="top_sp_wrap">
    <div class="top_sp_title sbc_font_main">
        Top Members (all time)
    </div>
    <div class="top_sp_bottom">
<?php
    // Users
    while ($trow = mysqli_fetch_assoc($top_data['result']))
    {
?>
        <div class="top_sp_item">
            <div class="top_sp_avatardiv">
                <span class="helper">
                </span>
                <script>sbc_avatar(<?php echo $trow['id'];?>,'');</script> 
            </div>
            <div class="top_sp_points">
                <script>sbc_username(<?php echo $trow['id'];?>,'');</script> 
                <?php echo number_format($trow['sketch_points']);?> points
            </div>
        </div>
<?php
    }
    mysqli_data_seek($top_data['result'],0);
?>
    </div>
</div>
<!-- End Top Sketch Points -->
<?php
}
?>




<!-- End Home -->
</div>