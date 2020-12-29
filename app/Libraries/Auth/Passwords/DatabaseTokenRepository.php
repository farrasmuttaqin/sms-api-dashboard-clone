<?php
namespace Firstwap\SmsApiDashboard\Libraries\Auth\Passwords;

use Illuminate\Support\Carbon;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Auth\Passwords\DatabaseTokenRepository as DatabaseTokenRepositoryDefault;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class DatabaseTokenRepository extends DatabaseTokenRepositoryDefault
{

    /**
     * Create a new token record.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @return string
     */
    public function create(CanResetPasswordContract $user)
    {
        $this->deleteExisting($user);

        // We will create a new, random token for the user so that we can e-mail them
        // a safe link to the password reset form. Then we will insert a record in
        // the database so that we can verify the token within the actual reset.
        $token = $this->createNewToken();

        $this->storeToken($user, $token);

        return $token;
    }

    /**
     * Store token to database
     *
     * @return  void
     */
    protected function storeToken(CanResetPasswordContract $user, $token)
    {
        return $user->query()->update($this->getPayload($token, new Carbon));
    }


    /**
     * Determine if a token record exists and is valid.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $token
     * @return bool
     */
    public function exists(CanResetPasswordContract $user, $token)
    {
        return  $user['forget_token'] &&
                $user['expired_token'] &&
                ! $this->tokenExpired($user['expired_token']) &&
                $this->hasher->check($token, $user['forget_token']);
    }


    /**
     * Delete all existing reset tokens from the database.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @return int
     */
    protected function deleteExisting(CanResetPasswordContract $user)
    {
        return $user->query()->update($this->getPayload());
    }


    /**
     * Delete expired tokens.
     *
     * @return int
     */
    public function deleteExpired()
    {
        $expiredAt = Carbon::now()->subSeconds($this->expires)->toDateTimeString();

        return $this->getTable()->where('expired_token', '<', $expiredAt)->update($this->getPayload());
    }


    /**
     * Build the record payload for the table.
     *
     * @param  string  $token
     * @param  string  $expired
     * @return array
     */
    protected function getPayload($token = null, $expired = null)
    {
        if($token){
            $token = $this->hasher->make($token);
        }

        return ['forget_token' => $token, 'expired_token' => $expired];
    }
}