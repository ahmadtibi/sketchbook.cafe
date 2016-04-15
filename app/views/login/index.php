<?php
require('header.php');

// Start Form
echo $data['Form']->start();
?>
<div class="loginWrap">
    <div class="loginTitle">
        Login
    </div>
    <div class="loginInnerWrap">
        <div class="loginInnerLeft">
            Username:
        </div>
        <div class="loginInnerRight">
<?php
echo $data['Form']->field['username'];
?>
        </div>
    </div>

    <div class="loginInnerWrap">
        <div class="loginInnerLeft">
            Password:
        </div>
        <div class="loginInnerRight">
<?php
echo $data['Form']->field['password'];
?>
        </div>
    </div>

    <div class="loginInnerWrap">
        <div class="loginInnerLeft">
            IP Lock:
        </div>
        <div class="loginInnerRight">
<?php
echo $data['Form']->field['ip_lock'];
?>
            <span class="loginIpLockedSpan">
                Bind IP Address to Session
            </span>
        </div>
    </div>

    <div class="loginInnerWrap">
        <div class="loginInnerLeft">
            &nbsp;
        </div>
        <div class="loginInnerRight">
<?php
echo $data['Form']->field['submit'];
?>
        </div>
    </div>
</div>

<?php
// End Form
echo $data['Form']->end();

require('footer.php');
?>