<?php
// Initiailize Objects and Vars
$Form       = &$data['Form'];
$updated    = &$data['updated'];

// Form Start
echo $Form->start();
?>
<div class="adminPageTitle">
    Fix Forum Table
</div>
<?php
// updated?
if ($updated == 1)
{
?>
<div>
    Forum updated
</div>
<?php
}
?>

<div>

    <div class="innerWrap">
        <div class="innerLeft">
            Forum:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['forum_id'];
?>
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
// Form End
echo $Form->end();
?>