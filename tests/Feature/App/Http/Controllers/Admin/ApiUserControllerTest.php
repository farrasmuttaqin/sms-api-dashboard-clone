<?php

namespace Tests\Feature\App\Http\Controllers\Admin;

use Firstwap\SmsApiDashboard\Entities\ApiUser;
use Firstwap\SmsApiDashboard\Entities\Client;
use Firstwap\SmsApiDashboard\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApiUserControllerTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * Test get api user data for select input
     *
     * @return  void
     */
    public function test_api_user_data_for_select_input()
    {
        $client = Client::has('apiUsers')->first();
        $this->assertNotNull($client);
        $user = $this->initializeUserLogin();

        /**
         * Test If user is unauthenticated
         */
        $uri = route('apiuser.select');
        $header = ['X-Requested-With' => 'XMLHttpRequest'];
        $response = $this
                ->withoutMiddleware('web')
                ->get($uri, $header);
        //Should get status 401 Unauthenticated
        $response->assertStatus(401);

        /**
         * Test If the request is AJAX
         */
        $request = ['client_id' => $client->getKey()];
        $uri = route('apiuser.select', $request);
        $header = ['X-Requested-With' => 'XMLHttpRequest'];
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user)
                ->get($uri, $header);

        $response->assertStatus(200);
        $response->assertJsonStructure([['user_id', 'user_name']]);

        /**
         * Test If the authenticated user is admin company
         */
        $user = $this->initializeUserLogin('Admin');

        $request = ['client_id' => $client->getKey()];
        $uri = route('apiuser.select', $request);
        $header = ['X-Requested-With' => 'XMLHttpRequest'];
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user)
                ->get($uri, $header);

        $response->assertStatus(200);
        $response->assertJsonStructure([['user_id', 'user_name']]);

        /**
         * Test If the authenticated user with Report Role
         */
        $user = $this->initializeUserLogin('Report');
        $request = ['client_id' => $user->client_id];
        $uri = route('apiuser.select', $request);
        $header = ['X-Requested-With' => 'XMLHttpRequest'];
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user)
                ->get($uri, $header);

        $response->assertStatus(200);
        $response->assertJsonCount($user->apiUsers->count());

        /**
         * Test If the authenticated user without any role
         */
        $user = factory(User::class)->create();
        session()->flush();
        $request = ['client_id' => $client->getKey()];
        $uri = route('apiuser.select', $request);
        $header = ['X-Requested-With' => 'XMLHttpRequest'];
        $response = $this
                ->withoutMiddleware()
                ->actingAs($user)
                ->get($uri, $header);
        //Should return unauthorization response error
        $response->assertStatus(403);
        $response->assertJsonStructure(['errors']);
    }

    /**
     * Test get api user data for select input with wrong format
     *
     * @return  void
     */
    public function test_api_user_data_for_select_input_with_wrong_format()
    {
        $client = Client::has('apiUsers')->first();
        $this->assertNotNull($client);
        $user = $this->initializeUserLogin();

        /**
         * Test If the request is not AJAX
         */
        $request = ['client_id' => $client->getKey()];
        $uri = route('apiuser.select', $request);
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user)
                ->get($uri);

        $response->assertStatus(404);

        /**
         * Test If the client id is not exists in database
         */
        $request = ['client_id' => -1];
        $uri = route('apiuser.select', $request);
        $header = ['X-Requested-With' => 'XMLHttpRequest'];
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user)
                ->get($uri, $header);

        $response->assertStatus(200);
        $response->assertJsonCount(0);

        /**
         * Test If the client id is not numeric
         */
        $request = ['client_id' => 'fuu'];
        $uri = route('apiuser.select', $request);
        $header = ['X-Requested-With' => 'XMLHttpRequest'];
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user)
                ->get($uri, $header);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('client_id');
        $response->assertJsonStructure(['message', 'errors' => ['client_id']]);

        /**
         * Test If the client id is empty
         */
        $request = ['client_id' => ''];
        $uri = route('apiuser.select', $request);
        $header = ['X-Requested-With' => 'XMLHttpRequest'];
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user)
                ->get($uri, $header);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('client_id');
        $response->assertJsonStructure(['message', 'errors' => ['client_id']]);

        /**
         * Test If the client id parameters is doesn't exists
         */
        $uri = route('apiuser.select');
        $header = ['X-Requested-With' => 'XMLHttpRequest'];
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user)
                ->get($uri, $header);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('client_id');
        $response->assertJsonStructure(['message', 'errors' => ['client_id']]);
    }

    /**
     * Test get all api user data without login
     *
     * @return  void
     */
    public function test_get_all_api_users_without_login()
    {
        /**
         * Test If without login
         */
        $uri = route('apiuser.all');
        $header = ['X-Requested-With' => 'XMLHttpRequest'];
        $response = $this
                ->withoutMiddleware('web')
                ->get($uri, $header);
        //return Authenticated error
        $response->assertStatus(401);
        $response->assertJsonStructure(['errors']);
    }

    /**
     * Test get all api user data without XMLHttpRequest header
     *
     * @return  void
     */
    public function test_get_all_api_users_without_ajax_request()
    {
        /**
         * Test If without login
         */
        $uri = route('apiuser.all');
        $user = $this->initializeUserLogin('Super Admin');
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user)
                ->get($uri);

        //page not found view
        $response->assertStatus(404);
    }

    /**
     * Test get all api user data with super admin role
     *
     * @return  void
     */
    public function test_get_all_api_users_with_user_login_have_super_admin_role()
    {
        /**
         * Test If without login
         */
        $uri = route('apiuser.all');
        $header = ['X-Requested-With' => 'XMLHttpRequest'];
        $user = $this->initializeUserLogin('Super Admin');
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user)
                ->get($uri, $header);

        $response->assertStatus(200);
        $countCurrentApiUsers = ApiUser::count();
        $response->assertJsonCount($countCurrentApiUsers);
    }

    /**
     * Test get all api user data with super admin role
     *
     * @return  void
     */
    public function test_get_all_api_users_with_user_login_have_company_admin_role()
    {
        /**
         * Test If without login
         */
        $uri = route('apiuser.all');
        $header = ['X-Requested-With' => 'XMLHttpRequest'];
        $user = $this->initializeUserLogin('Admin');
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user)
                ->get($uri, $header);

        $response->assertStatus(200);
        $countCurrentApiUsers = $user->client->apiUsers()->count();
        $response->assertJsonCount($countCurrentApiUsers);
    }

    /**
     * Test get all api user data with super admin role
     *
     * @return  void
     */
    public function test_get_all_api_users_with_user_login_have_report_role()
    {
        /**
         * Test If without login
         */
        $uri = route('apiuser.all');
        $header = ['X-Requested-With' => 'XMLHttpRequest'];
        $user = $this->initializeUserLogin('Report');
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user)
                ->get($uri, $header);

        $response->assertStatus(200);
        $countCurrentApiUsers = $user->apiUsers()->count();
        $response->assertJsonCount($countCurrentApiUsers);
    }

}
