<?php

use Firstwap\SmsApiDashboard\Entities\Role;
use Firstwap\SmsApiDashboard\Entities\User;
use Illuminate\Database\Seeder;

class RolePrivilegeTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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

        if (!$superAdmin = Role::whereRoleName('Super Admin')->first()) {
            $superAdmin = Role::create(['role_name' => 'Super Admin']);
            $superAdmin->privileges()->createMany($privilegesSuperAdmin);
        }

        if (!$admin = Role::whereRoleName('Admin')->first()) {
            $admin = Role::create(['role_name' => 'Admin']);
            $admin->privileges()->createMany($privilegesAdmin);
        }

        if (!$report = Role::whereRoleName('Report')->first()) {
            $report = Role::create(['role_name' => 'Report']);
            $report->privileges()->createMany($privilegesReport);
        }
        
        if ($user = User::where('email', 'muhammad.rizal@1rstwap.com')->first()) {
            $user->roles()->attach([$superAdmin->role_id, $admin->role_id, $report->role_id]);
        }

        if ($user = User::where('email', 'demo@1rstwap.com')->first()) {
            $user->roles()->attach([$superAdmin->role_id, $admin->role_id, $report->role_id]);
        }

        if ($user = User::where('email', 'demo_admin1@1rstwap.com')->first()) {
            $user->roles()->attach([$admin->role_id, $report->role_id]);
        }

        if ($user = User::where('email', 'demo_admin2@1rstwap.com')->first()) {
            $user->roles()->attach([$admin->role_id, $report->role_id]);
        }

        if ($user = User::where('email', 'demo_report1@1rstwap.com')->first()) {
            $user->roles()->attach([$report->role_id]);
        }
        if ($user = User::where('email', 'demo_report2@1rstwap.com')->first()) {
            $user->roles()->attach([$report->role_id]);
        }
    }

}
