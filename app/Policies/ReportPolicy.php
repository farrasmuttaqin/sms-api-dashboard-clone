<?php

namespace Firstwap\SmsApiDashboard\Policies;

use Firstwap\SmsApiDashboard\Entities\User;
use Firstwap\SmsApiDashboard\Entities\Report;
use Firstwap\SmsApiDashboard\Entities\Privilege;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{

    use HandlesAuthorization;

    /**
     * Determine whether the user can view the report.
     *
     * @param  \Firstwap\SmsApiDashboard\Entities\User  $user
     * @param  \Firstwap\SmsApiDashboard\Entities\Report  $report
     * @return bool
     */
    public function index(User $user)
    {
        return $user->hasPrivileges(Privilege::REPORT_PAGE_READ);
    }

    /**
     * Determine whether the user can generate reports.
     *
     * @param  \Firstwap\SmsApiDashboard\Entities\User  $user
     * @return bool
     */
    public function generate(User $user)
    {
        return $user->hasPrivileges(Privilege::REPORT_PAGE_GENERATE);
    }

    /**
     * Determine whether the user can download reports.
     *
     * @param  \Firstwap\SmsApiDashboard\Entities\User  $user
     * @param  \Firstwap\SmsApiDashboard\Entities\Report  $model
     * @return bool
     */
    public function download(User $user, Report $model = null)
    {
        $approved = $user->hasPrivileges(Privilege::REPORT_PAGE_DOWNLOAD);

        if ($model) {
            if (!$approved = $user->getKey() === $model->created_by) {
                if (!$approved = $user->hasPrivileges(Privilege::REPORT_ACC_SYSTEM)) {
                    $approved = $user->getKey() === $model->apiUserDashboard->created_by;
                }
            }
        }

        return $approved;
    }

    /**
     * Determine whether the user can delete the report.
     *
     * @param  \Firstwap\SmsApiDashboard\Entities\User  $user
     * @param  \Firstwap\SmsApiDashboard\Entities\Report  $report
     * @return bool
     */
    public function delete(User $user, Report $model = null)
    {
        $approved = $user->hasPrivileges(Privilege::REPORT_PAGE_DELETE);

        if ($model) {
            if (!$approved = $user->getKey() === $model->created_by) {
                if (!$approved = $user->hasPrivileges(Privilege::REPORT_ACC_SYSTEM)) {
                    if ($user->hasPrivileges(Privilege::REPORT_ACC_COMPANY)) {
                        $approved = $user->client_id === $model->apiUserDashboard->client_id;
                    }
                }
            }
        }

        return $approved;
    }

}
