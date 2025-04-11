<?php

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use App\Http\Controllers\areaController;
use App\Http\Controllers\doctorController;
use App\Http\Controllers\authController;

Route::post('/signup/admin', [AuthController::class, 'signupAdmin']);
Route::post('/signup/doctor', [AuthController::class, 'signupDoctor']);
Route::post('/signup/patient', [AuthController::class, 'signupPatient']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

/* ------ AREAS ------ */
Route::get('/areas', [areaController::class, 'index']);
Route::get('/areas/{id}', [areaController::class, 'show']);
Route::delete('/areas/{id}', [areaController::class, 'destroy'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::post('/areas', [areaController::class, 'store'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::put('/areas/{id}', [areaController::class, 'update'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::patch('/areas/{id}', [areaController::class, 'updatePartial'])->middleware(['auth:sanctum', 'abilities:server-admin']);


/* ------ DOCTORES ------ */
Route::get('/doctores', [doctorController::class, 'index']);
Route::get('/doctores/{id}', [doctorController::class, 'show']);
Route::delete('/doctores/{id}', [doctorController::class, 'destroy'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::post('/doctores', [doctorController::class, 'store'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::post('/doctores/update/{id}', [doctorController::class, 'update'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::post('/doctores/patch/{id}', [doctorController::class, 'updatePartial'])->middleware(['auth:sanctum', 'abilities:server-admin']);

/* ------ PACIENTES ------ */
Route::get('/paciente', [pacienteController::class, 'index']);
Route::post('/paciente', [pacienteController::class, 'store'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::get('/paciente/{id}', [pacienteController::class, 'show']);
Route::delete('/paciente/{id}', [pacienteController::class, 'destroy'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::put('/epaciente/{id}', [pacienteController::class, 'update'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::patch('/paciente/{id}', [pacienteController::class, 'updatePartial'])->middleware(['auth:sanctum', 'abilities:server-admin']);