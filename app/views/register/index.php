<?php
// Start Form
echo $data['Form']->start();
?>
<div class="registerPageWrap">
    <div class="registerTitle">
        Register at the Sketchbook Cafe
    </div>
    <div class="registerInnerWrap">
        <div class="registerInnerLeft">
            Username
        </div>
        <div class="registerInnerRight">
<?php
echo $data['Form']->field['username'];
?>
        </div>
    </div>

    <div class="registerInnerWrap">
        <div class="registerInnerLeft">
            E-mail
        </div>
        <div class="registerInnerRight">
<?php
echo $data['Form']->field['email'];
?>
        </div>
    </div>

    <div class="registerInnerWrap">
        <div class="registerInnerLeft">
            Password
        </div>
        <div class="registerInnerRight">
<?php
echo $data['Form']->field['pass1'];
?>
        </div>
    </div>

    <div class="registerInnerWrap">
        <div class="registerInnerLeft">
            Password Again
        </div>
        <div class="registerInnerRight">
<?php
echo $data['Form']->field['pass2'];
?>
        </div>
    </div>

    <div class="registerInnerWrap">
        <div class="registerInnerLeft">

        </div>
        <div class="registerInnerRight">
<?php
echo $data['Form']->field['termsofservice'];
?>
            I have read and agreed to the 
            <a href="https://www.sketchbook.cafe/docs/tos/" target="_blank" class="fb">Terms of Service</a>
            and 
            <a href="https://www.sketchbook.cafe/docs/privacy/" target="_blank" class="fb">Privacy Policy</a>
        </div>
    </div>

    <div class="registerInnerWrap">
        <div class="registerInnerLeft">

        </div>
        <div class="registerInnerRight">
            <div class="g-recaptcha" data-sitekey="6LcBqxwTAAAAAGlxWOMV2SsEzOxz2B1lpUxj8dJJ"></div>
        </div>
    </div>

    <div class="registerInnerWrap">
        <div class="registerInnerLeft">

        </div>
        <div class="registerInnerRight">
<?php
echo $data['Form']->field['submit'];
?>
        </div>
    </div>

</div>

<?php
// End Form
echo $data['Form']->end();
?>