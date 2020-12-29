<?php

namespace Tests\Feature\App\Http\Controllers\Auth;

use Carbon\Carbon;
use Firstwap\SmsApiDashboard\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ResetPasswordControllerTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * Test Visit Reset password
     *
     * @return void
     */
    public function test_visit_reset_form()
    {
        //initial users
        $user = factory(User::class)->create([
            'email' => 'foo@bar.com',
            'password' => bcrypt('rahasia'),
        ]);
        //Init reset token to users
        $broker = $this->app['auth.password.broker']->getRepository();
        $token = $broker->create($user);
        $user = $user->fresh();

        //Test visit reset form
        $uri = route('auth.password.reset', ['token' => $token]);
        $response = $this->get($uri);
        //should show view reset password form
        $response->assertSuccessful();
        $response->assertViewIs('auth.passwords.reset');

        //Test visit reset form without token
        $uri = route('auth.password.reset', ['token' => null]);
        $response = $this->get($uri);
        //should show view request reset link form
        $response->assertStatus(200);
        $response->assertViewIs('auth.passwords.email');
    }

    /**
     * Test Submit Reset password form With correct value
     * The input form are email, token, password, password_confirmation
     *
     * @return void
     */
    public function test_submit_reset_form_with_correct_query()
    {
        //initial users
        $user = factory(User::class)->create([
            'email' => 'jhon@doe.com',
            'password' => bcrypt('rahasia'),
        ]);
        //Init reset token to users
        $broker = $this->app['auth.password.broker']->getRepository();
        $token = $broker->create($user);
        $user = $user->fresh();

        //Test submit reset form with correct query
        $uri = route('auth.password.request');
        $query = [
            'token' => $token,
            'email' => $user->email,
            'password' => 'qwerty',
            'password_confirmation' => 'qwerty'
        ];

        $response = $this->post($uri, $query);
        //should redirect to home/dashboard
        $response->assertRedirect('/');
    }

    /**
     * Test Submit Reset password form With wrong token and expired time
     *
     * @return void
     */
    public function test_submit_reset_form_with_wrong_token_and_expired_time()
    {
        //initial users
        $user = factory(User::class)->create([
            'email' => 'jhon@doe.com',
            'password' => bcrypt('rahasia'),
        ]);

        //Init reset token to users
        $broker = $this->app['auth.password.broker']->getRepository();
        $token = $broker->create($user);
        $user = $user->fresh();

        //Test if submit reset form with wrong token query
        $uri = route('auth.password.request');
        $query = [
            'token' => "loreminsumdolorsitametoken",
            'email' => $user->email,
            'password' => 'qwerty',
            'password_confirmation' => 'qwerty'
        ];
        $response = $this->post($uri, $query);
        $response->assertSessionHasErrors('email');
        $this->assertEquals(trans('passwords.token'), session('errors')->first('email'));

        //Test if submit reset form with expired token
        $user->expired_token = Carbon::parse('-100 hour')->toDateTimeString();
        $user->save();
        $query2 = array_merge([], $query, ['token' => $token]);
        $response = $this->post($uri, $query2);
        $response->assertSessionHasErrors('email');
        $this->assertEquals(trans('passwords.token'), session('errors')->first('email'));
    }

}
