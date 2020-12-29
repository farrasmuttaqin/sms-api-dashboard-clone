<?php

namespace Tests\Unit\App\Libraries\Repositories;

use Faker\Factory;
use Firstwap\SmsApiDashboard\Entities\ApiUser;
use Firstwap\SmsApiDashboard\Entities\Message;
use Firstwap\SmsApiDashboard\Entities\StatusCode;
use Firstwap\SmsApiDashboard\Entities\User;
use Firstwap\SmsApiDashboard\Libraries\Repositories\MessageRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MessageRepositoryTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * Initialize message content
     *
     * @return array;
     */
    protected function initializeMessages($api_user)
    {
        $status = $this->statusMessages();
        // $api_user = factory(ApiUser::class)->create();
        $faker = Factory::create();
        $messages['delivered'] = factory(Message::class, 10)->create([
            'send_datetime' => function() use ($faker) {

                return $faker->dateTimeBetween('-24 hours','now')
                                ->format('Y-m-d H:i:s');
            },
            'message_status' => function() use ($status, $faker) {
                return '0+0+0+0';
            },
            'user_id_number' => $api_user->getKey(),
            'user_id' => $api_user->user_name,
        ]);

        $messages['undelivered'] = factory(Message::class, 10)->create([
            'send_datetime' => function() use ($faker) {
                return $faker->dateTimeBetween('-7 days 00:00:00', '23:59:59')
                                ->format('Y-m-d H:i:s');
            },
            'message_status' => function() use ($status,$faker) {
                return $faker->randomElement($status['Undelivered']);
            },
            'user_id_number' => $api_user->getKey(),
            'user_id' => $api_user->user_name,
        ]);

        $messages['sent'] = factory(Message::class, 10)->create([
            'send_datetime' => function() use ($faker) {
                return $faker->dateTimeBetween('-30 days 00:00:00', '23:59:59')
                                ->format('Y-m-d H:i:s');
            },
            'message_status' => function() use ($status, $faker) {
                return $faker->randomElement($status['Sent']);
            },
            'user_id_number' => $api_user->getKey(),
            'user_id' => $api_user->user_name,
        ]);

        return $messages;
    }

    /**
     * Get Status Error Code
     *
     * @return array
     */
    protected function statusMessages()
    {
        $status = StatusCode::statusErrorCode();
        $results = [];

        foreach ($status as $key => $value) {
            $results[$value][] = $key;
        }

        return $results;
    }

    /**
     * Get a database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    protected function connection($connection = null)
    {
        return Model::getConnectionResolver()->connection($connection);
    }

    /**
     * Test get summary for dashboard method
     *
     * @return void
     */
    public function test_getSummaryForDashboard_method()
    {
        Cache::flush();
        $user = $this->initializeUserLogin('Report');
        Message::getQuery()->delete();
        $messages = $this->initializeMessages($user->apiUsers->first());

        // Test if user has Report role
        auth()->login($user);
        $object = new MessageRepository();
        $daily = $object->getDailySummary();
        $weekly = $object->getWeeklySummary();
        $monthly = $object->getMonthlySummary();
        $this->assertGreaterThanOrEqual(10, $daily['total']);
        $this->assertGreaterThanOrEqual(20, $weekly['total']);
        $this->assertGreaterThanOrEqual(30, $monthly['total']);

        //Test if user has Admin role
        $user = $this->initializeUserLogin('Admin');
        Message::getQuery()->delete();
        $messages = $this->initializeMessages($user->apiUsers->first());
        auth()->login($user);
        $object = new MessageRepository();
        $daily = $object->getDailySummary();
        $weekly = $object->getWeeklySummary();
        $monthly = $object->getMonthlySummary();
        $this->assertGreaterThanOrEqual(10, $daily['total']);
        $this->assertGreaterThanOrEqual(20, $weekly['total']);
        $this->assertGreaterThanOrEqual(30, $monthly['total']);
        //Test if user has Super Admin role
        $user = $this->initializeUserLogin('Super Admin');
        Message::getQuery()->delete();
        $messages = $this->initializeMessages($user->apiUsers->first());
        auth()->login($user);
        $object = new MessageRepository();
        $daily = $object->getDailySummary();
        $weekly = $object->getWeeklySummary();
        $monthly = $object->getMonthlySummary();
        $this->assertGreaterThanOrEqual(10, $daily['total']);
        $this->assertGreaterThanOrEqual(20, $weekly['total']);
        $this->assertGreaterThanOrEqual(30, $monthly['total']);
     }
}
