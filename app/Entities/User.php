<?php

namespace Firstwap\SmsApiDashboard\Entities;

use Firstwap\SmsApiDashboard\Entities\ApiUser;
use Firstwap\SmsApiDashboard\Entities\Client;
use Firstwap\SmsApiDashboard\Entities\Role;
use Firstwap\SmsApiDashboard\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{

    use SoftDeletes,
        Notifiable;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'api_dashboard';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'AD_USER';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ad_user_id';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['client_id', 'name', 'email', 'password'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['forget_token', 'password', 'remember_token', 'expired_token'];

    /**
     * The attributes that should be visible in serialization.
     *
     * @var array
     */
    protected $visible = ['active', 'ad_user_id', 'created_at', 'email', 'name', 'company_name'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['company_name'];

    /**
     * Constant representing a session key to store user timezone value
     *
     * @var string
     */
    const TIMEZONE_SESSION_KEY = 'user_timezone';

    /**
     * Define a many-to-many relationship.
     * The roles that belong to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'AD_ROLE_USER', 'user_id', 'role_id');
    }

    /**
     * Define a many-to-many relationship.
     * The api user that belong to the sms api dashboard user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function apiUsers()
    {
        return $this->belongsToMany(tap(new ApiUser, function($instance) {
            return $instance->setConnection($this->connection);
        }), "AD_USER_APIUSER", 'ad_user_id', 'api_user_id');
    }

    /**
     * Define an inverse one-to-many relationship.
     * Get the client that owns user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Delete user avatar file
     *
     * @return void
     */
    public function deleteAvatar($value = null)
    {
        $value = $this->getOriginal('avatar');

        if(Storage::exists($value)){
            Storage::delete($value);
        }
    }

    /**
     * Mutator for attribute password
     *
     * @return  void
     */
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = \Hash::needsRehash($value)
                                            ? bcrypt($value)
                                            : $value;
        }
    }

    /**
     * Accessor for attribute avatar
     * This accessor will check the avatar is available on filesystem or not
     *
     * @return  string
     */
    public function getAvatarUrlAttribute()
    {
        $value = $this->getAttribute('avatar');

        return is_file(storage_path('app') . DIRECTORY_SEPARATOR . $value)
                    ? url("img/$value")
                    : url('images/avatars/nopic.png');
    }

    /**
     * Accessor for attribute isAdmin
     *
     * @return  bool
     */
    public function getIsAdminAttribute()
    {
       return $this->hasPrivileges(Privilege::USER_ACC_SYSTEM);
    }

    /**
     * Accessor for attribute clientName
     * This accessor will check the avatar is available on filesystem or not
     *
     * @return  string
     */
    public function getCompanyNameAttribute()
    {
        return $this->client ? $this->client->company_name : null;
    }

    /**
     * Get current User timezone
     *
     * @return string
     */
    public function getTimezoneAttribute()
    {
        $value = session(self::TIMEZONE_SESSION_KEY, 0);

        return $value <= 0 ? $value : '+'.$value;
    }


    /**
     * Scope to get user except authenticated user
     *
     * @param  Builder $query
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotMe(Builder $query, $user): Builder
    {
        return $query->where($user->getKeyName(), '!=', $user->getKey());
    }

    /**
     * Check the user has privileges
     *
     * @param   String|array    $privilegeName
     * @param   bool            $matchAll
     * @return  bool
     */
    public function hasPrivileges($privilegesName, $matchAll = false): bool
    {
        $matched = [];
        $privilegesName = array_wrap($privilegesName);
        $privileges = $this->getPrivileges();

        foreach ($privilegesName as $privilege) {
            if (in_array($privilege, $privileges)) {
                if (!$matchAll) {
                    return true;
                }
                $matched[] = $privilege;
            }
        }

        return count($matched) === count($privilegesName);
    }

    /**
     * Get Current User Privileges
     *
     * @return array
     */
    public function getPrivileges(): array
    {
        if (!$privileges = $this->getPrivilegesFromSession()) {
            $privileges = Privilege::user($this)->get()
                            ->pluck('privilege_name')
                            ->all();

            $this->storePrivilegesToSession($privileges);
        }

        return $privileges;
    }

    /**
     * Store privileges to session
     *
     * @return void
     */
    protected function storePrivilegesToSession($privileges)
    {
        session([Privilege::SESSION_PRIVILEGE_KEY => $privileges]);
    }

    /**
     * Get privileges from session
     *
     * @return array
     */
    protected function getPrivilegesFromSession()
    {
        return session(Privilege::SESSION_PRIVILEGE_KEY,[]);
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return env('API_DASHBOARD_DB_DATABASE').'.'.$this->table;
    }

    /**
     * Create a new model instance for a related model.
     *
     * @param  string  $class
     * @return mixed
     */
    protected function newRelatedInstance($class)
    {
        return is_string($class) ? parent::newRelatedInstance($class) : $class;
    }
}
