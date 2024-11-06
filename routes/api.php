<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\LawyerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix("v1")->group(function () {
    Route::prefix("auth")->controller(UserController::class)->group(function () {
        Route::post("register", "register");
        Route::post("login", "login");
        Route::middleware("auth:api")->group(function () {
            Route::post("change-password","changePassword");
            Route::get("profile","show");
            Route::patch("update","updateProfile");
            Route::post("logout","logout");
            Route::delete("delete","deleteUser");
        });
    });

    Route::apiResource("lawyers",LawyerController::class);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
