<?php

namespace App\Observers;

use App\File;
use Illuminate\Support\Facades\DB;
use App\Permission;
use Illuminate\Support\Facades\Storage;
class FileObserver
{
    /**
     * Handle the file "created" event.
     *
     * @param  \App\File  $file
     * @return void
     */
    public function created(File $file)
    {
        $parent = $file->folder;

        foreach(DB::select('select id from permission_types', [1]) as $result) {

            if ($parent && $parent->user_id != $file->user_id) {
                Permission::createForFile($parent->user_id, $result->id, $file->id);
            }

            Permission::createForFile($file->user_id, $result->id, $file->id);
        }
    }

    /**
     * Handle the file "updated" event.
     *
     * @param  \App\File  $file
     * @return void
     */
    public function updated(File $file)
    {
        //
    }

    /**
     * Handle the file "deleted" event.
     *
     * @param  \App\File  $file
     * @return void
     */
    public function deleted(File $file)
    {
        //
    }

    /**
     * Handle the file "restored" event.
     *
     * @param  \App\File  $file
     * @return void
     */
    public function restored(File $file)
    {
        //
    }

    /**
     * Handle the file "force deleted" event.
     *
     * @param  \App\File  $file
     * @return void
     */
    public function forceDeleted(File $file)
    {
        Storage::delete("/uploads/{$file->name}");
        unlink(public_path('thumbnails') . "/$file->id-sm.png");
    }
}
