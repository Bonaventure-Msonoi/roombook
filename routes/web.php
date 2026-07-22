<?php

use App\Http\Controllers\Admin\VehicleManagementController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\VehicleBookingController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::prefix('boardroom')->name('boardroom.')->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/calendar/events', [DashboardController::class, 'events'])->name('calendar.events');

        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::get('/rooms/{room:slug}', [RoomController::class, 'show'])->name('rooms.show');

        Route::get('/bookings/availability', [BookingController::class, 'availability'])->name('bookings.availability');
        Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::get('/my-bookings', [BookingController::class, 'mine'])->name('bookings.mine');
        Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');

        Route::get('/admin/bookings', [BookingController::class, 'adminIndex'])
            ->middleware('admin')
            ->name('bookings.admin');
    });

    Route::prefix('vehicles')->name('vehicles.')->group(function () {
        Route::get('/dashboard', VehicleDashboardController::class)->name('dashboard');
        Route::get('/calendar/events', [VehicleDashboardController::class, 'events'])->name('calendar.events');

        Route::get('/pool', [VehicleController::class, 'index'])->name('index');
        Route::get('/pool/{vehicle}', [VehicleController::class, 'show'])->name('show');

        Route::get('/bookings/availability', [VehicleBookingController::class, 'availability'])->name('bookings.availability');
        Route::get('/bookings/create', [VehicleBookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [VehicleBookingController::class, 'store'])->name('bookings.store');
        Route::get('/my-bookings', [VehicleBookingController::class, 'mine'])->name('bookings.mine');
        Route::delete('/bookings/{vehicleBooking}', [VehicleBookingController::class, 'destroy'])->name('bookings.destroy');

        Route::get('/admin/bookings', [VehicleBookingController::class, 'adminIndex'])
            ->middleware('admin')
            ->name('bookings.admin');
    });

    Route::prefix('admin/vehicles')->name('admin.vehicles.')->middleware('super_user')->group(function () {
        Route::get('/', [VehicleManagementController::class, 'index'])->name('index');
        Route::get('/create', [VehicleManagementController::class, 'create'])->name('create');
        Route::post('/', [VehicleManagementController::class, 'store'])->name('store');
        Route::get('/{vehicle}/edit', [VehicleManagementController::class, 'edit'])->name('edit');
        Route::put('/{vehicle}', [VehicleManagementController::class, 'update'])->name('update');
        Route::delete('/{vehicle}', [VehicleManagementController::class, 'destroy'])->name('destroy');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
