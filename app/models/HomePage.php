<?php
// @author          Kameloh
// @lastUpdated     2016-05-20

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Forums\Forums as Forums;
use SketchbookCafe\OnlineOrganizer\OnlineOrganizer as OnlineOrganizer;
use SketchbookCafe\OnlineList\OnlineList as OnlineList;

class HomePage
{
    private $user_id = 0;
    private $time = 0;
    private $twitch_json = '';
    private $forum_data = [];
    private $online_data = [];

    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize
        $db         = &$obj_array['db'];
        $User       = &$obj_array['User'];
        $this->time = SBC::getTime();

        // Open Connection
        $db->open();

        // User Optional
        $User->setFrontpage();
        $User->optional($db);
        $this->user_id = $User->getUserId();

        // Streamers Update
        $this->streamersUpdate($db);

        // Get Forums
        $this->getForums($obj_array);

        // Online Organizer
        $OnlineOrganizer = new OnlineOrganizer($db);
        $OnlineOrganizer->updateUser($this->user_id);
        $OnlineOrganizer->clean();

        // Online List
        $OnlineList = new OnlineList($obj_array);
        $OnlineList->process();
        $this->online_data = $OnlineList->getOnlineListData();

        // Process Data
        $ProcessAllData = new ProcessAllData();

        // Close Connection
        $db->close();
    }

    // Streamers Update
    final private function streamersUpdate(&$db)
    {
        $method = 'HomePage->streamersUpdate()';

        // Initialize
        $id                 = 1; // of course
        $time               = $this->time;
        $cooldown           = 120; // 300 seconds (5 minutes) timer
        $stream_lastupdate  = 0;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Timer for Site
        $sql = 'SELECT id, twitch_json, stream_lastupdate
            FROM sketchbookcafe
            WHERE id=1
            LIMIT 1';
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);

        // Check
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id < 1)
        {
            SBC::devError('Something went wrong - could not find site settings',$method);
        }

        // Calculate
        $current_time = $time - $row['stream_lastupdate'];
        if ($current_time >= $cooldown)
        {
            $twitch_json = $this->getTwitchStreamers();

            // Update Timer
            $sql = 'UPDATE sketchbookcafe
                SET twitch_json=?,
                stream_lastupdate=?
                WHERE id=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('sii',$twitch_json,$time,$id);
            SBC::statementExecute($stmt,$db,$sql,$method);
        }
        else
        {
            $twitch_json = $row['twitch_json'];
        }

        // Set
        $this->twitch_json = $twitch_json;
    }

    // Get Twitch Streamers
    final private function getTwitchStreamers()
    {
        $method = 'HomePage->getTwitchStreamers()';

        // Twitch Settings
        require '../app/twitch_api_settings.php';

        $channelsApi = 'https://api.twitch.tv/kraken/streams/?channel=';
        $channelName = 'kameloh,Johnlestudio,Shticky,Alarios711,journeyful,LOIZA0319,AkaNoBall,Furious_Spartan,Glumduk,SamanthaJoanneArt,SinixDesign,Mioree,CGlas,CreeseArt,PunArt,KillerNEN,adobe,Faebelina,LuenKulo,RissaRambles,Arucelli,fred04142,ElectroKittenz';
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

        return $response;
    }

    // Get Twitch JSON
    final public function getTwitchJSON()
    {
        return $this->twitch_json;
    }

    // Get Forums
    final public function getForums(&$obj_array)
    {
        $method = 'HomePage->getForums()';

        // Get Forums
        $Forums = new Forums($obj_array);
        $Forums->findAll();
        $this->forum_data = $Forums->getData();
    }

    // Get Forum Data
    final public function getForumData()
    {
        return $this->forum_data;
    }

    // Get Online Data
    final public function getOnlineData()
    {
        return $this->online_data;
    }
}