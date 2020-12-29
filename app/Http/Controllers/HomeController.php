<?php

namespace Firstwap\SmsApiDashboard\Http\Controllers;

use Firstwap\SmsApiDashboard\Libraries\Repositories\MessageRepository;
use Firstwap\SmsApiDashboard\Libraries\Repositories\ApiUserRepository;
use Illuminate\Http\Request;
use Firstwap\SmsApiDashboard\Entities\ApiUser;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(MessageRepository $repo, ApiUserRepository $apiRepo)
    {
        $this->repo = $repo;
        $this->apiRepo = $apiRepo;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     * @return  Array
     */
    public function index()
    {
        if (request()->has('summary')) {
            return $this->getSummaryMessages();
        }

        $apiUsers = $this->apiRepo->ownApiUsers();
        $totalCredit = 0;

        // Parsing each credits to number format
        foreach ($apiUsers as &$apiUser) {
            if ($apiUser->is_postpaid === 0) {
                $totalCredit    += $apiUser->credit;
                $apiUser->credit = number_format($apiUser->credit, 0, ",", ".");
            }
        }

        return view('home', [
            'apiUsers' => $apiUsers,
            'totalCredit' => number_format($totalCredit, 0, ",", ".")
        ]);
    }


    protected function getSummaryMessages()
    {
        $data = [];
        $time = request('summary');
        $function = 'get'.ucfirst($time).'Summary';

        if(method_exists($this->repo, $function)){
            $data = $this->repo->$function() ?? [];
        }

        return response()->json($data);
    }

}
