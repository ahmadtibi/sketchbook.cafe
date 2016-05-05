<?php
// @author          Kameloh
// @lastUpdated     2016-05-04

namespace SketchbookCafe\SBC;

class SBC
{
    // Construct
    public function __construct()
    {

    }

    // User Error
    // Example:     SBC::userError('Sorry, error message');
    public static function userError($message)
    {
        // Global
        global $db;
        if ($db)
        {
            $db->close();
        }

        echo $message;
        exit;
    }

    // Development Error
    // Example:     SBC::devError('$user_id or $r_user_id is not set',$method);
    public static function devError($message,$method)
    {
        // Global
        global $db;
        if ($db)
        {
            $db->close();
        }

        echo 'Dev error: '.$message.' for '.$method;
        exit;
    }

    // Statement Execute and then close statement
    // Example:     SBC::statementExecute($stmt,$db,$sql,$method);
    public static function statementExecute(&$stmt,&$db,$sql,$method)
    {
        self::statementExecuteCommand($stmt,$db,$sql,$method);
        $stmt->close();
    }

    // Statement Row and then close statement
    // Example:     $row = SBC::statementFetchRow($stmt,$db,$sql,$method);
    public static function statementFetchRow(&$stmt,&$db,$sql,$method)
    {
        // Execute
        self::statementExecuteCommand($stmt,$db,$sql,$method);

        // Results
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();
        return $row;
    }

    // Statement Execute Command
    // Example:         SBC::statementExecuteCommand($stmt,$db,$sql,$method);
    public static function statementExecuteCommand(&$stmt,&$db,$sql,$method)
    {
        if (!$stmt->execute())
        {
            // Global
            if ($db)
            {
                $db->close();
            }
            echo 'Could not execute statement ('.$sql.') for '.$method;
            exit;
        }
    }

    // Get Time
    public static function getTime()
    {
        return time();
    }

    // Get IP Address
    public static function getIpAddress()
    {
        $ip_address = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'];

        return $ip_address;
    }

    // Random Digit
    public static function rd()
    {
        return rand(100000, 9999999);
    }

    // Get Session Code
    public static function getSessionCode($input)
    {
        // Quick clean
        $value  = isset($input) ? trim(addslashes($input)) : '';

        // Check max size and character check
        if (isset($value{254}) || preg_match('/[^A-Za-z0-9]/',$value))
        {
            self::userError('Invalid session. Please <a href="https://www.sketchbook.cafe/logout/>logout</a> and try again.');
        }

        // Return
        return $value;
    }

    // ID Clean
    public static function idClean($input)
    {
        // Replace commas
        $input  = str_replace(',',' ',$input);

        // Clean Spaces
        $input  = preg_replace('/\s+/',' ',$input);

        // Trim extra spaces and convert to array
        $input  = explode(' ',trim($input));

        // Unique Values Only
        $input  = array_unique($input);

        // Convert to friendly mysql
        $input  = implode(',',$input);

        // Return
        return $input;
    }

    // Check Number - cannot be 0
    public static function checkNumber($value,$name)
    {
        $method = 'SBC->checkNumber()';

        // Set value
        $value = isset($value) ? (int) $value : 0;
        if ($value < 1)
        {
            self::devError($name.' is not set',$method);
        }

        // return
        return $value;
    }

    // Check Empty - cannot be empty
    public static function checkEmpty($value,$name)
    {
        $method = 'SBC->checkEmpty()';

        // Set value
        $value = isset($value) ? $value : '';
        if (empty($value))
        {
            self::devError($name.' is empty',$method);
        }

        // Return
        return $value;
    }

    // Current Page
    public static function currentPage($ppage,$total)
    {
        $ppage  = isset($ppage) ? (int) $ppage : 0;
        $total  = isset($total) ? (int) $total : 0;

        // Calculate
        $pageno = floor ($total / $ppage);

        // Return
        return $pageno;
    }

    // One Zero
    public static function oneZero($number)
    {
        $number = isset($number) ? (int) $number : 0;
        if ($number != 1)
        {
            $number = 0;
        }
        return $number;
    }
}