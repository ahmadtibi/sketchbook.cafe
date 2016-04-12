<?php

class Home extends Controller
{
	/*
	public function index($name = 'empty', $otherName = '')
	{
		echo $name . ' ' . $otherName;
	}
	*/

    protected $user;
    public function __construct()
    {
        $this->user = $this->model('UserHome');
    }

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

}