<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/import/products', [ProductImportController::class, 'uploadCsv']);
Route::get('/import/results/{importId}', [ProductImportController::class, 'importSummary']);

Route::post('/upload/chunk', [ImageUploadController::class, 'uploadChunk']);
Route::post('/upload/complete', [ImageUploadController::class, 'completeUpload']);

Route::get('/products', [ProductController::class, 'index']);