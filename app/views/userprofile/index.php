<?php
$User           = &$data['User'];
$user_row       = &$data['user_row'];
$entries_result = &$data['entries_result'];
$entries_rownum = &$data['entries_rownum'];
?>
<style type="text/css">
.user_page_wrap {
    overflow: hidden;

    //margin-left: 20px;
    //margin-right: 20px;
    //margin-top: 15px;
    //margin-bottom: 15px;

    // border: 1px solid #FFFFFF;

    // -webkit-box-shadow: 0px 0px 5px 0px rgba(69,69,69,1);
    //-moz-box-shadow: 0px 0px 5px 0px rgba(69,69,69,1);
     //box-shadow: 0px 0px 5px 0px rgba(69,69,69,1);
}
.user_page_top_wrap {
 
    min-height: 255px;

    text-align: center;

    color: #FFFFFF;
    background-image: url(https://www.sketchbook.cafe/img/77-hSLK6.jpg);
}
.user_page_top_avatar_div {
    font-size: 0px;
    margin-top: 50px;

    display: inline-block;
    overflow: hidden;

    vertical-align: middle;

    white-space: nowrap;
    text-align: center;


    height: 138px;
    max-width: 138px;
    border: 0px solid #151515;

    //-moz-border-radius: 138px 138px 138px 138px;
    //border-radius: 138px 138px 138px 138px;
}
.user_page_inner_wrap {
    overflow: hidden;
}

.user_page_right_wrap {
    width: 50%;
    float: right;
    overflow: hidden;

    border: 1px solid #151515;
}
.user_page_left_wrap {
    overflow: hidden;

    border: 1px solid #151515;
}
.user_page_middle_wrap {
    margin-top: 6px;
    margin-bottom: 6px;
    overflow: hidden;

    -webkit-box-shadow: 0px 0px 5px 0px rgba(133,133,133,1);
    -moz-box-shadow: 0px 0px 5px 0px rgba(133,133,133,1);
    box-shadow: 0px 0px 5px 0px rgba(133,133,133,1);
}
.user_page_inner_top {
    padding: 15px;
    color: #FFFFFF;
    background-color: #616161;
}
.user_page_inner_bottom {
    padding: 15px;
    color: #151515;
    background-color: #FFFFFF;
}
.user_page_gallery_wrap {
    text-align: center;
    max-height: 355px;
    font-size: 0px;
    overflow: hidden;
}
</style>

<!-- Start Page Wrap -->
<div class="user_page_wrap">
    <div class="user_page_top_wrap">
        <div>
            top page - maybe a banner?
        </div>

        <div class="user_page_top_avatar_div">
            <span class="helper"></span>
            <img src="https://www.sketchbook.cafe/<?php echo $user_row['avatar_url'];?>">
        </div>
    </div>

<?php
// About Me
if (!empty($user_row['aboutme']))
{
?>
    <div class="user_page_middle_wrap">
        <div class="user_page_inner_top">
            <b>About</b>
            (<?php echo $user_row['sketch_points'];?> sketch points thingy)
        </div>
        <div class="user_page_inner_bottom">
            <?php echo $user_row['aboutme'];?>
        </div>
    </div>
<?php
}

// Entries Gallery
if ($entries_rownum > 0)
{
?>

    <div class="user_page_middle_wrap">
<?php
/*
        <div class="user_page_inner_top">
            <b>Gallery</b>
        </div>
*/
?>
        <div class="user_page_inner_bottom">
            <div class="user_page_gallery_wrap">
<?php
    // Loop
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
    </div>
<?php
}
?>

    <div class="user_page_middle_wrap">
        <div class="user_page_inner_top">
            <b>Comments</b>
        </div>
        <div class="user_page_inner_bottom">
            Put user profile comments here
        </div>
    </div>




    <div class="user_page_inner_wrap">

        <div class="user_page_right_wrap">
            <div>
                Top Avatar Right?
            </div>
            <div>
                User Info Here
            </div>

            <div>
                Profile Comments?
            </div>
        </div>

        <div class="user_page_left_wrap">
            <div>
                Gallery Here?
            </div>

            <div>
                what else?
            </div>
        </div>
    </div>


    <div>
        Registered: <?php echo $User->mytz($user_row['date_registered'],'F jS, Y - g:iA');?>
    </div>
    <div>
        Last Login: <?php echo $User->mytz($user_row['date_lastlogin'],'F jS, Y - g:iA');?>
    </div>
    <div>
        User Title: <?php echo $user_row['title'];?>
    </div>
    <div>
        Total Posts: <?php echo $user_row['total_posts'];?>
    </div>
    <div>
        Total Entries: <?php echo $user_row['total_entries'];?>
    </div>

    <div>
        <script>sbc_username(<?php echo $user_row['id'];?>,'');</script>
    </div>
    <div>
        <script>sbc_avatar(<?php echo $user_row['id'];?>,'');</script>
    </div>


<!-- End Page Wrap -->
</div>