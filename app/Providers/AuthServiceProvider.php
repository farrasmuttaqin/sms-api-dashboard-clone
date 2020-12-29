<?php

namespace Firstwap\SmsApiDashboard\Providers;

use Firstwap\SmsApiDashboard\Entities\Report;
use Firstwap\SmsApiDashboard\Entities\User;
use Firstwap\SmsApiDashboard\Policies\ReportPolicy;
use Firstwap\SmsApiDashboard\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\View;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Report::class => ReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        $this->sharePrivilegesToView();
    }

    /**
     * Share model policies to view
     *
     * @return void
     */
    protected function sharePrivilegesToView(){
        $privileges = $this->mapModelClassPolicies();

        View::composer('*', function ($view) use ($privileges){
            $view->with('policies', $privileges);
        });
    }

    /**
     * List class model that have policy
     *
     * @return array
     */
    protected function mapModelClassPolicies(){
        $classes = array_keys($this->policies);
        $keys = array_map(function($item){
           return strtolower(class_basename($item));
        }, $classes);

        return array_combine($keys, $classes);
    }
}