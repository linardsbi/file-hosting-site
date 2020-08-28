<?php

namespace App\Observers;

use App\Folder;
use Illuminate\Support\Facades\DB;
use App\Permission;

class FolderObserver
{
    /**
     * Handle the folder "created" event.
     *
     * @param  \App\Folder  $folder
     * @return void
     */
    public function created(Folder $folder)
    {
        $parent = $folder->parent();

        foreach(DB::select('select id from permission_types', [1]) as $result) {

            if ($parent && $parent->user_id != $folder->user_id) {
                Permission::createForFolder($parent->user_id, $result->id, $folder->id);
            }

            Permission::createForFolder($folder->user_id, $result->id, $folder->id);
        }
    }

    /**
     * Handle the folder "updated" event.
     *
     * @param  \App\Folder  $folder
     * @return void
     */
    public function updated(Folder $folder)
    {
        //
    }

    /**
     * Handle the folder "deleted" event.
     *
     * @param  \App\Folder  $folder
     * @return void
     */
    public function deleted(Folder $folder)
    {
        //
    }

    /**
     * Handle the folder "restored" event.
     *
     * @param  \App\Folder  $folder
     * @return void
     */
    public function restored(Folder $folder)
    {
        //
    }

    /**
     * Handle the folder "force deleted" event.
     *
     * @param  \App\Folder  $folder
     * @return void
     */
    public function forceDeleted(Folder $folder)
    {
        //
    }
}
