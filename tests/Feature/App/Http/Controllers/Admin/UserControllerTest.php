<?php

namespace Tests\Feature\App\Http\Controllers\Admin;

use Firstwap\SmsApiDashboard\Entities\ApiUser;
use Firstwap\SmsApiDashboard\Entities\Client;
use Firstwap\SmsApiDashboard\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tests\TestCase;

class UserControllerTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Test Visit User management page
     *
     * @return  void
     */
    public function test_visit_user_management_page()
    {
        $uri = route('user.index');
        
        /**
         * Test If user is unauthenticated
         */
        $response = $this->get($uri);
        //Should redirect to login page
        $response->assertRedirect(route('auth.login'));

        /**
         * Test If authenticated user doesn't have privilege to visit user page
         */
        $dummy = factory(User::class)->create();
        $response = $this
                ->actingAs($dummy)
                ->get($uri);
        //Should redirect to dashboard
        $response->assertRedirect(url('/'));

        /**
         * Test If authenticated user has privilege to visit user page
         */
        $user = $this->initializeUserLogin();
        $response = $this
                ->actingAs($user)
                ->get($uri);

        $response->assertSuccessful();
        $response->assertViewIs('users.index');
        $response->assertSeeText(trans('app.user_page'));
    }

    /**
     * Test Visit Create User page
     *
     * @return  void
     */
    public function test_visit_create_user_page()
    {
        $user = $this->initializeUserLogin();
        $uri = route('user.create');
        $response = $this
                ->actingAs($user)
                ->get($uri);

        $response->assertSuccessful();
        $response->assertViewIs('users.form');
        $response->assertSeeText(trans('app.user_create'));
    }

    /**
     * Test Visit Edit User page
     *
     * @return  void
     */
    public function test_visit_edit_user_page()
    {
        $userLogin = $this->initializeUserLogin();

        //Test visit with existing user
        $dummyUser = factory(User::class)->create();
        $uri = route('user.edit', ['user' => $dummyUser->getKey()]);
        $response = $this
                ->actingAs($userLogin)
                ->get($uri);

        $response->assertSuccessful();
        $response->assertViewIs('users.form');
        $response->assertSeeText(trans('app.user_edit'));

        //Test if user doesn't exists in database
        $uri = route('user.edit', ['user' => 0]);
        $response = $this
                ->actingAs($userLogin)
                ->get($uri);

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHasErrors(['user']);
    }

    /**
     * Test Update user with correct parameter
     * 
     * @return void
     */
    public function test_update_user_with_correct_parameter()
    {
        $user = factory(User::class)->create();
        $userLoggin = $this->initializeUserLogin();
        $client = Client::has('apiUsers')->first();
        $uri = route('user.edit', ['user' => $user->getKey()]);
        $parameters = array_merge($user->toArray(), [
            'name' => 'UNIT TEST',
            'roles' => $userLoggin->roles->pluck('role_id')->implode(','),
            'client_id' => $client->getKey(),
        ]);

        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($userLoggin)
                ->post($uri, $parameters);

        $response->assertRedirect(route('user.index'));
        $user = $user->refresh();

        $this->assertEquals($user->name, 'UNIT TEST');
    }

    /**
     * Test Update user with incorrect parameter
     * 
     * @return void
     */
    public function test_update_user_with_incorrect_parameter()
    {
        /**
         * Update when user doesn't not exists
         */
        $uri = route('user.edit', ['user' => 0]);
        $userLoggin = $this->initializeUserLogin();

        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($userLoggin)
                ->post($uri, []);

        $response->assertSessionHasErrors(['user']);
        $this->assertEquals(
                trans('validation.exists', ['attribute' => 'user']),
                session('errors')->first('user')
        );
        
        /**
         * Update when user exists but there are no input request
         */
        $user = factory(User::class)->create();
        $uri = route('user.edit', ['user' => $user->getKey()]);
        $userLoggin = $this->initializeUserLogin();

        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($userLoggin)
                ->post($uri, []);

        $response->assertSessionHasErrors(['name','email','roles','client_id']);
    }

    /**
     * Test Update user by user role
     * 
     * @return void
     */
    public function test_update_user_by_user_role()
    {
        /**
         * Update by Super Admin
         */
        $user = factory(User::class)->create();
        $uri = route('user.edit', ['user' => $user->getKey()]);
        $userLoggin = $this->initializeUserLogin('Super Admin');
        $role = $userLoggin->roles
                ->where('role_name','Report')
                ->pluck('role_id')
                ->implode(',');

        $parameters = array_merge($user->toArray(), [
            'name' => 'UNIT TEST',
            'roles' => $role,
            'client_id' => $userLoggin->client_id,
        ]);
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($userLoggin)
                ->post($uri, $parameters);
        $response->assertRedirect(route('user.index'));
        $user = $user->refresh();
        $this->assertEquals($user->name, 'UNIT TEST');

        /**
         * Update by Admin Company
         * With same company
         */
        $userLoggin = $this->initializeUserLogin('Admin');
        $user = factory(User::class)->create([
            'client_id' => $userLoggin->client_id
        ]);
        $uri = route('user.edit', ['user' => $user->getKey()]);
        $role = $userLoggin->roles
                ->where('role_name','Report')
                ->pluck('role_id')
                ->implode(',');

        $parameters = array_merge($user->toArray(), [
            'name' => 'UNIT TEST',
            'roles' => $role,
            'client_id' => $user->client_id,
        ]);

        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($userLoggin)
                ->post($uri, $parameters);

        $response->assertRedirect(route('user.index'));
        $user = $user->refresh();
        $this->assertEquals($user->name, 'UNIT TEST');

        /**
         * Update by Admin Company
         * With same company but try to change with different company
         */
        $userLoggin = $this->initializeUserLogin('Admin');
        $user = factory(User::class)->create([
            'client_id' => $userLoggin->client_id
        ]);

        $uri = route('user.edit', ['user' => $user->getKey()]);
        $role = $userLoggin->roles
                ->where('role_name','Report')
                ->pluck('role_id')
                ->implode(',');

        $client = Client::where('client_id','!=',$userLoggin->client_id)->first();
        $parameters = array_merge($user->toArray(), [
            'name' => 'UNIT TEST',
            'roles' => $role,
            'client_id' => $client->getKey()
        ]);
        
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($userLoggin)
                ->post($uri, $parameters);
        
        //Should redirect to homepage because unauthorize
        //user admin company can not change client id to other company
        $response->assertRedirect(url('/'));
        //No change for user
        $new = $user->refresh();
        $this->assertEquals($user->name, $new->name);

        /**
         * Update by user with Report Role
         */
        $userLoggin = $this->initializeUserLogin('Report');
        $user = factory(User::class)->create();
        $uri = route('user.edit', ['user' => $user->getKey()]);
        $role = $userLoggin->roles
                ->where('role_name','Report')
                ->pluck('role_id')
                ->implode(',');

        $parameters = array_merge($user->toArray(), [
            'name' => 'UNIT TEST',
            'roles' => $role,
            'client_id' => $userLoggin->client_id,
        ]);
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($userLoggin)
                ->post($uri, $parameters);

        //Should redirect to homepage because unauthorize
        //user report can't edit user
        $response->assertRedirect(url('/'));
        //No change for user
        $new = $user->refresh();
        $this->assertEquals($user->name, $new->name);
    }
    
    /**
     * Test Request user data with table format
     * Test if get table data without XMLHttpRequest header
     *
     * @return void
     */
    public function test_get_table_data_for_user()
    {
        $user = $this->initializeUserLogin();

        //Test get table without XMLHttpRequest header
        $uri = route('user.table');
        $response = $this
                ->actingAs($user)
                ->get($uri);
        //HTTP Status 404 Not Found
        $response->assertStatus(404);

        //Test get table with XMLHttpRequest header
        $response = $this
                ->actingAs($user)
                ->get($uri, ['X-Requested-With' => 'XMLHttpRequest']);
        //HTTP Status 200 OK
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'total']);

        /**
         * Test If User with Admin Role get data
         */
        $user = $this->initializeUserLogin('Admin');
        $response = $this
                ->actingAs($user)
                ->get($uri, ['X-Requested-With' => 'XMLHttpRequest']);
        //HTTP Status 200 OK
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'total']);

        /**
         * Test If User with Report role get data
         */
        $user = $this->initializeUserLogin('Report');
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user)
                ->get($uri, ['X-Requested-With' => 'XMLHttpRequest']);
        //HTTP Status 403 Unauthorize

        $response->assertStatus(403);
        $response->assertJsonStructure(['errors']);
    }
 
    /**
     * Test Request user data with table format
     * Test if get table data without XMLHttpRequest header
     *
     * @return void
     */
    public function test_get_table_data_with_search()
    {
        $user = $this->initializeUserLogin();
        //Test if search inactive user
        User::unguard();
        $dummy = factory(User::class)->create([
            'active' => 0
        ]);
        User::unguard(false);
        $uri = route('user.table',['active' => 0]);
        $response = $this
                ->actingAs($user)
                ->get($uri, ['X-Requested-With' => 'XMLHttpRequest']);
        //HTTP Status 200 OK
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'total']);
        $data = $response->baseResponse->getContent();
        $object = json_decode($data);
        $this->assertNotEmpty($object->data);
        $this->assertEquals(0, $object->data[0]->active);
        
        //Test if search email user
        $dummy = factory(User::class)->create([
            'email' => 'abc@def.com'
        ]);
        $uri = route('user.table',['email' => 'abc@']);
        $response = $this
                ->actingAs($user)
                ->get($uri, ['X-Requested-With' => 'XMLHttpRequest']);
        //HTTP Status 200 OK
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'total']);
        $data = $response->baseResponse->getContent();
        $object = json_decode($data);
        $this->assertNotEmpty($object->data);
        $this->assertEquals('abc@def.com', $object->data[0]->email);
        //Test if search name user
        $dummy = factory(User::class)->create([
            'name' => 'Wakakaka Woke'
        ]);
        $uri = route('user.table',['name' => 'Wakakaka']);
        $response = $this
                ->actingAs($user)
                ->get($uri, ['X-Requested-With' => 'XMLHttpRequest']);
        //HTTP Status 200 OK
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'total']);
        $data = $response->baseResponse->getContent();
        $object = json_decode($data);
        $this->assertNotEmpty($object->data);
        $this->assertEquals('Wakakaka Woke', $object->data[0]->name);
        //Test if search client_id
        $dummy = factory(User::class)->create([
            'client_id' => $user->client_id
        ]);
        $uri = route('user.table',['client_id' => $user->client_id]);
        $response = $this
                ->actingAs($user)
                ->get($uri, ['X-Requested-With' => 'XMLHttpRequest']);
        //HTTP Status 200 OK
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'total']);
        $data = $response->baseResponse->getContent();
        $object = json_decode($data);
        $this->assertNotEmpty($object->data);
        $this->assertEquals($user->client->company_name, $object->data[0]->company_name);
        
    }
    /**
     * Test If Create user with correct input
     *
     * @return array
     */
    public function test_submit_form_with_correct_input()
    {
        Storage::fake();
        $client = Client::has('apiUsers')->first();
        $faker = \Faker\Factory::create();
        $user = $this->initializeUserLogin();
        $input = [
            'name' => $faker->name(),
            'email' => $faker->freeEmail,
            'password' => 'qwerty',
            'password_confirmation' => 'qwerty',
            'active' => '1',
            'client_id' => $client->client_id,
            'roles' => $user->roles->pluck('role_id')->implode(','),
            'api_users' => $client->apiUsers->pluck('user_id')->implode(','),
            'avatar' => UploadedFile::fake()->image('avatar.jpg')
        ];

        $uri = route('user.create');
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user)
                ->post($uri, $input);
        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('alert-success', trans('validation.success_save', ['name' => 'user ' . $input['name']]));
        $user = User::whereEmail($input['email'])->first();
        $this->assertNotNull($user);
        $this->assertTrue(isset($user->getAttributes()['avatar']));
        Storage::assertExists($user->getAttributes()['avatar']);

        return $input;
    }

    /**
     * Test If Create user with correct input
     * and authenticated user have super Admin, Company Admin, or Report role
     * @return array
     */
    public function test_submit_form_with_correct_input_with_different_user_role()
    {
        Storage::fake();
        $uri = route('user.create');
        $faker = \Faker\Factory::create();
        
        /**
         * Test If User have Super Admin Role
         */
        $user = $this->initializeUserLogin('Admin');
        $input = [
            'name' => $faker->name(),
            'email' => $faker->freeEmail,
            'password' => 'qwerty',
            'password_confirmation' => 'qwerty',
            'active' => '1',
            'client_id' => $user->client_id,
            'roles' => $user->roles->pluck('role_id')->implode(','),
            'api_users' => $user->client->apiUsers->random('2')->pluck('user_id')->implode(','),
            'avatar' => UploadedFile::fake()->image('avatar.jpg')
        ];
        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user)
                ->post($uri, $input);
        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('alert-success', trans('validation.success_save', ['name' => 'user ' . $input['name']]));
        
        /**
         * Test If User have Report Role
         */
        $user2 = $this->initializeUserLogin('Report');
        $input = [
            'name' => $faker->name(),
            'email' => $faker->freeEmail,
            'password' => 'qwerty',
            'password_confirmation' => 'qwerty',
            'active' => '1',
            'client_id' => $user->client_id,
            'roles' => $user->roles->pluck('role_id')->implode(','),
            'api_users' => $user->client->apiUsers->random('2')->pluck('user_id')->implode(','),
            'avatar' => UploadedFile::fake()->image('avatar.jpg')
        ];

        $response = $this
                ->withoutMiddleware('web')
                ->actingAs($user2)
                ->post($uri, $input);
        
        $response->assertSessionHasErrors(['unauthorized']);
    }

    /**
     * Test If Create user with incorrect input
     *
     * @return void
     */
    public function test_submit_form_with_incorrect_input()
    {
        Storage::fake();
        $client = Client::has('apiUsers')->first();
        $faker = \Faker\Factory::create();
        $user = $this->initializeUserLogin();

        /**
         * Test if the request without name, and email input
         */
        $input = [
            'name' => '',
            'password' => 'qwerty',
            'password_confirmation' => 'qwerty',
            'active' => '1',
            'client_id' => $client->client_id,
            'roles' => $user->roles->pluck('role_id')->implode(','),
            'api_users' => $client->apiUsers->pluck('user_id')->implode(',')
        ];
        $uri = route('user.create');
        $response = $this->withoutMiddleware()->post($uri, $input);
        $response->assertSessionHasErrors(['name' => trans('validation.required', ['attribute' => 'name'])]);
        $response->assertSessionHasErrors(['email' => trans('validation.required', ['attribute' => 'email'])]);

        /**
         * Test if the request with existing email
         */
        $input = [
            'name' => $faker->name,
            'email' => $user->email,
            'password' => 'qwerty',
            'password_confirmation' => 'qwerty',
            'active' => '1',
            'client_id' => $client->client_id,
            'roles' => $user->roles->pluck('role_id')->implode(','),
            'api_users' => $client->apiUsers->pluck('user_id')->implode(',')
        ];
        $uri = route('user.create');
        $response = $this->withoutMiddleware()->post($uri, $input);
        $response->assertSessionHasErrors(['email' => trans('validation.unique', ['attribute' => 'email'])]);

        /**
         * Test if the request with doesn't client_id
         */
        $input = [
            'name' => $faker->name,
            'email' => $user->email,
            'password' => 'qwerty',
            'password_confirmation' => 'qwerty',
            'active' => '1',
            'client_id' => '0',
            'roles' => $user->roles->pluck('role_id')->implode(','),
            'api_users' => $client->apiUsers->pluck('user_id')->implode(',')
        ];

        $uri = route('user.create');
        $response = $this->withoutMiddleware()->post($uri, $input);
        $response->assertSessionHasErrors(['client_id' => trans('validation.exists', ['attribute' => trans('app.company')])]);
    }

    /**
     * Test Create new user with wrong avatar format
     *
     * @return  void
     */
    public function test_upload_avatar_with_wrong_format()
    {
        Storage::fake();
        $client = Client::has('apiUsers')->first();
        $user = $this->initializeUserLogin();
        /**
         * Test if avatar is not image file
         */
        $input = [
            'name' => 'Foo Bar',
            'email' => 'foo@bar.com',
            'password' => 'qwerty',
            'password_confirmation' => 'qwerty',
            'active' => '1',
            'client_id' => $client->client_id,
            'roles' => $user->roles->pluck('role_id')->implode(','),
            'api_users' => $client->apiUsers->pluck('user_id')->implode(','),
            'avatar' => UploadedFile::fake()->create('avatar.pdf', 100)
        ];
        $uri = route('user.create');
        $response = $this->withoutMiddleware()->post($uri, $input);
        $response->assertSessionHasErrors(['avatar' => trans('validation.image', ['attribute' => 'avatar'])]);

        /**
         * Test If avatar is an image and has 1000 KB size
         */
        $file = UploadedFile::fake()->image('avatar.png');
        $input['avatar'] = tap($file, function($file) {
            $file->sizeToReport = 1000 * 1024;
        });
        $uri = route('user.create');
        $response = $this->withoutMiddleware()->post($uri, $input);
        $errors = ['avatar' => trans('validation.max.file', ['attribute' => 'avatar', 'max' => 500])];
        $response->assertSessionHasErrors($errors);
    }

    /**
     * Test Delete User with correct parameter
     * 
     * @return void
     */
    public function test_delete_user_with_correct_parameter()
    {
        $input = $this->test_submit_form_with_correct_input();
        $userLoggin = $this->initializeUserLogin();

        $user = User::where('email', $input['email'])->first();
        $uri = route('user.delete', ['user' => $user->getKey()]);

        $response = $this->withoutMiddleware()
                ->actingAs($userLoggin)
                ->delete($uri,[],['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        $response->assertJson(['deleted' => true]);
    }

    /**
     * Test Delete User with incorrect parameter
     * 
     * @return void
     */
    public function test_delete_user_with_incorrect_parameter()
    {
        $input = $this->test_submit_form_with_correct_input();
        $userLoggin = $this->initializeUserLogin();

        $user = User::where('email', $input['email'])->first();

        /**
         * Test if parameter empty
         */
        $uri = route('user.delete', ['user' => '']);

        $response = $this->withoutMiddleware()
                ->actingAs($userLoggin)
                ->delete($uri,[],['X-Requested-With' => 'XMLHttpRequest']);
        //Method not allow status code
        $response->assertStatus(405);

        /**
         * Test if user id is wrong
         */
        $uri = route('user.delete', ['user' => 'foo']);

        $response = $this->withoutMiddleware()
                ->actingAs($userLoggin)
                ->delete($uri,[],['X-Requested-With' => 'XMLHttpRequest']);
        $response->assertJson(['deleted' => false]);
    }

    /**
     * Test delete data by user privilege
     * 
     * @return void
     */
    public function test_delete_user_by_authenticated_user_role()
    {
        User::unguard();
        
        //Super admin Role
        $userLoggin = $this->initializeUserLogin('Super Admin');
        $user = factory(User::class)->create([
            'client_id' => $userLoggin->client_id,
            'created_by' => $userLoggin->getKey(),
        ]);
        $uri = route('user.delete', ['user' => $user->getKey()]);
        $response = $this->withoutMiddleware()
                ->actingAs($userLoggin)
                ->delete($uri,[],['X-Requested-With' => 'XMLHttpRequest']);
        $response->assertStatus(200);
        $response->assertJson(['deleted' => true]);

        //Admin Role
        $userLoggin = $this->initializeUserLogin('Admin');
        $user = factory(User::class)->create([
            'client_id' => $userLoggin->client_id,
            'created_by' => $userLoggin->getKey(),
        ]);
        $uri = route('user.delete', ['user' => $user->getKey()]);
        $response = $this->withoutMiddleware()
                ->actingAs($userLoggin)
                ->delete($uri,[],['X-Requested-With' => 'XMLHttpRequest']);
        $response->assertStatus(200);
        $response->assertJson(['deleted' => true]);
        
        /**
         * Admin Role
         * Test If Admin role try to delete user from other company
         */
        $userLoggin = $this->initializeUserLogin('Admin');
        $client = Client::where('client_id','!=',$userLoggin->client_id)->first();
        $this->assertNotNull($client);
        $user = factory(User::class)->create([
            'client_id' => $client->getKey(),
            'created_by' => $userLoggin->getKey(),
        ]);

        $uri = route('user.delete', ['user' => $user->getKey()]);
        $response = $this->withoutMiddleware('web')
                ->actingAs($userLoggin)
                ->delete($uri,[],['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertJson(['deleted' => false]);
        
        /**
         * Report Role
         */
        $userLoggin = $this->initializeUserLogin('Report');
        $user = factory(User::class)->create([
            'client_id' => $userLoggin->client_id,
            'created_by' => $userLoggin->getKey(),
        ]);

        $uri = route('user.delete', ['user' => $user->getKey()]);
        $response = $this->withoutMiddleware('web')
                ->actingAs($userLoggin)
                ->delete($uri,[],['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(403);
        $response->assertJsonStructure(['errors']);
        
        User::unguard(false);
    }

}
