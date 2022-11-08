<?php

use Newelement\DmpKscape\Http\Controllers\DmpKscapeController;

// These are /api/* routes
Route::get('/dmp-kscape-settings', [DmpKscapeController::class, 'getSettings']);
Route::get('/dmp-kscape-now-playing', [DmpKscapeController::class, 'getNowPlaying']);

Route::get('/dmp-kscape-install', [DmpKscapeController::class, 'install']);
Route::get('/dmp-kscape-update', [DmpKscapeController::class, 'update']);
