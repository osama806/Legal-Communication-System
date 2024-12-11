<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourtController;
use App\Http\Controllers\CourtRoomController;
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
            Route::get("get-issues", [IssueController::class, "getAll"]);
            Route::get("get-issues/{id}", [IssueController::class, "showOne"]);
            Route::apiResource("get-employees", EmployeeController::class)->except(["update", "destroy"]);
            Route::apiResource("get-lawyers", LawyerController::class)->except(["update", "destroy"]);
            Route::apiResource("get-courts", CourtController::class)->only(["index", "show"]);
            Route::apiResource("get-court-rooms", CourtRoomController::class)->only(["index", "show"]);
            Route::apiResource("get-representatives", RepresentativeController::class)->except(["update", "destroy"]);
            Route::apiResource('get-agencies', AgencyController::class)->only(['index', 'show']);
            Route::apiResource('get-rates', RateController::class)->only(['index', 'show']);
            Route::apiResource('get-specializations', SpecializationController::class)->except(['update', 'destroy']);
        });
    });

    Route::prefix("employees")->group(function () {
        Route::middleware(['auth:api', 'refresh.token', 'security'])->group(function () {
            Route::get('profile', [EmployeeController::class, 'profile']);
            Route::controller(UserController::class)->group(function () {
                Route::get('get-users', 'getAll');
                Route::get('get-users/{id}', 'showOne');
                Route::put("get-users/{id}", "updateUser");
                Route::delete("get-users/{id}", "destroyUser");
            });

            Route::controller(RepresentativeController::class)->group(function () {
                Route::get('get-representatives', 'getAll');
                Route::get('get-representatives/{id}', 'showOne');
                Route::put("get-representatives/{id}", "update");
                Route::delete("get-representatives/{id}", "destroy");
            });

            Route::apiResource('fetch-lawyers', LawyerController::class)->except('store');
            Route::apiResource('courts', CourtController::class);
            Route::apiResource('court-rooms', CourtRoomController::class);
            Route::apiResource('fetch-specializations', SpecializationController::class)->except('store');
            Route::get("get-issues", [IssueController::class, "getAll"]);
            Route::get("get-issues/{id}", [IssueController::class, "showOne"]);
            Route::apiResource('fetch-rates', RateController::class)->except(['store', 'update']);
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
        Route::apiResource('all-rates', RateController::class)->except(['index', 'show']);
        Route::get('get-lawyers', [LawyerController::class, 'indexForUser']);
        Route::get('get-lawyers/{id}', [LawyerController::class, 'showForUser']);
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

            Route::apiResource('/', LawyerController::class)->only(['index', 'show']);
            Route::apiResource("fetch-courts", CourtController::class)->only(["index", "show"]);
            Route::apiResource("fetch-court-rooms", CourtRoomController::class)->only(["index", "show"]);
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
        Route::apiResource('all-lawyers', LawyerController::class)->only(['index', 'show']);
        Route::apiResource("all-courts", CourtController::class)->only(["index", "show"]);
        Route::apiResource("all-court-rooms", CourtRoomController::class)->only(["index", "show"]);
    });
});
