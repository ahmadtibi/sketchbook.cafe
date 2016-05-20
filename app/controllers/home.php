<?php
// @author          Kameloh
// @lastUpdated     2016-05-20
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

        // View
        $this->view('sketchbookcafe/header');
		$this->view('home/index', 
        [
            'User'          => $User,
            'twitch_json'   => &$twitch_json,
        ]);
        $this->view('sketchbookcafe/footer');
	}

}