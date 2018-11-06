n <?php
/**
 * Created by PhpStorm.
 * User: ducchien
 * Date: 25/09/2018
 * Time: 17:36
 */

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
class CategoryPolicy
{
    use HandlesAuthorization;
    public function view(User $user)
    {
        return $user->hasAccess(['category.view']);
    }

    /**
     * Determine whether the user can create blog.
     *
     * @param  \App\User $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasAccess(['category.create']);
    }

    /**
     * Determine whether the user can update the blog.
     *
     * @param  \App\User $user
     *
     * @return mixed
     */
    public function update(User $user)
    {
        return $user->hasAccess(['category.update']);
    }

    /**
     * Determine whether the user can delete the blog.
     *
     * @param  \App\User $user
     *
     * @return mixed
     */
    public function delete(User $user)
    {
        return $user->hasAccess(['category.delete']);
    }

}
