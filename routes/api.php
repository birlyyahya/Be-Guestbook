<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Console\Scheduling\Event;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\GuestController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::prefix('events')->group(function () {
    Route::get('/', [EventsController::class, 'index'])->name('index');
    Route::get('/{slug}', [EventsController::class, 'show'])->name('show');
});

Route::prefix('guests')->group(function () {
    Route::get('/', [GuestController::class, 'index'])->name('index');
    Route::get('/{id}', [GuestController::class, 'show'])->name('show');
    Route::post('/check-in/{event_id}', [GuestController::class, 'checkIn'])->name('checkIn');
    Route::post('/confirm/{code}', [GuestController::class, 'confirmAttendance'])->name('confirmattendance');
    Route::get('/list-check-in/{event_id}', [GuestController::class, 'listCheckIn'])->name('listCheckIn');
});

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('events')->group(function () {
        Route::post('/', [EventsController::class, 'store'])->name('store');
        Route::post('/{slug}', [EventsController::class, 'update'])->name('update');
    });

    Route::prefix('guests')->group(function () {
        Route::post('/', [GuestController::class, 'store'])->name('store');
        Route::post('/{id}', [GuestController::class, 'update'])->name('update');
    });
});




