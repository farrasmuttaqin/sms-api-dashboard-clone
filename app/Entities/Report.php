<?php

namespace Firstwap\SmsApiDashboard\Entities;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Firstwap\SmsApiDashboard\Entities\ApiUser;
use Firstwap\SmsApiDashboard\Entities\Message;

class Report extends Model
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
    protected $table = 'AD_REPORT';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'report_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'report_name',
        'start_date',
        'end_date',
        'message_status',
        'file_type',
        'created_by',
        'generate_status',
        'percentage',
        'pid',
    ];

    /**
     * Constant that indicate the report in queue
     */
    const REPORT_QUEUE = 0;

    /**
     * Constant that indicate the report in process
     */
    const REPORT_PROCESS = 1;

    /**
     * Constant that indicate the report has finished
     */
    const REPORT_FINISHED = 2;

    /**
     * Constant that indicate the report is failed
     */
    const REPORT_FAILED = 3;

    /**
     * Constant that indicate the report is canceled
     */
    const REPORT_CANCELED = 4;

    /**
     * Prefix cache key
     */
    const CACHE_KEY = 'REPORT_';

    /**
     * Define a many-to-many relationship.
     * The api user that belong to Report.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function apiUsers()
    {
        $database = $this->getConnection()->getDatabaseName();
        return $this->belongsToMany(tap(new ApiUser, function ($instance) {
            return $instance->setConnection($this->connection);
        }), "$database.AD_API_USER_REPORT", 'report_id', 'api_user_id');
    }

    /**
     * Define an inverse one-to-many relationship.
     * Get the reports that owns api user dashboard
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function apiUserDashboard()
    {
        return $this->belongsTo(User::class, 'created_by', 'ad_user_id');
    }

    /**
     * Get Download report url
     *
     * @return string
     */
    public function getDownloadUrlAttribute()
    {
        return route('report.download',['report' => $this->getKey()]);
    }

    /**
     * Set start date and convert it to server timezone
     *
     * @return void
     */
    public function setStartDateAttribute($value)
    {
        $timezoneServer = date_default_timezone_get();
        $value = $this->asDateTime($value)->setTimezone($timezoneServer);

        $this->attributes['start_date'] = $value;
    }

    /**
     * Get start date and convert it to user timezone
     *
     * @return void
     */
    public function getStartDateAttribute($value)
    {
        $timezone = auth()->user()->timezone ?? date_default_timezone_get();
        $value = $this->asDateTime($value)->setTimezone($timezone);

        return $value->toDateTimeString();
    }

    /**
     * Get start date original value
     *
     * @return void
     */
    public function getStartDateOriAttribute()
    {
        return $this->attributes['start_date'];
    }

    /**
     * Get end date and convert it to user timezone
     *
     * @return void
     */
    public function getEndDateOriAttribute()
    {
        return $this->attributes['end_date'];
    }

    /**
     * Get Pid value
     * Using pid value from cache if exists
     * otherwise using value from database
     *
     * @return void
     */
    public function getPidAttribute()
    {
        if ($pid = $this->getManifest('pid')) {
            return $pid;
        }

        return $this->attributes['pid'];
    }

    /**
     * Get percentage value
     * Using percentage value from cache if exists
     * otherwise using value from database
     *
     * @return void
     */
    public function getPercentageAttribute()
    {
        if ($percentage = $this->getManifest('percentage')) {
            return $percentage;
        }

        return $this->attributes['percentage'] ?? 0;
    }


    /**
     * Get Cache Key value
     *
     * @return void
     */
    public function getCacheKeyAttribute()
    {
        return self::CACHE_KEY . $this->getKey();
    }


    /**
     * Set end date and convert it to server timezone
     *
     * @return void
     */
    public function setEndDateAttribute($value)
    {
        $timezoneServer = date_default_timezone_get();
        $value = $this->asDateTime($value)->setTimezone($timezoneServer);

        $this->attributes['end_date'] = $value;
    }

    /**
     * Get end date and convert it to user timezone
     *
     * @return void
     */
    public function getEndDateAttribute($value)
    {
        $timezone = auth()->user()->timezone ?? date_default_timezone_get();
        $value = $this->asDateTime($value)->setTimezone($timezone);

        return $value->toDateTimeString();
    }

    /**
     * Get Message builder to generate report
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getReportBuilder()
    {
        return Message::reportData($this);
    }

    /**
     * Get Total data for generating report
     *
     * @return integer
     */
    public function getTotalData()
    {
        return $this->getReportBuilder()->count();
    }

    /**
     * Get Status error code base on message_status attribute
     *
     * @return array
     */
    public function getStatusErrorCode()
    {
        $status = array_filter(explode(',', $this->getAttribute('message_status')));
        $errorCode = [];

        if (!empty($status) && count($status) < 4)
        {
            $statusCodes = StatusCode::statusErrorCode();

            foreach ($statusCodes as $key => $item) {
                if (in_array(strtolower($item), $status)) {
                    $errorCode[] = strval($key);
                }
            }
        }

        return $errorCode;
    }


    /**
     * Create a new model instance for a related model.
     *
     * @param  mixed  $class
     * @return mixed
     */
    protected function newRelatedInstance($class)
    {
        return is_string($class) ? parent::newRelatedInstance($class) : $class;
    }


    /**
     * Check if report is processing to generate
     *
     * @return boolean
     */
    public function isProcessing()
    {
        return $this->pid > 0 && posix_getpgid($this->pid);
    }

    /**
     * Get manifest generator data
     *
     * @param string $key   Key of manifest ['status', 'pid', 'total', 'percentage', 'files']
     * @return Mixed
     */
    public function getManifest($key = '')
    {
        $data   = Cache::get($this->cache_key);

        if (!empty($key) && isset($data) && array_key_exists($key, $data)) {
            return $data[$key];
        }

        return $data;
    }

    /**
     * Request to Cancel report that still on progress
     * Changing the status value on cache to canceled value
     *
     * @return  void
     */
    public function cancelReport()
    {
        $this->updateManifest(['status' => self::REPORT_CANCELED]);
        $this->update(['generate_status' => self::REPORT_CANCELED]);
    }

    /**
     * Update manifest report
     *
     * @param array $data    Manifest data that will store to cache ['status', 'percentage', 'pid', 'total', 'files']
     * @return void
     */
    public function updateManifest(array $data)
    {
        $key = $this->cache_key;

        if ($value = Cache::get($key)) {
            $data = array_merge($value, $data);
        } else {
            $default = [
                'percentage' => $this->percentage,
                'pid'        => 0,
                'total'      => 0,
                'status'     => $this->generate_status,
                'fileName'   => '',
                'temp'       => null,
            ];

            $data = array_merge($default, $data);
        }

        Cache::put($key, $data, now()->addDay(1));
    }
}
