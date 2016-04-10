<?php
require 'header.php';

echo $data['Form']->start();
echo $data['Form']->field['username'];
echo $data['Form']->field['termsofservice'];
echo $data['Form']->field['submit'];
echo $data['Form']->end();
?>
<style type="text/css">
.registerPageOverlay {
    overflow: hidden;
    width: 80%;
    margin-left: auto;
    margin-right: auto;
}
.registerTitle {
    font-size: 24px;
    font-family: Georgia, serif;
    line-height: 50px;
    height: 50px;

    color: #313131;
    background-color: #BCBCBC;
}
</style>
<div class="registerPageOverlay">
    <div class="registerTitle">
        Registration!
    </div>


</div>
<?php
require 'footer.php';
?>