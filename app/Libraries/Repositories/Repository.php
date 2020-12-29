<?php

namespace Firstwap\SmsApiDashboard\Libraries\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;

abstract class Repository
{

    /**
     * Authenticated user
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $user;

    /**
     * Model that will process
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Get Model instance
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    abstract public function model();

    /**
     * Get Model Builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract public function builder();

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function user()
    {
        return auth()->user() ?? $this->deny();
    }

    /**
     * Check the user has privileges
     *
     * @param 	array $privilegeName
     * @return  boolean
     */
    public function privileges(...$privilegeName): bool
    {
        return $this->user()->hasPrivileges($privilegeName);
    }

    /**
     * Paginate the given query.
     *
     * @param  Illuminate\Database\Eloquent\Builder  $builder
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function pagination(Builder $builder)
    {
        $pagesize = request('pagesize') ?? 10;
        $pagenum = request('pagenum') ?? 0;
        $sortorder = request('sortorder') ?? 'DESC';
        $sortdatafield = request('sortdatafield') ?? $this->model()->getKeyName();

        return $builder
                ->orderBy($sortdatafield, $sortorder)
                ->paginate($pagesize, ['*'], 'page', $pagenum+1);
    }

    /**
     * Throws an unauthorized exception.
     *
     * @param  string  $message
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function deny()
    {
        throw new AuthorizationException(trans('app.unauthorized'));
    }

}
