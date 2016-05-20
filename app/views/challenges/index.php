<?php
// Initialize
$result         = &$data['result'];
$rownum         = &$data['rownum'];
$app_result     = &$data['app_result'];
$app_rownum     = &$data['app_rownum'];
$Member         = &$data['Member'];
$User           = &$data['User'];
$ChallengeForm  = &$data['ChallengeForm'];
$app_id         = &$data['app_id'];
?>
<style type="text/css">
.challenges_page_wrap {
    overflow: hidden;

}
.challenges_page_title {
    font-size: 24px;
    margin-top: 9px;
    margin-bottom: 9px;
    margin-left: 15px;
}
.challengepage_wrap {
    overflow: hidden;

    margin-top: 6px;
    margin-bottom: 6px;

    padding: 15px;
    background-color: #FFFFFF;

    border-top: 1px solid #A9A9A9;

    -webkit-box-shadow: 0px 4px 5px 0px rgba(163,163,163,1);
    -moz-box-shadow: 0px 4px 5px 0px rgba(163,163,163,1);
    box-shadow: 0px 4px 5px 0px rgba(163,163,163,1);
}
.challengepage_title {
    font-size: 20px;
}
.challengepage_user {
    font-size: 16px;
}
.challengepage_points {
    font-size: 13px;
    margin-left: 9px;
    color: #ADADAD;
}

.challengepage_wrap a:link, .challengepage_wrap a:visited, .challengepage_wrap a:active {
    color: red;
}
.challengepage_wrap a:hover {
    text-decoration: underline;
}


.challengepage_left {
    float: left;
    overflow: hidden;
    width: 325px;
}
.challengepage_right {
    overflow: hidden;
    text-align: right;

}
.challengepage_info {
    font-size: 14px;
    display: inline-block;
    overflow: hidden;

    margin-top: 6px;
    margin-right: 15px;

    color: #797979;
}
.challengepage_gallery_wrap {
    overflow: hidden;
}
.challenges_page_top_wrap {
    overflow: hidden;
}
.challenges_page_top_right {
    text-align: right;
    float: right;
    width: 200px;
    overflow: hidden;
}
.challenges_page_top_left {
    overflow: hidden;
}
.challenges_page_create_wrap {
    padding: 30px;

    overflow: hidden;
    display: none;

    color: #151515;
    background-color: #FFFFFF;
}
.challenges_page_create_button {
    margin-right: 10px;
    margin-top: 10px;

    font-size: 13px;
    padding: 6px;
    text-align: center;

    cursor: pointer;

    -moz-border-radius: 2px 2px 2px 2px;
    border-radius: 2px 2px 2px 2px;

    color: #151515;
    background-color: #C1C1C1;
}
.challenges_page_create_button:hover {
    background-color: #ABABAB;
}
.challenges_page_pending_wrap {
    text-align: center;
}
.challenges_page_pending_wrap a:link, .challenges_page_pending_wrap a:active, .challenges_page_pending_wrap a:visited {
    color: red;
}
.challenges_page_pending_wrap a:hover {
    text-decoration: underline;
}
.challenges_page_admin_wrap {
    margin: 20px;
    background-color: #FFFFFF;
}
.challenges_page_admin_title {
    padding-left: 15px;
    padding-right: 15px;
    padding-top: 10px;
    padding-bottom: 10px;
}
.challenges_page_admin_item {
    padding-left: 15px;
    padding-right: 15px;
    padding-top: 10px;
    padding-bottom: 10px;
}
.challenges_page_admin_item a:link, .challenges_page_admin_item a:visited, .challenges_page_admin_item a:active {
    color: red;
}
.challenges_page_admin_item a:hover {
    text-decoration: underline;
}
</style>

<?php
// Pending Applications
if ($app_rownum > 0)
{
?>
<div class="challenges_page_admin_wrap">
    <div class="challenges_page_admin_title">
        <b>Pending Applications</b>
    </div>
<?php
    // Loop
    while ($trow = mysqli_fetch_assoc($app_result))
    {
?>
    <div class="challenges_page_admin_item">
        <a href="https://www.sketchbook.cafe/challenges/pending/<?php echo $trow['id'];?>/"><?php echo $trow['name'];?></a>
        by
        <script>sbc_username(<?php echo $trow['user_id'];?>,'');</script>
    </div>
<?php
    }
    mysqli_data_seek($app_result,0);
?>
</div>
<?php
}
?>

