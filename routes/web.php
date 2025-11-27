<?php

use App\Http\Controllers\CallerIdController;
use App\Http\Controllers\OutboundRoutesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VoipAccountsController;
use App\Http\Controllers\VoipTrunksController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Auth/Login', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('accountvoip')->name('accountvoip.')->group(function () {
        Route::get('/index', [VoipAccountsController::class, 'index'])->name('index');
        Route::post('/store', [VoipAccountsController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [VoipAccountsController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [VoipAccountsController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [VoipAccountsController::class, 'destroy'])->name('delete');
    });

    Route::prefix('trunkvoip')->name('trunkvoip.')->group(function () {
        Route::get('/index', [VoipTrunksController::class, 'index'])->name('index');
        Route::post('/store', [VoipTrunksController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [VoipTrunksController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [VoipTrunksController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [VoipTrunksController::class, 'destroy'])->name('delete');
    });

    Route::prefix('outboundroutes')->name('outboundroutes.')->group(function () {
        Route::get('/index', [OutboundRoutesController::class, 'index'])->name('index');
        Route::post('/store', [OutboundRoutesController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [OutboundRoutesController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [OutboundRoutesController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [OutboundRoutesController::class, 'destroy'])->name('delete');
    });

    Route::prefix('clid')->name('clid.')->group(function () {
        Route::get('/index', [CallerIdController::class, 'index'])->name('index');
        Route::post('/store', [CallerIdController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [CallerIdController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [CallerIdController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [CallerIdController::class, 'destroy'])->name('delete');
    });


});

require __DIR__ . '/auth.php';
