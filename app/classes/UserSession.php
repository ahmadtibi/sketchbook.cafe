<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-26
// User Sessions - Creates/Cleans/Updates login sessions
// This assumes that the user is authenticated either through
// registration or login
namespace SketchbookCafe\UserSession;

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\GenerateRandom\GenerateRandom as GenerateRandom;

class UserSession
{
    private $user_id = 0;
    private $user_session = '';
    private $ip_address = '';
    private $new_session_id = 0;
    private $rd = 0;
    private $ip_lock = 1;

    // Generated
    private $hasinfo = 0;

    // Consruct
    public function __construct($input)
    {
        $method = 'UserSession->__construct()';

        // Make sure the ID is set
        $user_id = isset($input['user_id']) ? (int) $input['user_id'] : 0;
        if ($user_id < 1)
        {
            SBC::devError('$user_id is not set',$method);
        }

        // IP Lock
        $ip_lock = isset($input['ip_lock']) ? (int) $input['ip_lock'] : 0;
        if ($ip_lock != 1)
        {
            $ip_lock = 0;
        }

        // Set values
        $this->user_id      = $user_id;
        $this->ip_address   = SBC::getIpAddress();
        $this->ip_lock      = $ip_lock;
        $this->hasinfo      = 1;
    }

    // Check Info
    private function checkInfo()
    {
        $method = 'UserSession->checkInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::userError('Session does not have info');
        }
    }

    // Clean Sessions
    private function cleanSessions(&$db)
    {
        $method = 'UserSession->cleanSession()';

        // Make sure db is correct
        $db->sql_switch('sketchbookcafe');

        // Set vars and initialize
        $user_id            = $this->user_id;
        $delete_lower_id    = 0;

        // Let's keep this a small number for now
        $max_sessions   = 2;
        $limit          = $max_sessions + 1;
        $seek           = $max_sessions - 1; // assumes there's at least $max_sessions found

        // Get the last few sessions
        $sql = 'SELECT id
            FROM login_sessions
            WHERE user_id=?
            ORDER BY date_created
            DESC
            LIMIT '.$limit;
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        SBC::statementExecuteCommand($stmt,$db,$sql,$method);
        $result = $stmt->get_result();
        $rownum = $db->sql_numrows($result);
        $stmt->close();

        // Do we have more sessions than we can handle?
        if ($rownum > $max_sessions)
        {
            // Seek
            mysqli_data_seek($result,$seek);
            $row = mysqli_fetch_assoc($result);

            // Set vars
            $delete_lower_id = $row['id'];

            // Delete older sessions
            $sql = 'DELETE FROM login_sessions
                WHERE user_id=?
                AND id<?';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('ii',$user_id,$delete_lower_id);
            SBC::statementExecute($stmt,$db,$sql,$method);

            // Clear memory
            $db->sql_freeresult($result);
        }
    }

    // Create Session
    public function createSession(&$db)
    {
        $method = 'UserSession->createSession()';

        // Make sure info is set
        $this->checkInfo();

        // Make sure database is correct
        $db->sql_switch('sketchbookcafe');

        // Generate Session
        $this->user_session = GenerateRandom::process(250);

        // Set values
        $ip_address         = $this->ip_address;
        $ip_lock            = $this->ip_lock;
        $time               = SBC::getTime();
        $rd                 = SBC::rd();
        $user_session       = $this->user_session;
        $user_id            = $this->user_id;

        // Save for later
        $this->rd = $rd;

        // Insert new session into database
        $sql = 'INSERT INTO login_sessions
            SET rd=?,
            user_id=?, 
            date_created=?, 
            session_code=?, 
            ip_address=?,
            ip_lock=?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiissi',$rd,$user_id,$time,$user_session,$ip_address,$ip_lock);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Get new Session ID
        $sql = 'SELECT id
            FROM login_sessions
            WHERE rd=?
            AND user_id=?
            AND date_created=?
            AND session_code=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('iiis',$rd,$user_id,$time,$user_session);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // New Session ID
        $this->new_session_id = $row['id'];
        if ($this->new_session_id < 1)
        {
            SBC::devError('could not get new session ID',$method);
        }

        // Clean Old Sessions
        $this->cleanSessions($db);

        // Update User Sessions
        $this->updateUser($db);

        // Set Cookies!
        $this->setCookies();
    }

    // Set Cookies
    private function setCookies()
    {
        $method = 'UserSession->setCookies()';

        // Make sure info is set
        $this->checkInfo();

        // Set Vars
        $time           = SBC::getTime();
        $cookie_path    = '/';
        $cookie_domain  = '.sketchbook.cafe';
        $cookie_life    = 5184000;
        $cookie_time    = $time + $cookie_life;
        $https          = true;
        $http_only      = true;

        // Set Cookies
        setcookie('id',$this->user_id,$cookie_time,$cookie_path,$cookie_domain,$https,$http_only);
        setcookie('rd',$this->rd,$cookie_time,$cookie_path,$cookie_domain,$https,$http_only);
        setcookie('session_id',$this->new_session_id,$cookie_time,$cookie_path,$cookie_domain,$https,$http_only);
        setcookie('session_code',$this->user_session,$cookie_time,$cookie_path,$cookie_domain,$https,$http_only);
    }

    // Update User
    private function updateUser(&$db)
    {
        $method = 'UserSession->updateUser()';

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Variables (keeping it simple for sessions)
        $user_id        = $this->user_id;
        $session_id1    = 0;
        $session_id2    = 0;
        $use            = 0;
        $new_session_id = $this->new_session_id;

        // Just in case
        if ($new_session_id < 1)
        {
            SBC::devError('$new_session_id is not set',$method);
        }

        // Get user session
        $sql = 'SELECT session_id1, session_id2
            FROM users
            WHERE id=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('i',$user_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Check which one is older
        $session_id1    = $row['session_id1'];
        $session_id2    = $row['session_id2'];
        if ($session_id1 < $session_id2)
        {
            $use = 1;
        }
        else
        {
            $use = 2;
        }

        // Update user
        $sql = 'UPDATE users
            SET session_id'.$use.'=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$new_session_id,$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}