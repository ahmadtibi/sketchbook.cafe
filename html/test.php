<?php
define('BOOT',1);
exit;

/*
use SketchbookCafe\Db\Db as Db;

// Functions + Classes
require '../app/functions/error.php';
require '../app/functions/sbc_function.php';
require '../app/functions/sbc_class.php';
require '../app/classes/Db.php';

// Database Object
require '../app/database_settings.php';
$db = new Db($database_settings);
unset($database_settings); // just in case


// =============================================

class MainApp
{
    protected $db = '';

    public function __construct(&$db)
    {
        // Set as DB
        $this->db = &$db;
        $db = $this->db;

        $db->open();

        $this->mainAppInnerMethod();

        $db->close();
        echo 'hello';
    }

    public function mainAppInnerMethod()
    {
        $db = $this->db;
        $sql = 'SELECT username FROM users WHERE id=2 LIMIT 1';
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        echo 'username is '.$row['username'].' YES mainAppInnerMethod ';

        $test = array
        (
            'testmanhero',
            'testmantwo',
        );

        // Jon
        call_user_func_array(['JonController', 'testmanhero'],$test);
    }
}

class JonController
{
    // Construct
    public function __construct()
    {
        echo '<div>jon was here</div>';
    }

    // testmanhero
    public function testmanhero()
    {
        echo 'hihi';
    }
}


$MainApp = new MainApp($db);
echo 'maybe';

?>

<div>
    <b>Developer Page</b>
</div>
<div>
    <img src="https://s3-us-west-2.amazonaws.com/sketchbookcafe/2016-01-26+deer.jpg">
</div>
<div>
    :D

</div>
*/
?>