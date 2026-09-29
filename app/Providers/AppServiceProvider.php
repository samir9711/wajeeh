<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Functional\AdminAuthService;

use App\Services\Functional\UserAuthService;
use App\Exceptions\Handler;

class AppServiceProvider extends ServiceProvider
{
   protected $facades = [
    'UserService' => \App\Services\Model\User\UserService::class,

    'ProductService' => \App\Services\Model\Product\ProductService::class,

    'ContactInfoService' => \App\Services\Model\ContactInfo\ContactInfoService::class,

    'ContactDepartmentService' => \App\Services\Model\ContactDepartment\ContactDepartmentService::class,

    'AdminService' => \App\Services\Model\Admin\AdminService::class,

    'AboutUsService' => \App\Services\Model\AboutUs\AboutUsService::class,



    'AdminAuthService' => AdminAuthService::class,

    'UserAuthService' => UserAuthService::class,


     ];
    /**
     * Register any application services.
     */
    public function register(): void
    {
        foreach ($this->facades as $facade => $service) {
            $this->app->singleton($facade, function ($app) use ($service) {
                return $app->make($service);
            });
        }

        $this->app->singleton(\Illuminate\Contracts\Debug\ExceptionHandler::class, Handler::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
