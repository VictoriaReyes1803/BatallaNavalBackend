<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Events\TestEvent;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/error', function () {
    return response()->json(['error' => 'Unauthorized'], 401);
})->name('error');


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/testevent', function () {
    event(new TestEvent('testeo'));
    return response()->json([
        'status' => true
    ]);;
});

Route::prefix('/user')->group(function () {

    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/authenticatetoken', function () {
            return response()->json([
                'status' => true
            ]);
        });

        Route::get('/logout', [UserController::class, 'logout']);
    

    });

});
