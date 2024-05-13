<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    //return view('dashboard');
    return view('welcome');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Routes of
    // Posts, Profile,
    // accessible to authenticated users only

    // Profiles
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');



    // Posts. use a resource route because it contains exact routes we need for a typical CRUD application.
    Route::resource('posts', PostController::class);
    Route::get('/posts/{post}/published', [PostController::class, 'publish'])->name('posts.published');
    Route::get('/posts/{post}/draft', [PostController::class, 'draft'])->name('posts.draft');
});

require __DIR__ . '/auth.php';
