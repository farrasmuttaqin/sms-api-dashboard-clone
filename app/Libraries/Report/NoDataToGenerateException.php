<?php

namespace Firstwap\SmsApiDashboard\Libraries\Report;

use Exception;

class NoDataToGenerateException extends Exception
{

    /**
     * Construct the exception
     *
     * @param string $message   The Exception message to throw.
     * @param int $code         The Exception code.
     */
    public function __construct(string $message = "", int $code = 0)
    {
        parent::__construct(trans('app.no_data_report'), 404);
    }

}
