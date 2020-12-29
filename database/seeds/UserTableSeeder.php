<?php

use Firstwap\SmsApiDashboard\Entities\Client;
use Firstwap\SmsApiDashboard\Entities\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $clients = Client::with('apiUsers')->limit(2)->get();
        if ($clients->count() < 1) {
            $clients = $this->initializeClientAndApiUsers();
        }
        $client = $clients->first();
        

        // SUPER ADMIN
        $super = User::whereEmail('muhammad.rizal@1rstwap.com')->first();
        $super2 = User::whereEmail('demo@1rstwap.com')->first();
        if (is_null($super)) {
            $super = User::create(array(
                        'name' => 'Muhammad Rizal',
                        'email' => 'muhammad.rizal@1rstwap.com',
                        'password' => \Hash::make('1rstwap'),
                        'client_id' => $client->getKey(),
            ));

            $super->apiUsers()->attach($client->apiUsers->pluck('user_id')->all());
            
        }

        if (is_null($super2)) {
            $super2 = User::create(array(
                        'name' => 'Super Admin',
                        'email' => 'demo@1rstwap.com',
                        'password' => \Hash::make('1rstwap'),
                        'client_id' => $client->getKey(),
            ));
            $super2->created_by = $super->getKey();
            $super2->save();
            $super2->apiUsers()->attach($client->apiUsers->pluck('user_id')->all());
        }
        // ADMIN COMPANY
        $admin1 = User::whereEmail('demo_admin1@1rstwap.com')->first();
        $admin2 = User::whereEmail('demo_admin2@1rstwap.com')->first();

        if (is_null($admin1)) {
            $admin1 = User::create(array(
                        'name' => 'Admin Company 1',
                        'email' => 'demo_admin1@1rstwap.com',
                        'password' => \Hash::make('1rstwap'),
                        'client_id' => $client->getKey(),
            ));
            $admin1->created_by = $super2->getKey();
            $admin1->save();
            $admin1->apiUsers()->attach($client->apiUsers->pluck('user_id')->all());
        }

        if (is_null($admin2)) {
            $admin2 = User::create(array(
                        'name' => 'Admin Company 2',
                        'email' => 'demo_admin2@1rstwap.com',
                        'password' => \Hash::make('1rstwap'),
                        'client_id' => $clients->last()->getKey(),
            ));

            $admin2->created_by = $super2->getKey();
            $admin2->save();
            $admin2->apiUsers()->attach($client->apiUsers->pluck('user_id')->all());
        }

        //User Report
        $report1 = User::whereEmail('demo_report1@1rstwap.com')->first();
        $report2 = User::whereEmail('demo_report2@1rstwap.com')->first();

        if (is_null($report1)) {
            $report1 = User::create(array(
                        'name' => 'User Report 1',
                        'email' => 'demo_report1@1rstwap.com',
                        'password' => \Hash::make('1rstwap'),
                        'client_id' => $client->getKey(),
            ));

            $report1->created_by = $admin1->getKey();
            $report1->save();
            $report1->apiUsers()->attach($client->apiUsers->pluck('user_id')->random(3)->all());
        }

        if (is_null($report2)) {
            $report2 = User::create(array(
                        'name' => 'User Report 2',
                        'email' => 'demo_report2@1rstwap.com',
                        'password' => \Hash::make('1rstwap'),
                        'client_id' => $clients->last()->getKey(),
            ));
            $report2->created_by = $admin2->getKey();
            $report2->save();
            $report2->apiUsers()->attach($client->apiUsers->pluck('user_id')->random(2)->all());
        }
    }

    /**
     * Initialize client and api users data
     * 
     * @return Collection
     */
    protected function initializeClientAndApiUsers()
    {
        $apiUserArray = [
            "user_id" => "1",
            "version" => "766431",
            "user_name" => "testapi",
            "password" => "c40b0c360f3d4959b53b103b25759542",
            "active" => "1",
            "counter" => "762003",
            "credit" => "499996116",
            "last_access" => "2017-07-24 04:40:18",
            "created_date" => "2010-03-30 00:00:00",
            "updated_date" => "2013-06-18 07:47:54",
            "created_by" => "31",
            "updated_by" => "44",
            "cobrander_id" => null,
            "client_id" => "1",
            "delivery_status_url" => "http://10.32.6.52/simulators/pushstatus.php",
            "url_invalid_count" => "0",
            "url_active" => "1",
            "url_last_retry" => "2013-10-07 06:23:50",
            "use_blacklist" => "0",
            "is_postpaid" => "0",
            "expired_date" => null,
            "credit_active" => "1",
            "try_count" => "0",
            "inactive_reason" => "",
            "datetime_try" => "2017-07-24 04:40:18",
            "billing_profile_id" => "1",
            "billing_report_group_id" => null,
            "billing_tiering_group_id" => "5",
        ];

        $clientArray = [
            "client_id" => "9999",
            "company_name" => "TEST COMPANY",
            "company_url" => "http://www.1rstwap.com",
            "country_code" => "idn",
            "contact_name" => "operation dept.",
            "contact_email" => "ops@1rstwap.com",
            "contact_phone" => "",
            "created_date" => "2010-10-20 13:10:13",
            "created_by" => "0",
        ];

        $hasClient = Client::count();

        if ($hasClient < 1) {
            for ($j = 0; $j < 2; $j++) {
                Client::insert(array_merge($clientArray,['client_id' => 9997 + $j]));
            }

            $clients = Client::limit(2)->get();
        }else{
            $clients = Client::limit(2)->get();
        }

        $clients->load('apiUsers');
        foreach($clients as $j => $client){
            if ($client->apiUsers->isEmpty()) {
                for ($i = 0; $i < 5; $i++) {
                    $apiUserArray['client_id'] = $client->client_id;
                    $apiUserArray['user_id'] = $j + $i;
                    $apiUserArray['user_name'] .= $j + $i;
                    ApiUser::insert($apiUserArray);
                }
                $client->load('apiUsers');
            }
        }
        return $clients;
    }

}