<div class="challenges_page_wrap">
    <div class="challenges_page_top_wrap">
<?php
// Users Only
if ($User->loggedIn())
{
?>
        <div class="challenges_page_top_right">
            <div id="challenges_page_create_button" class="challenges_page_create_button">
                Create Challenge
            </div>
        </div>
<?php
}
?>
        <div class="challenges_page_top_left">

            <div class="challenges_page_title sbc_font_main">
                Challenges
            </div>

        </div>
    </div>

<?php
// Users Only
if ($User->loggedIn())
{
    // Pending App?
    if ($app_id > 0)
    {
?>
    <div id="challenges_page_create_wrap" class="challenges_page_create_wrap">
        <div class="challenges_page_pending_wrap">
            You have a pending application:
            <br/><a href="https://www.sketchbook.cafe/challenges/pending/<?php echo $app_id;?>/">https://www.sketchbook.cafe/challenges/pending/<?php echo $app_id;?>/</a>
        </div>
    </div>
<?php
    }
    else
    {
        // Start Form
        echo $ChallengeForm->start();
?>
    <div id="challenges_page_create_wrap" class="challenges_page_create_wrap">

        <div class="innerWrap">
            <div class="innerLeft">
                Title:
            </div>
            <div class="innerRight">
<?php
        echo $ChallengeForm->field['title'];
?>
            </div>
        </div>

        <div class="innerWrap">
            <div class="innerLeft">
                Points:
            </div>
            <div class="innerRight">
<?php
        echo $ChallengeForm->field['points'];
?>
            </div>
        </div>

        <div class="innerWrap">
            <div class="innerLeft">
                Description:
            </div>
            <div class="innerRight">
<?php
        echo $ChallengeForm->field['description'];
?>
            </div>
        </div>

        <div class="innerWrap">
            <div class="innerLeft">
                Requirements:
            </div>
            <div class="innerRight">
<?php
        echo $ChallengeForm->field['requirements'];
?>
            </div>
        </div>

        <div class="innerWrap">
            <div class="innerLeft">
                &nbsp;
            </div>
            <div class="innerRight">
<?php
        echo $ChallengeForm->field['submit'];
?>
            </div>
        </div>


    </div>
<?php
        // End Form
        echo $ChallengeForm->end();
    }
}
?>


<?php
// Loop
while ($trow = mysqli_fetch_assoc($result))
{
?>
    <div class="challengepage_wrap sbc_font">
        <div class="challengepage_left">

            <div class="challengepage_title">
                <a href="https://www.sketchbook.cafe/forum/thread/<?php echo $trow['thread_id'];?>/"><?php echo $trow['name'];?></a>
                <span class="challengepage_user">
                    by 
                    <script>sbc_username(<?php echo $trow['owner_user_id'];?>,'');</script>
                </span>
                <span class="challengepage_points">
                    <?php echo $trow['points'];?> points
                </span>
            </div>
            <div class="challengepage_description">
                <?php echo $trow['description'];?>
            </div>

            <div class="challengepage_info">
                6.4 difficulty
            </div>
            <div class="challengepage_info">
                <?php echo number_format(rand(19,2428924));?> views
            </div>
            <div class="challengepage_info">
                <?php echo rand(2,282);?> entries
            </div>


        </div>

        <div class="challengepage_right">


    <!-- Challenge Gallery -->
    <div class="challengepage_gallery_wrap">
<?php
    // Images Array
    $images_array   = explode(',',$trow['images_array']);

    foreach ($images_array as $id)
    {
?>
        <div class="challenge_thumbnail_div">
            <span class="helper"></span>
            <script>sbc_challenge_thumbnail(<?php echo $id;?>);</script>
        </div>
<?php
    }
?>
    <!-- End Challenge Gallery -->
    </div>




        </div>

    </div>

<?php
}
mysqli_data_seek($result,0);
?>
</div>