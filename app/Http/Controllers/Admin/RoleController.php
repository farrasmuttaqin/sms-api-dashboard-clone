<?php

namespace Firstwap\SmsApiDashboard\Http\Controllers\Admin;

use Firstwap\SmsApiDashboard\Http\Controllers\Controller;
use Firstwap\SmsApiDashboard\Libraries\Repositories\RoleRepository;
use Firstwap\SmsApiDashboard\Entities\Privilege;
use Illuminate\Http\Request;

class RoleController extends Controller
{

    /**
     * Create a new UserController instance.
     *
     * @return void
     */
    function __construct(RoleRepository $repo)
    {
        $this->privileges([Privilege::USER_ACC_SYSTEM,Privilege::USER_ACC_COMPANY]);
        $this->middleware('ajax')->only(['select']);
        $this->repo = $repo;
    }

    /**
     * Get All role data with select format
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function select()
    {
        $data = $this->repo->data();

        return response()->json($data, 200);
    }

}
