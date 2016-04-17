<?php
// Initialize Vars
$Form   = &$data['Form'];

// Start Form
echo $Form->start();
?>
<div class="settingsInnerTopWrap">
    <div class="settingsInnerTitle">
        Site Settings
    </div>
    <div class="settingsInnerDescription">
        Change various site settings such as timezone
    </div>
</div>
<div class="settingsInnerBottomWrap">

    <div class="innerWrap">
        <div class="innerLeft">
            Timezone:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['timezone'];
?>
            <div class="innerRightInfo">
                Your current timezone.
            </div>
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