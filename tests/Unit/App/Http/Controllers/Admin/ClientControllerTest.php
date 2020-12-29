<?php

namespace Tests\Unit\App\Http\Controllers\Admin;

use Firstwap\SmsApiDashboard\Http\Controllers\Admin\ClientController;
use Firstwap\SmsApiDashboard\Libraries\Repositories\ClientRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class ClientControllerTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * Test select method to get all company data
     *
     * @return  void
     */
    public function test_select_method()
    {
        $repo = new ClientRepository();
        $controller = new ClientController($repo);

        /**
         * Test select method to get all company data
         * If Authenticated user is super admin
         */
        $user = $this->initializeUserLogin('Super Admin');
        auth()->login($user);
        $request = Request::create('/', 'GET');
        $response = $controller->select($request);
        $data = $response->getContent();
        $this->assertJson($data);
        $arrayObject = json_decode($data);
        $this->assertNotEmpty($arrayObject);
        $this->assertObjectHasAttribute('client_id', current($arrayObject));
        $this->assertObjectHasAttribute('company_name', current($arrayObject));

        /**
         * Test select method to get all company data
         * If Authenticated user is company admin
         * company admin only get data his company
         */
        $user = $this->initializeUserLogin('Admin');
        auth()->login($user);
        $request = Request::create('/', 'GET');
        $response = $controller->select($request);
        $data = $response->getContent();
        $this->assertJson($data);
        $arrayObject = json_decode($data);
        $this->assertNotEmpty($arrayObject);
        $this->assertEquals(1, count($arrayObject));
        $this->assertObjectHasAttribute('client_id', current($arrayObject));
        $this->assertObjectHasAttribute('company_name', current($arrayObject));
        $userClient = $user->client
                        ->setVisible(['client_id', 'company_name'])
                        ->toJson();
        $this->assertJsonStringEqualsJsonString($userClient, json_encode(current($arrayObject)));


        /**
         * Test select method to get all company data
         * If Authenticated user only have Report role
         */
        $user = $this->initializeUserLogin('Report');
        auth()->login($user);
        $request = Request::create('/', 'GET');
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage(trans('app.unauthorized'));
        $response = $controller->select($request);

    }

}
