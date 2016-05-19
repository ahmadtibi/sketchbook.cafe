<?php
// Initialize
$result     = &$data['result'];
$rownum     = &$data['rownum'];
?>
Challenge Applications

<?php
// Did we find any results?
if ($rownum > 0)
{
?>
<div style="border: 1px solid #151515;">
<?php
    while ($trow = mysqli_fetch_assoc($result))
    {
?>
    <div>
        <div>
            Application ID#<?php echo $trow['id'];?>
            by <script>sbc_username(<?php echo $trow['user_id'];?>,'');</script>
        </div>
        <div>
            <b>Name:</b>
            <br/><?php echo $trow['name'];?>
            (<?php echo $trow['points'];?> Points)
        </div>
        <div>
            <b>Description:</b>
            <br/><?php echo $trow['description'];?>
        </div>
        <div>
            <b>Requirements:</b>
            <br/><?php echo $trow['requirements'];?>
        </div>
    </div>
<?php
    }
    mysqli_data_seek($result,0);
?>
</div>
<?php
}
?>