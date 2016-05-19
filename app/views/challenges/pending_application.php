<?php
// Initialize
$app_row        = &$data['app_row'];
$ChallengeForm  = &$data['ChallengeForm'];
$AdminForm      = &$data['AdminForm'];
$User           = &$data['User'];
?>
<style type="text/css">
.challengepending_wrap {
    overflow: hidden;
    margin-left: 20px;
    margin-right: 20px;
    margin-top: 10px;
    margin-bottom: 10px;

    -webkit-box-shadow: 0px 0px 5px 0px rgba(145,145,145,1);
    -moz-box-shadow: 0px 0px 5px 0px rgba(145,145,145,1);
    box-shadow: 0px 0px 5px 0px rgba(145,145,145,1);
}
.challengepending_title {
    font-size: 19px;
    font-weight: bold;
    padding-left: 15px;
    padding-right: 15px;
    padding-top: 10px;
    padding-bottom: 10px;

    background-color: #FFFFFF;
    color: #151515;
}
.challengepending_bottom_wrap {
    overflow: hidden;

    padding-left: 15px;
    padding-right: 15px;
    padding-bottom: 10px;

    color: #151515;
    background-color: #FFFFFF;
}
.challengepending_bottom_wrap a:link, .challengepending_bottom_wrap a:visited, .challengepending_bottom_wrap a:active {
    color: red;
}
.challengepending_bottom_wrap a:hover {
    text-decoration: underline;
}

.challengepending_top {

}
.challengepending_bottom {
    margin-bottom: 9px;
}

.challengepending_bottom_right {
    overflow: hidden;
    float: right;
    width: 70%;
}
.challengepending_bottom_left {
    overflow: hidden;
}
.challengepending_admin_wrap {
    padding: 15px;
    text-align: center;

    background-color: #FFFFFF;
}
</style>

<div class="challengepending_wrap">
    <div class="challengepending_title">
        Challenge Application #<?php echo $app_row['id'];?>
    </div>

    <div class="challengepending_bottom_wrap">

<?php
// Form Start
echo $ChallengeForm->start();
echo $ChallengeForm->field['app_id'];
?>
        <div class="challengepending_bottom_right">
            <div>
                <b>Edit Application</b>
            </div>


           <div class="innerWrap">
                <div class="innerLeft">
                    Title:
                </div>
                <div class="innerRight">
<?php
echo $ChallengeForm->field['title'];
?>
                </div>
            </div>

            <div class="innerWrap">
                <div class="innerLeft">
                    Points:
                </div>
                <div class="innerRight">
<?php
echo $ChallengeForm->field['points'];
?>
                </div>
            </div>

            <div class="innerWrap">
                <div class="innerLeft">
                    Description:
                </div>
                <div class="innerRight">
<?php
echo $ChallengeForm->field['description'];
?>
                </div>
            </div>

            <div class="innerWrap">
                <div class="innerLeft">
                    Requirements:
                </div>
                <div class="innerRight">
<?php
echo $ChallengeForm->field['requirements'];
?>
                </div>
            </div>

            <div class="innerWrap">
                <div class="innerLeft">
                    &nbsp;
                </div>
                <div class="innerRight">
<?php
echo $ChallengeForm->field['submit'];
?>
                </div>
            </div>


        </div>
<?php
// End Form
echo $ChallengeForm->end();
?>

        <div class="challengepending_bottom_left">

            <div class="challengepending_top">
                <b>User:</b>
            </div>
            <div class="challengepending_bottom">
                <script>sbc_username(<?php echo $app_row['user_id'];?>,'');</script>
            </div>

            <div class="challengepending_top">
                <b>Challenge Title:</b>
            </div>
            <div class="challengepending_bottom">
                <?php echo $app_row['name'];?>
            </div>

            <div class="challengepending_top">
                <b>Description:</b>
            </div>
            <div class="challengepending_bottom">
                <?php echo $app_row['description'];?>
            </div>

            <div class="challengepending_top">
                <b>Requirements:</b>
            </div>
            <div class="challengepending_bottom">
                <?php echo $app_row['requirements'];?>
            </div>

            <div class="challengepending_top">
                <b>Points:</b>
            </div>
            <div class="challengepending_bottom">
                <?php echo $app_row['points'];?>
            </div>

        </div>

    </div>
<?php
// Admin?
if ($User->isAdmin())
{
    // Start Form
    echo $AdminForm->start();
    echo $AdminForm->field['app_id'];
?>
    <div class="challengepending_admin_wrap">
        <div class="fb">
            Admin Action
        </div>
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
</div>