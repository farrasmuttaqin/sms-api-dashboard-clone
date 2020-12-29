<?php

namespace Tests\Unit\App\Libraries\Auth\Passwords;

use Carbon\Carbon;
use Firstwap\SmsApiDashboard\Entities\User;
use Firstwap\SmsApiDashboard\Libraries\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseTokenRepositoryTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * Create an instance of DatabaseTokenRepository
     *
     * @return \Firstwap\SmsApiDashboard\Libraries\Auth\Passwords\DatabaseTokenRepository;
     */
    protected function createInstance()
    {
        return $this->app['auth.password.broker']->getRepository();
    }

    /**
     * Test create method
     * This method will store forget_token and expired_token to user
     *
     * @return void
     */
    public function test_create_method()
    {
        $object = $this->createInstance();
        $user = factory(User::class)->create();

        $token = $object->create($user);
        $user->refresh();
        //token and expired date should not null
        $this->assertNotNull($token);
        $this->assertNotNull($user->expired_token);
        $this->assertNotNull($user->forget_token);
        //check the token
        $this->assertTrue($object->getHasher()->check($token, $user->forget_token));
    }

    /**
     * Test exists method
     * exists method will check if token is exists and not expired
     *
     * @return void
     */
    public function test_exists_method()
    {
        $object = $this->createInstance();
        $user = factory(User::class)->create();
        $token = $object->create($user);
        $user->refresh();

        /**
         * If forget_token exists on database
         */
        $result = $object->exists($user, $token);
        $this->assertTrue($result);

        /**
         * If forget_token is expired
         */
        $user->expired_token = Carbon::parse('-100 hour')->toDateTimeString();
        $result = $object->exists($user, $token);
        $this->assertFalse($result);

        /**
         * If expired_token field is null
         */
        $user->expired_token = null;
        $result = $object->exists($user, $token);
        $this->assertFalse($result);

        /**
         * If forget_token field is null
         */
        $user->forget_token = null;
        $result = $object->exists($user, $token);
        $this->assertFalse($result);
    }

    /**
     * Test deleteExpired method
     * This method will delete all expired token
     *
     * @return  void
     */
    public function test_deleteExpired_method()
    {
        $object = $this->createInstance();
        $users = factory(User::class, 5)->create();

        $users->each(function($user, $index)use ($object) {
            $object->create($user);
            $user->refresh();
        });

        //users have forget_token and expired_token field
        foreach ($users as $user) {
            $this->assertNotNull($user->forget_token);
            $this->assertNotNull($user->expired_token);
        }

        //change expired_token to be expired date
        foreach ($users as &$user) {
            $user->expired_token = Carbon::parse('-100 hour')->toDateTimeString();
            $user->save();
        }
        //Test delete expired token
        $count = $object->deleteExpired();
        //efected row should be 5
        $this->assertEquals(5, $count);

        //check forget_token and expired_token field should be null
        foreach ($users as $user) {
            $user->refresh();
            $this->assertNull($user->forget_token);
            $this->assertNull($user->expired_token);
        }
    }

}
