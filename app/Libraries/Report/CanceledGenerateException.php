<?php

namespace Firstwap\SmsApiDashboard\Libraries\Report;

use Exception;

class CanceledGenerateException extends Exception
{

    /**
     * Construct the exception
     *
     * @param string $message   The Exception message to throw.
     * @param int $code         The Exception code.
     */
    public function __construct(string $message = "", int $code = 404)
    {
        parent::__construct($message ?: trans('app.no_data_report'), $code);
    }

}
