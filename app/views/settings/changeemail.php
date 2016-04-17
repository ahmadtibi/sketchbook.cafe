<?php
// Initialize Vars
$Form           = &$data['Form'];
$current_email  = &$data['current_email'];

// Start Form
echo $Form->start();
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
echo $current_email;
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            New E-mail:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['email1'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            New E-mail Again:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['email2'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Confirm Password:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['password'];
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