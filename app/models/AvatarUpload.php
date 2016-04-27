<?php
// @author          Jonathan Maltezo (Kameloh)
// @lastUpdated     2016-04-27

use SketchbookCafe\SBC\SBC as SBC;
use SketchbookCafe\ImageFile\ImageFile as ImageFile;
use SketchbookCafe\UserTimer\UserTimer as UserTimer;

class AvatarUpload
{
    private $rd = 0;
    private $ip_address = '';

    // Construct
    public function __construct(&$obj_array)
    {
        $method = 'AvatarUpload->__construct()';

        // Set Objects
        $db     = &$obj_array['db'];
        $User   = &$obj_array['User'];

        // Initialize Vars
        $this->rd           = SBC::rd();
        $this->ip_address   = SBC::getIpAddress();

        // Image File
        $ImageFile = new ImageFile(array
        (
            'name'          => 'imagefile',
            'user_id'       => 1,
            'max_filesize'  => 102400, // 102400 bytes = 100KB
            'required'      => 1,
            'allow_gif'     => 1,
            'allow_png'     => 1,
            'allow_jpg'     => 1, 
            'allow_apng'    => 0, 
            'width_min'     => 10,
            'width_max'     => 200,
            'height_min'    => 10,
            'height_max'    => 200,
        ));
        $ImageFile->sendFile();

        // Check?
        if (!$ImageFile->hasFile())
        {
            SBC::devError('there is no file',$method);
        }

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);
        $user_id = $User->getUserId();

        // User Timer
        $UserTimer = new UserTimer(array
        (
            'user_id'   => $user_id, 
        ));
        $UserTimer->setColumn('change_avatar');
        $UserTimer->checkTimer($db);

        // Create Avatar
        $this->createAvatar($db,$User,$ImageFile);

        // User Timer
        $UserTimer->update($db);

        // Close Connection
        $db->close();

        // We'll use error to close everything. Then redirect to URL
        error('r1000 https://www.sketchbook.cafe/settings/avatar/');
    }

    // Create Avatar
    private function createAvatar(&$db,&$User,&$ImageFile)
    {
        $method = 'AvatarUpload->createAvatar()';

        // Get File Information
        $avatarInfo = $ImageFile->getInfo();

        // User Vars
        $user_id        = $User->getUserId();
        $old_avatar_id  = $User->avatar_id;

        // Set SQL Vars
        $time           = SBC::getTime();
        $ip_address     = $this->ip_address;
        $rd             = $this->rd;
        $image_rd       = $avatarInfo['rd'];
        $image_rd_code  = $avatarInfo['rd_code'];
        $image_filetype = $avatarInfo['filetype'];
        $image_filesize = $avatarInfo['filesize'];

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Create New Avatar in Database
        $sql = 'INSERT INTO avatars
            SET rd=?, 
            rd_code=?,
            user_id=?, 
            ip_created=?, 
            date_created=?, 
            filetype=?, 
            filesize=?,
            ispending=1,
            isdeleted=1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('isisisi',$rd,$image_rd_code,$user_id,$ip_address,$time,$image_filetype,$image_filesize);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Get New Avatar ID
        $sql = 'SELECT id
            FROM avatars
            WHERE rd=?
            AND user_id=?
            AND date_created=?
            LIMIT 1';
        $stmt   = $db->prepare($sql);
        $stmt->bind_param('iii',$rd,$user_id,$time);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Avatar ID?
        $avatar_id  = isset($row['id']) ? (int) $row['id'] : 0;
        if ($avatar_id < 1)
        {
            SBC::devError('Could not insert new avatar',$method);
        }

        // Create Filenames
        $filename   = $avatar_id.'-'.$user_id.'-'.$image_rd_code.'.'.$image_filetype;
        $avatar_url = 'avatars/' . $filename;

        // Let's create the file
        $createimage = @copy($ImageFile->file,$avatar_url) or die('Could not create new file');

        // Update avatar information
        $sql = 'UPDATE avatars
            SET avatar_url=?,
            ispending=0, 
            isdeleted=0
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('si',$avatar_url,$avatar_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Update user
        $sql = 'UPDATE users
            SET avatar_id=?,
            avatar_url=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('isi',$avatar_id,$avatar_url,$user_id);
        SBC::statementExecute($stmt,$db,$sql,$method);

        // Do we have an old avatar?
        if ($old_avatar_id > 0)
        {
            // Update
            $sql = 'UPDATE avatars
                SET isdeleted=1
                WHERE id=?
                LIMIT 1';
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i',$old_avatar_id);
            SBC::statementExecute($stmt,$db,$sql,$method);

            // Delete Old Avatar File
            $this->deleteFile($db,$old_avatar_id);
        }
    }

    // Delete File
    final private function deleteFile(&$db,$old_avatar_id)
    {
        $method = 'AvatarUpload->deleteFile()';

        // Double check!
        if ($old_avatar_id < 1)
        {
            SBC::devError('$old_avatar_id is not set',$method);
        }

        // Switch
        $db->sql_switch('sketchbookcafe');

        // Verify Avatar Info
        $sql = 'SELECT id, user_id, rd_code, filetype, isdeleted
            FROM avatars
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$old_avatar_id);
        $row    = SBC::statementFetchRow($stmt,$db,$sql,$method);

        // Do we have an ID?
        $avatar_id  = isset($row['id']) ? (int) $row['id'] : 0;
        if ($avatar_id < 1)
        {
            return null;
        }

        // Can we delete it?
        if ($row['isdeleted'] != 1)
        {
            return null;
        }

        // Delete File
        $filename   = $row['id'].'-'.$row['user_id'].'-'.$row['rd_code'].'.'.$row['filetype'];
        $directory  = 'avatars';
        unlink($directory . '/' . $filename);

        // Delete from database
        $sql = 'DELETE FROM avatars
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i',$old_avatar_id);
        SBC::statementExecute($stmt,$db,$sql,$method);
    }
}