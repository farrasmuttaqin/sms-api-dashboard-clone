<?php

namespace Tests\Unit\App\Http\Controllers\Admin;

use Firstwap\SmsApiDashboard\Entities\ApiUser;
use Firstwap\SmsApiDashboard\Entities\Client;
use Firstwap\SmsApiDashboard\Http\Controllers\Admin\ApiUserController;
use Firstwap\SmsApiDashboard\Libraries\Repositories\ApiUserRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ApiUserControllerTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * Test select method if client id / company exists
     *
     * @return  void
     */
    public function test_select_method()
    {
        $repo = new ApiUserRepository();
        $controller = new ApiUserController($repo);

        /**
         * Test If client id exists
         * Authenticated user is super admin
         */
        $user = $this->initializeUserLogin('Super Admin');
        auth()->login($user);
        $request = Request::create('/', 'GET', ['client_id' => $user->client_id]);
        $response = $controller->select($request);
        $data = $response->getContent();
        $this->assertJson($data);
        $arrayObject = json_decode($data);
        $this->assertNotEmpty($arrayObject);
        $this->assertObjectHasAttribute('user_name', current($arrayObject));
        $this->assertObjectHasAttribute('user_id', current($arrayObject));

        /**
         * Test If client id exists
         * Authenticated user is admin company
         */
        $user = $this->initializeUserLogin('Admin');
        auth()->login($user);
        $request = Request::create('/', 'GET', ['client_id' => $user->client_id]);
        $response = $controller->select($request);
        $data = $response->getContent();
        $this->assertJson($data);
        $arrayObject = json_decode($data);
        $this->assertNotEmpty($arrayObject);
        $this->assertObjectHasAttribute('user_name', current($arrayObject));
        $this->assertObjectHasAttribute('user_id', current($arrayObject));

        /**
         * Test If client id exists
         * Authenticated user is admin company
         * But the client id does not belong to admin company
         */
        $user = $this->initializeUserLogin('Admin');
        auth()->login($user);
        $client = Client::has('apiUsers')
                    ->where('client_id', '!=', $user->client_id)
                    ->first();
        $this->assertNotNull($client);
        $request = Request::create('/', 'GET', ['client_id' => $client->getKey()]);
        $response = $controller->select($request);
        $data = $response->getContent();
        $this->assertJson($data);
        //The admin company can not access api users other company
        //so the return should be empty
        $arrayObject = json_decode($data);
        $this->assertEmpty($arrayObject);

        /**
         * Test If get api users with authenticated user which have only Report role
         */
        $user = $this->initializeUserLogin('Report');
        auth()->login($user);
        $request = Request::create('/', 'GET', ['client_id' => $user->client_id]);
        $response = $controller->select($request);
        $data = $response->getContent();
        $this->assertJson($data);
        $arrayObject = json_decode($data);
        $this->assertNotEmpty($arrayObject);
        $this->assertObjectHasAttribute('user_name', current($arrayObject));
        $this->assertObjectHasAttribute('user_id', current($arrayObject));

        $apiUsers = tap($user->apiUsers, function($apiUsers) {
            $apiUsers->map->setVisible(['user_name', 'user_id']);
        });
        //users with role Report can only access the api users which they have
        $apiUsersArray = $apiUsers->pluck('user_name')->all();
        $this->assertNotEmpty($apiUsersArray);
        foreach ($arrayObject as $value) {
            $this->assertTrue(in_array($value->user_name, $apiUsersArray));
        }
    }

    /**
     * Test select method if client id / company doesn't exists
     *
     * @return  void
     */
    public function test_select_method_if_client_id_doesnt_exists()
    {
        $repo = new ApiUserRepository();
        $controller = new ApiUserController($repo);

        /**
         * Test If client id doesn't exists
         */
        $user = $this->initializeUserLogin('Super Admin');
        auth()->login($user);
        $request = Request::create('/', 'GET', ['client_id' => 0]);
        $response = $controller->select($request);
        $data = $response->getContent();
        $this->assertJson($data);
        $arrayObject = json_decode($data);
        $this->assertEmpty($arrayObject);
    }

    /**
     * Test select method if client id is not numeric
     *
     * @return  void
     */
    public function test_select_method_if_client_id_is_not_numeric()
    {
        $repo = new ApiUserRepository();
        $controller = new ApiUserController($repo);

        /**
         * Test If client id is not numeric
         */
        $request = Request::create('/', 'GET', ['client_id' => "a"]);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The given data was invalid.');
        $response = $controller->select($request);
    }
    
    /**
     * Test select method if client id is empty
     *
     * @return  void
     */
    public function test_select_method_if_client_id_is_empty()
    {
        $repo = new ApiUserRepository();
        $controller = new ApiUserController($repo);

        /**
         * Test If client id is empty
         */
        $request = Request::create('/', 'GET', ['client_id' => ""]);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The given data was invalid.');
        $response = $controller->select($request);
    }

    /**
     * Test all() method
     *
     * @return  void
     */
    public function test_all_method_without_login()
    {
        $repo = new ApiUserRepository();
        $controller = new ApiUserController($repo);

        /**
         * Test execute all() method without login
         * This should throw AuthorizationException
         */
        $this->expectException(AuthorizationException::class);
        $response = $controller->all();
    }
    
    /**
     * Test all() method
     *
     * @return  void
     */
    public function test_all_method_with_login()
    {
        $repo = new ApiUserRepository();
        $controller = new ApiUserController($repo);

        /**
         * Test execute all() method with Super Admin User
         */
        $user = $this->initializeUserLogin('Super Admin');
        auth()->login($user);
        $response = $controller->all();
        //Super Admin Can show all api user
        $currentApiUserCount = ApiUser::count();
        $responseCount = count($response->getData());
        $this->assertEquals($currentApiUserCount, $responseCount);
        
        /**
         * Test execute all() method with Company Admin User
         */
        $user = $this->initializeUserLogin('Admin');
        auth()->login($user);
        $response = $controller->all();
        //Company Admin Can show all api user that belongs to company
        $currentApiUserCount = $user->client->apiUsers()->count();
        $responseCount = count($response->getData());
        $this->assertEquals($currentApiUserCount, $responseCount);

        /**
         * Test execute all() method with Report User
         */
        $user = $this->initializeUserLogin('Report');
        auth()->login($user);
        $response = $controller->all();
        //Company Admin Can show all api user that belongs to company
        $currentApiUserCount = $user->apiUsers()->count();
        $responseCount = count($response->getData());
        $this->assertEquals($currentApiUserCount, $responseCount);
    }

}
