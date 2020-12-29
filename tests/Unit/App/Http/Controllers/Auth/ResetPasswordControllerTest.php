<?php

namespace Tests\Unit\App\Http\Controllers\Auth;

use Firstwap\SmsApiDashboard\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordControllerTest extends TestCase
{

    /**
     * Test Reset password method success
     *
     * @return void
     */
    public function test_reset_password_method_success()
    {
        //Mock and stub password broker instance
        $broker = Password::broker();
        $broker = \Mockery::mock('broker');
        $broker->shouldReceive('reset')->once()->andReturn(Password::PASSWORD_RESET);
        //mock Password facade when call broker method
        //should return broker mock
        Password::shouldReceive('broker')->once()->andReturn($broker);

        $controller = new ResetPasswordController();
        $parameters = ['email' => 'foo@bar.com', 'password' => 'foobar', 'password_confirmation' => 'foobar', 'token' => 'xxxxx'];
        $request = Request::create('password/reset', 'POST', $parameters);

        /**
         * Test If reset password success
         */
        $response = $controller->reset($request);
        $message = $response->getSession()->get('alert-success');
        $this->assertEquals(trans('passwords.reset'), $message);
    }

    /**
     * Test Reset password if token invalid
     *
     * @return void
     */
    public function test_reset_method_with_token_invalid()
    {
        //Mock and stub password broker instance
        $broker = Password::broker();
        $broker = \Mockery::mock('broker');
        $broker->shouldReceive('reset')->once()->andReturn(Password::INVALID_TOKEN);
        //stub Password facade when call broker method
        //should return broker mock
        Password::shouldReceive('broker')->once()->andReturn($broker);

        $controller = new ResetPasswordController();

        /**
         * Test If Token is invalid
         */
        $parameters = ['email' => 'foo@bar.com', 'password' => 'foobar', 'password_confirmation' => 'foobar', 'token' => 'xxxxx'];
        $request = Request::create('password/reset', 'POST', $parameters);
        $response = $controller->reset($request);
        $message = $response->getSession()->get('errors')->first('email');
        $this->assertEquals(trans('passwords.token'), $message);
        
    }
    
    /**
     * Test Reset password if invalid user
     *
     * @return void
     */
    public function test_reset_method_with_invalid_user()
    {
        //Mock and stub password broker instance
        $broker = Password::broker();
        $broker = \Mockery::mock('broker');
        $broker->shouldReceive('reset')->once()->andReturn(Password::INVALID_USER);
        //stub Password facade when call broker method
        //should return broker mock
        Password::shouldReceive('broker')->once()->andReturn($broker);

        $controller = new ResetPasswordController();

        /**
         * Test If User is invalid
         */
        $parameters = ['email' => 'foo@bar.com', 'password' => 'foobar', 'password_confirmation' => 'foobar', 'token' => 'xxxxx'];
        $request = Request::create('password/reset', 'POST', $parameters);
        $response = $controller->reset($request);
        $message = $response->getSession()->get('errors')->first('email');
        $this->assertEquals(trans('passwords.user'), $message);
        
    }
}
