<?php
// @author          Kameloh
// @lastUpdated     2016-05-03

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\Message\Message as Message;
use SketchbookCafe\TableChallengeCategory\TableChallengeCategory as TableChallengeCategory;

class AdminChallengeCategoriesSubmit
{
    private $time = 0;
    private $user_id = 0;
    private $rd = 0;
    private $ip_address = '';
    private $name = '';
    private $name_code = '';

    private $category_id = 0;

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AdminChallengeCategoriesSubmit->__construct()';

        // Initialize
        $db                 = &$obj_array['db'];
        $User               = &$obj_array['User'];
        $this->rd           = SBC::rd();
        $this->ip_address   = SBC::getIpAddress();
        $this->time         = SBC::getTime();

        // Category
        $NameObject = new Message(array
        (
            'name'          => 'name',
            'min'           => 1,
            'column_max'    => 250,
            'nl2br'         => 0,
            'basic'         => 0,
            'ajax'          => 0,
            'images'        => 0,
            'videos'        => 0,
        ));
        $NameObject->insert($_POST['category']);

        // Set
        $this->name         = $NameObject->getMessage();
        $this->name_code    = $NameObject->getMessageCode();

        // Open Connection
        $db->open();

        // Admin Required
        $User->admin($db);
        $User->requireAdminFlag('challenge_categories');
        $this->user_id = $User->getUserId();

        // Count Categories
        $this->countCategories($db);

        // Create Category
        $this->createCategory($db);

        // Create Tables
        $this->createTables($db);

        // Mark as not deleted
        $this->updateCategory($db);

        // Close Connection
        $db->close();

        // Header
        header('Location: https://www.sketchbook.cafe/admin/challenge_categories/');
        exit;
    }

    // Count Categories
    final private function countCategories(&$db)
    {
        $method = 'AdminChallengeCategoriesSubmit->countCategories()';

        $max_categories = 5;

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Count (non statement)
        $sql = 'SELECT COUNT(*)
            FROM challenge_categories
            WHERE isdeleted=0';
        $result = $db->sql_query($sql);
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        // Total
        $total  = isset($row[0]) ? (int) $row[0] : 0;
        if ($total >= $max_categories)
        {
            SBC::devError('Max categories reached('.$max_categories.')',$method);
        }
    }

    // Create Category
    final private function createCategory(&$db)
    {
        $method = 'AdminChallengeCategoriesSubmit->createCategory()';

        // Initialize
        $user_id    = $this->user_id;
        $rd         = $this->rd;
        $time       = $this->time;
        $ip_address = $this->ip_address;
        $name       = SBC::checkEmpty($this->name,'$this->name');
        $name_code  = SBC::checkEmpty($this->name_code,'$this->name_code');

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Insert new category
        $sql = 'INSERT INTO challenge_categories
            SET rd=?,
            user_id=?,
            date_created=?,
            date_updated=?,
            ip_created=?,
            ip_updated=?,
            name=?,
            name_code=?,
            isdeleted=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iiiissss',$rd,$user_id,$time,$time,$ip_address,$ip_address,$name,$name_code);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Get ID
        $sql = 'SELECT id
            FROM challenge_categories
            WHERE rd=?
            AND user_id=?
            AND date_created=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('iii',$rd,$user_id,$time);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Id?
        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id < 1)
        {
            SBC::devError('Could not insert new category into database',$method);
        }
        $this->category_id = $id;
    }

    // Create Tables
    final private function createTables(&$db)
    {
        $method = 'AdminChallengeCategoriesSubmit->createTables()';
        $category_id = SBC::checkNumber($this->category_id,'$this->category_id');

        // Challenge Table
        $TableChallengeCategory = new TableChallengeCategory($category_id);
        $TableChallengeCategory->checkTables($db);
    }

    // Update Category
    final private function updateCategory(&$db)
    {
        $method = 'AdminChallengeCategoriesSubmit->updateCategory';
        $id     = SBC::checkNumber($this->category_id,'$this->category_id');
        $db->sql_switch('sketchbookcafe');

        // Update category
        $sql = 'UPDATE challenge_categories
            SET isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}