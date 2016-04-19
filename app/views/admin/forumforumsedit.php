<?php
// Initialize Objects and Variables
$User   = &$data['User'];
$Form   = &$data['Form'];

// Start Form
echo $Form->start();
echo $Form->field['id'];
?>
<div>
    <b>Edit Forum</div>
</div>

<div class="adminRightWrap">
    <div class="innerWrap">
        <div class="innerLeft">
            Forum:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['name'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Description:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['description'];
?>
        </div>
    </div>
</div>


<?php
// End Form
echo $Form->end();
?>