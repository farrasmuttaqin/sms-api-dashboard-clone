<?php

namespace Tests\Feature\App\Http\Controllers\Admin;

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
     * Test get all client data for select input
     *
     * @return  void
     */
    public function test_get_client_data_for_select_input()
    {
        $header = ['X-Requested-With' => 'XMLHttpRequest'];

        /**
         * Test If user is unauthenticated
         */
        $uri = route('client.select');
        $response = $this
                ->withoutMiddleware('web')
                ->get($uri, $header);
        //Should get status 401 Unauthenticated
        $response->assertStatus(401);

        /**
         * Test If the request is AJAX
         * and User have Super Admin role
         */
        $user = $this->initializeUserLogin('Super Admin');
        $uri = route('client.select');
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user)
                ->get($uri, $header);

        $response->assertStatus(200);
        $response->assertJsonStructure([['client_id', 'company_name']]);
        
        /**
         * Test If the request is AJAX
         * and User have Company Admin role
         */
        $user = $this->initializeUserLogin('Admin');
        $uri = route('client.select');
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user)
                ->get($uri, $header);

        $response->assertStatus(200);
        $response->assertJsonStructure([['client_id', 'company_name']]);
        $response->assertJsonCount(1);
        $response->assertJson([
            $user->client->setVisible(['client_id','company_name'])->toArray()
        ]);
        
        /**
         * Test If the request is AJAX
         * and User have Report role
         */
        $user = $this->initializeUserLogin('Report');
        $uri = route('client.select');
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user)
                ->get($uri, $header);
        //Unauthorized http code
        $response->assertStatus(403);
        
    }

}
