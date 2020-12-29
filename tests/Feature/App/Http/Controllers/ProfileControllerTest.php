<?php

namespace Tests\Feature\App\Http\Controllers;

use Firstwap\SmsApiDashboard\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{

    use DatabaseTransactions;
    
    /**
     * Test Visit Edit Profile
     */
    public function test_visit_edit_profile()
    {
        $uri = route('profile.edit');
        
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
        $response->assertViewIs('users.form');
    }
    
    
    /**
     * Test Update profile with correct parameter
     * 
     * @return void
     */
    public function test_update_user_with_correct_parameter()
    {
        Storage::fake();
        $userLoggin = $this->initializeUserLogin('Super Admin');
        $userLoggin->password = 'qwerty';
        $uri = route('profile.edit');
        $parameters = array_merge($userLoggin->toArray(), [
            'name' => 'UNIT TEST',
            'current_password' => 'qwerty',
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($userLoggin)
                ->post($uri, $parameters);

        $response->assertRedirect(route('profile.edit'));
        $userLoggin = $userLoggin->refresh();

        $this->assertEquals($userLoggin->name, 'UNIT TEST');

        $userLoggin = $this->initializeUserLogin('Report');
        $userLoggin->password = 'qwerty';
        $uri = route('profile.edit');
        $parameters = array_merge($userLoggin->toArray(), [
            'name' => 'UNIT TEST',
            'current_password' => 'qwerty',
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($userLoggin)
                ->post($uri, $parameters);

        $response->assertRedirect(route('profile.edit'));
        $userLoggin = $userLoggin->refresh();

        $this->assertEquals($userLoggin->name, 'UNIT TEST');
    }
    
    /**
     * Test Update profile with incorrect parameter
     * Test if current password wrong
     * @return void
     */
    public function test_update_user_with_incorrect_parameter()
    {
        Storage::fake();
        $userLoggin = $this->initializeUserLogin();
        $userLoggin->password = 'qwerty';
        $uri = route('profile.edit');
        $parameters = array_merge($userLoggin->toArray(), [
            'name' => 'UNIT TEST',
            'current_password' => 'adsadsa'
        ]);

        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($userLoggin)
                ->post($uri, $parameters);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrorsIn(trans('auth.failed'));
    }

}
