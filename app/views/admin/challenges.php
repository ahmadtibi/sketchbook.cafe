<?php
$Form   = &$data['Form'];
?>
<div class="adminPageTitle">
    Challenges
</div>
<div class="innerWrap">


<?php
// Form Start
echo $Form->start();
?>
    <div>
        <b>Create Challenge</b>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Category:
        </div>
        <div class="innerRight innerRightLineHeight">
<?php
echo $Form->field['category'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Thread ID:
        </div>
        <div class="innerRight innerRightLineHeight">
<?php
echo $Form->field['thread'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Username:
        </div>
        <div class="innerRight innerRightLineHeight">
<?php
echo $Form->field['username'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Name:
        </div>
        <div class="innerRight innerRightLineHeight">
<?php
echo $Form->field['name'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Points:
        </div>
        <div class="innerRight innerRightLineHeight">
<?php
echo $Form->field['points'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Description:
        </div>
        <div class="innerRight innerRightLineHeight">
<?php
echo $Form->field['description'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Requirements:
        </div>
        <div class="innerRight innerRightLineHeight">
<?php
echo $Form->field['requirements'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            &nbsp;
        </div>
        <div class="innerRight innerRightLineHeight">
<?php
echo $Form->field['submit'];
?>
        </div>
    </div>
<?php
// End Form
echo $Form->end();
?>

</div>