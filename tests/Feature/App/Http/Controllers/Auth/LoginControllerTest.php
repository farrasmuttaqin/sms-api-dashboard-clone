<?php

namespace Test\Feature\App\Http\Controllers\Auth;

use Firstwap\SmsApiDashboard\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mews\Captcha\Facades\Captcha;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * Test login success
     * - test correct login view
     * - test post login if  credentials are correct
     *
     * @return  void
     */
    public function test_login_success()
    {
        $urlLogin = route('auth.login');

        /**
         * Test visit login page
         */
        $response = $this->get($urlLogin);
        $response->assertSuccessful();
        $response->assertViewIs('auth.login');

        /**
         * Test post login credential
         */
        Captcha::shouldReceive('check')->once()->andReturn(true);
        $query = [
            'email' => 'foo@bar.com',
            'password' => 'secret123',
            'captcha' => 'xxxx',
            'timezone' => '7'
        ];
        $user = factory(User::class)->create([
            'email' => $query['email'],
            'password' => bcrypt($query['password']),
        ]);
        $response = $this->post($urlLogin, $query);
        $response->assertRedirect(url('/'));

        //Test if visit login page after authenticated
        //It should redirect to dashboard again
        $response = $this->get($urlLogin);
        $response->assertRedirect(url('/'));
    }

    /**
     * Test if user already logged in than visit login page
     * 
     * @return void
     */
    public function test_user_already_logged_in_and_visit_login_page()
    {
        $urlLogin = route('auth.login');
        $user = factory(User::class)->create();

        /**
         * Test visit login page with authenticated user
         */
        $response = $this->actingAs($user)->get($urlLogin);
        
        //should redirect to dashboard
        $response->assertRedirect(url('/'));
    }
    
    
    /**
     * Test login with wrong request
     * - test if wrong password
     * - test if empty email
     * - test if empty captcha
     * - test if empty password
     *
     * @return  void
     */
    public function test_login_wrong_request()
    {
        $url = route('auth.login');
        Captcha::shouldReceive('check')->times()->andReturn(true);

        /**
         * Test post login with wrong password
         * It should has an error session with key email
         */
        $query = [
            'email' => 'foo@bar.com',
            'password' => 'secret123',
            'captcha' => 'xxxx',
            'timezone' => '7'
        ];
        $user = factory(User::class)->create([
            'email' => $query['email'],
            'password' => bcrypt('rahasia'),
        ]);
        $response = $this->post($url, $query);
        $response->assertSessionHasErrors(['email']);
        $this->assertEquals(trans('auth.failed'), session('errors')->first('email'));

        //empty email
        $query2 = array_merge([], $query, ['email' => '']);
        $response = $this->post($url, $query2);
        $response->assertSessionHasErrors(['email']);
        $this->assertEquals(trans('validation.required', ['attribute' => 'email']), session('errors')->first('email'));

        //empty captcha
        $query2 = array_merge([], $query, ['captcha' => '']);
        $response = $this->post($url, $query2);
        $response->assertSessionHasErrors(['captcha']);
        $this->assertEquals(trans('validation.required', ['attribute' => 'captcha']), session('errors')->first('captcha'));

        //empty password
        $query2 = array_merge([], $query, ['password' => '']);
        $response = $this->post($url, $query2);
        $response->assertSessionHasErrors(['password']);
        $this->assertEquals(trans('validation.required', ['attribute' => 'password']), session('errors')->first('password'));

        //user login are disabled
        $user->active = 0;
        $user->save();
        $query2 = array_merge([], $query, ['password' => 'rahasia']);
        $response = $this->post($url, $query2);
        $response->assertSessionHasErrors(['email']);
        $this->assertEquals(trans('auth.disabled'), session('errors')->first('email'));
    }

    /**
     * Test refreshCaptcha method
     * refreshCaptcha method can only access using ajax request
     * @return void
     */
    public function test_refreshCaptcha_method()
    {
        $uri = route('captcha.refresh');
        //Test request without ajax header
        //It should return 404 page/status
        $response = $this->get($uri);
        $response->assertStatus(404);

        //Test request without ajax header
        //It should return 404 page/status
        $response = $this->get($uri, ['X-Requested-With' => 'XMLHttpRequest']);
        $response->assertStatus(200);
        $response->assertJsonStructure(['source']);
    }

}
