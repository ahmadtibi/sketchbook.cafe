<?php
// Initialize Vars
$current_page   = &$data['current_page'];
$User           = &$data['User'];
$ComposeForm    = &$data['ComposeForm'];

// Initialize Vars
$mail_total = $User->mail_total;
?>

<style type="text/css">
.mailbox_index_wrap {
    margin-left: 3px;
    margin-right: 3px;
}

.mailbox_wrap {
    overflow: hidden;
}
.mailbox_top_wrap {
    overflow: hidden;
    height: 40px;
    padding-left: 20px;
    padding-right: 20px;
    padding-top: 6px;
    padding-bottom: 6px;

    background-color: #7D7D7D;
}
.mailbox_title {
    overflow: hidden;
    font-size: 24px;
    line-height: 40px;

    color: #FFFFFF;
}
.mailbox_title a:link, .mailbox_title a:visited, .mailbox_title a:active {
    color: #FFFFFF;
}
.mailbox_title a:hover {
    text-decoration: underline;
}
.mailbox_top_right {
    float: right;
    overflow: hidden;


    text-align: right;


}
.mailbox_compose_button {
    width: 150px;

    line-height: 40px;
    text-align: center;
    font-size: 14px;

    cursor: pointer;

    -moz-border-radius: 2px 2px 2px 2px;
    border-radius: 2px 2px 2px 2px;

    color: #151515;
    background-color: #BBBBBB;
}
.mailbox_compose_button:hover {
    background-color: #CECECE;
}
.mailbox_compose_div {
    display: none;

    padding-top: 20px;
    padding-bottom: 20px;
    padding-left: 35px;
    padding-right: 35px;

    background-color: #FFFFFF;
}
.mailbox_compose_title {
    font-weight: bold;
    font-size: 20px;
}
.mailbox_compose_inner_wrap {
    overflow: hidden;
    margin-bottom: 3px;
}
.mailbox_compose_left {
    float: left;
    font-size: 14px;
    text-align: right;
    padding-right: 18px;
    min-width: 155px;
    overflow: hidden;
}
.mailbox_compose_right {
    overflow: hidden;
}
.mailbox_note_wrap {
    margin-left: 3px;
    margin-right: 3px;
}
</style>

<div class="mailbox_wrap">


    <div class="mailbox_top_wrap">
        <div class="mailbox_top_right">

            <div id="mailbox_compose_button" class="mailbox_compose_button sbc_font">
                Compose Note
            </div>
        </div>
        <div class="mailbox_title sbc_font">
            <a href="https://www.sketchbook.cafe/mailbox/">Mailbox</a>
        </div>
    </div>

    <!-- Compose Note -->
<?php
// Start Form
echo $ComposeForm->start();
?>
    <div id="mailbox_compose_div" class="mailbox_compose_div">

        <div class="mailbox_compose_inner_wrap">
            <div class="mailbox_compose_left sbc_font">
                &nbsp;
            </div>
            <div class="mailbox_compose_right sbc_font">
                <div class="mailbox_compose_title sbc_font">
                    Compose Note
                </div>
            </div>
        </div>


        <div class="mailbox_compose_inner_wrap">
            <div class="mailbox_compose_left sbc_font">
                Username:
            </div>
            <div class="mailbox_compose_right sbc_font">
<?php
echo $ComposeForm->field['username'];
?>
            </div>
        </div>

        <div class="mailbox_compose_inner_wrap">
            <div class="mailbox_compose_left sbc_font">
                Title:
            </div>
            <div class="mailbox_compose_right sbc_font">
<?php
echo $ComposeForm->field['title'];
?>
            </div>
        </div>

        <div class="mailbox_compose_inner_wrap">
            <div class="mailbox_compose_left sbc_font">
                Message:
            </div>
            <div class="mailbox_compose_right sbc_font">
<?php
echo $ComposeForm->field['message'];
?>
            </div>
        </div>
    </div>
<?php
// End Form
echo $ComposeForm->end();
?>
    <!-- End Compose Note -->


</div>








<?php
/*



<div style="height: 50px;">
</div>



<div class="mailboxTitle sbc_font">
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
*/
?>