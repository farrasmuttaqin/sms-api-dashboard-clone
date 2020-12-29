<?php

use Faker\Generator as Faker;
use Firstwap\SmsApiDashboard\Entities\ApiUser;
use Firstwap\SmsApiDashboard\Entities\Client;

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

$factory->define(ApiUser::class, function (Faker $faker) {
    return [
        "user_id" => $faker->unique()->numberBetween(1000,2000),
        "version" => "766431",
        "user_name" => $faker->text(10),
        "password" => "c40b0c360f3d4959b53b103b25759542",
        "active" => 1,
        "counter" => "762003",
        "credit" => "499996116",
        "last_access" => "2017-07-24 04:40:18",
        "created_date" => "2010-03-30 00:00:00",
        "updated_date" => "2013-06-18 07:47:54",
        "created_by" => "31",
        "updated_by" => "44",
        "cobrander_id" => null,
        "client_id" => function() {
            return factory(Client::class)->create()->getKey();
        },
        "delivery_status_url" => "http://10.32.6.52/simulators/pushstatus.php",
        "url_invalid_count" => 0,
        "url_active" => 1,
        "url_last_retry" => "2013-10-07 06:23:50",
        "use_blacklist" => 0,
        "is_postpaid" => 0,
        "expired_date" => null,
        "credit_active" => 1,
        "try_count" => 0,
        "inactive_reason" => "",
        "datetime_try" => "2017-07-24 04:40:18",
        "billing_profile_id" => 1,
        "billing_report_group_id" => null,
        "billing_tiering_group_id" => "5",
    ];
});
