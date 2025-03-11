<?php

    use App\Http\Controllers\PositionController;
    use App\Http\Controllers\TokenController;
    use App\Http\Controllers\UserController;
    use Illuminate\Support\Facades\Route;


    Route::get('/token', [TokenController::class, 'generateToken']);

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'getUsers']);
        Route::post('/', [UserController::class, 'register'])->middleware('check.registration.token');
        Route::get('/{id}', [UserController::class, 'getUserById']);
    });


    Route::get('/positions', [PositionController::class, 'getPositions']);
