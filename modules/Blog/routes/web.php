<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function (): void {
    Route::get('/blog', function () {
        return inertia('Blog/Index', [
            'posts' => [
                ['title' => 'Welcome to Glugox', 'excerpt' => 'Modular architecture made easy.'],
                ['title' => 'Generating Modules', 'excerpt' => 'Use the Glugox generator to scaffold modules quickly.'],
            ],
        ]);
    });
});
