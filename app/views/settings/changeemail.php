<?php
require 'header.php' ;
// Settings
$settings_page = 'changeemail';

// Start Form
echo $data['Form']->start();

require 'settings_top.php';
?>
<div class="settingsInnerTopWrap">
    <div class="settingsInnerTitle">
        Change E-mail
    </div>
    <div class="settingsInnerDescription">
        Change your account's e-mail. Requires password verification.
    </div>
</div>
<div class="settingsInnerBottomWrap">

    <div class="innerWrap">
        <div class="innerLeft">
            Current E-mail:
        </div>
        <div class="innerRight innerRightLineHeight">
<?php
echo $data['current_email'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            New E-mail:
        </div>
        <div class="innerRight">
<?php
echo $data['Form']->field['email1'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            New E-mail Again:
        </div>
        <div class="innerRight">
<?php
echo $data['Form']->field['email2'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Confirm Password:
        </div>
        <div class="innerRight">
<?php
echo $data['Form']->field['password'];
?>
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