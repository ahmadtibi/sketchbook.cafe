<?php
$User       = &$data['User'];
$Form       = &$data['Form'];
$updated    = &$data['updated'];

// Form Start
echo $Form->start();
?>
<div class="adminPageTitle">
    Fix User Table
</div>
<?php
// updated?
if ($updated == 1)
{
?>
<div>
    User updated
</div>
<?php
}
?>
<div>
    <div class="innerWrap">
        <div class="innerLeft">
            Username
        </div>
        <div class="innerRight">
<?php
echo $Form->field['username'];
?>
            <div class="innerRightInfo">
                Fix and update this user's tables.
            </div>
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