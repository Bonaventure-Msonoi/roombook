<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\RolePrivilegeController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\VehicleManagementController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\VehicleBookingController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleDashboardController;
use App\Http\Controllers\WalkInFeedbackController;
use App\Http\Controllers\ServiceDesk\WalkInFeedbackController as ServiceDeskWalkInFeedbackController;
use App\Support\Privileges;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

Route::prefix('walk-in')->name('walk-in.')->group(function () {
    Route::get('/', fn () => redirect()->route('walk-in.feedback.create'))->name('index');
    Route::get('/feedback', [WalkInFeedbackController::class, 'create'])->name('feedback.create');
    Route::post('/feedback', [WalkInFeedbackController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('feedback.store');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::prefix('boardroom')->name('boardroom.')->middleware('privilege:'.Privileges::BOOK_BOARDROOM)->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/calendar/events', [DashboardController::class, 'events'])->name('calendar.events');

        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::get('/rooms/{room:slug}', [RoomController::class, 'show'])->name('rooms.show');

        Route::get('/bookings/availability', [BookingController::class, 'availability'])->name('bookings.availability');
        Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
        Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
        Route::get('/my-bookings', [BookingController::class, 'mine'])->name('bookings.mine');
        Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');

        Route::get('/admin/bookings', [BookingController::class, 'adminIndex'])
            ->middleware('privilege:'.Privileges::VIEW_ADMIN_BOOKINGS)
            ->name('bookings.admin');
    });

    Route::prefix('vehicles')->name('vehicles.')->middleware('privilege:'.Privileges::BOOK_VEHICLES)->group(function () {
        Route::get('/dashboard', VehicleDashboardController::class)->name('dashboard');
        Route::get('/calendar/events', [VehicleDashboardController::class, 'events'])->name('calendar.events');

        Route::get('/pool', [VehicleController::class, 'index'])->name('index');
        Route::get('/pool/{vehicle}', [VehicleController::class, 'show'])->name('show');

        Route::get('/bookings/availability', [VehicleBookingController::class, 'availability'])->name('bookings.availability');
        Route::get('/bookings/create', [VehicleBookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [VehicleBookingController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/{vehicleBooking}/edit', [VehicleBookingController::class, 'edit'])->name('bookings.edit');
        Route::put('/bookings/{vehicleBooking}', [VehicleBookingController::class, 'update'])->name('bookings.update');
        Route::get('/my-bookings', [VehicleBookingController::class, 'mine'])->name('bookings.mine');
        Route::delete('/bookings/{vehicleBooking}', [VehicleBookingController::class, 'destroy'])->name('bookings.destroy');

        Route::get('/admin/bookings', [VehicleBookingController::class, 'adminIndex'])
            ->middleware('privilege:'.Privileges::VIEW_ADMIN_BOOKINGS)
            ->name('bookings.admin');
    });

    Route::get('/admin/analytics', [AnalyticsController::class, 'index'])
        ->middleware('privilege:'.Privileges::VIEW_ADMIN_BOOKINGS)
        ->name('admin.analytics.index');
    Route::get('/admin/analytics/export', [AnalyticsController::class, 'export'])
        ->middleware('privilege:'.Privileges::VIEW_ADMIN_BOOKINGS)
        ->name('admin.analytics.export');

    Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])
        ->middleware('privilege:'.Privileges::VIEW_AUDIT_LOG)
        ->name('admin.audit-logs.index');

    Route::prefix('admin/role-privileges')->name('admin.role-privileges.')->middleware('super_user')->group(function () {
        Route::get('/', [RolePrivilegeController::class, 'edit'])->name('edit');
        Route::put('/', [RolePrivilegeController::class, 'update'])->name('update');
    });

    Route::prefix('admin/users')->name('admin.users.')->middleware('privilege:'.Privileges::MANAGE_USERS)->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::patch('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('reset-password');
        Route::patch('/{user}/approve', [UserManagementController::class, 'approve'])->name('approve');
        Route::patch('/{user}/reject', [UserManagementController::class, 'reject'])->name('reject');
        Route::patch('/{user}/deactivate', [UserManagementController::class, 'deactivate'])->name('deactivate');
        Route::patch('/{user}/reactivate', [UserManagementController::class, 'reactivate'])->name('reactivate');
    });

    Route::prefix('service-desk/feedback')->name('service-desk.feedback.')->middleware('privilege:'.Privileges::MANAGE_WALKIN_FEEDBACK)->group(function () {
        Route::get('/', [ServiceDeskWalkInFeedbackController::class, 'index'])->name('index');
        Route::get('/export', [ServiceDeskWalkInFeedbackController::class, 'export'])->name('export');
    });

    Route::prefix('admin/vehicles')->name('admin.vehicles.')->middleware('privilege:'.Privileges::MANAGE_VEHICLES)->group(function () {
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
});

require __DIR__.'/auth.php';
