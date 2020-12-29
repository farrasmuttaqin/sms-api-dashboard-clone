<?php

use Carbon\Carbon;
use Faker\Generator as Faker;
use Firstwap\SmsApiDashboard\Entities\ApiUser;
use Firstwap\SmsApiDashboard\Entities\Message;
use Firstwap\SmsApiDashboard\Entities\StatusCode;

/*
  |--------------------------------------------------------------------------
  | Model Factories
  |--------------------------------------------------------------------------
  |
  | This directory should contain each of the model factory definitions for
  | your application. Factories provide a convenient way to generate new
  | model instances for testing / seeding your application's database.
  |
 */
$status = StatusCode::all()->pluck('error_code')->all();
$apiUsers = ApiUser::all();
$senders = \DB::connection('mysql_sms_api')
            ->table('SENDER')
            ->select('user_id','sender_name','sender_id')
            ->get()
            ->groupBy('user_id')
            ->all();
$factory->define(Message::class, function (Faker $faker) use ($index, $status, $apiUsers, $senders) {
    static $index = 1;
    $apiUser = $apiUsers->random();
    $dateTime = Carbon::now()->subMinutes($index)->format('Y-m-d H:i:s');

    return [
        'message_id' => '0GPI' . $dateTime . '.000.' . str_random(5),
        'destination' => 628990000000 + $index++,
        'message_content' => 'DUMMY TEXT DELETE THIS IF MENGGANGGU WKWKWKW',
        'message_type' => '0',
        'message_status' => $faker->randomElement($status),
        'send_datetime' => $dateTime,
        'status_datetime' => NULL,
        'sender_id' => isset($senders[$apiUser->user_id]) ? $senders[$apiUser->user_id]->first()->sender_id : 1,
        'user_id_number' => $apiUser->user_id,
        'acknowledged' => NULL,
        'broadcast_sms_id' => NULL,
        'sender' => isset($senders[$apiUser->user_id]) ? $senders[$apiUser->user_id]->first()->sender_name : '1rstWAP',
        'user_id' => $apiUser->user_name,
    ];
});
