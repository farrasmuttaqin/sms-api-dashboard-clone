<?php

namespace Firstwap\SmsApiDashboard\Libraries\Repositories;

use Firstwap\SmsApiDashboard\Entities\ApiUser;
use Firstwap\SmsApiDashboard\Entities\Privilege;
use Firstwap\SmsApiDashboard\Libraries\Repositories\Repository as RepositoryContract;
use Illuminate\Database\Eloquent\Builder;

class ApiUserRepository extends RepositoryContract
{

    /**
     * Create a new ApiUserRepository.php instance.
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
        return $this->model ?? new ApiUser;
    }

    /**
     * Get ApiUser Builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function builder()
    {
        $builder = $this->model()->query()->orderBy('user_name');

        if ($this->privileges(Privilege::API_USER_ACC_SYSTEM)) {
            return $builder;
        }

        if ($this->privileges(Privilege::API_USER_ACC_COMPANY, Privilege::API_USER_ACC_OWN)) {
            return $this->ownBuilder($builder);
        }

        return $this->deny();
    }

    /**
     * Get Own Api User Builder
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function ownBuilder($builder)
    {
        return $builder->whereHas('apiDashboardUsers', function($query) {
                    $user = $this->user();
                    $query->where($user->getTable().'.'.$user->getKeyName(), $user->getKey());
                });
    }

    /**
     * Get model data
     *
     * @param  $clientId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function dataByClient($client)
    {
        return $this->builder()
                        ->where('client_id', $client)
                        ->get();
    }

    /**
     * Get own api users (just an active api users)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function ownApiUsers()
    {
        $builder = $this->model()->query()->orderBy('user_name');

        return $this->ownBuilder($builder)->where('active', 1)->get();
    }
}
