<?php

use App\Http\Controllers\LinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LinkController::class, 'index']);

Route::post('/shorten', [LinkController::class, 'store']);

Route::get('/{url}', [LinkController::class, 'redirect']);
