<?php

namespace Modules\Blog;

use Glugox\Module\Support\AbstractModule;

class BlogModule extends AbstractModule
{
    public function providers(): array
    {
        return [
            \Modules\Blog\Providers\BlogServiceProvider::class,
        ];
    }

    public function routes(): array
    {
        return [
            'routes/web.php',
        ];
    }
}
