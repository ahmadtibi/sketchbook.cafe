<?php
// Initialize
$Form   = &$data['Form'];


// Start Form
echo $Form->start();
?>
<div>
    Add Stream page
</div>
<div>
<?php
echo $Form->field['username'];
echo $Form->field['submit'];
?>
</div>
<?php
// End Form
echo $Form->end();
?>