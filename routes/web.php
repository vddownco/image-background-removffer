<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;

Route::get('/', [ImageController::class, 'viewPage']);
Route::post('/remove-background', [ImageController::class, 'removeBackground'])->name('remove.background');
