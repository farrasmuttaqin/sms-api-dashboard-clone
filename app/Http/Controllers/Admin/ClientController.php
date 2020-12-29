<?php

namespace Firstwap\SmsApiDashboard\Http\Controllers\Admin;

use Firstwap\SmsApiDashboard\Http\Controllers\Controller;
use Firstwap\SmsApiDashboard\Entities\Privilege;
use Firstwap\SmsApiDashboard\Libraries\Repositories\ClientRepository;
use Illuminate\Http\Request;

class ClientController extends Controller
{

    /**
     * Create a new UserController instance.
     *
     * @return void
     */
    function __construct(ClientRepository $repo)
    {
        $this->privileges([Privilege::USER_ACC_SYSTEM, Privilege::USER_ACC_COMPANY])->only('select');
        $this->middleware('ajax')->only(['select']);
        $this->repo = $repo;
    }

    /**
     * Get All client data with select format
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function select()
    {
        $data = $this->repo->data();

        $data->map->setVisible(['client_id', 'company_name']);

        return response()->json($data);
    }

}
