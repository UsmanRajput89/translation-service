<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TranslationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);
// Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('translations')->group(function () {
        Route::get('/', [TranslationController::class, 'getAllTranslations']);          
        Route::post('/create', [TranslationController::class, 'store']);
        Route::get('/search', [TranslationController::class, 'search']); 
        Route::get('/export', [TranslationController::class, 'export']); 
        Route::put('/{id}', [TranslationController::class, 'update']);      
        Route::get('/{id}', [TranslationController::class, 'show']);       
    });




});