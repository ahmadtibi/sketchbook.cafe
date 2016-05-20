<?php
$comment_id = &$data['comment_id'];
$is_locked  = &$data['is_locked'];
?>
<a href="#" onClick="sbc_thread_lockpost(<?php echo $comment_id;?>); return false;">
<?php
$text = $is_locked ? 'unlock post' : 'lock post';
echo $text;
?>
</a>