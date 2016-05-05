<?php
// Initialize
$Form               = &$data['Form'];
$categories_result  = &$data['categories_result'];
$categories_rownum  = &$data['categories_rownum'];
?>
<div class="adminPageTitle">
    Challenge Categories
</div>

<?php
// Loop Categories
while ($trow = mysqli_fetch_assoc($categories_result))
{
?>
<div>
    Category: <?php echo $trow['name'];?>
</div>
<?php
}
mysqli_data_seek($categories_result,0);
?>



<?php
// Start Form
echo $Form->start();
?>
<div class="innerWrap">
    <div>
        <b>Create Category</b>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Name:
        </div>
        <div class="innerRight innerRightLineHeight">
<?php
echo $Form->field['category'];
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

</div>
<?php
// End Form
echo $Form->end();
?>