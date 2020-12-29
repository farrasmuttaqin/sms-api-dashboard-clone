<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

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

$factory->define(Client::class, function (Faker $faker) {
    return [
        "client_id" => $faker->unique()->numberBetween(1000),
        "customer_id" => $faker->text(5),
        "company_name" => $faker->text(10),
        "company_url" => "http://www.1rstwap.com",
        "country_code" => "idn",
        "contact_name" => "operation dept.",
        "contact_email" => "ops@1rstwap.com",
        "contact_phone" => "",
        "created_date" => "2010-10-20 13:10:13",
        "created_by" => "0",
    ];
});
