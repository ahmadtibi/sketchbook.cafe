<?php
$Form   = &$data['Form'];

// Form Start
echo $Form->start();
echo $Form->field['thread_id'];
?>
<div>
    <b>Edit Thread Title</b>
</div>
<div>
<?php
echo $Form->field['title'];
echo $Form->field['submit'];
?>
</div>
<?php
// Form End
echo $Form->end();
?>