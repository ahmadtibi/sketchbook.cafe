<?php
// Initialize Objects and Variables
$User               = &$data['User'];
$Form               = &$data['Form'];
$categories_result  = &$data['categories_result'];
$categories_rownum  = &$data['categories_rownum'];
$forums_result      = &$data['forums_result'];
$forums_rownum      = &$data['forums_rownum'];

echo 'we found '.$categories_rownum.' categories with '.$forums_rownum.' forums';

// Start Form
echo $Form->start();
?>
<div class="adminPageTitle">
    Forums
</div>
<div>
    <b>Create New Forum</b>
</div>
<div>

    <div class="innerWrap">
        <div class="innerLeft">
            Category:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['categories'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Forum:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['forumname'];
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