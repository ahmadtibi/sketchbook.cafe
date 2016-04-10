<?php
require 'header.php';

?>
<style type="text/css">
.registerPageWrap {
    overflow: hidden;
    width: 80%;
    margin-top: 12px;
    margin-bottom: 12px;
    margin-left: auto;
    margin-right: auto;

    color: #313131;
    background-color: #BCBCBC;
}
.registerTitle {
    padding-left: 12px;
    font-size: 17px;
    font-family: Georgia, serif;
    line-height: 50px;
    height: 50px;

    color: #FFFFFF;
    background-color: #7D7D7D;
}
.registerInnerWrap {
    overflow: hidden;
    min-height: 50px;
}
.registerInnerWrap a:link, .registerInnerWrap a:visited, .registerInnerWrap a:active {
    color: #151515;
}
.registerInnerWrap a:hover {
    text-decoration: underline;
    color: #353535;
}
.registerInnerLeft {
    font-family: Georgia, serif;
    font-size: 15px;
    text-align: right;
    padding-right: 12px;
    float: left;
    width: 25%;
    overflow: hidden;
    min-height: 50px;
    line-height: 50px;

}
.registerInnerRight {
    overflow: hidden;
    min-height: 50px;
    line-height: 50px;
}
</style>

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
            I have read and agreed to the <a href="#" class="fb">Terms of Service</a>
            and <a href="#" class="fb">Privacy Policy</a>
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

require 'footer.php';
?>