<?php

namespace Tests\Unit\App\Http\Controllers;

use Firstwap\SmsApiDashboard\Http\Controllers\ProfileController;
use Firstwap\SmsApiDashboard\Libraries\Repositories\UserRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use function auth;
use function route;
use function trans;

class ProfileControllerTest extends TestCase
{

    use DatabaseTransactions;
    
    /**
     * Test Update profile with correct parameter
     * 
     * @return void
     */
    public function test_update_user_with_correct_parameter()
    {
        Storage::fake();
        $userLoggin = $this->initializeUserLogin();
        $userLoggin->password = 'qwerty';
        auth()->login($userLoggin);
        $repo = new UserRepository();
        $controller = new ProfileController($repo);
        $uri = route('profile.edit');
        $parameters = array_merge($userLoggin->toArray(), [
            'name' => 'UNIT TEST',
            'current_password' => 'qwerty',
        ]);

        $request = Request::create($uri,'POST',$parameters);
        $response = $controller->update($request);

        $this->assertEquals($userLoggin->name, 'UNIT TEST');
    }
    
    /**
     * Test Update profile with incorrect parameter
     * Test if current password wrong
     *
     * @return void
     */
    public function test_update_user_with_incorrect_parameter()
    {
        Storage::fake();
        $userLoggin = $this->initializeUserLogin('Report');
        $userLoggin->password = 'qwerty';
        auth()->login($userLoggin);
        $repo = new UserRepository();
        $controller = new ProfileController($repo);
        $uri = route('profile.edit');
        $parameters = array_merge($userLoggin->toArray(), [
            'name' => 'UNIT TEST',
            'current_password' => 'asdadas',
        ]);

        $request = Request::create($uri,'POST',$parameters);
        $response = $controller->update($request);
        
        $url = $response->getTargetUrl();
        $errors = session('errors');
        $this->assertEquals(route('profile.edit'),$url);
        $this->assertEquals(trans('auth.failed'),$errors->first());
        
        /**
         * Test If failed saving data
         */
        $repo = $this->getMockBuilder(UserRepository::class)
                ->setMethods(['save'])
                ->getMock();
        
        $repo->expects($this->once())
                ->method('save')
                ->willReturn(false);
        $controller = new ProfileController($repo);
        $userLoggin = $this->initializeUserLogin('Report');
        $userLoggin->password = 'qwerty';
        auth()->login($userLoggin);
        
        $uri = route('profile.edit');
        $parameters = array_merge($userLoggin->toArray(), [
            'name' => 'UNIT TEST',
            'current_password' => 'qwerty',
        ]);
        $request = Request::create($uri,'POST',$parameters);
        $response = $controller->update($request);
        $errors = session('errors');
        $this->assertEquals(trans('validation.failed_save', ['name' => trans('app.your_profile')]),$errors->first());
        
        
        
    }

}
