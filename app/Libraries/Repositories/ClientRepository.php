<?php

namespace Firstwap\SmsApiDashboard\Libraries\Repositories;

use Firstwap\SmsApiDashboard\Entities\Client;
use Firstwap\SmsApiDashboard\Entities\Privilege;
use Firstwap\SmsApiDashboard\Libraries\Repositories\Repository as RepositoryContract;
use Illuminate\Database\Eloquent\Builder;

class ClientRepository extends RepositoryContract
{

    /**
     * Create a new ClientRepository instance.
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
        return $this->model ?? new Client;
    }

    /**
     * Get Client Builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function builder()
    {
        $builder = $this->model()->query();

        if ($this->privileges(Privilege::USER_ACC_SYSTEM)) {
            return $builder;
        } else if ($this->privileges(Privilege::USER_ACC_COMPANY)) {
            return $builder->where('client_id', $this->user()->client_id);
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
