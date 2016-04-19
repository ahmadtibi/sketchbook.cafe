<?php
$User               = &$data['User'];
$categories_result  = &$data['categories_result'];
$categories_rownum  = &$data['categories_rownum'];
$forums_result      = &$data['forums_result'];
$forums_rownum      = &$data['forums_rownum'];
?>
<style type="text/css">
.forumWrap {
    margin-top: 12px;
    margin-bottom: 12px;
    margin-left: 15px;
    margin-right: 15px;
}
.forumCategoryWrap {
    margin-top: 15px;
    margin-bottom: 15px;
    background-color: #C1C1C1;

    color: #151515;

    -webkit-box-shadow: 0px 2px 2px 0px rgba(135,135,135,1);
    -moz-box-shadow: 0px 2px 2px 0px rgba(135,135,135,1);
    box-shadow: 0px 2px 2px 0px rgba(135,135,135,1);
}
.forumCategoryName {
    padding-left: 15px;
    padding-top: 15px;
    font-size: 16px;
    font-weight: bold;
    font-family: Georgia, serif;

    color: #313131;
}
.forumCategoryDescription {
    padding-left: 15px;
    padding-top: 5px;
    padding-bottom: 15px;
    font-size: 12px;
    font-family: Georgia, serif;
    line-height: 18px;

    color: #525252;
}

.forumCellTopLeft {
    display: table-cell;
    font-size: 10px;
    font-weight: bold;
    font-family: Georgia, serif;

    color: #666666;
    padding-left: 9px;
    padding-top: 12px;
}
.forumCellTopMiddle {
    display: table-cell;
    font-size: 10px;
    font-weight: bold;
    font-family: Georgia, serif;

    color: #666666;
    text-align: center;
}
.forumCellTopRight {
    padding-right: 15px;
    display: table-cell;
    font-size: 10px;
    font-weight: bold;
    font-family: Georgia, serif;

    color: #666666;
    text-align: right;
}

.forumTable {
    width: 100%;
    background-color: #FFFFFF;
}
.forumCell {

}
.forumCellForum {
    padding-left: 20px;
    padding-top: 12px;
    padding-bottom: 12px;
    font-size: 13px;
    font-family: Georgia, serif;
}
.forumCellThreads {
    width: 75px;
    text-align: center;
    font-size: 13px;
    font-family: Georgia, serif;
    line-height: 45px;
    min-height: 45px;
}
.forumCellPosts {
    width: 75px;
    text-align: center;
    font-size: 13px;
    font-family: Georgia, serif;
    line-height: 45px;
    min-height: 45px;
}
.forumCellFreshness {
    padding-right: 15px;
    text-align: right;
    width: 285px;
    font-size: 13px;
    font-family: Georgia, serif;
}

.forumForumName {
    font-weight: bold;
    font-size: 14px;
    font-family: Georgia, serif;

    color: #151515;
}
.forumForumName a:link, .forumForumName a:visited, .forumForumName a:active {
    color: #C13A3A;
}
.forumForumName a:hover {
    text-decoration: underline;
}
.forumForumDescription {
    padding-top: 6px;
    font-size: 12px;
    font-family: Georgia, serif;

    color: #434343;
}

.forumTd {
    display: table-cell;
}
.forumFreshnessThread {
    font-weight: bold;
    font-size: 12px;
    font-family: Georgia, serif;
}
.forumFreshnessThread a:link, .forumFreshnessThread a:visited, .forumFreshnessThread a:active {
    color: #C13A3A;
}
.forumFreshnessThread a:hover {
    text-decoration: underline;
}
.forumFreshnessUsername {
    padding-top: 6px;
    font-size: 11px;
    font-family: Georgia, serif;

    color: #494949;
}
.forumFreshnessUsername a:link, .forumFreshnessUsername a:visited, .forumFreshnessUsername a:active {
    color: #C13A3A;
}
.forumFreshnessUsername a:hover {
    text-decoration: underline;
}
</style>
<div class="forumWrap">


<?php
// Categories
while ($crow = mysqli_fetch_assoc($categories_result))
{
    $category_id = $crow['id'];
?>
    <div class="forumCategoryWrap">
        <div class="forumCategoryName">
            <?php echo $crow['name'];?>
        </div>
        <div class="forumCategoryDescription">
            <?php echo $crow['description'];?>
        </div>

        <div class="table forumTable">
            <div class="tr">
                <div class="forumCellTopLeft">
                    Forum
                </div>
                <div class="forumCellTopMiddle">
                    Threads
                </div>
                <div class="forumCellTopMiddle">
                    Posts
                </div>
                <div class="forumCellTopRight">
                    Freshness
                </div>
            </div>
<?php
    // Forum
    while ($frow = mysqli_fetch_assoc($forums_result))
    {
        // Parent Forum?
        if ($category_id == $frow['parent_id'])
        {
?>

            <div class="tr">
                <div class="forumTd forumCell forumCellForum">
                    <div class="forumForumName">
                        <a href="https://www.sketchbook.cafe/forum/<?php echo $frow['id'];?>/"><?php echo $frow['name'];?></a>
                    </div>
                    <div class="forumForumDescription">
                        <?php echo $frow['description'];?>
                    </div>
                </div>
                <div class="forumTd forumCell forumCellThreads">
                    <?php echo rand(1,429);?>
                </div>
                <div class="forumTd forumCell forumCellPosts">
                    <?php echo number_format(rand(192,482932));?>
                </div>
                <div class="forumTd forumCell forumCellFreshness">
                    <div class="forumFreshnessThread">
                        <a href="#">A real thread Kappa</a>
                    </div>
                    <div class="forumFreshnessUsername">
                        <?php echo rand(2,60);?> minutes ago by <a href="">Kameloh</a>
                    </div>
                </div>
            </div>

<?php
        }
    }
    mysqli_data_seek($forums_result,0);
?>
        </div>
    </div>
<?php
}
mysqli_data_seek($categories_result,0);
?>


</div>