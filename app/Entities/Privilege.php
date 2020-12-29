<?php

namespace Firstwap\SmsApiDashboard\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Privilege extends Model
{

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'api_dashboard';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'AD_PRIVILEGES';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'privilege_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['privilege_name'];

    /**
     * Can access all sms api dashboard user
     */
    const USER_ACC_SYSTEM = 'user.acc.system';

    /**
     * Can access all sms api dashboard user that have same company
     */
    const USER_ACC_COMPANY = 'user.acc.company';

    /**
     * Can access user page
     */
    const USER_PAGE_READ = 'user.page.read';

    /**
     * Can access create and edit page on user menu
     */
    const USER_PAGE_WRITE = 'user.page.write';

    /**
     * Can delete user that can be accessed
     */
    const USER_PAGE_DELETE = 'user.page.delete';

    /**
     * Can access all API user
     */
    const API_USER_ACC_SYSTEM = 'apiuser.acc.system';

    /**
     * Can access all API user data owned by company
     */
    const API_USER_ACC_COMPANY = 'apiuser.acc.company';

    /**
     * Can access all API user owned by user
     */
    const API_USER_ACC_OWN = 'apiuser.acc.own';

    /**
     * Can access all reports
     */
    const REPORT_ACC_SYSTEM = 'report.acc.system';

    /**
     * Can access reports that owned by company
     */
    const REPORT_ACC_COMPANY = 'report.acc.company';

    /**
     * Can access reports that owned by user
     */
    const REPORT_ACC_OWN = 'report.acc.own';

    /**
     * Can access report page
     */
    const REPORT_PAGE_READ = 'report.page.read';

    /**
     * Can download reports that can be accessed
     */
    const REPORT_PAGE_DOWNLOAD = 'report.page.download';

    /**
     * Can generate report
     */
    const REPORT_PAGE_GENERATE = 'report.page.generate';

    /**
     * Can delete reports that can be accessed
     */
    const REPORT_PAGE_DELETE = 'report.page.delete';

    /**
     * Session Key to save privileges
     */
    const SESSION_PRIVILEGE_KEY = 'auth_user_privilege';

    /**
     * Define a many-to-many relationship.
     * The privileges that belong to the role.
     *
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'AD_PRIVILEGE_ROLE', 'privilege_id', 'role_id');
    }

    /**
     * Scope to get privileges by users
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUser($query, $user)
    {
        return $query->whereHas('roles', function($query) use ($user) {
                    $query->whereHas('users', function($query) use ($user) {
                        $query->where($user->getKeyName(), $user->getKey());
                    });
                });
    }

}
