<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\LawyerController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\RepresentativeController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix("v1")->group(function () {
    Route::prefix("ai")->group(function () {
        Route::get("users", [UserController::class, "usersAI"]);
        Route::get("lawyers", [LawyerController::class, "lawyersAI"]);
        Route::get("issues", [IssueController::class, "issuesAI"]);
        Route::get("rates", [RateController::class, "ratesAI"]);
    });

    Route::prefix("admin")->group(function () {
        Route::post("auth/signup", [AdminController::class, "signup"]);
        Route::post('auth/signin', [AdminController::class, 'signin']);
        Route::middleware('auth:api')->controller(AdminController::class)->group(function () {
            Route::post('auth/signout', 'signout');
            Route::post("signup-user", "registerUser");
            Route::post("signup-employee", "registerEmployee");
            Route::post("signup-lawyer", "registerLawyer");
            Route::post("signup-representative", "registerRepresentative");
            Route::get('profile', 'profile');
            Route::get("get-users", "getUsers");
            Route::get("get-users/{id}", "getUser");
            Route::get("get-employees", "getEmployees");
            Route::get("get-employees/{id}", "getEmployee");
            Route::get("get-lawyers", "getLawyers");
            Route::get("get-lawyers/{id}", "getLawyer");
            Route::get("get-representatives", "getRepresentatives");
            Route::get("get-representatives/{id}", "getRepresentative");
        });
        Route::middleware('auth:api')->group(function () {
            Route::apiResource('get-rates', RateController::class)->only(['index', 'show', 'destroy']);
            Route::apiResource('specializations', SpecializationController::class)->except(['update', 'destroy']);
        });
    });

    Route::prefix("users")->group(function () {
        Route::post("auth/signup", [UserController::class, "store"]);
        Route::post('auth/signin', [UserController::class, 'login']);
        Route::middleware('auth:api')->controller(UserController::class)->group(function () {
            Route::post('auth/signout', 'logout');
            Route::get('profile', 'profile');
            Route::post('change-password', 'changePassword');
            Route::put("{id}", "update");
            Route::delete("{id}", "destroy");
            Route::put('get-agencies/{id}/isolate', 'agencyIsolate');
            Route::post('{id}/send-notify-to-lawyer', 'agencyRequest');
            Route::get('{id}/notifications', 'getNotifications');
        });
        Route::post('get-lawyers/{id}/rating', [RateController::class, 'store'])->middleware('auth:api');
        Route::put('get-rates/{id}', [RateController::class, 'update'])->middleware('auth:api');
    });

    Route::prefix("employees")->group(function () {
        Route::post('auth/signin', [EmployeeController::class, 'signin']);
        Route::middleware('auth:api')->controller(EmployeeController::class)->group(function () {
            Route::post('auth/signout', 'signout');
            Route::get('profile', 'profile');
            Route::put("get-users/{id}", "updateUser");
            Route::delete("get-users/{id}", "destroyUser");
            Route::put("get-lawyers/{id}", "updateLawyer");
            Route::delete("get-lawyers/{id}", "destroyLawyer");
            Route::put("get-representatives/{id}", "updateRepresentative");
            Route::delete("get-representatives/{id}", "destroyRepresentative");
        });
        Route::middleware('auth:api')->group(function () {
            Route::apiResource('get-specializations', SpecializationController::class)->only(['update', 'destroy']);
        });
    });

    Route::prefix("lawyers")->group(function () {
        Route::post('auth/signin', [LawyerController::class, 'login']);
        Route::middleware('auth:lawyer')->group(function () {
            Route::post('auth/signout', [LawyerController::class, 'logout']);
            Route::get('profile', [LawyerController::class, 'profile']);
            Route::apiResource('get-issues', IssueController::class)->except(['update']);
            Route::post('get-issues/{id}/change-status', [IssueController::class, 'updateStatus']);
            Route::post('get-issues/{id}/finish', [IssueController::class, 'endIssue']);
            Route::get('{id}/notifications', [LawyerController::class, 'getNotifications']);
            Route::post('{id}/send-notify-to-representative', [LawyerController::class, 'sendNotificationToRep']);
        });
    });

    Route::prefix("representatives")->group(function () {
        Route::post('auth/signin', [RepresentativeController::class, 'login']);
        Route::middleware('auth:representative')->controller(RepresentativeController::class)->group(function () {
            Route::post('auth/signout', 'logout');
            Route::get('profile', 'profile');
            Route::get('{id}/notifications', 'getNotifications');
            Route::post('{id}/send-notify-to-all', 'sendNotificationsToAll');
        });
    });
});
