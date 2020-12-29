<?php

namespace Test\Unit\App\Http\Controllers\Auth;

use Firstwap\SmsApiDashboard\Entities\User;
use Firstwap\SmsApiDashboard\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mews\Captcha\Facades\Captcha;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{

    /**
     * Test Authenticated method
     * - test if user is active
     * - test if user is not active
     *
     * @return  void
     */
    public function test_authenticated_method()
    {
        //Initialize variable
        $controller = new LoginController();
        $user = factory(User::class)->make();
        $request = Request::create('/login');
        $request->setLaravelSession($this->app['session']->driver());

        /**
         * If user is active, it should redirect to dashboard
         */
        $result = $this->invokeMethod($controller, 'authenticated', [$request, $user]);
        $this->assertEquals(302, $result->status());
        $this->assertEquals(url('/'), $result->getTargetUrl());

        /**
         * If user is not active it should throw validationException and
         * will have errors messages
         */
        try {
            $user->active = 0;
            $result = $this->invokeMethod($controller, 'authenticated', [$request, $user]);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey($controller->username(), $errors);
            $this->assertTrue(in_array(trans('auth.disabled'), $errors[$controller->username()]));
        }
    }

    /**
     * Test validateLogin method
     *
     * @return  void
     */
    public function test_validateLogin_method()
    {
        //Initialize variable
        $controller = new LoginController();

        /**
         * test if the request don't have parameters
         */
        try {
            $request = Request::create('/login', 'POST', []);
            $this->invokeMethod($controller, 'validateLogin', [$request]);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey($controller->username(), $errors);
            $this->assertArrayHasKey('password', $errors);
            $this->assertArrayHasKey('captcha', $errors);
        }
        //Mock Captcha
        Captcha::shouldReceive('check')->times()->andReturn(true);

        /**
         * test if the request has all parameters
         * the function should not throw exception
         */
        $parameters = [
            $controller->username() => 'mbusite@1rstwap.com',
            'password' => 'foo',
            'captcha' => 'bar',
            'timezone' => '7'
        ];
        $request = Request::create('/login', 'POST', $parameters);
        $this->invokeMethod($controller, 'validateLogin', [$request]);

        /**
         * test if the request has empty parameters
         * the function should not throw exception
         */
        try {
            $parameters = [$controller->username() => '', 'password' => '', 'captcha' => ''];
            $request = Request::create('/login', 'POST', $parameters);
            $this->invokeMethod($controller, 'validateLogin', [$request]);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey($controller->username(), $errors);
            $this->assertEquals(trans('validation.required', ['attribute' => $controller->username()]), $errors[$controller->username()][0]);
            $this->assertArrayHasKey('password', $errors);
            $this->assertEquals(trans('validation.required', ['attribute' => 'password']), $errors['password'][0]);
            $this->assertArrayHasKey('captcha', $errors);
            $this->assertEquals(trans('validation.required', ['attribute' => 'captcha']), $errors['captcha'][0]);
        }
    }

    /**
     * Test refreshCaptcha method
     * refreshCaptcha method will return json
     * the json should has source attribute
     *
     * @return  void
     */
    public function test_refreshCaptcha_method()
    {
        //Initialize variable
        $controller = new LoginController();

        $request = Request::create('captcha/refresh', 'GET', []);
        $response = $controller->refreshCaptcha($request);
        $content = $response->content();
        $this->assertEquals(200, $response->status());
        $this->assertObjectHasAttribute('source', json_decode($content));
    }

}
