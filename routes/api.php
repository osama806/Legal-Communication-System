<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgencyController;
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
        Route::get("agencies", [AgencyController::class, "index"]);
        Route::get("lawyers", [LawyerController::class, "lawyersAI"]);
        Route::get("issues", [IssueController::class, "issuesAI"]);
        Route::get("rates", [RateController::class, "ratesAI"]);
    });

    Route::prefix("admin")->group(function () {
        Route::controller(AdminController::class)->group(function () {
            Route::post("auth/signup", "signup");
            Route::post('auth/signin', 'signin');
        });

        Route::middleware('auth:api')->group(function () {
            Route::controller(AdminController::class)->group(function () {
                Route::post('auth/signout', 'signout');
                Route::get('profile', 'profile');
            });

            Route::post("signup-user", [UserController::class, "registerUser"]);
            Route::apiResource("get-users", UserController::class)->only(["index", "show"]);

            Route::apiResource("get-employees", EmployeeController::class)->except(["update", "destroy"]);

            Route::apiResource("get-lawyers", LawyerController::class)->except(["update", "destroy"]);

            Route::apiResource("get-representatives", RepresentativeController::class)->except(["update", "destroy"]);

            Route::apiResource('get-rates', RateController::class)->except(['store', 'update']);
            Route::apiResource('specializations', SpecializationController::class)->except(['update', 'destroy']);
        });
    });

    Route::prefix("users")->group(function () {
        Route::controller(UserController::class)->group(function () {
            Route::post("auth/signup", "store");
            Route::post('auth/signin', 'login');
        });

        Route::middleware('auth:api')->group(function () {
            Route::controller(UserController::class)->group(function () {
                Route::post('auth/signout', 'logout');
                Route::get('profile', 'profile');
                Route::post('change-password', 'changePassword');
                Route::put("{id}", "update");
                Route::delete("{id}", "destroy");
                Route::get('{id}/notifications', 'getNotifications');
            });

            Route::post('{id}/send-notify-to-lawyer', [AgencyController::class, 'store']);
            Route::put('get-agencies/{id}/isolate', [AgencyController::class, 'destroy']);
            Route::get('get-lawyers', [LawyerController::class, 'indexForUser']);
            Route::get('get-lawyers/{id}', [LawyerController::class, 'showForUser']);
            Route::post('get-lawyers/{id}/rating', [RateController::class, 'store']);
            Route::put('get-rates/{id}', [RateController::class, 'update']);
        });
    });

    Route::prefix("employees")->group(function () {
        Route::post('auth/signin', [EmployeeController::class, 'signin']);
        Route::middleware('auth:api')->group(function () {
            Route::post('auth/signout', [EmployeeController::class, 'signout']);
            Route::get('profile', [EmployeeController::class, 'profile']);
            Route::put("get-users/{id}", [UserController::class, "updateUser"]);
            Route::delete("get-users/{id}", [UserController::class, "destroyUser"]);
            Route::put("get-lawyers/{id}", [LawyerController::class, "update"]);
            Route::delete("get-lawyers/{id}", [LawyerController::class, "destroy"]);
            Route::put("get-representatives/{id}", [RepresentativeController::class, "update"]);
            Route::delete("get-representatives/{id}", [RepresentativeController::class, "destroy"]);
            Route::apiResource('get-specializations', SpecializationController::class)->only(['update', 'destroy']);
        });
    });

    Route::prefix("lawyers")->group(function () {
        Route::post('auth/signin', [LawyerController::class, 'login']);
        Route::middleware('auth:lawyer')->group(function () {
            Route::controller(LawyerController::class)->group(function () {
                Route::post('auth/signout', 'logout');
                Route::get('profile', 'profile');
                Route::get('{id}/notifications', 'getNotifications');
                Route::post('{id}/send-notify-to-representative', 'agencyAccepted');
            });

            Route::controller(IssueController::class)->group(function () {
                Route::post('get-issues/{id}/change-status', 'updateStatus');
                Route::post('get-issues/{id}/finish', 'endIssue');
            });

            Route::apiResource('get-issues', IssueController::class)->except(['update']);
        });
    });

    Route::prefix("representatives")->group(function () {
        Route::post('auth/signin', [RepresentativeController::class, 'login']);
        Route::middleware('auth:representative')->controller(RepresentativeController::class)->group(function () {
            Route::post('auth/signout', 'logout');
            Route::get('profile', 'profile');
            Route::get('{id}/notifications', 'getNotifications');
            Route::post('{id}/send-notify-to-all', 'agencyAcceptance');
        });
    });
});
