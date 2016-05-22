<?php
// Initialize
$stream_data    = &$data['stream_data'];
?>
<div class="settingsInnerTopWrap">
    <div class="settingsInnerTitle">
        Stream Settings
    </div>
    <div class="settingsInnerDescription">
        Change your twitch stream settings
    </div>
</div>
<div class="settingsInnerBottomWrap">
<?php
// Do we have an ID?
if ($stream_data['stream_id'] > 0)
{
    // Start Form
    $Form = &$stream_data['StreamForm'];
    echo $Form->start();
    echo $Form->field['stream_id'];
?>
    <div class="innerWrap">
        <div class="innerLeft">
            Twitch Username:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['username'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            &nbsp;
        </div>
        <div class="innerRight">
<?php
echo $Form->field['deletethis'];
?>
            delete stream
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


<?php
    // End Form
    echo $Form->end();
}
else
{
?>
    You do not have an active stream.
<?php
}
?>
</div>