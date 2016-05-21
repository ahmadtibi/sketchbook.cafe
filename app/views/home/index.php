<?php
// Initialize
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
    margin-top: 15px;
    margin-bottom: 15px;
    margin-left: 15px;
    margin-right: 15px;

    -webkit-box-shadow: 0px 2px 5px 0px rgba(148,148,148,1);
    -moz-box-shadow: 0px 2px 5px 0px rgba(148,148,148,1);
    box-shadow: 0px 2px 5px 0px rgba(148,148,148,1);
}
.streamers_top_title {
    font-weight: bold;
    font-size: 18px;
    padding-left: 15px;
    padding-top: 5px;
    color: #151515;
    background-color: #FFFFFF;
}
.streamers_bottom_wrap {
    padding-top: 10px;
    padding-bottom: 15px;
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

    -webkit-box-shadow: 0px 0px 5px 0px rgba(214,214,214,1);
    -moz-box-shadow: 0px 0px 5px 0px rgba(214,214,214,1);
    box-shadow: 0px 0px 5px 0px rgba(214,214,214,1);
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
</style>
<?php
if ($total_streams > 0)
{
?>
<div class="streamers_wrap">
    <div class="streamers_top_title">
        Streaming Live
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

    -moz-border-radius: 2px 2px 2px 2px;
    border-radius: 2px 2px 2px 2px;
}

.forum_wrap {
    overflow: hidden;
    margin-left: 10px;
    margin-right: 10px;
    padding: 5px;
}
.forum_category_wrap {
    overflow: hidden;
    margin-bottom: 15px;

    background-color: #C1C1C1;

    -webkit-box-shadow: 0px 2px 2px 0px rgba(135,135,135,1);
    -moz-box-shadow: 0px 2px 2px 0px rgba(135,135,135,1);
    box-shadow: 0px 2px 2px 0px rgba(135,135,135,1);
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
    font-size: 16px;
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