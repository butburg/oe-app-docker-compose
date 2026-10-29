<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image links (FEAT-011)
    |--------------------------------------------------------------------------
    |
    | Uploaded images are only served through /img/{variant}/{token}. The token
    | changes every window by itself, a link stays valid for the current and the
    | next window (1 to 2 windows long).
    |
    | x_accel: let nginx send the file (X-Accel-Redirect). Turn it off when the
    | app runs without nginx, e.g. with "php artisan serve".
    |
    */

    'link_window_days' => (int) env('IMAGES_LINK_WINDOW_DAYS', 7),

    'x_accel' => (bool) env('IMAGES_X_ACCEL', true),

];
