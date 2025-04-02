<?php

use Illuminate\Support\Facades\Route;
use Cubenl\PostcodeNL\Http\Controllers\PostcodeNLController;

Route::prefix('api')->group(function () {
    Route::get('/address/autocomplete', [PostcodeNLController::class, 'autocompleteAddress']);
});
