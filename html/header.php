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
                <a href=""><b>Kameloh</b></a>
                <span style="padding-left: 12px;">
                    <a href="">Inbox (0)</a>
                </span>
                <span style="padding-left: 12px;">
                    <a href="">Notifications (0)</a>
                </span>
            </div>


<?php
// THIS IS TEMPORARY I SWEAR
?>
<style type="text/css">
.headerUserItemWrap {
    margin-top: 10px;
    margin-bottom: 6px;

    overflow: hidden;
    text-align: center;
}
.headerUserItem:hover {
    background-color: #ACACAC;
}

.headerUserItemWrap a:active, .headerUserItemWrap a:link, .headerUserItemWrap a:visited {
    color: #575757;
}
.headerUserItemWrap a:hover {
    color: #151515;
}
.headerUserWideItem {
    margin-top: 4px;
    margin-bottom: 4px;
    min-height: 50px;
    line-height: 50px;
    font-size: 13px;
    font-family: Georgia, serif;
    font-weight: bold;

    background-color: #B2B2B2;
}
.headerUserWideItem:hover {
    background-color: #ACACAC;
}
.headerUserWideItemIcon {
    margin-left: 9px;
    margin-right: 9px;
    vertical-align: middle;
}

<?php
/*
.headerUserItem {
    overflow: hidden;
    display: inline-block;
    width: 50px;
    height: 50px;
    margin-right: 1px;
    margin-left: 1px;

    font-size: 14px;
    text-align: center;

    -moz-border-radius: 3px 3px 3px 3px;
    border-radius: 3px 3px 3px 3px;

    background-color: #B2B2B2;
}

.headerUserItemIcon {
    margin-top: 12px;
    text-align: center;
}
*/
?>
</style>


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

<?php
/*
                <div class="headerUserItemWrap">
                    <a href="#">
                        <div class="headerUserItem">
                            <img src="https://www.sketchbook.cafe/images/icon_puzzle.png" class="headerUserItemIcon">
                        </div>
                    </a>

                    <a href="#">
                        <div class="headerUserItem">
                            <img src="https://www.sketchbook.cafe/images/icon_puzzle.png" class="headerUserItemIcon">
                        </div>
                    </a>

                    <a href="#">
                        <div class="headerUserItem">
                            <img src="https://www.sketchbook.cafe/images/icon_puzzle.png" class="headerUserItemIcon">
                        </div>
                    </a>

                    <a href="#">
                        <div class="headerUserItem">
                            <img src="https://www.sketchbook.cafe/images/icon_puzzle.png" class="headerUserItemIcon">
                        </div>
                    </a>

                    <a href="#">
                        <div class="headerUserItem">
                            <img src="https://www.sketchbook.cafe/images/icon_puzzle.png" class="headerUserItemIcon">
                        </div>
                    </a>

                </div>
*/
?>




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
</div>
