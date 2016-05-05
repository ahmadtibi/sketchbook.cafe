<?php
// Initialize
$Form       = &$data['Form'];

// Start Form
echo $Form->start();
echo $Form->field['thread_id'];
?>
<div>
    <b>Action:</b>
</div>
<?php
echo $Form->field['action'];
echo $Form->field['submit'];

// End Form
echo $Form->end();