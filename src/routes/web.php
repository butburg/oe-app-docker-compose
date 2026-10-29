<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\Admin;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\MailController as AdminMailController;
use App\Http\Controllers\Dev\MailPreviewController;

Route::get('/', [PostController::class, 'gallery'])
    ->name('dashboard');

Route::view('/about', 'about')->name('about');
Route::view('/impressum', 'impressum')->name('impressum');

// Link from the mail after an email change in the profile, works without being logged in
Route::get('/profile/email/confirm/{user}/{hash}', [ProfileController::class, 'showEmailChange'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('profile.email.confirm');
Route::post('/profile/email/confirm/{user}/{hash}', [ProfileController::class, 'confirmEmailChange'])
    ->middleware(['signed', 'throttle:6,1']);

Route::middleware('auth', 'verified')->group(function () {

    // Admin
    Route::middleware(Admin::class)->group(function () {
        Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.index');
        Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
        Route::get('/admin/posts', [PostController::class, 'all'])->name('posts.index');
        Route::get('/admin/mails', [AdminMailController::class, 'index'])->name('admin.mails.index');
        Route::post('/admin/mails/preview', [AdminMailController::class, 'preview'])->name('admin.mails.preview');
        Route::post('/admin/mails', [AdminMailController::class, 'send'])->name('admin.mails.send');
        Route::get('/posts/{post}/make-draft', [PostController::class, 'makedraft'])->name('posts.make-draft');
    });

    // Profiles
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // before /profile/{user}, otherwise "notifications" would be taken for a user
    Route::get('/profile/notifications', [ProfileController::class, 'notifications'])->name('profile.notifications');
    Route::patch('/profile/notifications', [ProfileController::class, 'updateNotifications'])->name('profile.notifications.update');
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');


    Route::patch('/profile/update-name', [ProfileController::class, 'updateName'])
        ->name('profile.updateName');
    Route::patch('/profile/update-description', [ProfileController::class, 'updateDescription'])
        ->name('profile.updateDescription');
    Route::patch('/profile/update-email', [ProfileController::class, 'updateEmail'])
        ->middleware('throttle:3,1')
        ->name('profile.updateEmail');
    Route::post('/profile/email/resend', [ProfileController::class, 'resendEmailChange'])
        ->middleware('throttle:3,1')
        ->name('profile.email.resend');
    Route::delete('/profile/email/pending', [ProfileController::class, 'cancelEmailChange'])
        ->name('profile.email.cancel');
    Route::patch('/profile/update-image', [ProfileController::class, 'updateImage'])
        ->name('profile.updateImage');

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/profile/update-image', [ProfileController::class, 'updateImage'])->name('profile.updateImage');


    // Posts
    Route::get('/posts/{post}/publish', [PostController::class, 'publish'])->name('posts.publish');
    Route::get('/posts/{post}/toggle-sensitive', [PostController::class, 'toggleSensitive'])->name('posts.toggleSensitive');


    // Resources route because it contains exact routes we need for a typical CRUD.
    // Posts have no show page, the gallery displays them.
    Route::resource('posts', PostController::class)->except('show');
    Route::resource('comments', CommentController::class);
});

// Mail previews with sample data, only on a developer machine (FEAT-016)
if (app()->environment('local')) {
    Route::get('/dev/mail', [MailPreviewController::class, 'index'])->name('dev.mail.index');
    Route::get('/dev/mail/{mail}', [MailPreviewController::class, 'show'])->name('dev.mail');
}

require __DIR__ . '/auth.php';
