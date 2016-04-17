<?php
// Initialize Vars
$Form   = &$data['Form'];

// Start Form
echo $Form->start();
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
echo $Form->field['pass1'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            New Password Again:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['pass2'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            <b>Current Password</b>:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['current_password'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            &nbsp;
        </div>
        <div class="innerRight">
<?php
echo $Form->field['submit'];
?>
        </div>
    </div>

</div>
<?php
// End Form
echo $Form->end();
?>