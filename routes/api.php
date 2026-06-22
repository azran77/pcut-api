<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// ─── Public routes ────────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ─── Authenticated routes ─────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // ── Student routes ────────────────────────────────────────────────────────
    Route::middleware('role:student,educator,admin')->group(function () {
        Route::get('/survey/items',           [SurveyController::class, 'items']);
        Route::get('/survey/domains',         [SurveyController::class, 'domains']);
        Route::post('/survey/submit',         [SurveyController::class, 'submit']);
        Route::get('/survey/my-submissions',  [SurveyController::class, 'mySubmissions']);
        Route::get('/survey/submission/{id}', [SurveyController::class, 'submission']);
    });

    // ── Educator routes ───────────────────────────────────────────────────────
    Route::middleware('role:educator,admin')->group(function () {
        Route::get('/dashboard/overview',              [DashboardController::class, 'overview']);
        Route::get('/dashboard/class/{classId}',       [DashboardController::class, 'classStats']);
        Route::get('/dashboard/student/{studentId}',   [DashboardController::class, 'studentProfile']);
        Route::get('/dashboard/wright-map',            [DashboardController::class, 'wrightMap']);
        Route::get('/dashboard/domain-stats',          [DashboardController::class, 'domainStats']);
        Route::get('/dashboard/my-classes',            [DashboardController::class, 'myClasses']);
        Route::get('/dashboard/students',              [DashboardController::class, 'students']);
    });

    // ── Admin routes ──────────────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Users
        Route::get('/users',             [AdminController::class, 'users']);
        Route::get('/users/{id}',        [AdminController::class, 'showUser']);
        Route::post('/users',            [AdminController::class, 'createUser']);
        Route::put('/users/{id}',        [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}',     [AdminController::class, 'deleteUser']);
        Route::post('/users/{id}/role',  [AdminController::class, 'assignRole']);

        // Classes
        Route::get('/classes',           [AdminController::class, 'classes']);
        Route::post('/classes',          [AdminController::class, 'createClass']);
        Route::put('/classes/{id}',      [AdminController::class, 'updateClass']);
        Route::delete('/classes/{id}',   [AdminController::class, 'deleteClass']);
        Route::post('/classes/{id}/enroll',   [AdminController::class, 'enrollStudent']);
        Route::delete('/classes/{id}/enroll', [AdminController::class, 'unenrollStudent']);
        Route::post('/classes/{id}/educator', [AdminController::class, 'assignEducator']);

        // Survey management
        Route::get('/submissions',          [AdminController::class, 'submissions']);
        Route::get('/analytics',            [AdminController::class, 'analytics']);
        Route::delete('/submissions/{id}',  [AdminController::class, 'deleteSubmission']);
        Route::get('/export/responses',     [AdminController::class, 'exportResponses']);
    });
});
