<?php
$User               = &$data['User'];
$categories_result  = &$data['categories_result'];
$categories_rownum  = &$data['categories_rownum'];
$forums_result      = &$data['forums_result'];
$forums_rownum      = &$data['forums_rownum'];
$thread             = &$data['thread'];
$online_result      = &$data['online_result'];
$online_rownum      = &$data['online_rownum'];
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
    margin-top: 15px;
    margin-bottom: 15px;

    background-color: #C1C1C1;

    -webkit-box-shadow: 0px 2px 2px 0px rgba(135,135,135,1);
    -moz-box-shadow: 0px 2px 2px 0px rgba(135,135,135,1);
    box-shadow: 0px 2px 2px 0px rgba(135,135,135,1);
}
.forum_category_name {
    margin-top: 15px;
    margin-left: 20px;

    font-size: 24px;
    font-family: Georgia, serif;

    color: #151515;
}

.forum_category_description {
    margin-left: 20px;
    margin-bottom: 15px;

    font-size: 13px;
    line-height: 21px;
    font-family: Georgia, serif;

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
    font-size: 13px;
    font-family: Georgia, serif;
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
    font-size: 12px;
    font-family: Georgia, serif;
}


.forum_table {
    width: 100%;
}
.forum_table_forum_top {
    padding-left: 20px;
    padding-top: 15px;
    padding-bottom: 5px;

    font-weight: bold;
    font-size: 11px;
    font-family: Georgia, serif;

    color: #151515;
}
.forum_table_threads_top {
    width: 100px;
    text-align: center;
    padding-top: 15px;
    padding-bottom: 5px;

    font-weight: bold;
    font-size: 11px;
    font-family: Georgia, serif;
}
.forum_table_posts_top {
    width: 100px;
    text-align: center;
    padding-top: 15px;
    padding-bottom: 5px;

    font-weight: bold;
    font-size: 11px;
    font-family: Georgia, serif;
}
.forum_table_freshness_top {
    width: 325px;
    text-align: right;
    padding-right: 20px;
    padding-top: 15px;
    padding-bottom: 5px;

    font-weight: bold;
    font-size: 11px;
    font-family: Georgia, serif;
}

.forum_table_forum_bottom {
    padding-top: 15px;
    padding-bottom: 15px;
}
.forum_table_threads_bottom {
    width: 100px;
    text-align: center;
    font-size: 13px;
    font-family: Georgia, serif;
}
.forum_table_posts_bottom {
    width: 100px;
    text-align: center;
    font-size: 13px;
    font-family: Georgia, serif;
}
.forum_table_freshness_bottom {
    width: 325px;
    padding-right: 20px;
    text-align: right;
}


.forum_freshness_thread {
    max-width: 325px;
    overflow: hidden;
    font-weight: bold;
    font-size: 13px;
    font-family: Georgia, serif;
}
.forum_freshness_userdiv {
    padding-top: 3px;
    font-size: 13px;
    font-family: Georgia, serif;
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
while ($crow = mysqli_fetch_assoc($categories_result))
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
                    Forum
                </div>
                <div class="td forum_table_threads_top">
                    Threads
                </div>
                <div class="td forum_table_posts_top">
                    Posts
                </div>
                <div class="td forum_table_freshness_top">
                    Freshness
                </div>
            </div>
<?php
    // Forums
    while ($frow = mysqli_fetch_assoc($forums_result))
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
                <div class="td forum_table_threads_bottom">
                    <?php echo number_format($frow['total_threads']);?>
                </div>
                <div class="td forum_table_posts_bottom">
                    <?php echo number_format($frow['total_posts']);?>
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
    mysqli_data_seek($forums_result,0);
?>
    </div>
<?php
}
mysqli_data_seek($categories_result,0);
?>
<!-- End Forum -->
</div>


<style type="text/css">
.onlinelist_wrap {
    overflow: hidden;
    margin-left: 15px;
    margin-right: 15px;
    margin-bottom: 15px;

    -webkit-box-shadow: 0px 0px 4px 0px rgba(125,125,125,1);
    -moz-box-shadow: 0px 0px 4px 0px rgba(125,125,125,1);
    box-shadow: 0px 0px 4px 0px rgba(125,125,125,1);
}

.onlinelist_title {
    margin-top: 15px;
    margin-left: 20px;

    font-size: 17px;
    font-family: Georgia, serif;

    color: #151515;
}
.onlinelist_inner_wrap {
    overflow: hidden;
    padding: 5px;
}
.onlinelist_userdiv {
    float: left;
    display: inline-block;
    overflow: hidden;
    min-width: 50px;
    height: 30px;
    margin: 3px;

    border: 1px solid #FFFFFF;

}
.onlinelist_avatardiv {
    float: left;
    display: inline-block;
    overflow: hidden;
    width: 30px;
    text-align: center;
    background-color: #FFFFFF;
}
.onlinelist_user {
    font-size: 12px;
    font-family: Georgia, serif;
    line-height: 30px;
    padding-left: 40px;
    padding-right: 10px;

    background-color: #60E0DA;
}
.onlinelist_user a:link, .onlinelist_user a:visited, .onlinelist_user a:active {
    color: #FFFFFF;
}
.onlinelist_user a:hover {
    text-decoration: underline;
}
.onlinelist_avatar {
    max-width: 30px;
    max-height: 30px;
}
</style>

<!-- Who's Online -->
<div class="onlinelist_wrap">
    <div class="onlinelist_title">
        Who's Online (24 hours)
    </div>
    <div class="onlinelist_inner_wrap">
<?php
// Online?
if ($online_rownum > 0)
{
    // Loop Users
    while ($trow = mysqli_fetch_assoc($online_result))
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
    mysqli_data_seek($online_result,0);
}
?>
    </div>
</div>
<!-- End Who's Online -->