<?php

namespace Firstwap\SmsApiDashboard\Http\Controllers\Admin;

use Firstwap\SmsApiDashboard\Http\Controllers\Controller;
use Firstwap\SmsApiDashboard\Entities\Privilege;
use Firstwap\SmsApiDashboard\Libraries\Repositories\ApiUserRepository;
use Illuminate\Http\Request;

class ApiUserController extends Controller
{

    /**
     * Create a new UserController instance.
     *
     * @return void
     */
    function __construct(ApiUserRepository $repo)
    {
        $this->repo = $repo;
        $this->privileges($this->getPrivileges());
        $this->middleware('ajax');
    }

    /**
     * Get api user data base on client_id
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function select(Request $request)
    {
        $this->validate($request, ['client_id' => 'required|numeric']);

        $data = $this->repo->dataByClient($request->get('client_id'));

        $data->map->setVisible(['user_id', 'user_name']);

        return response()->json($data);
    }

    /**
     * Get all api user data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function all()
    {
        $data = $this->repo->builder()->get();

        $data->map->setVisible(['user_id', 'user_name']);

        return response()->json($data);
    }

    /**
     * Get privileges for ApiUserController
     *
     * @return array
     */
    protected function getPrivileges()
    {
        return [
            Privilege::API_USER_ACC_SYSTEM,
            Privilege::API_USER_ACC_COMPANY,
            Privilege::API_USER_ACC_OWN
        ];
    }

}
