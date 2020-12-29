<?php

namespace Firstwap\SmsApiDashboard\Policies;

use Firstwap\SmsApiDashboard\Entities\User;
use Firstwap\SmsApiDashboard\Entities\Privilege;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{

    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @param  \Firstwap\SmsApiDashboard\Entities\User  $user
     * @param  \Firstwap\SmsApiDashboard\Entities\User  $model
     * @return mixed
     */
    public function index(User $user)
    {
        return $user->hasPrivileges(Privilege::USER_PAGE_READ);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \Firstwap\SmsApiDashboard\Entities\User  $user
     * @param  \Firstwap\SmsApiDashboard\Entities\User  $model
     * @return mixed
     */
    public function create(User $user, User $model = null)
    {
        $approved = false;
        if ($approved = $user->hasPrivileges(Privilege::USER_PAGE_WRITE)) {
            if ($model) {
                if (!$approved = $user->hasPrivileges(Privilege::USER_ACC_SYSTEM)) {
                    $approved = $user->hasPrivileges(Privilege::USER_ACC_COMPANY) && $user->client_id == $model->client_id;
                }
            }
        }
        return $approved;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \Firstwap\SmsApiDashboard\Entities\User  $user
     * @param  \Firstwap\SmsApiDashboard\Entities\User  $model
     * @return mixed
     */
    public function update(User $user, User $model = null)
    {
        $approved = $user->hasPrivileges(Privilege::USER_PAGE_WRITE);

        if ($model) {
            if (!$approved = $user->hasPrivileges(Privilege::USER_ACC_SYSTEM)) {
                if ($user->hasPrivileges(Privilege::USER_ACC_COMPANY)) {
                    $approved = $user->client_id == $model->client_id;

                    /**
                     * Check if user want to change the client_id
                     */
                    $dirty = $model->getDirty();
                    if (isset($dirty['client_id'])) {
                        $approved = $user->client_id == $dirty['client_id'];
                    }
                }else{
                    $approved = $user->getKey() === $model->getKey();
                }
            }
        }

        return $approved;
    }

    /**
     * Determine whether the user can update role and API users other user.
     *
     * @param  \Firstwap\SmsApiDashboard\Entities\User  $user
     * @param  \Firstwap\SmsApiDashboard\Entities\User  $model
     * @return mixed
     */
    public function grant(User $user, User $model = null)
    {
        $approved = false;

        if (!$approved = $user->hasPrivileges(Privilege::USER_ACC_SYSTEM)) {
            if ($approved = $user->hasPrivileges(Privilege::USER_ACC_COMPANY)) {
                if ($model) {
                    $approved = $user->client_id == $model->client_id
                            && $user->getKey() !== $model->getKey();
                }
            }
        }

        return $approved;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \Firstwap\SmsApiDashboard\Entities\User  $user
     * @param  \Firstwap\SmsApiDashboard\Entities\User  $model
     * @return mixed
     */
    public function delete(User $user, User $model = null)
    {
        $approved = $user->hasPrivileges(Privilege::USER_PAGE_DELETE);

        if ($model) {
            if (!$approved = $user->hasPrivileges(Privilege::USER_ACC_SYSTEM)) {
                if ($user->hasPrivileges(Privilege::USER_ACC_COMPANY)) {
                    $approved = $user->client_id == $model->client_id;
                }
            }
        }

        return $approved;
    }

}
