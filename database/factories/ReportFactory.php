<?php

use Carbon\Carbon;
use Faker\Generator as Faker;
use Firstwap\SmsApiDashboard\Entities\Report;
use Firstwap\SmsApiDashboard\Entities\Role;
use Firstwap\SmsApiDashboard\Entities\User;

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

$factory->define(Report::class, function (Faker $faker) {
    return [
        'report_name' => 'GENERATE BY FACTORY',
        'start_date' => Carbon::parse('-30 days')->startOfDay()->toDateTimeString(),
        'end_date' => Carbon::now()->endOfDay()->toDateTimeString(),
        'message_status' => 'delivered,sent,undelivered,rejected',
        'file_type' => 'xlsx',
        'created_by' =>  function () {
            return tap(factory(User::class)->create(), function(User $user){
                $role = Role::inRandomOrder()->first();
                $user->roles()->sync($role);
            })->getKey();
        },
    ];
});
