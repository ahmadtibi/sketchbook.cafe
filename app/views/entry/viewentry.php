<?php
use SketchbookCafe\DisplayComment\DisplayComment as DisplayComment;

// Initialize
$User           = &$data['User'];
$Member         = &$data['Member'];
$Comment        = &$data['Comment'];
$entry_row      = &$data['entry_row'];
$challenge_row  = &$data['challenge_row'];
$AdminForm      = &$data['AdminForm'];

// Vars
$challenge_id   = $entry_row['challenge_id'];

// Object Array
$obj_array = array
(
    'User'      => &$User,
    'Comment'   => &$Comment,
    'Member'    => &$Member,
);
$DisplayComment = new DisplayComment($obj_array);
?>
<style type="text/css">
.entry_page_wrap {
    margin-left: 20px;
    margin-right: 20px;
    margin-top: 10px;
    margin-bottom: 10px;

    -webkit-box-shadow: 0px 0px 5px 0px rgba(102,102,102,1);
    -moz-box-shadow: 0px 0px 5px 0px rgba(102,102,102,1);
    box-shadow: 0px 0px 5px 0px rgba(102,102,102,1);
}
.entry_image_div {
    text-align: center;
    padding: 5px;

    background-color: #FFFFFF;
}
.entry_admin_div {
    width: 50%;
    margin-left: auto;
    margin-right: auto;
    margin-bottom: 15px;
    margin-top: 15px;
    text-align: center;

    padding: 15px;
    background-color: #EDEDED;

    -moz-border-radius: 5px 5px 5px 5px;
    border-radius: 5px 5px 5px 5px;
}
.entry_image {
    max-width: 100%;

    -webkit-box-shadow: 0px 0px 5px 0px rgba(224,224,224,1);
    -moz-box-shadow: 0px 0px 5px 0px rgba(224,224,224,1);
    box-shadow: 0px 0px 5px 0px rgba(224,224,224,1);
}
</style>


<!-- Start Page Wrap -->
<div class="entry_page_wrap">

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
                <a href="https://www.sketchbook.cafe/forum/thread/<?php echo $challenge_row[$challenge_id]['thread_id'];?>/"><?php echo $challenge_row[$challenge_id]['name'];?></a>
            </div>

            <div class="challengegallery_description">
                <?php echo $challenge_row[$challenge_id]['description'];?>
            </div>
        </div>

    </div>

    <div class="entry_image_div">

<?php
// Admin only
if ($User->isAdmin() && $entry_row['ispending'] == 1)
{
    // Start Form
    echo $AdminForm->start();
    echo $AdminForm->field['entry_id'];
?>
        <div class="entry_admin_div">
            <b>Pending Entry</b>
            <div>
<?php
    echo $AdminForm->field['action'];
    echo $AdminForm->field['confirm'];
?>
                <span class="f12">
                    Confirm
                </span>
<?php
    echo $AdminForm->field['submit'];
?>
            </div>
        </div>
<?php
    // End Form
    echo $AdminForm->end();
}
?>


        <script>sbc_image(<?php echo $entry_row['image_id'];?>,'entry_image');</script>
    </div>

    <div>
<?php
// Comment
echo $DisplayComment->process(array
(
    'comment_id'    => $entry_row['comment_id'],
));
?>
    </div>

<!-- End Page Wrap -->
</div>