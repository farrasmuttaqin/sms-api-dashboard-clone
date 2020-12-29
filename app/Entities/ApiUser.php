<?php

namespace Firstwap\SmsApiDashboard\Entities;

use Firstwap\SmsApiDashboard\Entities\Message;
use Firstwap\SmsApiDashboard\Entities\User;
use Illuminate\Database\Eloquent\Model;

class ApiUser extends Model
{

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_sms_api';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'USER';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Define a many-to-many relationship.
     * The api user that belong to the sms api dashboard user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function apiDashboardUsers()
    {
        return $this->belongsToMany(User::class, env('API_DASHBOARD_DB_DATABASE').'.'.'AD_USER_APIUSER', 'api_user_id', 'ad_user_id');
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return env('SMS_API_DB_DATABASE').'.'.$this->table;
    }
}
