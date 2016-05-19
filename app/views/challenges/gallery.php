<?php
// Initialize
$entries_result = &$data['entries_result'];
$entries_rownum = &$data['entries_rownum'];
$PageNumbers    = &$data['PageNumbers'];
$challenge_row  = &$data['challenge_row'];
?>
<!-- Start Page Wrap -->
<div class="challengegallery_wrap">

    <div class="challengegallery_top_wrap">

        <div class="challengegallery_top_right">
            <div class="challengegallery_item">
                6.4 difficulty (142 votes)
            </div>
            <div class="challengegallery_item">
                <?php echo number_format(rand(249,29402));?> views
            </div>
            <div class="challengegallery_item">
                158 entries
            </div>
        </div>

        <div class="challengegallery_top_left">

            <div class="challengegallery_title challenge_bottom_links">
                <a href="https://www.sketchbook.cafe/forum/thread/<?php echo $challenge_row['thread_id'];?>/"><?php echo $challenge_row['name'];?></a>
            </div>

            <div class="challengegallery_description">
                <?php echo $challenge_row['description'];?>
            </div>
        </div>

    </div>


<?php
// Page Numbers
if ($entries_rownum > 0)
{
?>
    <div class="challengegallery_pagenumbers_wrap">
        <div class="pageNumbersWrap">
            <div class="pageNumbersLeft">
                Viewing <?php echo $PageNumbers['pages_min'];?>-<?php echo $PageNumbers['pages_max'];?> posts (<?php echo $PageNumbers['pages_total'];?> total). 
            </div>
            <div class="pageNumbersRight">
<?php
    echo $PageNumbers['pagenumbers'];
?>
            </div>
        </div>
    </div>
<?php
}
?>

    <div class="challengegallery_gallery_wrap">
<?php
// Images
if ($entries_rownum > 0)
{
    // Loop
    while ($trow = mysqli_fetch_assoc($entries_result))
    {
?>
        <div class="challenge_thumbnail_div">
            <span class="helper"></span>
            <a href="https://www.sketchbook.cafe/entry/<?php echo $trow['id'];?>/">
                <script>sbc_challenge_thumbnail(<?php echo $trow['image_id'];?>);</script>
            </a>
        </div>
<?php
    }
    mysqli_data_seek($entries_result,0);
}
?>
    </div>

<?php
// Page Numbers
if ($entries_rownum > 0)
{
?>
    <div class="challengegallery_pagenumbers_wrap">
        <div class="pageNumbersWrap">
            <div class="pageNumbersLeft">
                &nbsp;
            </div>
            <div class="pageNumbersRight">
<?php
    echo $PageNumbers['pagenumbers'];
?>
            </div>
        </div>
    </div>
<?php
}
?>


<!-- End Page Wrap -->
</div>