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
    File: 
<?php
echo $data['Form']->field['imagefile'];
echo $data['Form']->field['upload'];
?>
</div>
<?php
require 'settings_bottom.php';

// End Form
echo $data['Form']->end();

require 'footer.php';
?>