<?php

use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

// Uploaded images (FEAT-011). Loaded outside the web middleware group in bootstrap/app.php:
// no session, so image requests do not write sessions or change the intended/previous URL.
Route::get('/img/{variant}/{token}', [ImageController::class, 'show'])
    ->where(['variant' => '[0-9a-f]{16}', 'token' => '[0-9a-f]{32}'])
    ->name('images.show');
