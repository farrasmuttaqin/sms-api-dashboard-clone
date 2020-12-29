<?php

namespace Firstwap\SmsApiDashboard\Entities;

use Firstwap\SmsApiDashboard\Entities\ApiUser;
use Firstwap\SmsApiDashboard\Entities\StatusCode;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    /**
     * Constant for messages summary cache key
     *
     * @var string
     */
    const SUMMARY_SESSION_KEY = 'user_message_summary';

    /**
     * List of GSM 7bit characters
     *
     * @var string
     */
    const GSM_7BIT_CHARS = '~[^A-Za-z0-9 \r\n¤@£$¥èéùìòÇØøÅå\x{0394}_\x{5C}\x{03A6}\x{0393}\x{039B}\x{03A9}\x{03A0}\x{03A8}\x{03A3}\x{0398}\x{039E}ÆæßÉ!\"#$%&\'\(\)*+,\-.\/:;<=>;?¡ÄÖÑÜ§¿äöñüà^{}\[\~\]\|\x{20AC}]~u';

    /**
     * SMS Legth for GSM 7bit with single sms
     *
     * @var integer
     */
    const GSM_7BIT_SINGLE_SMS = 160;

    /**
     * SMS Legth for GSM 7bit with multiple sms
     *
     * @var integer
     */
    const GSM_7BIT_MULTIPLE_SMS = 153;

    /**
     * SMS Legth for unicode character with single sms
     *
     * @var integer
     */
    const UNICODE_SINGLE_SMS = 70;

    /**
     * SMS Legth for unicode character with multiple sms
     *
     * @var integer
     */
    const UNICODE_MULTIPLE_SMS = 67;

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
    protected $table = 'USER_MESSAGE_STATUS';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_message_status_id';

    /**
     * User timezone value
     *
     * @var string
     */
    public static $userTimezone = 'UTC';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;


    /**
     * Define an inverse one-to-many relationship.
     * Get the api user that owns message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function apiUser()
    {
        return $this->belongsTo(ApiUser::class, 'user_id_number', 'user_id');
    }

    /**
     * Convert send_datetime to user timezone
     *
     * @param   String  $value
     * @return  String
     */
    public function getSendDatetimeAttribute($value)
    {
        return $this->parseDatetime($value);
    }

    /**
     * Convert receive_datetime to user timezone
     *
     * @param   String $value
     * @return  String
     */
    public function getReceiveDatetimeAttribute($value)
    {
        return $this->parseDatetime($value);
    }

    /**
     * Convert datetime to user timezone
     *
     * @param   String  $value
     * @return  String
     */
    public function parseDatetime($value)
    {
        if(!strtotime($value)){
            return $value;
        }

        $timezone = auth()->user()->timezone ?? self::$userTimezone;

        $value = $this->asDateTime($value)->setTimezone($timezone);

        return $value->toDateTimeString();
    }

    /**
     * Change error code to description status
     *
     * @return String
     */
    public function getDescriptionStatusAttribute()
    {
        $errorCode = StatusCode::statusErrorCode();
        $status     = $this->attributes['message_status'];

        return isset($errorCode[$status])
                    ? trans('app.'. strtolower($errorCode[$status]))
                    : trans('app.undelivered');
    }

    /**
     * Counting the message content
     *
     * @return  Integer
     */
    public function getMessageCountAttribute()
    {
        $message       = $this->attributes['message_content'] ?? '';
        $messageLength = mb_strlen($message);

        if ($this->isGsm7bit($message))
        {
            if ($messageLength <= self::GSM_7BIT_SINGLE_SMS)
            {
                return 1;
            }

            return ceil($messageLength / self::GSM_7BIT_MULTIPLE_SMS);
        }

        if ($messageLength <= self::UNICODE_SINGLE_SMS)
        {
            return 1;
        }

        return ceil($messageLength / self::UNICODE_MULTIPLE_SMS);
    }

    /**
     * Check if the message was Gsm_7bit or Unicode encoded
     *
     * @param   String  $message        Message content
     * @return  boolean                 true for Gsm7bit and false for unicode
     */
    private function isGsm7bit($message)
    {
        return preg_match(self::GSM_7BIT_CHARS, $message) === 0;
    }


    /**
     * Get message status base on Report query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Report $report
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReportData($query, Report $report)
    {
        if($apiUsers = $report->apiUsers) {
            $apiUsers = $apiUsers->pluck('user_id')->all();
            $query = $query->whereIn('user_id_number', $apiUsers);
        }

        $errorCode = $report->getStatusErrorCode();

        if (!empty($errorCode)) {
            $query = $query->whereIn('message_status', $errorCode);
        }

        $sub = $query->select('*')
                ->selectRaw("STR_TO_DATE(SUBSTRING(MESSAGE_ID, 5, 19), '%Y-%m-%d %H:%i:%s') AS RECEIVE_DATETIME");

        return self::select('*')
                        ->from(\DB::raw("({$sub->toSql()}) as USER_MESSAGE_STATUS"))
                        ->mergeBindings($sub->getQuery())
                        ->where('USER_MESSAGE_STATUS.RECEIVE_DATETIME', '>=', $report->start_date_ori)
                        ->where('USER_MESSAGE_STATUS.RECEIVE_DATETIME', '<=', $report->end_date_ori)
                        ->orderBy('USER_MESSAGE_STATUS.RECEIVE_DATETIME', 'desc');
    }

}
