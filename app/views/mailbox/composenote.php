<?php
// Start Form
echo $data['Form']->start();
?>
<div class="mailboxInnerTitle">
    Compose Note
</div>
<div>

    <div class="innerWrap">
        <div class="innerLeft">
            Username:
        </div>
        <div class="innerRight">
<?php
echo $data['Form']->field['username'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Title:
        </div>
        <div class="innerRight">
<?php
echo $data['Form']->field['title'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Message:
        </div>
        <div class="innerRight">
<?php
echo $data['Form']->field['message'];
?>
        </div>
    </div>

</div>
<?php
// End Form
echo $data['Form']->end();
?>