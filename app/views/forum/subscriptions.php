<?php
// Initialize
$threads_result = &$data['threads_result'];
$threads_rownum = &$data['threads_rownum'];
$sub_result     = &$data['sub_result'];
$sub_rownum     = &$data['sub_rownum'];

// Sub Array
$sub = [];
if ($sub_rownum > 0)
{
    while ($trow = mysqli_fetch_assoc($sub_result))
    {
        $sub[$trow['tid']]  = array
        (
            'tid'       => $trow['tid'],
            'lda'       => $trow['lda'],
            'pda'       => $trow['pda'],
        );
    }
    mysqli_data_seek($sub_result,0);
}
?>
<style type="text/css">
.subscriptions_wrap {
    overflow: hidden;
}
.subscriptions_title {
    font-size: 18px;
    font-family: Georgia, serif;
    padding: 15px;

    color: #151515;
}
.subscriptions_inner_wrap {
    overflow: hidden;
    padding: 15px;

    font-family: Georgia, serif;
    font-size: 14px;
}
</style>


<div class="subscriptions_wrap">
    <div class="subscriptions_title">
        Forum Subscriptions
    </div>
    <div class="subscriptions_inner_wrap">
<?php
// Threads?
if ($threads_rownum > 0)
{
    // Loop
    while ($trow = mysqli_fetch_assoc($threads_result))
    {
?>
        <div style="margin-bottom: 15px;">
            <a href="https://www.sketchbook.cafe/forum/thread/<?php echo $trow['id'];?>/"><?php echo $trow['title'];?></a>
            <br/>Last Updated: <?php echo $trow['date_updated'];?>
            <br/>isdeleted: <?php echo $trow['isdeleted'];?>
<?php
        // Quick Calculation
        if (isset($sub[$trow['id']]))
        {
            // Check
            if ($sub[$trow['id']]['pda'] < $trow['date_updated'])
            {
                echo '<div class="fb">thread updated O_O</div>';
            }
        }
?>
        </div>
<?php
    }
    mysqli_data_seek($threads_result,0);
}
?>

    </div>
</div>