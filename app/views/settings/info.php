<?php
require 'header.php' ;
// Settings
$settings_page = 'info';

// Start Form
echo $data['Form']->start();

require 'settings_top.php';
?>
<style type="text/css">
.innerWrap {
    overflow: hidden;
    margin-top: 6px;
    margin-bottom: 6px;
}
.innerLeft {
    width: 20%;
    float: left;
    overflow: hidden;
    padding-right: 12px;

    min-height: 30px;
    line-height: 30px;

    font-family: Georgia, serif;
    font-size: 13px;
    text-align: right;
}
.innerRight {
    overflow: hidden;

    min-height: 30px;

    font-family: Georgia, serif;
    font-size: 13px;

}
.innerWrap a:link, .innerWrap a:visited, .innerWrap a:active {
    color: #151515;
}
.innerWrap a:hover {
    text-decoration: underline;
    color: #151515;
}

.innerRightInfo {
    margin-top: 6px;
    margin-bottom: 6px;
    font-size: 11px;
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;

    color: #646464;
}
</style>
<div class="settingsInnerTopWrap">
    <div class="settingsInnerTitle">
        Profile Information
    </div>
    <div class="settingsInnerDescription">
        Change your profile information.
    </div>
</div>
<div class="settingsInnerBottomWrap">

    <div class="innerWrap">
        <div class="innerLeft">
            Title:
        </div>
        <div class="innerRight">
<?php
echo $data['Form']->field['title'];
?>
            <div class="innerRightInfo">
                User title displayed in forum posts.
            </div>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            &nbsp;
        </div>
        <div class="innerRight">
<?php
echo $data['Form']->field['submit'];
?>
        </div>
    </div>

</div>
<?php
require 'settings_bottom.php';

// End Form
echo $data['Form']->end();

require 'footer.php';
?>