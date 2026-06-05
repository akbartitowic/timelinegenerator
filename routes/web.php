<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProjectExportController;
use App\Livewire\HolidaysPage;
use App\Livewire\ProjectsPage;
use App\Livewire\TimelinePage;
use App\Livewire\UsersPage;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('projects.index'));

Route::get('/api/holidays-json', function (\App\Services\WorkingDaysService $svc) {
    return response()->json($svc->getHolidayDatesForFrontend());
})->middleware('auth');

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/projects', ProjectsPage::class)->name('projects.index');
    Route::get('/projects/{project}', TimelinePage::class)->name('projects.show');
    Route::get('/projects/{project}/export', [ProjectExportController::class, 'export'])->name('projects.export');

    Route::middleware('is_admin')->group(function () {
        Route::get('/admin/holidays', HolidaysPage::class)->name('admin.holidays');
        Route::get('/admin/users', UsersPage::class)->name('admin.users');
    });
});
