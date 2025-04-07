<?php

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use App\Http\Controllers\areaController;
use App\Http\Controllers\doctorController;
use App\Http\Controllers\AuthController;

Route::post('/signup/admin', [AuthController::class, 'signupAdmin']);
Route::post('/signup/doctor', [AuthController::class, 'signupDoctor']);
Route::post('/signup/patient', [AuthController::class, 'signupPatient']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

/* ------ AREAS ------ */
Route::get('/areas', [areaController::class, 'index'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::get('/areas/{id}', [areaController::class, 'show']);
Route::delete('/areas/{id}', [areaController::class, 'destroy']);
Route::post('/areas', [areaController::class, 'store']);
Route::put('/areas/{id}', [areaController::class, 'update']);
Route::patch('/areas/{id}', [areaController::class, 'updatePartial']);


/* ------ DOCTORES ------ */
Route::get('/doctores', [doctorController::class, 'index']);
Route::get('/doctores/{id}', [doctorController::class, 'show']);
Route::delete('/doctores/{id}', [doctorController::class, 'destroy']);
Route::post('/doctores', [doctorController::class, 'store']);
Route::post('/doctores/update/{id}', [doctorController::class, 'update']);
Route::post('/doctores/patch/{id}', [doctorController::class, 'updatePartial']);