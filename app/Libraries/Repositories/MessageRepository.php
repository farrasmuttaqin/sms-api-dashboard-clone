<?php

namespace Firstwap\SmsApiDashboard\Libraries\Repositories;

use Carbon\Carbon;
use Firstwap\SmsApiDashboard\Entities\Message;
use Firstwap\SmsApiDashboard\Entities\Privilege;
use Firstwap\SmsApiDashboard\Entities\StatusCode;
use Illuminate\Support\Facades\Cache;

class MessageRepository extends Repository
{

    /**
     * Expired summary cache in minutes
     *
     * @var  int
     */
    protected $expiresAt = 10;

    /**
     * Get Model Builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function builder()
    {
        $builder = $this->model()->query();

        if ($this->privileges(Privilege::API_USER_ACC_SYSTEM))
        {
            return $builder;
        }

        return $builder->whereHas('apiUser', function($query)
                {
                    $query->whereHas('apiDashboardUsers', function($query)
                    {
                        $query->where($this->user()->getTable() . '.ad_user_id', $this->user()->getKey());
                    });
                });
    }

    /**
     * Get Model instance
     *
     * @return \Firstwap\SmsApiDashboard\Entities\Message
     */
    public function model()
    {
        return $this->model ?: new Message();
    }

    /**
     * Get Summary builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getSummaryBuilder()
    {
        $status     = [];
        $errorCodes = StatusCode::statusErrorCode();
        $builder    = $this->builder()->selectRaw('COUNT(1) as total');

        foreach ($errorCodes as $code => $value)
        {
            $status[$value][] = "'$code'";
        }

        foreach ($status as $key => $values)
        {
            $status[$key] = implode(',', $values);
            $builder->selectRaw(
                    'COUNT'
                    . '(CASE WHEN message_status in (' . $status[$key] . ')'
                    . 'THEN 1 ELSE NULL END)'
                    . ' as ' . $key
            );
        }

        return $builder;
    }

    /**
     * Get current user timezone
     *
     * @return string
     */
    public function timezone()
    {
        return $this->user()->timezone ?: date_default_timezone_get();
    }

    /**
     * Get message data based on specified datetime
     *
     * @param Carbon\Carbon $startDate  Start date to get message in Carbon instance
     * @return array                    A summary array with format
     *                                  [ delivered => int, rejected => int, sent => int, total => int, undelivered => int ]
     */
    public function getSummaryMessageByTime(Carbon $startDate)
    {
        $startDate = $startDate
                ->tz(date_default_timezone_get())
                ->toDateTimeString();

        $subQuery = $this->model()
            ->select('*')
            ->selectRaw("STR_TO_DATE(SUBSTRING(MESSAGE_ID, 5, 19), '%Y-%m-%d %H:%i:%s') AS RECEIVE_DATETIME");

        return $this->getSummaryBuilder()
            ->from(\DB::raw("({$subQuery->toSql()}) as USER_MESSAGE_STATUS"))
            ->mergeBindings($subQuery->getQuery())
            ->where('USER_MESSAGE_STATUS.RECEIVE_DATETIME', '>=', $startDate)
            ->first()
            ->toArray();
    }

    /**
     * Get daily messages status summary start from 24 hours ago until now
     *
     * @return array    A summary array with format
     *                  [ delivered => int, rejected => int, sent => int, total => int, undelivered => int ]
     */
    public function getDailySummary()
    {
        $key = $this->getCacheKey().'_daily';

        return Cache::remember($key, $this->expiresAt, function () {
                    $start = Carbon::parse('-24 hours', $this->timezone());
                    return $this->getSummaryMessageByTime($start);
                });
    }

    /**
     * Get weekly messages status summary start from 7 days ago until now
     *
     * @return array    A summary array with format
     *                  [ delivered => int, rejected => int, sent => int, total => int, undelivered => int ]
     */
    public function getWeeklySummary()
    {
        $key = $this->getCacheKey().'_weekly';

        return Cache::remember($key, $this->expiresAt, function () {
                    $start = Carbon::parse('-7 days midnight', $this->timezone());
                    return $this->getSummaryMessageByTime($start);
                });
    }

    /**
     * Get monthly messages status summary start from 30 days ago until now
     *
     * @return array    A summary array with format
     *                  [ delivered => int, rejected => int, sent => int, total => int, undelivered => int ]
     */
    public function getMonthlySummary()
    {
        $key = $this->getCacheKey().'_monthly';

        return Cache::remember($key, $this->expiresAt, function () {
                    $start = Carbon::parse('-30 days midnight', $this->timezone());
                    return $this->getSummaryMessageByTime($start);
                });
    }

    /**
     * Get summary cache key to keep temporary summary data
     *
     * @return string   A cache key to store summary data
     */
    protected function getCacheKey()
    {
        return Message::SUMMARY_SESSION_KEY.'_'.$this->user()->getKey();
    }

}
