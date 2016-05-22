<?php
// @author          Kameloh
// @lastUpdated     2016-05-21
class Home extends Controller
{
    protected $obj_array = '';

    protected $user;
    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

	public function index ()
	{
        // Objects
        $User = $this->obj_array['User'];

		// Model
        $Page = $this->model('HomePage',$this->obj_array);
        $twitch_json = $Page->getTwitchJSON();
        $forum_data = $Page->getForumData();
        $online_data = $Page->getOnlineData();
        $entries_data = $Page->getEntriesData();
        $stream_data = $Page->getStreamData();
        $top_data = $Page->getTopData();

        // View
        $this->view('sketchbookcafe/header');
		$this->view('home/index', 
        [
            'User'          => $User,
            'twitch_json'   => &$twitch_json,
            'forum_data'    => &$forum_data,
            'online_data'   => &$online_data,
            'entries_data'  => &$entries_data,
            'stream_data'   => &$stream_data,
            'top_data'      => &$top_data,
        ]);
        $this->view('sketchbookcafe/footer');
	}

}