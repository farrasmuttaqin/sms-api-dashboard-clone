<?php

namespace Firstwap\SmsApiDashboard\Entities;

use Firstwap\SmsApiDashboard\Entities\Privilege;
use Firstwap\SmsApiDashboard\Entities\User;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

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
    protected $table = 'AD_ROLES';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'role_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['role_name'];


    /**
     * Define a many-to-many relationship.
     * The privileges that belong to the role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function privileges()
    {
        return $this->belongsToMany(Privilege::class, 'AD_PRIVILEGE_ROLE', 'role_id', 'privilege_id');
    }

    /**
     * Define a many-to-many relationship.
     * The users that belong to the role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'AD_ROLE_USER', 'role_id', 'user_id');
    }

}
