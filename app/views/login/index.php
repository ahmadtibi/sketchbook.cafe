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
echo $data['Form']->field['iplock'];
?>
            <span class="loginIpLockedSpan">
                Session bound to IP Address
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