<?php
// Might have to use some globals here
// Fix this later!
global $User, $Member, $Images;

// Initialize Vars
$mail_total = $User->mail_total;
?>
<!doctype html>
<html>
<head>
    <title>Sketchbook Cafe</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="https://www.sketchbook.cafe/css/sketchbookcafe.css">

<link href='https://fonts.googleapis.com/css?family=Ek+Mukta:400,300' rel='stylesheet' type='text/css'>


    <link href='https://fonts.googleapis.com/css?family=Alegreya+SC' rel='stylesheet' type='text/css'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <script src="https://www.sketchbook.cafe/js/sketchbookcafe.js"></script>
</head>
<script language="javascript" type="text/javascript">
// We'll use simple javascript for now - convert to JSON later
var member_id = new Array();
var member_username = new Array();
var member_avatar_url = new Array();

var image_id = new Array();
var image_url = new Array();
var image_thumb = new Array();
var image_s3 = new Array();

<?php
$image_folder = 'img/';
$thumb_folder = 'img_thumb/';

// Process Images
if ($Images->rownum > 0)
{
    while ($trow = mysqli_fetch_assoc($Images->result))
    {
        if ($trow['isdeleted'] != 1)
        {
            echo 'image_id['.$trow['id'].'] = '.$trow['id'].';';
            echo 'image_s3['.$trow['id'].'] = '.$trow['s3'].';';

            // Thumbnails
            if ($trow['filetype'] != 'gif')
            {
                echo 'image_thumb['.$trow['id'].'] = \''.$thumb_folder.$trow['id'].'-'.$trow['rd_code'].'_325.'.$trow['filetype'].'\';';
            }
            else
            {
                // GIFs have PNG thumbnails
                echo 'image_thumb['.$trow['id'].'] = \''.$thumb_folder.$trow['id'].'-'.$trow['rd_code'].'_325.png\';';
            }

            // S3?
            if ($trow['s3'] == 1)
            {
                echo 'image_url['.$trow['id'].'] = \''.$trow['s3_url'].'\';';
            }
            else
            {
                echo 'image_url['.$trow['id'].'] = \''.$image_folder.$trow['id'].'-'.$trow['rd_code'].'.'.$trow['filetype'].'\';';
            }
        }
    }
    mysqli_data_seek($Images->result,0);
}

// Process Members
if ($Member->rownum > 0)
{
    while ($trow = mysqli_fetch_assoc($Member->result))
    {
        echo '
member_id['.$trow['id'].'] = '.$trow['id'].';
member_username['.$trow['id'].'] = \''.$trow['username'].'\';
member_avatar_url['.$trow['id'].'] = \''.$trow['avatar_url'].'\';
';
    }
    mysqli_data_seek($Member->result,0);
}
?>
</script>

<body>
<!-- Page Wrap -->
<div class="pageWrap">

