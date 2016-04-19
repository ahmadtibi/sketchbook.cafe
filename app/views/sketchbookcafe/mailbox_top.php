<?php
// Initialize Vars
$current_page   = &$data['current_page'];
$User           = &$data['User'];

// Initialize Vars
$mail_total = $User->mail_total;
?>
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
                Inbox (<?php echo $mail_total;?>)
            </div>
        </a>

    </div>
    <div class="mailboxRight">