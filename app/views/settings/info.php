<?php
require 'header.php' ;
// Settings
$settings_page = 'info';

// Start Form
echo $data['Form']->start();

require 'settings_top.php';
?>
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