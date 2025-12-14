<?php
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Controller;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middle ware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [RegisterController::class, 'login']);
Route::post('/logout', [RegisterController::class,'logout'])->middleware("auth:sanctum");
Route::put('/profile', [RegisterController::class,'update'])->middleware("auth:sanctum");




Route::middleware('auth:sanctum')->group(function () {
    Route::get('/services', [ServiceController::class, 'index']);       
    Route::get('/services/{id}', [ServiceController::class, 'show']);   
    Route::post('/services', [ServiceController::class, 'create']);     
    Route::put('/services/{id}', [ServiceController::class, 'update']); 
    Route::delete('/services/{id}', [ServiceController::class, 'destroy']); 
    Route::get('/services', [ServiceController::class, 'show_type']);



    Route::post('/storeCategory', [CategoryController::class, 'storeCategory']);
    Route::PUT('/editCategory/{id}', [CategoryController::class, 'editCategory']);
    Route::DELETE('/deleteCategory/{id}', [CategoryController::class, 'deleteCategory']);


});
    Route::get('/showOffers', [ServiceController::class, 'showOffers']); 
    Route::get('/showRequests', [ServiceController::class, 'showRequests']);
    Route::get('/categories', [CategoryController::class, 'index']);
