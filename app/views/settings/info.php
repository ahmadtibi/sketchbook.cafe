<?php
// Initialize Vars
$Form   = &$data['Form'];

// Start Form
echo $Form->start();
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
echo $Form->field['title'];
?>
            <div class="innerRightInfo">
                User title displayed in forum posts.
            </div>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Forum Signature:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['forumsignature'];
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