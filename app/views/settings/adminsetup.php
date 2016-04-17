<?php
// Initialize Vars
$Form   = &$data['Form'];

// Start Form
echo $Form->start();
?>
<div class="settingsInnerTopWrap">
    <div class="settingsInnerTitle">
        Admin Setup
    </div>
    <div class="settingsInnerDescription">
        Create a new administrator password since you don't have one already.
    </div>
</div>
<div class="settingsInnerBottomWrap">

    <div class="innerWrap">
        <div class="innerLeft">
            Password 1:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['pass1'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Password 2:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['pass2'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Password 3:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['pass3'];
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