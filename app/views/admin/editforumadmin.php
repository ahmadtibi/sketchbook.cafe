<?php
$Form       = &$data['Form'];
$Member     = &$data['Member'];
$forum_row  = &$data['forum_row'];
$admin_id   = &$data['id'];

// Form Start
echo $Form->start();
echo $Form->field['admin_id'];
?>
<div class="adminPageTitle">
    Edit Forum Admin
</div>
<div class="innerWrap">

    <div class="innerWrap">
        <div class="innerLeft">
            Admin:
        </div>
        <div class="innerRight innerRightLineHeight">
            <script>sbc_username(<?php echo $admin_id;?>,'');</script>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Forum:
        </div>
        <div class="innerRight innerRightLineHeight">
            <a href="https://www.sketchbook.cafe/forum/<?php echo $forum_row['id'];?>/"><?php echo $forum_row['name'];?></a>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            &nbsp;
        </div>
        <div class="innerRight">
            <?php echo $Form->field['lock_thread']; ?>
            Lock Thread
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            &nbsp;
        </div>
        <div class="innerRight">
            <?php echo $Form->field['lock_post']; ?>
            Lock Post
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            &nbsp;
        </div>
        <div class="innerRight">
            <?php echo $Form->field['bump_thread']; ?>
            Bump Thread
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            &nbsp;
        </div>
        <div class="innerRight">
            <?php echo $Form->field['move_thread']; ?>
            Move Thread
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            &nbsp;
        </div>
        <div class="innerRight">
            <?php echo $Form->field['sticky_thread']; ?>
            Sticky Thread
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            &nbsp;
        </div>
        <div class="innerRight">
            <?php echo $Form->field['lock_thread']; ?>
            Lock Thread
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            &nbsp;
        </div>
        <div class="innerRight">
            <?php echo $Form->field['edit_thread']; ?>
            Edit Thread
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            &nbsp;
        </div>
        <div class="innerRight">
            <?php echo $Form->field['edit_post']; ?>
            Edit Post
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
// End Form
echo $Form->end();
?>