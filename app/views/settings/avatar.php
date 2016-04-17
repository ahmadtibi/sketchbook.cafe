<?php
// Set Objects
$User   = &$data['User'];
$Form   = &$data['Form'];
?>
<div class="settingsInnerTopWrap">
    <div class="settingsInnerTitle">
        Change Avatar
    </div>
    <div class="settingsInnerDescription">
        Change your post and comment avatars.
    </div>
</div>
<?php
// Start Form
echo $Form->start();
?>
<div class="settingsInnerBottomWrap">
    <div class="settingsAvatarWrap">
        <div class="settingsAvatarLeft">
            <script>sbc_avatar(<?php echo $User->getUserId();?>)</script>
        </div>
        <div class="settingsAvatarRight">
            <div class="allowedFileTypes">
                <b>Types:</b> JPG, PNG, GIF
                <span class="allowedFileTypesSpacer">
                    &#183;
                </span>
                <b>Width:</b> 10-200px
                <span class="allowedFileTypesSpacer">
                    &#183;
                </span>
                <b>Height:</b> 10-200px
            </div>
            File: 
<?php
echo $Form->field['imagefile'];
echo $Form->field['upload'];
?>
        </div>
    </div>
</div>
<?php
// End Form
echo $Form->end();
?>