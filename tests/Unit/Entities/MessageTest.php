<?php

namespace Tests\Unit\App\Entities;

use Firstwap\SmsApiDashboard\Entities\Message;
use Firstwap\SmsApiDashboard\Entities\Report;
use Firstwap\SmsApiDashboard\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MessageTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * Test case for accessor message_status, descriptionStatus, receive_datetime on Message Model
     *
     * @return void
     */
    public function test_accessor_on_model_message()
    {
        $user = $this->initializeUserLogin();
        auth()->login($user);
        session([User::TIMEZONE_SESSION_KEY => '7']);

        $message = factory(Message::class)->create();
        $message->load('apiUser');
        $attributes = $message->getAttributes();

        $this->assertNotEquals($attributes['send_datetime'], $message->send_datetime);
        $this->assertNotEmpty($message->message_status);
        $this->assertNotEmpty($message->descriptionStatus);
        $this->assertEmpty($message->receive_datetime);
    }

    /**
     * Test accessor messages count
     *
     * @return  void
     */
    public function test_getMessageCount_accessor()
    {
        $message = factory(Message::class)->make([
            'message_content' => str_random(150),
        ]);

        $this->assertEquals(1, $message->messageCount);


        $message = factory(Message::class)->make([
            'message_content' => str_random(200),
        ]);

        $this->assertEquals(2, $message->messageCount);
    }

    /**
     * Test accessor messages count for UNICODE content
     *
     * @return  void
     */
    public function test_getMessageCount_accessor_for_unicode_char()
    {
        $message = factory(Message::class)->make([
            'message_content' => "ل".str_random(50),
        ]);

        $this->assertEquals(1, $message->messageCount);


        $message = factory(Message::class)->make([
            'message_content' => "ل".str_random(100),
        ]);

        $this->assertEquals(2, $message->messageCount);
    }

    /**
     * Test case for function parseDatetime with datetime on UTC timezone
     * Function will return datetime on GMT+7 timezone
     *
     * @return void
     */
    public function test_parseDatetime()
    {
        $user = $this->initializeUserLogin();
        auth()->login($user);
        session([User::TIMEZONE_SESSION_KEY => '7']);

        $message = factory(Message::class)->create();
        $returnedDate = $message->parseDatetime('2018-01-29 17:20:20');
        $this->assertEquals('2018-01-30 00:20:20',$returnedDate);
    }

    /**
     * Test case for function parseDatetime with empty parameter
     * Function will return empty string
     *
     * @return void
     */
    public function test_parseDatetime_with_empy_parameter()
    {
        $message = factory(Message::class)->create();
        $returnedDate = $message->parseDatetime('');
        $this->assertEmpty($returnedDate);
    }

    /**
     * Test case for scope report data
     *
     * @return void
     */
    public function test_report_data_scope()
    {
        $user = $this->initializeUserLogin();
        auth()->login($user);
        session([User::TIMEZONE_SESSION_KEY => '7']);
        $apiUsers = $user->apiUsers;
        $message = factory(Message::class, 10)->create([
            'user_id' => $apiUsers->first()->user_name,
            'user_id_number' => $apiUsers->first()->getKey(),
        ]);
        $report = factory(Report::class)->create([
            'message_status' => 'delivered,sent,undelivered'
        ]);
        $report->apiUsers()->attach($apiUsers);
        $messages = Message::reportData($report)->get();
        $this->assertNotEmpty($messages);
        foreach ($messages as $message) {
            $this->assertNotNull($message->receive_datetime);
        }
    }

}
