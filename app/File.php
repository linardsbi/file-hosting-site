<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use \App\Http\Traits\UUID, SoftDeletes;

    protected $fillable = [
        'name', 'bytes', 'expires_at', "user_id", "type_id"
    ];

    public function folder() {
        return $this->hasOne("App\Folder", "id", "folder_id");
    }

    public function extension() {
        $str = explode(".", $this->real_name);
        return end($str);
    }
}
