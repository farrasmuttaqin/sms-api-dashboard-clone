<?php

namespace Firstwap\SmsApiDashboard\Libraries\Repositories;

use Firstwap\SmsApiDashboard\Entities\Privilege;
use Firstwap\SmsApiDashboard\Entities\User;
use Firstwap\SmsApiDashboard\Libraries\Repositories\Repository as RepositoryContract;
use Illuminate\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserRepository extends RepositoryContract
{
    use AuthorizesRequests;

    /**
     * Create a new UserRepository instance.
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
     * @return Model
     */
    public function model()
    {
        return $this->model ?? new User;
    }

    /**
     * Get User Builder
     *
     * @return Builder
     */
    public function builder()
    {
        $builder = $this->model()->query()->notMe($this->user());

        if ($this->privileges(Privilege::USER_ACC_SYSTEM)) {
            return $builder;
        } else if ($this->privileges(Privilege::USER_ACC_COMPANY)) {
            return $builder->where('client_id', $this->user()->client_id)
                            ->whereDoesntHave('roles', function($query) {
                                $query->whereHas('privileges', function($query) {
                                    $query
                                    ->where('privilege_name', '=', Privilege::USER_ACC_SYSTEM)
                                    ->orWhere('privilege_name', '=', Privilege::USER_ACC_COMPANY);
                                });
                            });
        }

        return $this->deny();
    }

    /**
     * Get model data with pagination format for table
     *
     * @param array $search
     * @return Collection
     */
    public function table(array $search = [])
    {
        $builder = $this->searchBuilder($search)->with('client');

        return $this->pagination($builder);
    }

    /**
     * Store new users to database
     *
     * @param  array $attributes
     * @param User $user
     * @return bool
     */
    public function save(array $attributes = [], User $user = null)
    {
        $user = $user ?? $this->model();

        $user->fill($attributes);

        //Authorize action
        $mode = $user->exists ? 'update' : 'create';
        $this->authorize($mode, $user);

        if (!empty($attributes['avatar'])) {
            $user->avatar = $attributes['avatar'];
        }

        if(empty($user->created_by)){
            $user->created_by = $this->user()->getKey();
        }

        $grant = $this->user()->can('grant', $user);

        if($grant && isset($attributes['active'])){
            $user->active = $attributes['active'];
        }

        $saved = $user->save();

        if ($saved && $grant) {
            if (!empty($attributes['roles'])) {
                $user->roles()->sync($attributes['roles']);
            }

            if (!empty($attributes['api_users'])) {
                $user->apiUsers()->sync($attributes['api_users']);
            }
        }

        return $saved;
    }

    /**
     * Store avatar image to storage
     *
     * @param UploadedFile $file
     * @return string
     */
    public function storeImage(UploadedFile $file)
    {
        return Storage::putFile('avatars', $file);
    }

    /**
     * Search data with some query
     *
     * @param  array $query
     * @return Builder
     */
    public function searchBuilder(array $query = [])
    {
        $builder = $this->builder();

        if (!empty($query['name'])) {
            $builder->where('name', 'like', '%' . $query['name'] . '%');
        }

        if (!empty($query['email'])) {
            $builder->where('email', 'like', '%' . $query['email'] . '%');
        }

        if (isset($query['active'])) {
            $builder->where('active', $query['active']);
        }

        if (isset($query['client_id'])) {
            $builder->where('client_id', $query['client_id']);
        }

        if (isset($query['ad_user_id'])) {
            $builder->where('ad_user_id', $query['ad_user_id']);
        }

        return $builder;
    }

    /**
     * find a data with specific primary key
     *
     * @param  array $query
     * @return \Firstwap\SmsApiDashboard\Entities\User
     */
    public function find($id)
    {
        return $this->searchBuilder(['ad_user_id' => $id])->first();
    }

    /**
     * Remove the model from database.
     *
     * @param  int  $userId
     * @return bool
     */
    public function delete($userId)
    {
        $user = $this->find($userId);

        if (!is_null($user)) {
            $this->authorize('delete', $user);
            $user = $user->delete();
        }

        return (bool) $user;
    }


}
