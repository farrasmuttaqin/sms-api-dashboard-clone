<?php

namespace Tests\Unit\App\Http\Controllers\Auth;

use Firstwap\SmsApiDashboard\Entities\User;
use Firstwap\SmsApiDashboard\Http\Controllers\Auth\ForgotPasswordController;
use Firstwap\SmsApiDashboard\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Mews\Captcha\Facades\Captcha;
use Tests\TestCase;

class ForgotPasswordControllerTest extends TestCase
{

    /**
     * Test sendResetLinkEmail method with correct parameter
     *
     * @return void
     */
    public function test_sendResetLinkEmail_method_with_correct_parameters()
    {
        //prevent mail from being sent
        Notification::fake();
        //Mock captcha validation to return true
        Captcha::shouldReceive('check')->once()->andReturn(true);
        //create dummy user
        $user = factory(User::class)->make();
        //Mock and stub password broker instance
        $broker = Password::broker();
        $broker = \Mockery::mock('broker');

        $controller = new ForgotPasswordController();
        $request = Request::create('password/email', 'POST', ['email' => 'foo@bar.com', 'captcha' => 'foo']);

        /**
         * test if email address is exists on database and email is sent
         */
        $broker->shouldReceive('sendResetLink')->once()->andReturn(Password::RESET_LINK_SENT);
        Password::shouldReceive('broker')->once()->andReturn($broker);
        $response = $controller->sendResetLinkEmail($request);
        $this->assertEquals(trans('passwords.sent'), session('alert-success'));
    }

    /**
     * Test sendResetLinkEmail method
     *
     * @return void
     */
    public function test_sendResetLinkEmail_metho_with_email_does_not_exists()
    {
        //prevent mail from being sent
        Notification::fake();
        //Mock captcha validation to return true
        Captcha::shouldReceive('check')->once()->andReturn(true);
        //create dummy user
        $user = factory(User::class)->make();
        //Mock and stub password broker instance
        $broker = Password::broker();
        $broker = \Mockery::mock('broker');

        $controller = new ForgotPasswordController();
        $request = Request::create('password/email', 'POST', ['email' => 'foo@bar.com', 'captcha' => 'foo']);

        /**
         * Test if email address doesn't exists on database
         */
        $response = $controller->sendResetLinkEmail($request);
        $errors = $response->getSession()->get('errors');
        $this->assertNotEmpty($errors);
        $this->assertNotEmpty($errors->first('email'));
        $this->assertEquals(trans('passwords.user'), $errors->first('email'));
    }

}
