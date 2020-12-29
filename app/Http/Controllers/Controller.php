<?php

namespace Firstwap\SmsApiDashboard\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    /**
     * Register privileges middleware on the controller.
     *
     * @param  array|string  $middleware
     * @param  array  $options
     * @return void
     */
    public function privileges($privilegeName, $options = [])
    {
        $privileges = implode(',', array_wrap($privilegeName));
        return $this->middleware("privileges:$privileges", $options);
    }

}
