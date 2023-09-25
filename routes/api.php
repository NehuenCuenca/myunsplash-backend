<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ImageController;

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


// IMAGE ROUTE'S
Route::get('images', [ImageController::class, 'getAllImages']);
Route::get('images/{name_match}', [ImageController::class, 'getImagesByNameMatch']);
Route::get('image/{image_id}', [ImageController::class, 'getImageById']);
Route::post('image/add', [ImageController::class, 'addImage']);
Route::delete('image/delete/{image_id}', [ImageController::class, 'deleteImage']);