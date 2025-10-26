<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Cloud\Packages\ModuleRegistry\ModuleRegistry;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('modules', function (ModuleRegistry $registry) {
    return response()->json([
        'modules' => $registry->all(),
    ]);
})->name('modules.index');

require __DIR__.'/settings.php';
