<?php
require 'header.php';

// Start Form
echo $data['Form']->start();
?>
<div class="loginWrap">
    <div class="loginTitle">
        Administrator Login
    </div>

    <div class="loginInnerWrap">
        <div class="loginInnerLeft">
            Password 1:
        </div>
        <div class="loginInnerRight">
<?php
echo $data['Form']->field['pass1'];
?>
        </div>
    </div>

    <div class="loginInnerWrap">
        <div class="loginInnerLeft">
            Password 2:
        </div>
        <div class="loginInnerRight">
<?php
echo $data['Form']->field['pass2'];
?>
        </div>
    </div>

    <div class="loginInnerWrap">
        <div class="loginInnerLeft">
            Password 3:
        </div>
        <div class="loginInnerRight">
<?php
echo $data['Form']->field['pass3'];
?>
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

require 'footer.php';
?>