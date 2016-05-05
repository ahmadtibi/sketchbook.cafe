<?php
// Initialize Objects and Vars
$categories_result  = &$data['categories_result'];
$categories_rownum  = &$data['categories_rownum'];
$forums_result      = &$data['forums_result'];
$forums_rownum      = &$data['forums_rownum'];
$f_admin_result     = &$data['f_admin_result'];
$f_admin_rownum     = &$data['f_admin_rownum'];
?>
<style type="text/css">
.adminManageForumCategoryWrap {
    overflow: hidden;
    margin-top: 15px;
    margin-bottom: 15px;
    padding: 12px;

    color: #151515;
    background-color: #FFFFFF;

    -webkit-box-shadow: 0px 2px 5px 0px rgba(92,92,92,1);
    -moz-box-shadow: 0px 2px 5px 0px rgba(92,92,92,1);
    box-shadow: 0px 2px 5px 0px rgba(92,92,92,1);
}
.adminManageForumForumWrap {
    overflow: hidden;
    margin: 25px;
}
.adminManageForumTable {
    width: 100%;
}
.adminManageForumCategoryName {
    font-size: 13px;
    font-family: Georgia, serif;
}
.adminManageForumCategoryDescription {
    padding-top: 6px;
    padding-bottom: 6px;
    font-size: 12px;
    font-family: Georgia, serif;
}
.adminManageForumTdTop {
    padding-top: 6px;
    padding-bottom: 6px;
    text-decoration: underline;
    font-size: 12px;
    font-family: Georgia, serif;
}
.adminmanageForumTd {
    padding-top: 3px;
    padding-bottom: 3px;

    font-size: 12px;
    font-family: Georgia, serif;
}
</style>

<div class="adminPageTitle">
    Manage Forums
</div>

<?php
// Categories
if ($categories_rownum > 0)
{
    // Loop
    while ($crow = mysqli_fetch_assoc($categories_result))
    {
?>
<div class="adminManageForumCategoryWrap">
    <div class="adminManageForumCategoryName">
        <b>Category: <?php echo $crow['name'];?></b> 
        [<a href="https://www.sketchbook.cafe/admin/forum_categories_edit/<?php echo $crow['id'];?>">edit</a>]
    </div>
    <div class="adminManageForumCategoryDescription">
        <?php echo $crow['description'];?>
    </div>
    <div class="table adminManageForumTable">

        <div class="tr">
            <div class="td adminManageForumTdTop">
                Forum
            </div>
            <div class="td adminManageForumTdTop">
                Description
            </div>
            <div class="td adminManageForumTdTop">
                Moderators
            </div>
        </div>

<?php
        // Forums
        if ($forums_rownum > 0)
        {
            // Loop Forums
            while ($frow = mysqli_fetch_assoc($forums_result))
            {
                // Parent ID == Category ID?
                if ($frow['parent_id'] == $crow['id'])
                {
?>

        <div class="tr">
            <div class="td adminmanageForumTd">
                [<a href="https://www.sketchbook.cafe/admin/forum_forums_edit/<?php echo $frow['id'];?>">edit</a>]
                <?php echo $frow['name'];?>
            </div>
            <div class="td adminmanageForumTd">
                <?php echo $frow['description'];?>
            </div>
            <div class="td adminmanageForumTd">
<?php
                    // Forum Admins
                    if ($f_admin_rownum > 0)
                    {
                        // Loop
                        while ($arow = mysqli_fetch_assoc($f_admin_result))
                        {
                            // Admin belong to forum?
                            if ($arow['forum_id'] == $frow['id'])
                            {
?>
                <div>
                    <script>sbc_username(<?php echo $arow['user_id'];?>);</script>
                </div>
<?php
                            }
                        }
                        mysqli_data_seek($f_admin_result,0);
                    }
?>
            </div>
        </div>
<?php
                }
            }
            mysqli_data_seek($forums_result,0);
        }
?>
    </div>
</div>
<?php
    }
    mysqli_data_seek($categories_result,0);
}
?>