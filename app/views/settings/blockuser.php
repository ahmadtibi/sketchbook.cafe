<?php
require 'header.php' ;
// Settings
$settings_page = 'blockuser';

// Start Form
echo $data['Form']->start();

require 'settings_top.php';
?>
<div class="settingsInnerTopWrap">
    <div class="settingsInnerTitle">
        Block User
    </div>
    <div class="settingsInnerDescription">
        Block users from messaging you on the site.
    </div>
</div>
<div class="settingsInnerBottomWrap">

    <div class="innerWrap">
        <div class="innerLeft">
            Username: 
        </div>
        <div class="innerRight">
<?php
echo $data['Form']->field['username'];
?>
            <div class="innerRightInfo">
                The user that you want to block.
            </div>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            &nbsp;
        </div>
        <div class="innerRight">
<?php
echo $data['Form']->field['submit'];
?>
        </div>
    </div>

    <div class="innerWrap">
        <div class="innerLeft">
            <?php echo $data['rownum'];?> Blocked User(s):
        </div>
        <div class="innerRight">
<?php
// Loop Blocked Users
while ($trow = mysqli_fetch_assoc($data['result']))
{
    $temp_id = $trow['cid'];
?>
            <div class="blockedUserDiv">
                <script>sbc_username(<?php echo $temp_id;?>,'');</script> 
                (<a href="https://www.sketchbook.cafe/settings/unblock/<?php echo $temp_id;?>/">unblock</a>)
            </div>
<?php
}
mysqli_data_seek($data['result'],0);
?>
        </div>
    </div>

</div>
<?php
require 'settings_bottom.php';

// End Form
echo $data['Form']->end();

require 'footer.php';
?>