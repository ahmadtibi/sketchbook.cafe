<?php
// Initialize Objects and Variables
$User   = &$data['User'];
$Form   = &$data['Form'];
$result = &$data['result'];
$rownum = &$data['rownum'];

// Start Form
echo $Form->start();
?>
<div class="adminPageTitle">
    Forum Categories
</div>
<div>
<?php
// Current Categories
if ($rownum > 0)
{
    // Loop
    while ($trow = mysqli_fetch_assoc($result))
    {
        // Categories First
        if ($trow['iscategory'] == 1)
        {
?>
    <div class="adminForumCategory">
        <div class="adminForumCategoryTitleWrap">
            <div class="adminForumCategoryTitleRight">
                 <a href="https://www.sketchbook.cafe/admin/forum_categories_edit/<?php echo $trow['id'];?>">edit</a>
            </div>
            <div class="adminForumCategoryTitle sbc_font sbc_font_size">
                <?php echo $trow['name'];?>
            </div>
        </div>
        <div class="adminForumCategoryDescription sbc_font sbc_font_size sbc_font_height sbc_font_link">
            <?php echo $trow['description'];?>
        </div>
    </div>
<?php
        }
    }
    mysqli_data_seek($result,0);
}
?>
</div>



<div>
    <b>Create New Category</b>
</div>
<div>
    <div class="innerWrap">
        <div class="innerLeft">
            Category:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['category'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Description:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['description'];
?>
        </div>
    </div>




</div>
<?php
// End Form
echo $Form->end();
?>