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
/*
            echo '
image_id['.$trow['id'].'] = '.$trow['id'].';
image_url['.$trow['id'].'] = \''.$image_folder.$trow['id'].'-'.$trow['rd_code'].'.'.$trow['filetype'].'\';
image_thumb['.$trow['id'].'] = \''.$thumb_folder.$trow['id'].'-'.$trow['rd_code'].'_325.'.$trow['filetype'].'\';
';
*/
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
        </div>
    </div>
</div>
<div class="headerMenu">
    <span class="headerMenuItem">
        <a href="https://www.sketchbook.cafe/">Home</a>
    </span>
    <span class="headerMenuItem">
        <a href="https://www.sketchbook.cafe/forum/">Forums</a>
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
    <span class="headerMenuItem">
        <a href="https://www.sketchbook.cafe/admin/pending_entries/">0 Pending Entries</a>
    </span>
<?php
}
?>
</div>
