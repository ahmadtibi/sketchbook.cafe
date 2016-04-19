<?php
$User           = &$data['User'];
$Form           = &$data['Form'];
$category_row   = &$data['category_row'];
$forum_row      = &$data['forum_row'];
?>
<style type="text/css">
.fpWrap {
    margin-left: 15px;
    margin-right: 15px;
    margin-top: 9px;
    margin-bottom: 9px;
    background-color: #FFFFFF;
}
.fpTable {
    width: 100%;
}
.fpTd {
    display: table-cell;
    border: 1px solid #151515;
}
.fpTdLeft {

}
.fpTdMiddle {
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

            <div class="fpTd fpTdLeft">
                Topic
            </div>
            <div class="fpTd fpTdMiddle">
                Posts
            </div>
            <div class="fpTd fpTdRight">
                Freshness
            </div>

        </div>
    </div>

</div>