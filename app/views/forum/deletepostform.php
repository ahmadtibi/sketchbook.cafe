<?php
$Form   = &$data['Form'];

// Start Form
echo $Form->start();
echo $Form->field['comment_id'];
?>
<div class="forumadmin_action_wrap">
    <div class="forumadmin_action_top">
        Delete Post
    </div>
    <div class="forumadmin_action_bottom">
        Action:
<?php
echo $Form->field['action'];
echo $Form->field['confirm'];
?>
        Confirm
<?php
echo $Form->field['submit'];
?>
    </div>
</div>
<?php
// End Form
echo $Form->end();
?>