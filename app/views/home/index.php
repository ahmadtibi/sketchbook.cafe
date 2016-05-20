<?php
// Initialize
$twitch_json    = &$data['twitch_json'];
$twitch_array   = [];
$twitch_array['streams'] = []; // initialize
if (!empty($twitch_json))
{
    $twitch_array = json_decode($twitch_json, true);
}
$total_streams = count($twitch_array['streams']);

/*

// Twitch Settings
require '../app/twitch_api_settings.php';

if (isset($twitch_api_settings['client_id']))
{
    $channelsApi = 'https://api.twitch.tv/kraken/streams/?channel=';
    $channelName = 'kameloh,SinixDesign,Mioree,CGlas,CreeseArt,PunArt,KillerNEN,adobe,Faebelina,LuenKulo,RissaRambles';
    $clientId = $twitch_api_settings['client_id'];
    $ch = curl_init();

    curl_setopt_array($ch, array(
        CURLOPT_HTTPHEADER => array(
            'Client-ID: ' . $clientId
        ),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL => $channelsApi . $channelName
    ));

    $response = curl_exec($ch);
    curl_close($ch);

    $twitch_array = json_decode($response, true);
}

$total_streams = count($twitch_array['streams']);
*/
?>
<style type="text/css">
.streamers_wrap {
    overflow: hidden;
    margin-top: 6px;
    margin-bottom: 6px;

    -webkit-box-shadow: 0px 2px 5px 0px rgba(148,148,148,1);
    -moz-box-shadow: 0px 2px 5px 0px rgba(148,148,148,1);
    box-shadow: 0px 2px 5px 0px rgba(148,148,148,1);
}
.streamers_top_title {
    font-weight: bold;
    font-size: 18px;
    padding-left: 15px;
    padding-top: 5px;
    color: #151515;
    background-color: #FFFFFF;
}
.streamers_bottom_wrap {
    padding-top: 5px;
    padding-bottom: 15px;

    overflow: hidden;
    text-align: center;
    font-size: 0px;

    background-color: #FFFFFF;
}
.streamers_item_wrap {
    margin: 3px;
    display: inline-block;
    overflow: hidden;
    border: 1px solid #151515;

    -webkit-box-shadow: 0px 0px 5px 0px rgba(214,214,214,1);
    -moz-box-shadow: 0px 0px 5px 0px rgba(214,214,214,1);
    box-shadow: 0px 0px 5px 0px rgba(214,214,214,1);
}
.streamers_item_wrap img {
    max-width: 215px;
}
.streamers_item_displayname {
    padding-left: 3px;
    font-size: 13px;
    text-align: left;
    overflow: hidden;
}
.streamers_item_viewers {
    padding-right: 3px;
    float: right;
    min-width: 25px;
    overflow: hidden;
    text-align: right;
    font-size: 12px;
}
.streamers_item_bottom_wrap {
    overflow: hidden;

    background-color: #151515;
    color: #FFFFFF;
}
</style>
<?php
if ($total_streams > 0)
{
?>
<div class="streamers_wrap">
    <div class="streamers_top_title">
        Streaming Live
    </div>
    <div class="streamers_bottom_wrap">
<?php
    // List the streams
    foreach ($twitch_array['streams'] as $stream)
    {
        // Make sure the streamer is on creative
        if ($stream['game'] == 'Creative')
        {
?>
        <a href="http://www.twitch.tv/<?php echo $stream['channel']['name'];?>/">
            <div class="streamers_item_wrap">
                <img src="<?php echo $stream['preview']['medium'];?>">
                <div class="streamers_item_bottom_wrap">
                    <div class="streamers_item_viewers">
                        <?php echo $stream['viewers'];?> viewers
                    </div>
                    <div class="streamers_item_displayname">
                        <?php echo $stream['channel']['display_name'];?>
                    </div>
                </div>
            </div>
        </a>
<?php
        }
    }
?>
    </div>
</div>
<?php
}
?>



Main Page Boop

<div style="text-align: center; padding: 25px;">
    <marquee>
        <img src="https://s3-us-west-2.amazonaws.com/sketchbookcafe/img/158-e7UMV.gif">
        <div>
            by SamanthaJoanneArt
        </div>
    </marquee>

</div>