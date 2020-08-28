<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Permission extends Model
{
    protected $table = "access_permissions";

    protected $fillable = ["permission_id", "user_id", "folder_id", "file_id"];

    public static function createForFolder($userId, $permissionId, $itemId) {
        Permission::createFor($userId, $permissionId, $itemId, true);
    }

    public static function createForFile($userId, $permissionId, $itemId) {
        Permission::createFor($userId, $permissionId, $itemId);
    }

    public static function createFor($userId, $permissionId, $itemId, $folder = false) {
        $type = ($folder) ? "folder_id" : "file_id";
        $permission = new Permission([
            'permission_id' => $permissionId,
            "user_id" => $userId,
            $type => $itemId,
        ]);

        $permission->save();
    }

    /**
     * @param $user_id
     * @param $item_id (file or folder id)
     * @param $access_type (by default, check if user has read permissions)
     */
    public static function userCanAccess($user_id, $item_id, $access_type = "read") {

        if (is_null($user_id)) return false;

        $id = DB::select("select pt.id from access_permissions as ap
                            left join permission_types as pt on ap.permission_id = pt.id
                            where ap.user_id='{$user_id}'
                            and (ap.folder_id='{$item_id}' or ap.file_id='{$item_id}')
                            and pt.name='{$access_type}'", [1]);

        return isset($id[0]);
    }

    public static function anyoneCanAccess($item_id, $access_type = "read") {
        $id = DB::select("select pt.id from access_permissions as ap
                            left join permission_types as pt on ap.permission_id = pt.id
                            left join users as u on ap.user_id = u.id
                            where u.email='guest'
                            and (ap.folder_id='{$item_id}' or ap.file_id='{$item_id}')
                            and pt.name='{$access_type}'", [1]);

        return isset($id[0]);
    }
}
