<?php

namespace App\Providers;

use App\Services\SeoService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Paginator::defaultView('admin.pagination');
        Paginator::defaultSimpleView('admin.pagination');

        Gate::define('view-404-monitor', function ($user) {
            return $user && $user->isAdministrator();
        });

        View::composer('layouts.app', function ($view) {
            $postId = (int) (request()->attributes->get('_post_id') ?: ($view->getData()['page']->ID ?? 0));
            $view->with('seoMeta', SeoService::forPost($postId ?: null));
        });
    }
}
