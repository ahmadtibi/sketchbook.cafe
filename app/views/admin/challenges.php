<?php
$Form               = &$data['Form'];
$categories_result  = &$data['categories_result'];
$categories_rownum  = &$data['categories_rownum'];
$challenges_result  = &$data['challenges_result'];
$challenges_rownum  = &$data['challenges_rownum'];
?>
<style type="text/css">
.temp_table {
    width: 100%;
    margin-bottom: 25px;
}
</style>
<div class="adminPageTitle">
    Challenges
</div>
<div class="innerWrap">

    <div>
        <b>Challenges</b>
    </div>
    <div class="table temp_table">
        <div class="tr">
            <div class="td">
                <b>ID</b>
            </div>
            <div class="td">
                <b>Name</b>
            </div>
            <div class="td">
                <b>Fix</b>
            </div>
        </div>
<?php
// Loop
while ($trow = mysqli_fetch_assoc($challenges_result))
{
?>
        <div class="tr">
            <div class="td">
                <?php echo $trow['id'];?>
            </div>
            <div class="td">
                <?php echo $trow['name'];?>
            </div>
            <div class="td">
                <a href="https://www.sketchbook.cafe/admin/fix_challenge_table/<?php echo $trow['id'];?>/">Fix Table</a>
            </div>
        </div>
<?php
}
mysqli_data_seek($challenges_result,0);
?>
    </div>

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