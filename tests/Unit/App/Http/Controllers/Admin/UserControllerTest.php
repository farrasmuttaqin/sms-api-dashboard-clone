<?php

namespace Tests\Unit\App\Http\Controllers\Admin;

use Firstwap\SmsApiDashboard\Entities\User;
use Firstwap\SmsApiDashboard\Http\Controllers\Admin\UserController;
use Firstwap\SmsApiDashboard\Libraries\Repositories\UserRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use DatabaseTransactions;
    
    /**
     * Test processRequestInput method
     *
     * @return  void
     */
    public function test_processRequestInput_method()
    {
        Storage::fake();
        $repo = new UserRepository;
        $controller = new UserController($repo);
        $input = [
            'name' => 'foo',
            'email' => 'foo@bar.com',
            'password' => 'qwerty',
            'password_confirmation' => 'qwerty',
            'active' => '1',
            'client_id' => '1',
            'roles' => '1,2,3',
            'api_users' => '1,2,3',
        ];
        $file = ['avatar' => UploadedFile::fake()->image('avatar.jpg')];
        $request = Request::create('/', 'POST', $input, [], $file);
        $return = $this->invokeMethod($controller, 'processRequestInput', [$request]);
        $this->assertTrue(is_array($return['roles']));
        $this->assertFalse(empty($return['roles']));
        $this->assertTrue(is_array($return['api_users']));
        $this->assertFalse(empty($return['api_users']));
        $this->assertNotEmpty($return['avatar']);
        Storage::assertExists($return['avatar']);

        /**
         * Test If roles and api_users empty string
         */
        $input = array_merge($input,['roles' => '', 'api_users' => '']);
        $request = Request::create('/', 'POST', $input, [], $file);
        $return = $this->invokeMethod($controller, 'processRequestInput', [$request]);
        $this->assertTrue(is_array($return['roles']));
        $this->assertTrue(empty($return['roles']));
        $this->assertTrue(is_array($return['api_users']));
        $this->assertTrue(empty($return['api_users']));
        $this->assertNotEmpty($return['avatar']);
        Storage::assertExists($return['avatar']);

        /**
         * Test If not avatar file
         */
        $request = Request::create('/', 'POST', $input);
        $return = $this->invokeMethod($controller, 'processRequestInput', [$request]);
        $this->assertArrayNotHasKey('avatar', $return);
        
        /**
         * Test If roles and api_users does not exists in request
         */
        unset($input['roles']);
        unset($input['api_users']);
        $request = Request::create('/', 'POST', $input, [], $file);
        $return = $this->invokeMethod($controller, 'processRequestInput', [$request]);
        $this->assertArrayNotHasKey('roles', $return);
        $this->assertArrayNotHasKey('api_users', $return);
        $this->assertNotEmpty($return['avatar']);
        Storage::assertExists($return['avatar']);
    }

    /**
     * Test Store method with correct input
     * And success insert data to database
     * 
     * @return void
     */
    public function test_store_method()
    {
        Storage::fake();
        $repo = $this->getMockBuilder(UserRepository::class)
                ->setMethods(['save'])
                ->getMock();
        
        $repo->expects($this->once())
                ->method('save')
                ->willReturn(true);

        $controller = new UserController($repo);
        $input = [
            'name' => 'foo',
            'email' => 'foo@bar.com',
            'password' => 'qwerty',
            'password_confirmation' => 'qwerty',
            'active' => '1',
            'client_id' => '1',
            'roles' => '1,2,3',
            'api_users' => '1,2,3',
        ];
        $file = ['avatar' => UploadedFile::fake()->image('avatar.jpg')];
        
        /**
         * Test if success save user data to database
         */
        $request = Request::create('/', 'POST', $input, [], $file);
        $return = $controller->store($request);
        $this->assertInstanceOf(RedirectResponse::class, $return);
        $session = session('alert-success');
        $this->assertEquals(trans('validation.success_save',['name'=>'user '.$input['name']]),$session);
        
    }

    /**
     * Test Store method with correct input
     * but fail inserted data to database
     * 
     * @return void
     */
    public function test_store_method_if_failed_insert_to_database()
    {
        Storage::fake();
        $repo = $this->getMockBuilder(UserRepository::class)
                ->setMethods(['save'])
                ->getMock();
        
        $repo->expects($this->once())
                ->method('save')
                ->willReturn(false);

        $controller = new UserController($repo);
        $input = [
            'name' => 'foo',
            'email' => 'foo@bar.com',
            'password' => 'qwerty',
            'password_confirmation' => 'qwerty',
            'active' => '1',
            'client_id' => '1',
            'roles' => '1,2,3',
            'api_users' => '1,2,3',
        ];
        $file = ['avatar' => UploadedFile::fake()->image('avatar.jpg')];
        
        /**
         * Test If failed store to database
         */
        $request = Request::create('/', 'POST', $input, [], $file);
        $return = $controller->store($request);
        $this->assertInstanceOf(RedirectResponse::class, $return);
        $session = session('errors');
        $this->assertEquals(trans('validation.failed_save',['name'=>'user']), $session->first());
    }
    
    /**
     * Test Store method with correct input
     * And success insert data to database
     * 
     * @return void
     */
    public function test_update_method()
    {
        $repo = new UserRepository();
        $controller = new UserController($repo);
        
        //Update Using Super Admin User
        $user = $this->initializeUserLogin('Super Admin');
        auth()->login($user);
        $otherUser = User::with(['roles','apiUsers','client'])
                        ->where($user->getKeyName(), '!=', $user->getKey())
                        ->first();
        $this->assertNotNull($otherUser);
        $input = [
            'name' => 'UPDATE NAME USER',
            'email' => $otherUser->email,
            'active' => $otherUser->active,
            'client_id' => $otherUser->client_id,
            'roles' => $otherUser->roles->pluck('role_id')->implode(','),
            'api_users' => $otherUser->apiUsers->pluck('user_id')->implode(','),
        ];
        $file = ['avatar' => UploadedFile::fake()->image('avatar.jpg')];
        $request = Request::create('/', 'POST', $input, [], $file);
        $return = $controller->update($request,$otherUser->getKey());
        $this->assertInstanceOf(RedirectResponse::class, $return);
        $session = session('alert-success');
        $this->assertEquals(trans('app.success_update',['name'=>'user '.$input['name']]), $session);
        $otherUser->refresh();
        $this->assertEquals(url('img/'.$otherUser->avatar), $otherUser->avatar_url);
        $file = ['avatar' => UploadedFile::fake()->image('avatar.jpg')];
        $request = Request::create('/', 'POST', $input, [], $file);
        $return = $controller->update($request,$otherUser->getKey());
        Storage::delete($otherUser->refresh()->avatar);
        
        Storage::fake();
        //Update Using Company Admin
        //Test if user is belong to other company
        $user = $this->initializeUserLogin('Admin');
        auth()->login($user);
        $otherUser = User::with(['roles','apiUsers','client'])
                        ->where($user->getKeyName(), '!=', $user->getKey())
                        ->where('client_id','!=', $user->client_id)
                        ->first();
        $this->assertNotNull($otherUser);
        $input = [
            'name' => 'UPDATE NAME USER',
            'email' => $otherUser->email,
            'active' => $otherUser->active,
            'client_id' => $otherUser->client_id,
            'roles' => $otherUser->roles->pluck('role_id')->implode(','),
            'api_users' => $otherUser->apiUsers->pluck('user_id')->implode(','),
        ];
        $file = ['avatar' => UploadedFile::fake()->image('avatar.jpg')];
        $request = Request::create('/', 'POST', $input, [], $file);
        $return = $controller->update($request, $otherUser->getKey());
        $this->assertInstanceOf(RedirectResponse::class, $return);
        $session = session('errors');
        $this->assertEquals(trans('validation.exists',['attribute'=>'user']), $session->first('user'));

        //Update Using Report users
        //This should throw AuthorizationException:
        $user = $this->initializeUserLogin('Report');
        auth()->login($user);
        $otherUser = User::with(['roles','apiUsers','client'])
                        ->where($user->getKeyName(), '!=', $user->getKey())
                        ->first();
        $this->assertNotNull($otherUser);
        $input = [
            'name' => 'UPDATE NAME USER',
            'email' => $otherUser->email,
            'active' => $otherUser->active,
            'client_id' => $otherUser->client_id,
            'roles' => $otherUser->roles->pluck('role_id')->implode(','),
            'api_users' => $otherUser->apiUsers->pluck('user_id')->implode(','),
        ];
        $this->expectException(AuthorizationException::class);
        $file = ['avatar' => UploadedFile::fake()->image('avatar.jpg')];
        $request = Request::create('/', 'POST', $input, [], $file);
        $return = $controller->update($request,$otherUser->getKey());
        
    }

    /**
     * Test table method in UserController
     * This function use to get data user for datatable html
     * 
     * @return void
     */
    public function test_table_method()
    {
        $repo = new UserRepository;
        $controller = new UserController($repo);
        $request = Request::create('/','GET');

        /**
         * Test table method if user have Super Admin role
         */
        $user = $this->initializeUserLogin('Super Admin');
        auth()->login($user);
        $response = $controller->table($request);
        $json = $response->getContent();
        $this->assertJson($json);
        $object = json_decode($json);
        $this->assertObjectHasAttribute('total', $object);
        $this->assertObjectHasAttribute('data', $object);
        $this->assertNotEmpty($object->data);

        /**
         * Test table method if user have Super Admin role
         */
        $user = $this->initializeUserLogin('Admin');
        auth()->login($user);
        $response = $controller->table($request);
        $json = $response->getContent();
        $this->assertJson($json);

        /**
         * Test table method if user have Super Admin role
         */
        $user = $this->initializeUserLogin('Report');
        auth()->login($user);
        $this->expectException(AuthorizationException::class);
        $response = $controller->table($request);
    }
}
