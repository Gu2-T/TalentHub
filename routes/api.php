<?php
 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;



 
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
    Route::post('/verify-email', [AuthController::class, 'verify'])->name('verify');
});
Route::middleware('auth:api')->group(function () {
    Route::post('/jobs', [JobController::class, 'store']);     // Employer creates job
    Route::get('/jobs', [JobController::class, 'index']);      // List jobs
    Route::get('/jobs/{id}', [JobController::class, 'show']);  // View single job
    Route::put('/jobs/{id}', [JobController::class, 'update']); // Employer updates job
    Route::delete('/jobs/{id}', [JobController::class, 'destroy']); // Employer deletes job
});
Route::middleware('auth:api')->group(function () {
     // Applicant applies
    Route::post('/jobs/{jobId}/apply', [ApplicationController::class, 'apply']);

    // Employer/Admin actions
    Route::put('/applications/{id}/shortlist', [ApplicationController::class, 'shortlist']);
    Route::put('/applications/{id}/reject', [ApplicationController::class, 'reject']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('/profiles/create', [ProfileController::class, 'createProfile']);
    Route::patch('/update-profile', [ProfileController::class, 'updateProfile']);
   
});
Route::get('/photo/{folder}/{filename}', [ProfileController::class, 'getPhoto'])
    ->where(['folder' => '.*', 'filename' => '.*']);


Route::middleware(['auth:api'])->group(function () {
    Route::get('/admin/employers/pending', [AdminController::class, 'pendingEmployers']);
    Route::patch('/admin/employers/{id}/approve', [AdminController::class, 'approveEmployer']);
    Route::patch('/admin/employers/{id}/reject', [AdminController::class, 'rejectEmployer']);
});
