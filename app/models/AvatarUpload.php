<?php

class AvatarUpload
{
    private $rd = 0;
    private $ip_address = '';

    // Construct
    public function __construct()
    {
        // Globals
        global $db,$User;

        // Functions + Classes
        sbc_class('ImageFile');
        sbc_class('IpTimer');
        sbc_function('rd');

        // Initialize Vars
        $this->rd           = rd();
        $this->ip_address   = $_SERVER['REMOTE_ADDR'];

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
            error('Dev error: there is no file for ImageFile->hasfile()');
        }

        // Open Connection
        $db->open();

        // User Required
        $User->required($db);

        // IP Timer
        $IpTimer = new IpTimer($db);
        $IpTimer->setColumn('avatar');
        $IpTimer->checkTimer($db);

        // Create Avatar
        $this->createAvatar($db,$User,$ImageFile);

        // IP Timer
        $IpTimer->update($db);

        // Close Connection
        $db->close();

        // We'll use error to close everything. Then redirect to URL
        error('r1000 https://www.sketchbook.cafe/settings/avatar/');
    }

    // Create Avatar
    private function createAvatar(&$db,&$User,&$ImageFile)
    {
        // Get File Information
        $avatarInfo = $ImageFile->getInfo();

        // User Vars
        $user_id        = $User->getUserId();
        $old_avatar_id  = $User->avatar_id;

        // Set SQL Vars
        $time           = time();
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
        if (!$stmt->execute())
        {
            error('Could not execute statement (insert new avatar) for AvatarUpload->createAvatar()');
        }
        $stmt->close();

        // Get New Avatar ID
        $sql = 'SELECT id
            FROM avatars
            WHERE rd=?
            AND user_id=?
            AND date_created=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii',$rd,$user_id,$time);
        if (!$stmt->execute())
        {
            error('Could not execute statement (find new avatar) for AvatarUpload->createAvatar()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

        // Avatar ID?
        $avatar_id  = isset($row['id']) ? (int) $row['id'] : 0;
        if ($avatar_id < 1)
        {
            error('Dev error: Could not insert new avatar in AvatarUpload->createAvatar()');
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
        if (!$stmt->execute())
        {
            error('Could not execute statement (update avatar info) for AvatarUpload->createAvatar()');
        }
        $stmt->close();

        // Update user
        $sql = 'UPDATE users
            SET avatar_id=?,
            avatar_url=?
            WHERE id=?
            LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('isi',$avatar_id,$avatar_url,$user_id);
        if (!$stmt->execute())
        {
            error('Could not execute statement (update user) for AvatarUpload->createAvatar()');
        }
        $stmt->close();

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
            if (!$stmt->execute())
            {
                error('Could not execute statement (update old avatar) for AvatarUpload->createAvatar()');
            }

            // Delete Old Avatar File
            $this->deleteFile($db,$old_avatar_id);
        }
    }

    // Delete File
    final private function deleteFile(&$db,$old_avatar_id)
    {
        // Double check!
        if ($old_avatar_id < 1)
        {
            error('Dev error: $old_avatar_id is not set for AvatarUpload->deleteFile()');
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
        if (!$stmt->execute())
        {
            error('Could not execute statement (verify avatar info) for AvatarUpload->deleteFile()');
        }
        $result = $stmt->get_result();
        $row    = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $stmt->close();

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
        if (!$stmt->execute())
        {
            error('Could not execute statement (delete from database) for AvatarUpload->deleteFile()');
        }
        $stmt->close();
    }
}