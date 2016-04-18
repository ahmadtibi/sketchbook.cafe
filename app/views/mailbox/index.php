<?php
// Initialize Vars
$Member         = &$data['Member'];
$result         = &$data['result'];
$rownum         = &$data['rownum'];
$User           = &$data['User'];
$user_id        = &$data['user_id'];
$isnew          = &$data['isnew'];

// Page Numbers
$pagenumbers    = &$data['pagenumbers'];
$pages_min      = &$data['pages_min'];
$pages_max      = &$data['pages_max'];
$pages_total    = &$data['pages_total'];
?>

<div class="pageNumbersWrap">
    <div class="pageNumbersLeft">
        Viewing <?php echo $pages_min;?>-<?php echo $pages_max;?> threads (<?php echo $pages_total;?> total). 
    </div>
    <div class="pageNumbersRight">

<?php
echo $pagenumbers;
?>

    </div>
</div>

<div class="inboxWrap">
    <div class="table inboxTable">

        <div class="tr inboxTop">
            <div class="tdInbox inboxUser inboxTopTd">
                User
            </div>
            <div class="tdInbox inboxTitle inboxTopTd">
                Title
            </div>
            <div class="tdInbox inboxLastUpdated inboxTopTd">
                Last Updated
            </div>
        </div>


<?php
// Do we have any threads?
if ($rownum > 0)
{
    // Loop
    while ($trow = mysqli_fetch_assoc($result))
    {
        // Total Replies
        $total_replies  = $trow['total_replies'];

        // Is new?
        $new = 0;

        // Is New?
        if ($isnew[$trow['id']] == 1)
        {
            $new = 1;
        }
        else
        {
            $new = 0;
        }

        // Replied?
        if ($trow['last_user_id'] != $user_id)
        {
            $is_replied = 1;
        }
        else
        {
            $is_replied = 0;
        }

        // Set User
        if ($user_id == $trow['user_id'])
        {
            $temp_user_id   = $trow['r_user_id'];
        }
        else
        {
            $temp_user_id   = $trow['user_id'];
        }
?>



        <div class="tr inboxTr">
            <div class="tdInbox inboxCell inboxInnerLeft">

                <div class="inboxAvatarWrap">
                    <div class="inboxAvatarDiv">
                        <script>sbc_avatar(<?php echo $temp_user_id;?>,'inboxAvatar');</script>
                    </div>
                    <div class="inboxAvatarUsername">
                        <script>sbc_username(<?php echo $temp_user_id;?>,'');</script>
                    </div>
                </div>

            </div>
            <div class="tdInbox inboxCell inboxThreadDiv inboxInnerMiddle">
                <a href="https://www.sketchbook.cafe/mailbox/note/<?php echo $trow['id'];?>/" class="<?php if ($new == 1) { echo 'fb'; } ?> <?php if ($trow['isremoved'] == 1) { echo ' strike '; } ?>"><?php echo $trow['title'];?></a>
                <div class="inboxPagesDiv">
                    <script>sbc_numbered_links('https://www.sketchbook.cafe/mailbox/note/<?php echo $trow['id'];?>/',10,<?php echo $total_replies;?>,'sbc_pagenumber');</script>
                </div>
            </div>
            <div class="tdInbox inboxCell inboxInnerRight">
                <?php echo $User->mytz($trow['date_updated'],'F jS, Y @ g:iA');?>
                by 
                <script>sbc_username(<?php echo $trow['last_user_id'];?>, '');</script>
            </div>
        </div>


<?php
    }
    mysqli_data_seek($result,0);
}
?>


    </div>
</div>

<div class="pageNumbersWrap">
    <div class="pageNumbersLeft">
        
    </div>
    <div class="pageNumbersRight">

<?php
echo $pagenumbers;
?>

    </div>
</div>