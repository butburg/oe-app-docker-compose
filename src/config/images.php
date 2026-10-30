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
    */

    'link_window_days' => max(1, (int) env('IMAGES_LINK_WINDOW_DAYS', 7)),

];
