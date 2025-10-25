<?php

namespace Modules\Blog\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class BlogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('blog::*', function ($view) {
            $view->with('module', 'blog');
        });
    }
}
