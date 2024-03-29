<?php
// Set vars
$settings_page = $data['settings_page'];
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

            <a href="https://www.sketchbook.cafe/settings/stream/">
                <div class="settingsInnerItem <?php if ($settings_page == 'stream') { echo 'settingsInnerItemSelected'; } ?>">
                    Stream Settings
                </div>
            </a>

            <a href="https://www.sketchbook.cafe/settings/sitesettings/">
                <div class="settingsInnerItem <?php if ($settings_page == 'sitesettings') { echo 'settingsInnerItemSelected'; } ?>">
                    Site Settings
                </div>
            </a>

            <a href="https://www.sketchbook.cafe/settings/changeemail/">
                <div class="settingsInnerItem <?php if ($settings_page == 'changeemail') { echo 'settingsInnerItemSelected'; } ?>">
                    Change Email
                </div>
            </a>

            <a href="https://www.sketchbook.cafe/settings/changepassword/">
                <div class="settingsInnerItem <?php if ($settings_page == 'changepassword') { echo 'settingsInnerItemSelected'; } ?>">
                    Change Password
                </div>
            </a>

            <a href="https://www.sketchbook.cafe/settings/blockuser/">
                <div class="settingsInnerItem <?php if ($settings_page == 'blockuser') { echo 'settingsInnerItemSelected'; } ?>">
                    Block User
                </div>
            </a>

        </div>
        <div class="settingsInnerRight">