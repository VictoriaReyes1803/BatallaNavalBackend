<?php

use App\Http\Controllers\GameController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/testevent', function () {
    event(new TestEvent('testeo'));
    return response()->json([
        'status' => true
    ]);
});

Route::prefix('game')->group(function(){
    Route::post('/queue', [GameController::class, 'queueGame']);
    Route::put('/join/random', [GameController::class, 'joinRandomGame']);
    Route::put('/end', [GameController::class, 'endGame']);
    Route::post('/dequeue', [GameController::class, 'dequeueGame']);
    Route::post('/cancel/random', [GameController::class, 'cancelRandomQueue']);
    Route::post('/send/board', [GameController::class, 'sendBoard']);
    Route::get('/history', [GameController::class, 'myGameHistory']);
    Route::post('/notify', [GameController::class, 'sendNotify']);
    Route::post('/attack', [GameController::class, 'attack']);
    Route::post('/attack/success', [GameController::class, 'attackSuccess']);
    Route::post('/attack/failed', [GameController::class, 'attackFailed']);
});
