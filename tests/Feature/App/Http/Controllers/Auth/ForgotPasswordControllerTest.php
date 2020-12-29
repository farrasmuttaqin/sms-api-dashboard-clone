<?php

namespace Tests\Feature\App\Http\Controllers\Auth;

use Firstwap\SmsApiDashboard\Entities\User;
use Firstwap\SmsApiDashboard\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Mews\Captcha\Facades\Captcha;
use Tests\TestCase;

class ForgotPasswordControllerTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * Test request reset link page
     *
     * @return void
     */
    public function test_request_reset_link_page()
    {
        $uri = route('auth.password.request');

        //Test visit form request reset email
        $response = $this->get($uri);
        $response->assertViewIs('auth.passwords.email');
        $response->assertSuccessful();

        //Test visit form request reset email if user
        // already authenticated
        $user = factory(User::class)->create([
            'email' => 'foo@bar.com',
            'password' => bcrypt('rahasia'),
        ]);
        $response = $this->actingAs($user)->get($uri);
        $response->assertRedirect(url('/'));
    }

    /**
     * Test submit reset link form with correct query
     *
     * @return void
     */
    public function test_submit_reset_link_form_with_correct_query()
    {
        //prevent mail from being sent
        Notification::fake();
        //Mock Captcha
        Captcha::shouldReceive('check')->once()->andReturn(true);

        $uri = route('auth.password.email');

        //initial users
        $user = factory(User::class)->create([
            'email' => 'foo@bar.com',
            'password' => bcrypt('rahasia'),
        ]);

        //Test if submit request reset link with correct parameter
        $query = ['email' => $user->email, 'captcha' => 'xxxx'];
        $response = $this->post($uri, $query);
        $response->assertSessionHas('alert-success', trans('passwords.sent'));
        Notification::assertSentTo(
            [$user], ResetPasswordNotification::class
        );
    }

    /**
     * Test submit reset link form with wrong email
     *
     * @return void
     */
    public function test_submit_reset_link_form_with_wrong_email()
    {
        //prevent mail from being sent
        Notification::fake();
        //Mock Captcha
        Captcha::shouldReceive('check')->times(2)->andReturn(true, true);

        $uri = route('auth.password.email');

        //initial users
        $user = factory(User::class)->create([
            'email' => 'foo@bar.com',
            'password' => bcrypt('rahasia'),
        ]);

        //Test if submit request reset link with empty email
        $query = ['email' => '', 'captcha' => 'xxxx'];
        $response = $this->post($uri, $query);
        $response->assertSessionHasErrors('email');
        $this->assertEquals(trans('validation.required', ['attribute' => 'email']), session('errors')->first('email'));

        //Test if submit request reset link with wrong email
        $query2 = array_merge([], $query, ['email' => 'jhean@doe.com']);
        $response = $this->post($uri, $query2);
        $response->assertSessionHasErrors('email');
        $this->assertEquals(trans('passwords.user'), session('errors')->first('email'));
    }

    /**
     * Test submit reset link form with wrong captcha
     *
     * @return void
     */
    public function test_submit_reset_link_form_with_wrong_captcha()
    {
        //prevent mail from being sent
        Notification::fake();

        $uri = route('auth.password.email');

        //initial users
        $user = factory(User::class)->create([
            'email' => 'foo@bar.com',
            'password' => bcrypt('rahasia'),
        ]);

        //Test if submit request reset link with wrong captcha
        $query = ['email' => 'foo@bar.com', 'captcha' => 'xxxx'];
        $response = $this->post($uri, $query);
        $response->assertSessionHasErrors('captcha');
        $this->assertEquals(trans('validation.captcha'), session('errors')->first('captcha'));
    }

}
