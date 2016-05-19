<?php
$Form   = &$data['Form'];

// Form Start
echo $Form->start();
echo $Form->field['entry_id'];
?>
<div>
    <b>Edit Entry</b>
</div>
<div>
    What should we edit here? 
</div>
<?php
// Form End
echo $Form->end();
?>