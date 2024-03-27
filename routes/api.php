<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NetSuiteController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('getUser', [AuthController::class, 'getUser'])->name('getUser');
    Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::post('me', [AuthController::class, 'me'])->name('me');
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('registerUser', [AuthController::class, 'registerUser'])->name('registerUser');
    Route::post('userList', [AuthController::class, 'userList'])->name('userList');
    Route::post('userListAdmin', [AuthController::class, 'userListAdmin'])->name('userListAdmin');
    Route::post('approveUser', [AuthController::class, 'approveUser'])->name('approveUser');
    Route::post('denieUser', [AuthController::class, 'denieUser'])->name('denieUser');
    Route::post('editUser', [AuthController::class, 'editUser'])->name('editUser');
    Route::post('dataCompany', [AuthController::class, 'dataCompany'])->name('dataCompany');
    Route::post('registerNetsuite', [AuthController::class, 'registerNetsuite'])->name('registerNetsuite');
    Route::post('editNetsuite', [AuthController::class, 'editNetsuite'])->name('editNetsuite');
    Route::get('makeRequest', [NetSuiteController::class, 'makeRequest'])->name('makeRequest');
});
