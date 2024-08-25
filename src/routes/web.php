<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestMailController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\Admin;

Route::get('/', [PostController::class, 'gallery'])->name('welcome');

Route::get('/dashboard', [PostController::class, 'gallery'])->middleware(['auth', 'verified'])->name('dashboard');
Route::view('/impressum', 'impressum')->name('impressum');
Route::get('/send-test-email', [TestMailController::class, 'sendTestEmail']);

Route::middleware('auth', 'verified')->group(function () {
    // Routes here are accessible to authenticated users only!!

    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard')->middleware(Admin::class);
    Route::get('/admin/posts', [PostController::class, 'all'])->name('posts.index')->middleware(Admin::class);

    // Profiles
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/profile/update-image', [ProfileController::class, 'updateImage'])->name('profile.updateImage');



    // Posts
    Route::get('/posts/{post}/publish', [PostController::class, 'publish'])->name('posts.publish');
    Route::get('/posts/{post}/make-draft', [PostController::class, 'makedraft'])->name('posts.make-draft');
    Route::get('/posts/{post}/hide', [PostController::class, 'hide'])->name('posts.hide');


    // Resources route because it contains exact routes we need for a typical CRUD.
    Route::resources([
        'posts' => PostController::class,
        'comments' => CommentController::class,
    ]);
});

require __DIR__ . '/auth.php';
