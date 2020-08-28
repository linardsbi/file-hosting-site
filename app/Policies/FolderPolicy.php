<?php

namespace App\Policies;

use App\Folder;
use App\Permission;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FolderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User  $user
     * @param  \App\Folder  $folder
     * @return mixed
     */
    public function view(User $user, Folder $folder)
    {
        return Permission::anyoneCanAccess($folder->id)
                || Permission::userCanAccess($user->id, $folder->id);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user, $parent_id)
    {
        return Permission::anyoneCanAccess($parent_id)
                || Permission::userCanAccess($user->id, $parent_id, "write");
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\Folder  $folder
     * @return mixed
     */
    public function update(User $user, Folder $folder)
    {
        return Permission::anyoneCanAccess($folder->id)
                || Permission::userCanAccess($user->id, $folder->id, "write");
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Folder  $folder
     * @return mixed
     */
    public function delete(User $user, Folder $folder)
    {
        return Permission::anyoneCanAccess($folder->id)
                || Permission::userCanAccess($user->id, $folder->id, "write");
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\Folder  $folder
     * @return mixed
     */
    public function restore(User $user, Folder $folder)
    {
        return Permission::anyoneCanAccess($folder->id)
                || Permission::userCanAccess($user->id, $folder->id, "write");
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Folder  $folder
     * @return mixed
     */
    public function forceDelete(User $user, Folder $folder)
    {
        return Permission::anyoneCanAccess($folder->id)
                || Permission::userCanAccess($user->id, $folder->id, "write");
    }
}
