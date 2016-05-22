<?php
// @author          Kameloh
// @lastUpdated     2016-05-20

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Forums\Forums as Forums;
use SketchbookCafe\OnlineOrganizer\OnlineOrganizer as OnlineOrganizer;
use SketchbookCafe\OnlineList\OnlineList as OnlineList;
use SketchbookCafe\Form\Form as Form;

class HomePage
{
    // Streams
    private $streamer_id = 0;
    private $StreamForm = [];
    private $sketch_points_required = 200;
    private $sketch_points = 0;
    private $stream_data = [];

    private $user_id = 0;
    private $time = 0;
    private $twitch_json = '';
    private $forum_data = [];
    private $online_data = [];
    private $entries_data = [];
    private $top_data = [];

    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        // Initialize
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];
        $this->time         = SBC::getTime();
        $this->obj_array    = &$obj_array;

        // Open Connection
        $db->open();

        // User Optional
        $User->setFrontpage();
        $User->findColumn('sketch_points');
        $User->optional($db);
        $this->user_id = $User->getUserId();

        if ($this->user_id > 0)
        {
            $this->sketch_points = (int) $User->getColumn('sketch_points');
            $this->checkIfStreamer($db);
        }

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

        // Fetch Recent Entries
        $this->fetchRecentEntries($db);

        // Get Top Sketch Point Users
        $this->getTopSketchers($db);

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
        $cooldown           = 300; // 300 seconds (5 minutes) timer
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
        if ($current_time >= $cooldown || $this->user_id == 1)
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

        // Initialize
        $db         = &$this->obj_array['db'];
        $channels   = '';

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Streamers
        $sql = 'SELECT twitch_username
            FROM streamers';
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        if ($rownum > 0)
        {
            while ($trow = mysqli_fetch_assoc($result))
            {
                if (!empty($trow['twitch_username']))
                {
                    $channels .= $trow['twitch_username'].' ';
                }
            }
            mysqli_data_seek($result,0);
        }

        // Clean
        $channels = str_replace(' ',',',trim($channels));
        if (empty($channels))
        {
            return null;
        }

        // Twitch Settings
        require '../app/twitch_api_settings.php';

        $channelsApi = 'https://api.twitch.tv/kraken/streams/?channel=';
        $channelName = $channels;
        // $channelName = 'kameloh,AustenMarie,Johnlestudio,Shticky,Alarios711,journeyful,LOIZA0319,AkaNoBall,Furious_Spartan,Glumduk,SamanthaJoanneArt,SinixDesign,Mioree,CGlas,CreeseArt,PunArt,KillerNEN,adobe,Faebelina,LuenKulo,RissaRambles,Arucelli,fred04142,ElectroKittenz';
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

    // Fetch Recent Entries
    final public function fetchRecentEntries(&$db)
    {
        $method = 'HomePage->fetchRecentEntries()';

        // Initialize
        $Images = &$this->obj_array['Images'];
        $Member = &$this->obj_array['Member'];

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Recent Entries
        $sql = 'SELECT id, image_id, user_id
            FROM challenge_entries
            WHERE ispending=0
            AND isdeleted=0
            ORDER BY id
            DESC
            LIMIT 25';
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        // Add Images
        $Images->idAddRows($result,'image_id');
        $Member->idAddRows($result,'user_id');

        $array = array
        (
            'result'    => &$result,
            'rownum'    => &$rownum,
        );

        $this->entries_data = &$array;
    }

    // Get Entries Data
    final public function getEntriesData()
    {
        return $this->entries_data;
    }

    // Check if the user is a streamer
    final private function checkIfStreamer(&$db)
    {
        $method = 'HomePage->checkIfStreamer()';

        // Initialize
        $user_id        = $this->user_id;
        $sketch_points  = $this->sketch_points;
        if ($user_id < 1 || $sketch_points < $this->sketch_points_required)
        {
            return null;
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get streamer info
        $sql = 'SELECT id
            FROM streamers
            WHERE user_id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id > 0)
        {
            $this->streamer_id = $id;
            // return null;
        }

        // Create Form
        $this->createStreamForm();
    }

    // Create Stream Form
    final public function createStreamForm()
    {
        $method = 'HomePage->createStreamForm()';

        // New Form
        $Form = new Form(array
        (
            'name'      => 'streamapplyform',
            'action'    => 'https://www.sketchbook.cafe/streams/add_stream_submit/',
            'method'    => 'POST',
            'inactive'  => 'Add Stream',
            'active'    => 'Adding Stream...',
        ));

        // Twitch Username
        $Form->field['username'] = $Form->input(array
        (
            'name'          => 'twitch_username',
            'type'          => 'text',
            'max'           => 50,
            'value'         => '',
            'placeholder'   => 'username at twitch',
            'css'           => 'input200',
        ));

        // Submit
        $Form->field['submit'] = $Form->submit(array
        (
            'name'      => 'Submit',
            'css'       => '',
        ));

        // Set
        $this->StreamForm = &$Form;
    }

    // Get Stream Form
    final public function getStreamData()
    {
        $array = array
        (
            'streamer_id'   => $this->streamer_id,
            'StreamForm'    => $this->StreamForm,
            'sketch_points' => $this->sketch_points,
        );
        return $array;
    }

    // Get Top Sketch Points users
    final private function getTopSketchers(&$db)
    {
        $method = 'HomePage->getTopSketchers()';

        // Initialize
        $Member = &$this->obj_array['Member'];

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Top 10
        $sql = 'SELECT id, sketch_points
            FROM users
            ORDER BY sketch_points
            DESC
            LIMIT 10';
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        // Add IDs
        $Member->idAddRows($result,'id');

        // Set
        $this->top_data = array
        (
            'result'    => &$result,
            'rownum'    => &$rownum,
        );
    }

    // Get Top data
    final public function getTopData()
    {
        return $this->top_data;
    }
}