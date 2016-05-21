<?php
// @author          Kameloh
// @lastUpdated     2016-05-21
namespace SketchbookCafe\OnlineList;

use SketchbookCafe\SBC\SBC as SBC;

class OnlineList
{
    private $online_result;
    private $online_rownum;
    private $guests = 0;
    private $online_list_data = [];

    private $obj_array = [];

    // Construct
    public function __construct(&$obj_array)
    {
        $this->obj_array = &$obj_array;
    }

    // Process
    final public function process()
    {
        $method = 'OnlineList->process()';

        // Initialize
        $db     = &$this->obj_array['db'];
        $Member = &$this->obj_array['Member'];

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Get Users
        $sql = 'SELECT id
            FROM online_users';
        $this->online_result = $db->sql_query($sql);
        $this->online_rownum = $db->sql_numrows($this->online_result);

        // Add members to list
        $Member->idAddRows($this->online_result,'id');

        // Count Guests
        $sql = 'SELECT COUNT(*)
            FROM online_ip';
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        // Set Total Guests
        $this->guests   = isset($row[0]) ? (int) $row[0] : 0;
    }

    // Get Result
    final public function getResult()
    {
        return $this->online_result;
    }

    // Get Rownum
    final public function getRownum()
    {
        return $this->online_rownum;
    }

    // Get Online List Data
    final public function getOnlineListData()
    {
        $array = array
        (
            'result'    => &$this->online_result,
            'rownum'    => &$this->online_rownum,
            'guests'    => &$this->guests,
        );
        return $array;
    }
}