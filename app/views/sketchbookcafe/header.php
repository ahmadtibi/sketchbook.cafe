<?php
// Might have to use some globals here
global $User, $Member;
?>
<!doctype html>
<html>
<head>
    <title>Sketchbook Cafe</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="https://www.sketchbook.cafe/css/sketchbookcafe.css">
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

<?php
// Process Members
if ($Member->rownum > 0){
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
                    <a href="https://www.sketchbook.cafe/mailbox/">Inbox (0)</a>
                </span>
                <span style="padding-left: 12px;">
                    <a href="">Notifications (0)</a>
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
        <a href="">Forums</a>
    </span>
    <span class="headerMenuItem">
        <a href="">Link 2</a>
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
