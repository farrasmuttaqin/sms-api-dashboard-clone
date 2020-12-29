<?php

namespace Firstwap\SmsApiDashboard\Entities;

use Firstwap\SmsApiDashboard\Entities\ApiUser;
use Firstwap\SmsApiDashboard\Entities\User;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
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
    protected $table = 'CLIENT';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'client_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Define a one-to-many relationship.
     * Get the api users that belong to the client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function apiUsers()
    {
        return $this->hasMany(ApiUser::class, 'client_id', 'client_id');
    }

}
