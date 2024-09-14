<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\Admin;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;


Route::get('/', [PostController::class, 'gallery'])
    ->name('dashboard');

Route::view('/impressum', 'impressum')->name('impressum');


Route::middleware('auth', 'verified')->group(function () {

    // Admin
    Route::middleware(Admin::class)->group(function () {
        Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.index');
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
        ->name('profile.updateEmail');
    Route::patch('/profile/update-image', [ProfileController::class, 'updateImage'])
        ->name('profile.updateImage');

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/profile/update-image', [ProfileController::class, 'updateImage'])->name('profile.updateImage');


    // Posts
    Route::get('/posts/{post}/publish', [PostController::class, 'publish'])->name('posts.publish');
    Route::post('/posts/{post}/toggle-sensitive', [PostController::class, 'toggleSensitive'])->name('posts.toggleSensitive');


    // Resources route because it contains exact routes we need for a typical CRUD.
    Route::resources([
        'posts' => PostController::class,
        'comments' => CommentController::class,
    ]);

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
