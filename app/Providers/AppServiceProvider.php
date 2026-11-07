<?php

namespace App\Providers;

use App\Services\SeoService;
use Illuminate\Pagination\Paginator;
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
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Paginator::defaultView('admin.pagination');
        Paginator::defaultSimpleView('admin.pagination');

        \Illuminate\Support\Facades\Gate::define('view-404-monitor', function ($user) {
            return $user && $user->isAdministrator();
        });

        View::composer('layouts.app', function ($view) {
            $postId = (int) (request()->attributes->get('_post_id') ?: ($view->getData()['page']->ID ?? 0));
            $view->with('seoMeta', SeoService::forPost($postId ?: null));
        });
    }
}
