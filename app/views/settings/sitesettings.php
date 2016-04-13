<?php
require 'header.php' ;
// Settings
$settings_page = 'sitesettings';

// Start Form
echo $data['Form']->start();

require 'settings_top.php';
?>
<div class="settingsInnerTopWrap">
    <div class="settingsInnerTitle">
        Site Settings
    </div>
    <div class="settingsInnerDescription">
        Change various site settings such as timezone
    </div>
</div>
<div class="settingsInnerBottomWrap">

    <div class="innerWrap">
        <div class="innerLeft">
            Timezone:
        </div>
        <div class="innerRight">
<?php
echo $data['Form']->field['timezone'];
?>
            <div class="innerRightInfo">
                Your current timezone.
            </div>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            &nbsp;
        </div>
        <div class="innerRight">
<?php
echo $data['Form']->field['submit'];
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