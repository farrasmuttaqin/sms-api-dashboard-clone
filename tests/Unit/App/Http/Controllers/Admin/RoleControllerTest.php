<?php

namespace Tests\Unit\App\Http\Controllers\Admin;

use Firstwap\SmsApiDashboard\Http\Controllers\Admin\RoleController;
use Firstwap\SmsApiDashboard\Libraries\Repositories\RoleRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * Test select method to get all role data
     *
     * @return  void
     */
    public function test_select_method()
    {
        $repo = new RoleRepository();
        $controller = new RoleController($repo);

        /**
         * Test select method to get all role data
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
        $this->assertObjectHasAttribute('role_id', current($arrayObject));
        $this->assertObjectHasAttribute('role_name', current($arrayObject));

        /**
         * Test select method to get all role data
         * If Authenticated user is company admin
         * company admin only get Report role
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
        $this->assertObjectHasAttribute('role_id', current($arrayObject));
        $this->assertObjectHasAttribute('role_name', current($arrayObject));
        $this->assertEquals('Report', current($arrayObject)->{'role_name'});


        /**
         * Test select method to get all role data
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
