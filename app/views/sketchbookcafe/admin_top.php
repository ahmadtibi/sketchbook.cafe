<?php
// Initialize Objects and Variables
$User           = &$data['User'];
$current_page   = &$data['current_page'];
?>
<style type="text/css">

.adminCategory {
    background-color: #C1C1C1;
    margin-bottom: 6px;
}
.adminItemTitle {
    padding: 6px;
    font-size: 13px;
    line-height: 18px;
    font-family: 'Courier New', Courier, 'Lucida Sans Typewriter', 'Lucida Typewriter', monospace;
    font-weight: bold;

    color: #FFFFFF;
    background-color: #ACACAC;
}
.adminItem {
    padding: 6px;
    font-size: 13px;
    line-height: 18px;
    font-family: 'Courier New', Courier, 'Lucida Sans Typewriter', 'Lucida Typewriter', monospace;
}
.adminItem:hover {
    background-color: #A2A2A2;
}
.adminItemSelected {
    background-color: #A2A2A2;
}
.adminWrap {
    margin: 12px;
    width: 80%;
    margin-left: auto;
    margin-right: auto;
    overflow: hidden;
}
.adminWrap a:link, .adminWrap a:active, .adminWrap a:visited {
    color: #151515;
}
.adminWrap a:hover {
    color: #393939;
    text-decoration: underline;
}
.adminLeft {
    padding-right: 15px;
    width: 20%;
    float: left;
    overflow: hidden;

}
.adminRight {
    padding: 12px;
    overflow: hidden;
    background-color: #C1C1C1;
}
.adminPageTitle {
    padding-bottom: 3px;
    font-weight: bold;
    font-size: 18px;
    font-family: Georgia, serif;
}
</style>

<div class="adminWrap">
    <div class="adminLeft">

        <div class="adminCategory">
            <div class="adminItemTitle">
                Forums
            </div>
<?php
// Forum Categories
if ($User->hasAdminFlag('manage_forum_categories'))
{
?>
            <a href="https://www.sketchbook.cafe/admin/forum_categories/">
                <div class="adminItem <?php if ($current_page == 'forumcategories') { echo ' adminItemSelected ' ;}?>">
                    Categories
                </div>
            </a>
<?php
}

// Forum.. Forums
if ($User->hasAdminFlag('manage_forum_forums'))
{
?>
            <a href="https://www.sketchbook.cafe/admin/forum_forums/">
                <div class="adminItem <?php if ($current_page == 'forumforums') { echo ' adminItemSelected ' ;}?>">
                    Forums
                </div>
            </a>
<?php
}
?>


        </div>


    </div>
    <div class="adminRight">