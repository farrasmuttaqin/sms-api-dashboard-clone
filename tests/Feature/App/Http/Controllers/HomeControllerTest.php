<?php

namespace Tests\Feature\App\Http\Controllers;

use Firstwap\SmsApiDashboard\Entities\ApiUser;
use Firstwap\SmsApiDashboard\Entities\Client;
use Firstwap\SmsApiDashboard\Entities\User;
use Firstwap\SmsApiDashboard\Libraries\Repositories\ApiUserRepository;
use Firstwap\SmsApiDashboard\Libraries\Repositories\MessageRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * Test Visit Dashboard
     */
    public function test_visit_dashboard()
    {
        $uri = url('/');

        /**
         * Test Visit dashboard without login first
         */
        $response = $this->get($uri);
        //Should redirect to login page
        $response->assertRedirect(route('auth.login'));
        /**
         * Test authenticate user
         */
        $user = factory(User::class)->make();
        $response = $this->actingAs($user)->get($uri);

        $response->assertViewIs('home');
        $response->assertViewHas('totalCredit', 0);
        $data = $response->getOriginalContent()->getData();
        $this->assertArrayHasKey('apiUsers', $data);
        $this->assertEmpty($data['apiUsers']);
    }

    /**
     * Test to check apiusers is postpaid or not
     */
    public function test_user_is_postpaid(){
        $uri = url('/');
        $userLoggin = $this->initializeUserLogin();

        ApiUser::unguard();
        $inactiveNonPostpaidApiUsers = $userLoggin->apiUsers->get(0);
        $inactiveNonPostpaidApiUsers->update(['is_postpaid' => 0, 'active' => 0]);
        $activePostpaidApiUsers = $userLoggin->apiUsers->get(1);
        $activePostpaidApiUsers->update(['is_postpaid' => 1, 'active' => 1]);
        ApiUser::reguard();

        $response = $this->actingAs($userLoggin)->get($uri);
        $response->assertViewIs('home');
        $response->assertSeeText($activePostpaidApiUsers->user_name);
        $response->assertSeeText( trans('app.unlimited') );
        $response->assertDontSeeText('<th>'.$inactiveNonPostpaidApiUsers->user_name."</th>");
    }

    /**
    * Test number format in view
    */
    public function test_view_credit_has_number_format(){
        $uri = url('/');
        $userLoggin = $this->initializeUserLogin();
        $response = $this->actingAs($userLoggin)->get($uri);
        $response->assertViewIs('home');
        $apiUser = $userLoggin->apiUsers->all();
        $credit  = 0;
        foreach ($apiUser as $key) {
            if ($key->is_postpaid === 0 && $key->active===1) {
                $credit    += $key->credit;
                $key->credit = number_format($key->credit, 0, ",", ".");
            }
        }
        $response->assertViewHas('totalCredit', number_format($credit, 0, ",", "."));
    }

    /**
     * Test get summary data
     */
    public function test_get_summary()
    {
        $header = ['X-Requested-With' => 'XMLHttpRequest'];
        $userLoggin = $this->initializeUserLogin();

        /**
         * Test daily summary
         */
        $uri = url('/?summary=daily');
        $response = $this->actingAs($userLoggin)->get($uri, $header);
        $response->assertJsonStructure(['total','rejected','delivered','sent','undelivered']);

        /**
         * Test weekly summary
         */
        $uri = url('/?summary=weekly');
        $response = $this->actingAs($userLoggin)->get($uri, $header);
        $response->assertJsonStructure(['total','rejected','delivered','sent','undelivered']);

        /**
         * Test monthly summary
         */
        $uri = url('/?summary=monthly');
        $response = $this->actingAs($userLoggin)->get($uri, $header);
        $response->assertJsonStructure(['total','rejected','delivered','sent','undelivered']);

        /**
         * Test unknown parameter
         */
        $uri = url('/?summary=xxxxx');
        $response = $this->actingAs($userLoggin)->get($uri, $header);
        $response->assertJsonCount(0);
    }

}
