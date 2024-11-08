<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\LawyerController;
use App\Http\Controllers\RepresentativeController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix("v1")->group(function () {
    Route::prefix("admin")->group(function () {
        Route::controller(AdminController::class)->group(function () {
            Route::post("create", "store");
            Route::middleware('auth:api')->group(function () {
                Route::post("create-employee", "registerEmployee");
                Route::post("create-lawyer", "registerLawyer");
                Route::post("create-representative", "registerRepresentative");
                Route::post("create-user", "registerUser");
            });
        });
    });

    Route::prefix("auth")->group(function () {
        // مسارات user
        Route::post('user/signin', [UserController::class, 'login']);
        Route::post('user/signout', [UserController::class, 'logout'])->middleware('auth:api');
        Route::post('user/change-password', [UserController::class, 'changePassword'])->middleware('auth:api');

        // مسارات lawyer
        Route::post('lawyer/signin', [LawyerController::class, 'login']);
        Route::post('lawyer/signout', [LawyerController::class, 'logout'])->middleware('auth:lawyer');

        // مسارات representative
        Route::post('representative/signin', [RepresentativeController::class, 'login']);
        Route::post('representative/signout', [RepresentativeController::class, 'logout'])->middleware('auth:representative');

    });

    Route::prefix("lawyers")->controller(LawyerController::class)->group(function () {
        Route::get("all", "index");
        Route::get("{id}", "show");
        Route::patch("{id}/update", "updateByEmployee")->middleware('auth:api');
        Route::delete("{id}/delete", "destroyByEmployee")->middleware('auth:api');
    });

    Route::prefix("representatives")->controller(RepresentativeController::class)->group(function () {
        Route::get("all", "index");
        Route::get("{id}", "show");
        Route::patch("{id}/update", "updateByEmployee")->middleware('auth:api');
        Route::delete("{id}/delete", "destroyByEmployee")->middleware('auth:api');
    });

    Route::prefix("users")->controller(UserController::class)->group(function () {
        Route::get("/", "index");
        Route::get("{id}", "show");
        Route::post("/", "store")->middleware('auth:api');
        Route::put("/", "update")->middleware('auth:api');
        Route::delete("/", "destroy")->middleware('auth:api');
        Route::patch("{id}/update", "updateByEmployee")->middleware('auth:api');
        Route::delete("{id}/delete", "destroyByEmployee")->middleware('auth:api');
    });

});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
