<?php
if (!defined('BOOT'))
{
    exit;
}
?>
<div class="settingsWrap">
    <div class="settingsTitle">
        <a href="https://www.sketchbook.cafe/settings/">Settings</a>
    </div>
    <div class="settingsInnerWrap">
        <div class="settingsInnerLeft">


            <a href="https://www.sketchbook.cafe/settings/avatar/">
                <div class="settingsInnerItem <?php if ($settings_page == 'avatar') { echo 'settingsInnerItemSelected'; } ?>">
                    Avatar
                </div>
            </a>

            <a href="https://www.sketchbook.cafe/settings/info/">
                <div class="settingsInnerItem <?php if ($settings_page == 'info') { echo 'settingsInnerItemSelected'; } ?>">
                    Profile Info
                </div>
            </a>

        </div>
        <div class="settingsInnerRight">