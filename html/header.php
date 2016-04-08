<!doctype html>
<html>
<head>
    <title>Sketchbook Cafe</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href='https://fonts.googleapis.com/css?family=Alegreya+SC' rel='stylesheet' type='text/css'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="https://www.sketchbook.cafe/js/sketchbookcafe.js"></script>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <link rel="stylesheet" type="text/css" href="https://www.sketchbook.cafe/css/sketchbookcafe.css">
</head>
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
            <div id="headerUserMenu" class="headerUserMenu">
                User Menu
            </div>
        </div>
<?php
}
else
{
?>
        <div>
            Register
        </div>
<?php
}
?>
    </div>
    <div class="headerLeft">
        <div class="headerTitle">
            <a href="">Sketchbook Cafe</a>
        </div>
    </div>
</div>
<div class="headerMenu">
    <span class="headerMenuItem">
        <a href="">Home</a>
    </span>
    <span class="headerMenuItem">
        <a href="">Forums</a>
    </span>
    <span class="headerMenuItem">
        <a href="">Challenges</a>
    </span>
</div>
