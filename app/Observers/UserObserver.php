<?php

namespace Firstwap\SmsApiDashboard\Observers;

use Firstwap\SmsApiDashboard\Entities\User;

class UserObserver
{

    /**
     * Listen to the User deleting event.
     *
     * @param  \Firstwap\SmsApiDashboard\Entities\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        if(array_key_exists('avatar', $user->getDirty())){
            $user->deleteAvatar();
        }
    }

}
