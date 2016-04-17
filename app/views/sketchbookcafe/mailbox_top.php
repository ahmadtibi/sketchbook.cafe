<?php
// Initialize Vars
$current_page   = &$data['current_page'];
?>
<style type="text/css">
.mailboxTitle {
    min-height: 50px;
    line-height: 50px;
    font-size: 24px;
    font-family: Georgia, serif;
    padding-left: 15px;

    background-color: #7D7D7D;
    color: #FFFFFF;
}
.mailboxTitle a:link, .mailboxTitle a:visited, .mailboxTitle a:active {
    color: #FFFFFF;
}
.mailboxTitle a:hover {
    color: #FFFFFF;
    text-decoration: underline;
}
.mailboxWrap {
    overflow: hidden;

    background-color: #BCBCBC;
}
.mailboxLeft {
    float: left;
    width: 250px;
    overflow: hidden;

}
.mailboxRight {
    overflow: hidden;
    padding: 15px;
    font-size: 13px;
    font-family: Georgia, serif;
    line-height: 18px;
}
.mailboxItem {
    min-height: 25px;
    padding-left: 15px;
    padding-top: 12px;
    padding-bottom: 12px;
    font-size: 14px;
    font-family: Georgia, serif;
    line-height: 25px;

    color: #505050;
}
.mailboxItem:hover {
    color: #151515;
    background-color: #A5A5A5;
}
.mailboxItemSelected {
    color: #151515;
    background-color: #A5A5A5;
}
.mailboxInnerTitle {
    font-size: 16px;
    font-family: Georgia, serif;
    font-weight: bold;
    margin-bottom: 12px;

    color: #353535;
}
</style>

<div class="mailboxTitle">
    <a href="https://www.sketchbook.cafe/mailbox/">Mailbox</a>
</div>
<div class="mailboxWrap">
    <div class="mailboxLeft">

        <a href="https://www.sketchbook.cafe/mailbox/compose/">
            <div class="mailboxItem<?php if ($current_page == 'compose') { echo ' mailboxItemSelected '; } ?>">
                Compose Note
            </div>
        </a>

        <a href="https://www.sketchbook.cafe/mailbox/">
            <div class="mailboxItem<?php if ($current_page == 'inbox') { echo ' mailboxItemSelected '; } ?>">
                Inbox (0)
            </div>
        </a>

    </div>
    <div class="mailboxRight">