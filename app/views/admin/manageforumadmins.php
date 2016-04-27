<?php
// Initialize Vars
$forums_result  = &$data['forums_result'];
$forums_rownum  = &$data['forums_rownum'];
$f_admin_result = &$data['f_admin_result'];
$f_admin_rownum = &$data['f_admin_rownum'];
$Form           = &$data['Form'];

// Create Forum Array
$forum_name[0] = '';
if ($forums_rownum > 0)
{
    // Loop
    while ($trow = mysqli_fetch_assoc($forums_result))
    {
        // Set
        $forum_name[$trow['id']] = $trow['name'];
    }
    mysqli_data_seek($forums_result,0);
}
?>
<div class="adminPageTitle">
    Manage Forum Admins
</div>

<style type="text/css">
.adminForumAdminsTable {
    width: 100%;
}
.adminForumAdminsTd {
    padding: 6px;
    font-size: 12px;
    font-family: Georgia, serif;
}
.adminForumAdminsSpacer {
    height: 50px;
}
</style>

<div class="table adminForumAdminsTable">
    <div class="tr">
        <div class="td fb adminForumAdminsTd">
            Username
        </div>
        <div class="td fb adminForumAdminsTd">
            Forum
        </div>
        <div class="td fb adminForumAdminsTd">
            Permissions
        </div>
    </div>

<?php
// List Forum Administrators
if ($f_admin_rownum > 0)
{
    // Loop
    while ($arow = mysqli_fetch_assoc($f_admin_result))
    {
?>
    <div class="tr">
        <div class="td adminForumAdminsTd">
            <a href="https://www.sketchbook.cafe/admin/edit_forum_admin/<?php echo $arow['id'];?>/">[edit]</a>
            <script>sbc_username(<?php echo $arow['user_id'];?>,'');</script>
        </div>
        <div class="td adminForumAdminsTd">
            <a href="https://www.sketchbook.cafe/forum/<?php echo $arow['forum_id'];?>/"><?php echo $forum_name[$arow['forum_id']];?></a>
        </div>
        <div class="td adminForumAdminsTd">
<?php
// Admin Flags
if ($arow['lock_thread'] == 1) { echo '<div>Lock Thread</div>'; }
if ($arow['lock_post'] == 1) { echo '<div>Lock Post</div>'; }
if ($arow['bump_thread'] == 1) { echo '<div>Bump Thread</div>'; }
if ($arow['move_thread'] == 1) { echo '<div>Move Thread</div>'; }
if ($arow['sticky_thread'] == 1) { echo '<div>Sticky Thread</div>'; }
if ($arow['edit_thread'] == 1) { echo '<div>Edit Thread</div>'; }
if ($arow['edit_post'] == 1) { echo '<div>Edit Post</div>'; }
?>
        </div>
    </div>
<?php
    }
    mysqli_data_seek($f_admin_result,0);
}
?>


</div>

<div class="adminForumAdminsSpacer">
</div>

<?php
// Form Start
echo $Form->start();
?>
<div>
    <b>Add Forum Admin</b>
</div>
<div class="settingsInnerBottomWrap">

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
            Username:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['username'];
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