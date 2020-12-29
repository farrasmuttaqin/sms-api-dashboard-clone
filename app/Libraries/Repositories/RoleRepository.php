<?php

namespace Firstwap\SmsApiDashboard\Libraries\Repositories;

use Firstwap\SmsApiDashboard\Entities\Role;
use Firstwap\SmsApiDashboard\Entities\Privilege;
use Firstwap\SmsApiDashboard\Libraries\Repositories\Repository as RepositoryContract;
use Illuminate\Database\Eloquent\Builder;

class RoleRepository extends RepositoryContract
{

    /**
     * Create a new RoleRepository instance.
     *
     * @return void
     */
    function __construct()
    {
        $this->model = $this->model();
    }

    /**
     * Get Model instance
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function model()
    {
        return $this->model ?? new Role;
    }

    /**
     * Get Role Builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function builder()
    {
        if ($this->privileges(Privilege::USER_ACC_SYSTEM)) {
            return $this
                    ->model()
                    ->query();
        } else if ($this->privileges(Privilege::USER_ACC_COMPANY)) {
            return $this
                    ->model()
                    ->query()
                    ->whereDoesntHave('privileges', function($query) {
                        $query
                            ->where('privilege_name', Privilege::USER_ACC_COMPANY)
                            ->orWhere('privilege_name', Privilege::USER_ACC_SYSTEM);
                    });
        }
        return $this->deny();
    }

    /**
     * Get model data
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function data()
    {
        return $this->builder()->get();
    }

}
