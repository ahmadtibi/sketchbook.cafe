<?php
$User           = &$data['User'];
$Form           = &$data['Form'];
$category_row   = &$data['category_row'];
$forum_row      = &$data['forum_row'];
$threads_result = &$data['threads_result'];
$threads_rownum = &$data['threads_rownum'];
$Member         = &$data['Member'];
$Comment        = &$data['Comment'];
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
    width: 100px;
}
.fpTdRight {
    width: 285px;
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
        <a href="https://www.sketchbook.cafe/forums/"><?php echo $category_row['name'];?></a>
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
    </div>

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
            <div class="fpTd fpTdRight fpTdMainTop">
                Freshness
            </div>
        </div>

<style type="text/css">
.fpTdTitle {
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
</style>


<?php
// Threads
if ($threads_rownum > 0)
{
    // Loop
    while ($trow = mysqli_fetch_assoc($threads_result))
    {
?>
        <div class="tr">
            <div class="fpTd fpTdMain">
                <div class="fpTdTitle">
                    <a href="https://www.sketchbook.cafe/forum/thread/<?php echo $trow['id'];?>"><?php echo $trow['title'];?></a>
                </div>
                <div class="fpTdTitleUser">
                    by <script>sbc_username(<?php echo $trow['user_id'];?>,' ');</script> 
                    on <?php echo $User->mytz($trow['date_created'],'F jS, Y - g:iA');?>
                </div>
            </div>
            <div class="fpTd fpTdMain fpPosts">
                <?php echo $trow['total_users'];?>
            </div>
            <div class="fpTd fpTdMain fpPosts">
                <?php echo $trow['total_comments'];?>
            </div>
            <div class="fpTd fpTdMain fpFreshness">
                <script>sbc_dateago(<?php echo time();?>, <?php echo $trow['date_updated'];?>);</script>
                by 
                <script>sbc_username(<?php echo $trow['last_user_id'];?>,' ');</script>

            </div>
        </div>

<?php
    }
    mysqli_data_seek($threads_result,0);
}
?>

    </div>

</div>