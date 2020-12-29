<?php

namespace Tests;

use Firstwap\SmsApiDashboard\Entities\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

    use CreatesApplication;

    /**
     * The database connections name to apply  the databse transactions
     *
     * @var array
     */
    protected $connectionsToTransact = ['api_dashboard', 'mysql_sms_api'];

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Initialize user that login and have some privileges
     *
     * @return \Firstwap\SmsApiDashboard\Entities\User
     */
    protected function initializeUserLogin($roleName = 'Super Admin')
    {
        session()->flush();
        $except = $this->exceptRole($roleName);

        $user = User::whereDoesntHave('roles', function($query) use ($except) {
                    $query->whereIn('role_name', $except);
                })
                ->first();

        if (is_null($user)) {
            $this->seed('UserTableSeeder');
            $this->seed('RolePrivilegeTableSeeder');

            return $this->initializeUserLogin($roleName);
        }

        return $user;
    }

    /**
     * Get Role name except the giving value
     *
     * @return array
     */
    protected function exceptRole($roleName)
    {
        $role = ['Super Admin', 'Admin', 'Report'];
        $index = array_search($roleName, $role);

        return $index < 0 ? [] : array_slice($role, 0, $index);
    }

}
