<?php
class Home extends Controller
{
	/*
	public function index($name = 'empty', $otherName = '')
	{
		echo $name . ' ' . $otherName;
	}
	*/

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
        $this->model('HomePage',$this->obj_array);

        // View
        $this->view('sketchbookcafe/header');
		$this->view('home/index', 
        [
            'User'  => $User,
        ]);
        $this->view('sketchbookcafe/footer');
	}

/*
	public function index ($name = '')
	{

		$name = $name;
        $this->user->test();
        $this->model('HomePage');
		$this->view('home/index', ['name' => $name]);

		// User::find(1);
	}

    public function testsubmit ()
    {
        $this->model('Testsubmit');
        $this->view('home/testsubmit');
    }

    public function create($name = '')
    {

    }
*/

}