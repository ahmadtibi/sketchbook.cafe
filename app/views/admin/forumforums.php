<?php
// Initialize Objects and Variables
$User               = &$data['User'];
$Form               = &$data['Form'];
$categories_result  = &$data['categories_result'];
$categories_rownum  = &$data['categories_rownum'];
$forums_result      = &$data['forums_result'];
$forums_rownum      = &$data['forums_rownum'];

// Start Form
echo $Form->start();
?>
<div class="adminPageTitle">
    Forums
</div>

<style type="text/css">
.adminForumForum {
    border: 1px solid #FFFFFF;
    margin-top: 6px;
    margin-bottom: 6px;
}
.adminForumTitleWrap {
    overflow: hidden;
}
.adminForumTitleRight {
    overflow: hidden;
    float: right;
    min-width: 100px;
    text-align: right;
    font-size: 12px;
    padding-right: 6px;

    min-height: 20px;
    line-height: 20px;
}
.adminForumTitle {
    overflow: hidden;

    padding-left: 12px;
    padding-right: 12px;
    font-size: 12px;
    min-height: 20px;
    line-height: 20px;
    text-decoration: underline;
    font-family: Georgia, serif;
}
.adminForumDescription {
    padding-top: 9px;
    padding-bottom: 9px;
    padding-left: 12px;
    padding-right: 12px;
    font-size: 12px;
    font-family: Georgia, serif;
    line-height: 14px;
    background-color: #D4D4D4;
}
</style>

<?php
// Categories
if ($categories_rownum > 0)
{
    // Loop Categories
    while ($trow = mysqli_fetch_assoc($categories_result))
    {
        // Category ID
        $category_id    = $trow['id'];
?>
    <div class="adminForumCategory">
        <div class="adminForumCategoryTitleWrap">
            <div class="adminForumCategoryTitleRight">
                 <a href="https://www.sketchbook.cafe/admin/forum_categories_edit/<?php echo $trow['id'];?>">edit</a>
            </div>
            <div class="adminForumCategoryTitle">
                <?php echo $trow['name'];?>
            </div>
        </div>
        <div class="adminForumCategoryDescription">
            <?php echo $trow['description'];?>
        </div>
<?php
        // Forums
        if ($forums_rownum > 0)
        {
            // Loop Forums
            while ($trow2 = mysqli_fetch_assoc($forums_result))
            {
                // Parent?
                if ($category_id == $trow2['parent_id'])
                {
?>
        <div class="adminForumForum">
            <div class="adminForumTitleWrap">
                <div class="adminForumTitleRight">
                    <a href="https://www.sketchbook.cafe/admin/forum_forums_edit/<?php echo $trow2['id'];?>/">edit</a>
                </div>
                <div class="adminForumTitle">
                    <?php echo $trow2['name'];?>
                </div>
            </div>
            <div class="adminForumDescription">
                <?php echo $trow2['description'];?>
            </div>
        </div>
<?php
                }
            }
            mysqli_data_seek($forums_result,0);
        }
?>
    </div>
<?php
    }
    mysqli_data_seek($categories_result,0);
}
?>
<div>
    <b>Create New Forum</b>
</div>
<div>

    <div class="innerWrap">
        <div class="innerLeft">
            Category:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['categories'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            Forum:
        </div>
        <div class="innerRight">
<?php
echo $Form->field['forumname'];
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