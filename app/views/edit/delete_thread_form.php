<?php
$Form   = &$data['Form'];

// Start Form
echo $Form->start();
echo $Form->field['thread_id'];
?>
<div class="fb">
    Delete Thread
</div>
<div>
    Action: 
<?php
echo $Form->field['action'];
echo $Form->field['confirm'];
?>
    Confirm
<?php
echo $Form->field['submit'];

// End Form
echo $Form->end();
?>