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

            Route::controller(UserController::class)->group(function () {
                Route::post("signup-user", "registerUser");
                Route::get("get-users", "getUsers");
                Route::get("get-users/{id}", "getUser");
            });

            Route::controller(EmployeeController::class)->group(function () {
                Route::post("signup-employee", "registerEmployee");
                Route::get("get-employees", "getEmployees");
                Route::get("get-employees/{id}", "getEmployee");
            });

            Route::controller(LawyerController::class)->group(function () {
                Route::post("signup-lawyer", "registerLawyer");
                Route::get("get-lawyers", "getLawyers");
                Route::get("get-lawyers/{id}", "getLawyer");
            });

            Route::controller(RepresentativeController::class)->group(function () {
                Route::post("signup-representative", "registerRepresentative");
                Route::get("get-representatives", "getRepresentatives");
                Route::get("get-representatives/{id}", "getRepresentative");
            });

            Route::apiResource('get-rates', RateController::class)->only(['index', 'show', 'destroy']);
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
            Route::get('get-lawyers', [LawyerController::class, 'fetchLawyers']);
            Route::get('get-lawyers/{id}', [LawyerController::class, 'fetchLawyer']);
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
            Route::put("get-lawyers/{id}", [LawyerController::class, "updateLawyer"]);
            Route::delete("get-lawyers/{id}", [LawyerController::class, "destroyLawyer"]);
            Route::put("get-representatives/{id}", [RepresentativeController::class, "updateRepresentative"]);
            Route::delete("get-representatives/{id}", [RepresentativeController::class, "destroyRepresentative"]);
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
                Route::post('{id}/send-notify-to-representative', 'sendNotificationToRep');
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
            Route::post('{id}/send-notify-to-all', 'sendNotificationsToAll');
        });
    });
});
