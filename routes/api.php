<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\AuthController;
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
        Route::get("agencies", [AgencyController::class, "agenciesAI"]);
        Route::get("lawyers", [LawyerController::class, "lawyersAI"]);
        Route::get("issues", [IssueController::class, "issuesAI"]);
        Route::get("rates", [RateController::class, "ratesAI"]);
    });

    // Auth admin, employee, lawyer & representative
    Route::prefix('auth')->group(function () {
        Route::post('signin', [AuthController::class, 'login']);
        Route::post('signout', [AuthController::class, 'logout'])->middleware(['auth:api,lawyer,representative']);
    });

    Route::prefix("admin")->group(function () {
        Route::post("auth/signup", [AdminController::class, "signup"]);
        Route::middleware(['auth:api', 'refresh.token', 'security'])->group(function () {
            Route::get('profile', [AdminController::class, 'profile']);
            Route::post("signup-user", [UserController::class, "registerUser"]);
            Route::apiResource("get-users", UserController::class)->only(["index", "show"]);
            Route::get("get-issues", [IssueController::class, "getForAdminAndEmployee"]);
            Route::get("get-issues/{id}", [IssueController::class, "showForAdminAndEmployee"]);
            Route::apiResource("get-employees", EmployeeController::class)->except(["update", "destroy"]);
            Route::apiResource("get-lawyers", LawyerController::class)->except(["update", "destroy"]);
            Route::apiResource("get-representatives", RepresentativeController::class)->except(["update", "destroy"]);
            Route::apiResource('get-agencies', AgencyController::class)->only(['index', 'show']);
            Route::apiResource('get-rates', RateController::class)->except(['store', 'update']);
            Route::apiResource('get-specializations', SpecializationController::class)->except(['update', 'destroy']);
        });
    });

    Route::prefix("employees")->group(function () {
        Route::middleware(['auth:api', 'refresh.token', 'security'])->group(function () {
            Route::get('profile', [EmployeeController::class, 'profile']);
            Route::controller(UserController::class)->group(function () {
                Route::get('get-users', 'indexForEmployee');
                Route::get('get-users/{id}', 'showForEmployee');
                Route::put("get-users/{id}", "updateUser");
                Route::delete("get-users/{id}", "destroyUser");
            });

            Route::controller(LawyerController::class)->group(function () {
                Route::get('get-lawyers', 'indexForEmployee');
                Route::get('get-lawyers/{id}', 'showForEmployee');
                Route::put("get-lawyers/{id}", "update");
                Route::delete("get-lawyers/{id}", "destroy");
            });

            Route::controller(RepresentativeController::class)->group(function () {
                Route::get('get-representatives', 'indexForEmployee');
                Route::get('get-representatives/{id}', 'showForEmployee');
                Route::put("get-representatives/{id}", "update");
                Route::delete("get-representatives/{id}", "destroy");
            });

            Route::apiResource('fetch-specializations', SpecializationController::class)->except('store');
            Route::get("get-issues", [IssueController::class, "getForAdminAndEmployee"]);
            Route::get("get-issues/{id}", [IssueController::class, "showForAdminAndEmployee"]);
        });
    });

    // Auth user
    Route::controller(UserController::class)->group(function () {
        Route::post("auth/user/signup", "store");
        Route::post('auth/user/signin', 'login');
        Route::post('auth/user/signout', 'logout')->middleware(['auth:api', 'refresh.token', 'security']);
    });

    Route::prefix("users")->middleware(['auth:api', 'refresh.token', 'security'])->group(function () {
        Route::controller(UserController::class)->group(function () {
            Route::get('profile', 'profile');
            Route::post('change-password', 'changePassword');
            Route::get('notifications', 'getNotifications');
        });
        Route::controller(AgencyController::class)->group(function () {
            Route::post('send-notify-to-lawyer', 'store');
            Route::put('get-agencies/{id}/isolate', 'destroy');
            Route::get('get-agencies', 'indexForUser');
            Route::get('get-agencies/{id}', 'showForUser');
        });

        Route::apiResource('/', UserController::class)->only(['destroy', 'update']);
        Route::get('get-lawyers', [LawyerController::class, 'indexForUser']);
        Route::get('get-lawyers/{id}', [LawyerController::class, 'showForUser']);
        Route::post('get-lawyers/{id}/rating', [RateController::class, 'store']);
        Route::put('get-rates/{id}', [RateController::class, 'update']);
        Route::get('get-issues', [IssueController::class, 'indexForUser']);
        Route::get('get-issues/{id}', [IssueController::class, 'showForUser']);
    });

    Route::prefix("lawyers")->group(function () {
        Route::middleware(['auth:lawyer', 'refresh.token', 'security'])->group(function () {
            Route::controller(LawyerController::class)->group(function () {
                Route::get('profile', 'profile');
                Route::get('notifications', 'getNotifications');
                Route::post('send-notify-to-representative', 'agencyAccepted');
            });

            Route::controller(IssueController::class)->group(function () {
                Route::post('get-issues/{id}/change-status', 'updateStatus');
                Route::post('get-issues/{id}/finish', 'endIssue');
            });

            Route::apiResource('get-issues', IssueController::class)->except(['update']);
            Route::get('get-representatives', [RepresentativeController::class, 'indexForLawyer']);
            Route::get('get-representatives/{id}', [RepresentativeController::class, 'showForLawyer']);
            Route::get('get-agencies', [AgencyController::class, 'indexForLawyer']);
            Route::get('get-agencies/{id}', [AgencyController::class, 'showForLawyer']);
        });
    });

    Route::prefix("representatives")->middleware(['auth:representative', 'refresh.token', 'security'])->group(function () {
        Route::controller(RepresentativeController::class)->group(function () {
            Route::get('profile', 'profile');
            Route::get('notifications', 'getNotifications');
            Route::post('send-notify-to-all', 'agencyAcceptance');
        });
        Route::controller(AgencyController::class)->group(function () {
            Route::get('get-agencies', 'indexForRepresentative');
            Route::get('get-agencies/{id}', 'showForRepresentative');
        });
    });
});
