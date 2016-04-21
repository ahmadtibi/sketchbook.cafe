<?php
// Initialize Vars
$Form   = &$data['Form'];

// Start Form
echo $Form->start();
echo $Form->field['thread_id'];

// Message
echo $Form->field['message'];

// End Form
echo $Form->end();
?>