<style type="text/css">
.header_wrap {
    min-height: 50px;
    overflow: hidden;

    background: rgb(158,158,158); /* Old browsers */
    background: -moz-linear-gradient(top,  rgba(158,158,158,1) 0%, rgba(172,172,172,1) 10%); /* FF3.6-15 */
    background: -webkit-linear-gradient(top,  rgba(158,158,158,1) 0%,rgba(172,172,172,1) 10%); /* Chrome10-25,Safari5.1-6 */
    background: linear-gradient(to bottom,  rgba(158,158,158,1) 0%,rgba(172,172,172,1) 10%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#9e9e9e', endColorstr='#acacac',GradientType=0 ); /* IE6-9 */



}
.header_right {
    font-size: 0px;
    text-align: right;
    overflow: hidden;
    float: right;
    min-width: 300px;
}
.header_left {

    font-size: 0px;
    overflow: hidden;
}
.header_title {
    padding-right: 15px;
    overflow: hidden;
    display: inline-block;
    min-width: 200px;

    text-align: center;
    line-height: 50px;
    font-size: 24px;

}
.header_title:hover {
    background-color: #C3C3C3;
}
.header_left_item {

    overflow: hidden;
    display: inline-block;
    padding-left: 9px;
    padding-right: 9px;

    text-align: center;
    line-height: 50px;
    font-size: 17px;

}
.header_left_item:hover {
    background-color: #C3C3C3;
}

.header_left a:link, .header_left a:visited, .header_left a:active {
    color: #151515;
}
.header_left a:hover {

}
.header_right_logout {
    overflow: hidden;
    display: inline-block;
    line-height: 50px;
    text-align: center;
    padding-left: 1px;
    padding-right: 1px;

    font-size: 14px;

    color: #7E7E7E;
}
.header_right_logout:hover {
    background-color: #C3C3C3;
}
.header_right_item {

    overflow: hidden;
    display: inline-block;
    line-height: 50px;
    text-align: center;
    padding-left: 9px;
    padding-right: 9px;

    font-size: 14px;
    min-width: 50px;
}
.header_right_item:hover {
    background-color: #C3C3C3;
}

.header_right a:link, .header_right a:visited, .header_right a:active {
    color: #444444;
}
.header_right a:hover {

}
.header_right_avatardiv {
    padding-left: 9px;
    padding-right: 9px;
    overflow: hidden;
    display: inline-block;
    height: 50px;
}
.header_right_avatardiv img {
    max-height: 35px;
    max-width: 35px;

    vertical-align: middle;
}
.header_right_avatardiv:hover {
    background-color: #C3C3C3;
}
</style>

<div class="header_wrap">
    <div class="header_right">
<?php
// Users
if ($User->loggedIn())
{
    $userid     = $User->getUserId();
    $username   = $User->getUsername();
?>
        <!-- Start Users -->

        <a href="https://www.sketchbook.cafe/logout/">
            <div class="header_right_logout">
                x
            </div>
        </a>

        <a href="https://www.sketchbook.cafe/u/<?php echo $username;?>/">
            <div class="header_right_item fb">
                <?php echo $username;?>
            </div>
        </a>

        <a href="https://www.sketchbook.cafe/mailbox/">
            <div class="header_right_item">
                <span class="<?php if ($mail_total > 0) { echo 'fb'; } ?>">
                    Inbox (<?php echo $mail_total;?>)
                </span>
            </div>
        </a>

        <a href="https://www.sketchbook.cafe/settings/">
            <div class="header_right_item">
                Settings
            </div>
        </a>

        <a href="https://www.sketchbook.cafe/forum/subscriptions/">
            <div class="header_right_item">
                Subscriptions
            </div>
        </a>

        <div class="header_right_avatardiv">
            <span class="helper">
            </span>
            <script>sbc_avatar(<?php echo $userid;?>,'');</script>
        </div>

        <!-- End Users -->
<?php
} 
else
{
?>
        <!-- Start Guests -->
        <a href="https://www.sketchbook.cafe/register/">
            <div class="header_right_item">
                Register
            </div>
        </a>

        <a href="https://www.sketchbook.cafe/login/">
            <div class="header_right_item">
                Login
            </div>
        </a>
        <!-- End Guests -->
<?php
}
?>
    </div>
    <div class="header_left">
        <a href="https://www.sketchbook.cafe">
            <div class="header_title sbc_font_main">
                Sketchbook Cafe
            </div>
        </a>

        <a href="https://www.sketchbook.cafe/challenges/">
            <div class="header_left_item sbc_font_main">
                Challenges
            </div>
        </a>
<?php
// Admins
if ($User->isAdmin())
{
?>
        <a href="https://www.sketchbook.cafe/admin/">
            <div class="header_left_item sbc_font_main">
                Admin
            </div>
        </a>
<?php
}
?>
    </div>
</div>

<?php
/*

<div class="headerWrap header">
    <div class="headerRight">
<?php
// User Logged In
if ($User->loggedIn())
{
?>
        <div id="headerUserWrap" class="headerUserWrap">
            <div class="headerUserDiv">
                <script>sbc_username(<?php echo $User->getUserId();?>, 'fb');</script>
                <span style="padding-left: 12px;">
                    <a href="https://www.sketchbook.cafe/mailbox/" class="<?php if ($mail_total > 0) { echo 'fb'; } ?>">Inbox (<?php echo $mail_total;?>)</a>
                </span>
                <span style="padding-left: 12px;">
                    <a href="https://www.sketchbook.cafe/forum/subscriptions/">Subscriptions</a>
                </span>
            </div>


            <div id="headerUserMenu" class="headerUserMenu">

                <div class="headerUserItemWrap" style="text-align: left;">

                    <a href="https://www.sketchbook.cafe/settings/">
                        <div class="headerUserWideItem">
                            <img src="https://www.sketchbook.cafe/images/icon_puzzle.png" class="headerUserWideItemIcon">
                            <span style="">
                                Settings
                            </span>
                        </div>
                    </a>

                    <a href="https://www.sketchbook.cafe/logout/">
                        <div class="headerUserWideItem">
                            <img src="https://www.sketchbook.cafe/images/icon_puzzle.png" class="headerUserWideItemIcon">
                            <span style="">
                                Logout
                            </span>
                        </div>
                    </a>

                </div>

            </div>
        </div>
<?php
}
else
{
?>
        <div class="headerGuestDiv">
            <a href="https://www.sketchbook.cafe/register/">Register</a>
            |
            <a href="https://www.sketchbook.cafe/login/">Login</a>
        </div>
<?php
}
?>
    </div>
    <div class="headerLeft">
        <div class="headerTitle">
            <a href="https://www.sketchbook.cafe">Sketchbook Cafe</a>
            <span class="headerMenuItemSpacer">
                &middot;
            </span>
            <span class="headerMenuItem">
                <a href="https://www.sketchbook.cafe/challenges/">Challenges</a>
            </span>

<?php
// Admins only
if ($User->isAdmin())
{
?>
            <span class="headerMenuItemSpacer">
                &middot;
            </span>
            <span class="headerMenuItem">
                <a href="https://www.sketchbook.cafe/admin/">Admin</a>
            </span>
<?php
}
?>
        </div>
    </div>
</div>
<?php
/*
<div class="headerMenu">
    <span class="headerMenuItem">
        <a href="https://www.sketchbook.cafe/">Home</a>
    </span>
    <span class="headerMenuItem">
        <a href="https://www.sketchbook.cafe/challenges/">Challenges</a>
    </span>
<?php
// Admins only
if ($User->isAdmin())
{
?>
    <span class="headerMenuItem">
        <a href="https://www.sketchbook.cafe/admin/">Admin</a>
    </span>
<?php
}
?>
</div>

*/
?>