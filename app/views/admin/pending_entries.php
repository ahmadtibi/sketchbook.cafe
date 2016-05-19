<?php
$Member             = &$data['Member'];
$Images             = &$data['Images'];

$challenge_row      = &$data['challenge_row'];
$entries_result     = &$data['entries_result'];
$entries_rownum     = &$data['entries_rownum'];

?>
pending entries (<?php echo $entries_rownum;?>)

<div>
<?php
// Entries
// id, difficulty, challenge_id, comment_id, image_id, user_id, date_created
if ($entries_rownum > 0)
{
    // Loop
    while ($trow = mysqli_fetch_assoc($entries_result))
    {
?>
    <div>
        <script>sbc_username(<?php echo $trow['user_id'];?>,'');</script>
    </div>
    <div style="overflow: hidden;">
        <div class="challenge_thumbnail_div">
            <span class="helper"></span>
            <script>sbc_challenge_thumbnail(<?php echo $trow['image_id'];?>);</script>
        </div>
    </div>

<?php
    }
    mysqli_data_seek($entries_result,0);
}
?>
</div>