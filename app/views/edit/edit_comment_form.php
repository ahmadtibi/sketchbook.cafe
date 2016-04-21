<?php
// Initialize Vars and Objects
$Form   = &$data['Form'];

// Start Form
echo $Form->start();

echo $Form->field['comment_id'];
echo $Form->field['message'];

// End Form
echo $Form->end();