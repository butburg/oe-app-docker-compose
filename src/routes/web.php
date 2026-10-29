<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Http\Middleware\Admin;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AdminController;

Route::get('/', [PostController::class, 'gallery'])
    ->name('dashboard');

Route::view('/about', 'about')->name('about');
Route::view('/impressum', 'impressum')->name('impressum');

// Link from the mail after an email change in the profile, works without being logged in
Route::get('/profile/email/confirm/{user}/{hash}', [ProfileController::class, 'confirmEmailChange'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('profile.email.confirm');

Route::middleware('auth', 'verified')->group(function () {

    // Admin
    Route::middleware(Admin::class)->group(function () {
        Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.index');
        Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
        Route::get('/admin/posts', [PostController::class, 'all'])->name('posts.index');
        Route::get('/posts/{post}/make-draft', [PostController::class, 'makedraft'])->name('posts.make-draft');
    });

    // Profiles
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
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

    // Mail
    #Route::get('/send-test-email', [TestMailController::class, 'sendTestEmail']);
    Route::get('/send-test-email', function () {
        Mail::raw('This is a test email', function ($message) {
            $message->to('recipient@example.com')->subject('Test Email');
        });
        return 'Test email sent!';
    });
});

require __DIR__ . '/auth.php';
