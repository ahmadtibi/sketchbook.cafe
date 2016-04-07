<?php namespace SketchbookCafe\Db;

class Db
{
    public $dbconn = false;

    // Database information
    private $dbname     = '';
    private $dbhost     = 'local';
    private $user       = '';
    private $password   = '';

    // Construct
    public function __construct($database_settings)
    {
        echo '<div class="fb">db->__construct();</div>';

        // Set object variables
        $this->dbhost       = $database_settings['host'];
        $this->user         = $database_settings['user'];
        $this->password     = $database_settings['password'];
        $this->dbname       = $database_settings['dbname'];
    }

    // Open Connection
    final public function open()
    {
        if (!$this->dbconn)
        {
            $dbconn = new \mysqli ($this->dbhost, $this->user, $this->password, $this->dbname);
            if ($dbconn->connect_error) {
                error('Database connection failed: '  . $dbconn->connect_error, E_USER_ERROR);
            }

            // Set Charset
            $dbconn->set_charset('utf8');

            // Set dbconn
            $this->dbconn = $dbconn;

        echo '<div class="fb">db->open();</div>';
        }
    }

    // Close Connection
    final public function close()
    {
        if ($this->dbconn)
        {
            mysqli_close($this->dbconn);
            $this->dbconn = false;

            echo '<div class="fb">db->close();</div>';
        }
    }

    // Destruct
    public function __destruct ()
    {
        if ($this->dbconn)
        {
            $this->close();
            echo '<div class="fb">db closed by destruct!</div>';
        }
        echo '<div class="fb">db->__destruct();</div>';
    }

    // Prepare
    public function prepare($query)
    {
        $stmt = $this->dbconn->prepare($query);
        return $stmt;
    }

    // Query
    public function sql_query($query)
    {
        $value = mysqli_query ($this->dbconn, $query) or die (mysqli_error($this->dbconn));
        return $value;
    }

    // Fetch Row
    public function sql_fetchrow($query)
    {
        return mysqli_fetch_array($query);
    }

    // Number of Rows
    public function sql_numrows($query)
    {
        return mysqli_num_rows($query);
    }

    // Free Result
    public function sql_freeresult($result)
    {
        mysqli_free_result($result);
    }

    // Escape
    public function escape($var)
    {
        return $this->dbconn->real_escape_string($var);
    }

    // Switch Database

}