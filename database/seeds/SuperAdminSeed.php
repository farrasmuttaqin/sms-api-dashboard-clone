<?php

use Firstwap\SmsApiDashboard\Entities\Client;
use Firstwap\SmsApiDashboard\Entities\Role;
use Firstwap\SmsApiDashboard\Entities\User;
use Illuminate\Database\Seeder;

class SuperAdminSeed extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $super = $this->createUser();
        if ($super->roles->isEmpty()) {
            $this->createRoleAndPrivileges($super);
        }
    }

    protected function createUser()
    {

        if (!$super = User::whereEmail('super.admin@1rstwap.com')->first()) {
            $client = Client::with('apiUsers')->first();
            $super = User::create([
                        'name' => 'Super Admin',
                        'email' => 'super.admin@1rstwap.com',
                        'password' => \Hash::make('1rstwap'),
                        'client_id' => $client->getKey(),
            ]);

            $super->apiUsers()->attach($client->apiUsers->pluck('user_id')->all());
        }

        return $super;
    }

    protected function createRoleAndPrivileges(User $super)
    {
        if (Role::count() < 3) {
            $privilegesSuperAdmin = [
                ['privilege_name' => 'user.acc.system'],
                ['privilege_name' => 'user.acc.company'],
                ['privilege_name' => 'user.page.read'],
                ['privilege_name' => 'user.page.write'],
                ['privilege_name' => 'user.page.delete'],
                ['privilege_name' => 'report.acc.system'],
                ['privilege_name' => 'apiuser.acc.system'],
            ];

            $privilegesAdmin = [
                ['privilege_name' => 'user.acc.company'],
                ['privilege_name' => 'user.page.read'],
                ['privilege_name' => 'user.page.write'],
                ['privilege_name' => 'user.page.delete'],
                ['privilege_name' => 'apiuser.acc.company'],
                ['privilege_name' => 'report.acc.company'],
            ];

            $privilegesReport = [
                ['privilege_name' => 'report.acc.own'],
                ['privilege_name' => 'apiuser.acc.own'],
                ['privilege_name' => 'report.page.read'],
                ['privilege_name' => 'report.page.download'],
                ['privilege_name' => 'report.page.generate'],
                ['privilege_name' => 'report.page.delete'],
            ];

            $superAdmin = Role::create(['role_name' => 'Super Admin']);
            $superAdmin->privileges()->createMany($privilegesSuperAdmin);
            $admin = Role::create(['role_name' => 'Admin']);
            $admin->privileges()->createMany($privilegesAdmin);
            $report = Role::create(['role_name' => 'Report']);
            $report->privileges()->createMany($privilegesReport);
        }

        $roles = Role::all();

        $super->roles()->attach($roles->modelKeys());
    }

}
