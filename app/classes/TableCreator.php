<?php
/**
*
* Table Creator Class
*
* @author       Jonathan Maltezo (Kameloh)
* @copyright    (c) 2016, Jonathan Maltezo (Kameloh)
* @lastupdated  2016-04-27
*
*/
namespace SketchbookCafe\TableCreator;

use SketchbookCafe\SBC\SBC as SBC;

class TableCreator
{
    private $tablename = '';
    private $database = '';
    private $columns = [];
    private $hasinfo = 0;

    // Construct
    public function __construct($tablename,$database,$columns)
    {
        $method = 'TableCreator->__construct()';

        // Initalize
        $ref    = '';
        $cinfo  = '';

        // Check Values
        if (empty($tablename))
        {
            SBC::devError('$tablename is not set',$method);
        }
        if (empty($database))
        {
            SBC::devError('$database is not set',$method);
        }
        if (empty($columns))
        {
            SBC::devError('$columns is not set',$method);
        }

        // Set values
        $this->tablename    = $this->validateVar($tablename,0); // 0 - not column
        $this->database     = $this->validateVar($database,0); // 0 - not column
        $this->columns      = $columns;

        // Check columns
        $i      = 0;
        $count  = count($this->columns);
        $key    = array_keys($this->columns);
        while ($i < $count)
        {
            // Values
            $ref    = $this->validateVar($key[$i],1); // 1 - column
            $cinfo  = $this->validateVar($this->columns[$ref],1); // 1 - column
            $i++;
        }

        // Has Info
        $this->hasinfo = 1;
    }

    // Has Info
    final public function hasInfo()
    {
        $method = 'TableCreator->hasInfo()';

        if ($this->hasinfo != 1)
        {
            SBC::devError('$hasinfo is not set',$method);
        }
    }

    // List Columns
    final public function listColumns()
    {
        $method = 'TableCreator->listColumns()';

        // Has info?
        $this->hasInfo();

        // Initialize
        $ref    = '';
        $cinfo  = '';
        $value  = '';

        // Count Array
        $i      = 0;
        $count  = count($this->columns);
        $key    = array_keys($this->columns);
        while ($i < $count)
        {
            // Values
            $ref    = $key[$i];
            $cinfo  = $this->columns[$ref];
            $value .= '<div>'.$ref.' '.$cinfo.'</div>';
            $i++;
        }

        // Return value
        return $value;
    }

    // Validate Var
    // type 0:  table, database
    // type 1:  columns
    private function validateVar($value,$type)
    {
        $method = 'TableCreator->validateVar()';

        // Quick clean
        $value  = isset($value) ? trim(addslashes($value)) : '';
        $type   = isset($type) ? (int) $type : 0;
        if ($type < 0 || $type > 1)
        {
            SBC::devError('invalid $type',$method);
        }

        // Not empty
        if (empty($value))
        {
            SBC::devError('$value is not set',$method);
        }

        // Length check
        if (isset($value{255}))
        {
            SBC::devError('value ('.$value.') is too long',$method);
        }

        // Check for valid characters only
        // Type 0: tables, databases
        if ($type == 0)
        {
            // Database and Table Name Only
            if (preg_match('/[^A-Za-z0-9_]/',$value))
            {
                SBC::devError('invalid value('.$value.')',$method);
            }
        }
        else if ($type == 1)
        {
            // Columns
            if (preg_match('/[^A-Za-z0-9_-]\s\(\)/',$value))
            {
                SBC::devError('invalid value('.$value.')',$method);
            }
        }

        // Return value
        return $value;
    }

    // Create Table
    final public function createTable(&$db)
    {
        $method = 'TableCreator->CreateTable()';

        // Has Info?
        $this->hasinfo();

        // Initialize Vars
        $ref    = '';
        $cinfo  = '';

        // Switch
        $db->sql_switch($this->database);

        // Check if the table exists
        $sql    = 'SHOW TABLES LIKE \''.$this->tablename.'\'';
        $result = $db->sql_query($sql);
        $rownum = $db->sql_numrows($result);

        // Count Array
        $i      = 0;
        $count  = count($this->columns);
        $key    = array_keys($this->columns);

        // No table?
        if ($rownum < 1)
        {
            // Create
            $sql = 'CREATE TABLE '.$this->tablename.'( ';

            // Loop columns
            while ($i < $count)
            {
                // Vars
                $ref    = $key[$i];
                $cinfo  = $this->columns[$ref];
                $sql    .= $ref.' '.$cinfo;

                // Commas
                if ($i < ($count - 1))
                {
                    $sql .= ', ';
                }

                $i++;
            }

            // Key[0] as id?
            if ($key[0] === 'id')
            {
                $sql .= ', PRIMARY KEY(id)';
            }

            // End
            $sql .= ' )';

            // Create Table
            $create = $db->sql_query($sql);
        }
        else
        {
            // Update table columns instead!
            $sql    = 'SELECT * FROM '.$this->tablename.' LIMIT 1';
            $result = $db->sql_query($sql);

            // Fetch fields, not results!
            $rownum = mysqli_fetch_fields($result);

            // Initialize Vars
            $hascolumn['blank'] = 0;

            // Check now
            foreach ($rownum as $val)
            {
                $test               = $val->name;
                $hascolumn[$test]   = 1;
            }

            // Start updating columns
            $i = 0;
            while ($i < $count)
            {
                // Vals
                $ref    = $key[$i];
                $cinfo  = $this->columns[$ref];

                // Does the table have the column?
                $thiscolumn = isset($hascolumn[$ref]) ? $hascolumn[$ref] : 0;
                if ($thiscolumn != 1)
                {
                    // ID?
                    if ($ref == 'id')
                    {
                        // Alter Table (id as primary key)
                        $sql    = 'ALTER TABLE '.$this->tablename.' 
                            ADD '.$ref.' '.$cinfo.' PRIMARY KEY';
                        $alter  = $db->sql_query($sql);
                    }
                    else
                    {
                        // Alter Table (normal column)
                        $sql    = 'ALTER TABLE '.$this->tablename.'
                            ADD COLUMN '.$ref.' '.$cinfo;
                        $alter  = $db->sql_query($sql);
                    }
                }
                $i++;
            }
        }
    }
}