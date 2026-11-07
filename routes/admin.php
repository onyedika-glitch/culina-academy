<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SubmissionController;
use App\Http\Controllers\Admin\IntakeController;
use App\Http\Middleware\EnsureAdministrator;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth:admin', EnsureAdministrator::class])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('pages', [PageController::class, 'index'])->name('pages.index');
        Route::get('pages/create', [PageController::class, 'create'])->name('pages.create');
        Route::post('pages', [PageController::class, 'store'])->name('pages.store');
        Route::get('pages/{id}/edit', [PageController::class, 'edit'])->name('pages.edit');
        Route::put('pages/{id}', [PageController::class, 'update'])->name('pages.update');
        Route::delete('pages/{id}', [PageController::class, 'destroy'])->name('pages.destroy');

        Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
        Route::get('courses/create', [CourseController::class, 'create'])->name('courses.create');
        Route::post('courses', [CourseController::class, 'store'])->name('courses.store');
        Route::get('courses/{id}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('courses/{id}', [CourseController::class, 'update'])->name('courses.update');
        Route::delete('courses/{id}', [CourseController::class, 'destroy'])->name('courses.destroy');

        Route::get('blog', [BlogController::class, 'index'])->name('blog.index');
        Route::get('blog/create', [BlogController::class, 'create'])->name('blog.create');
        Route::post('blog', [BlogController::class, 'store'])->name('blog.store');
        Route::get('blog/{id}/edit', [BlogController::class, 'edit'])->name('blog.edit');
        Route::put('blog/{id}', [BlogController::class, 'update'])->name('blog.update');
        Route::delete('blog/{id}', [BlogController::class, 'destroy'])->name('blog.destroy');

        Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
        Route::get('menus/{termTaxonomyId}/edit', [MenuController::class, 'edit'])->name('menus.edit');
        Route::put('menus/{termTaxonomyId}', [MenuController::class, 'update'])->name('menus.update');

        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::get('languages', [LanguageController::class, 'index'])->name('languages.index');

        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::get('media/api', [MediaController::class, 'api'])->name('media.api');
        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::delete('media/{id}', [MediaController::class, 'destroy'])->name('media.destroy');

        Route::get('submissions', [SubmissionController::class, 'index'])->name('submissions.index');
        Route::get('submissions/contacts/{id}', [SubmissionController::class, 'showContact'])->name('submissions.contacts.show');
        Route::delete('submissions/contacts/{id}', [SubmissionController::class, 'destroyContact'])->name('submissions.contacts.destroy');
        Route::get('submissions/applications/{id}', [SubmissionController::class, 'showApplication'])->name('submissions.applications.show');
        Route::put('submissions/applications/{id}/status', [SubmissionController::class, 'updateApplicationStatus'])->name('submissions.applications.status');
        Route::delete('submissions/applications/{id}', [SubmissionController::class, 'destroyApplication'])->name('submissions.applications.destroy');

        // Booking management
        Route::get('submissions/bookings/{id}', [SubmissionController::class, 'showBooking'])->name('submissions.bookings.show');
        Route::put('submissions/bookings/{id}/status', [SubmissionController::class, 'updateBookingStatus'])->name('submissions.bookings.status');
        Route::delete('submissions/bookings/{id}', [SubmissionController::class, 'destroyBooking'])->name('submissions.bookings.destroy');

        // Waiting list invite management
        Route::post('submissions/waitinglists/{id}/invite', [SubmissionController::class, 'inviteWaitlistEntry'])->name('submissions.waitinglists.invite');
        Route::delete('submissions/waitinglists/{id}', [SubmissionController::class, 'destroyWaitlistEntry'])->name('submissions.waitinglists.destroy');

        // Course Intake management
        Route::get('intakes', [IntakeController::class, 'index'])->name('intakes.index');
        Route::post('intakes', [IntakeController::class, 'store'])->name('intakes.store');
        Route::put('intakes/{id}', [IntakeController::class, 'update'])->name('intakes.update');
        Route::delete('intakes/{id}', [IntakeController::class, 'destroy'])->name('intakes.destroy');
        Route::put('intakes/{id}/toggle-override', [IntakeController::class, 'toggleOverride'])->name('intakes.toggle-override');
    });
});