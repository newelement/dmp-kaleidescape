<?php

use Newelement\DmpKscape\Http\Controllers\DmpKscapeController;

Route::put('/dmp-kscape/settings', [DmpKscapeController::class, 'updateSettings'])->name('dmp-kscape.settings');
