<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CityPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the role.
     *
     * @param  \App\User                         $user
     * @param  \App\Repositories\Categories\Role $role
     *
     * @return mixed
     */
    public function view(User $user)
    {
        return $user->hasAccess(['city.view']);
    }

    /**
     * Determine whether the user can create role.
     *
     * @param  \App\User $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasAccess(['city.create']);
    }

    /**
     * Determine whether the user can update the role.
     *
     * @param  \App\User                         $user
     * @param  \App\Repositories\Categories\Role $role
     *
     * @return mixed
     */
    public function update(User $user)
    {
        return $user->hasAccess(['city.update']);
    }

    /**
     * Determine whether the user can delete the role.
     *
     * @param  \App\User                         $user
     * @param  \App\Repositories\Categories\Role $role
     *
     * @return mixed
     */
    public function delete(User $user)
    {
        return $user->hasAccess(['city.delete']);
    }
}
