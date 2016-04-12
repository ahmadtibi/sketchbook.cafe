<?php
require 'header.php' ;
// Settings
$settings_page = 'avatar';

// Start Form
echo $data['Form']->start();

require 'settings_top.php';
?>
<div class="settingsInnerTopWrap">
    <div class="settingsInnerTitle">
        Change Avatar
    </div>
    <div class="settingsInnerDescription">
        Change your post and comment avatars.
    </div>
</div>
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
echo $data['Form']->field['imagefile'];
echo $data['Form']->field['upload'];
?>
        </div>
    </div>
</div>
<?php
require 'settings_bottom.php';

// End Form
echo $data['Form']->end();

require 'footer.php';
?>