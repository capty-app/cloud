<?php

use App\Http\Controllers\Api\UploadController;
use Illuminate\Support\Facades\Route;

Route::post('/galleries/{slug}/upload', UploadController::class);
