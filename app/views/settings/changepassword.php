<?php
require 'header.php' ;
// Settings
$settings_page = 'changepassword';

// Start Form
echo $data['Form']->start();

require 'settings_top.php';
?>
<div class="settingsInnerTopWrap">
    <div class="settingsInnerTitle">
        Change Password
    </div>
    <div class="settingsInnerDescription">
        Change your account's password. Requires password verification.
    </div>
</div>
<div class="settingsInnerBottomWrap">

    <div class="innerWrap">
        <div class="innerLeft">
            New Password:
        </div>
        <div class="innerRight">
<?php
echo $data['Form']->field['pass1'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            New Password Again:
        </div>
        <div class="innerRight">
<?php
echo $data['Form']->field['pass2'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            <b>Current Password</b>:
        </div>
        <div class="innerRight">
<?php
echo $data['Form']->field['current_password'];
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