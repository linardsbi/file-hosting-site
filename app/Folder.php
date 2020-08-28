<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use \App\Http\Traits\UUID;

    protected $fillable = [
        "user_id", "name", "parent_id", "type_id"
    ];

    public function files()
    {
        return $this->hasMany('App\File');
    }

    public function parent() {
        return (is_null($this->parent_id)) ? null : Folder::find($this->parent_id);
    }
}
