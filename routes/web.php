<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ImageView;

Route::get('/', ImageView::class);
//Route::post('/remove-background', [ImageController::class, 'removeBackground'])->name('remove.background');
