<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FileType extends Model
{
    public static function getIdByName($name) {
        return FileType::where("name", $name)->first()->id;
    }

    public static function getIdByType($type) {
        $row = FileType::where("allowed_types", "like", "%{$type}%")->first();
        if (!$row) {
            $row = FileType::where("name", "generic")->first();
        }
        return $row->id;
    }
}
