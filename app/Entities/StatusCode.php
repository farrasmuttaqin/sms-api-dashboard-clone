<?php

namespace Firstwap\SmsApiDashboard\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class StatusCode extends Model
{

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_bill_u_msg';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'DELIVERY_STATUS';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Status error code cache key
     */
    const STATUS_CACHE_KEY = 'status_error_code';

    /**
     * Get Delivery Status Code
     *
     * @param  Illuminate\Database\Eloquent\Builder $query
     * @return array
     */
    public function scopeStatusErrorCode($query)
    {
        $key = self::STATUS_CACHE_KEY;
        $minutes = Carbon::parse('tomorrow');

        return Cache::remember($key, $minutes, function () use ($query) {
                    return $query
                        ->get()
                        ->pluck('dashboard_status','error_code')
                        ->all();
                });
    }

}
