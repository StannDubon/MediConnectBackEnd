<?php

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use App\Http\Controllers\areaController;
use App\Http\Controllers\doctorController;
use App\Http\Controllers\areaDoctorController;
use App\Http\Controllers\authController;
use App\Http\Controllers\pacienteController;
use App\Http\Controllers\solicitudController;
use App\Http\Controllers\InventarioController;

Route::post('/signup/admin', [authController::class, 'signupAdmin']);
Route::post('/signup/doctor', [authController::class, 'signupDoctor']);
Route::post('/signup/patient', [authController::class, 'signupPatient']);

Route::post('/login', [authController::class, 'login']);
Route::post('/logout', [authController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [authController::class, 'user'])->middleware('auth:sanctum');

/* ------ AREAS ------ */
Route::get('/areas', [areaController::class, 'index']);
Route::get('/areas/{id}', [areaController::class, 'show']);
Route::delete('/areas/{id}', [areaController::class, 'destroy'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::post('/areas', [areaController::class, 'store'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::put('/areas/{id}', [areaController::class, 'update'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::patch('/areas/{id}', [areaController::class, 'updatePartial'])->middleware(['auth:sanctum', 'abilities:server-admin']);

/* ------ DOCTORES ------ */
Route::get('/doctores', [doctorController::class, 'index']);
Route::get('/doctores/areas', [doctorController::class, 'indexWithAreas']);
Route::get('/doctores/{id}', [doctorController::class, 'show']);
Route::delete('/doctores/{id}', [doctorController::class, 'destroy'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::post('/doctores', [doctorController::class, 'store'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::post('/doctores/update/{id}', [doctorController::class, 'update'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::post('/doctores/patch/{id}', [doctorController::class, 'updatePartial'])->middleware(['auth:sanctum', 'abilities:server-admin']);

/* ------ DOCTORES AREAS ------ */
Route::get('/areas_doctores', [areaDoctorController::class, 'index']);
Route::get('/areas_doctores/{id}', [areaDoctorController::class, 'show']);
Route::delete('/areas_doctores/{id}', [areaDoctorController::class, 'destroy'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::post('/areas_doctores', [areaDoctorController::class, 'store'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::put('/areas_doctores/{id}', [areaDoctorController::class, 'update'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::patch('/areas_doctores/{id}', [areaDoctorController::class, 'updatePartial'])->middleware(['auth:sanctum', 'abilities:server-admin']);

/* ------ SOLICITUDES ------ */
Route::get('/solicitud', [solicitudController::class, 'index']);
Route::get('/solicitud/{id}', [solicitudController::class, 'show']);
Route::delete('/solicitud/{id}', [solicitudController::class, 'destroy'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::post('/solicitud', [solicitudController::class, 'store'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::put('/solicitud/{id}', [solicitudController::class, 'update'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::patch('/solicitud/{id}', [solicitudController::class, 'updatePartial'])->middleware(['auth:sanctum', 'abilities:server-admin']);

/* ------ PACIENTES ------ */
Route::get('/paciente', [pacienteController::class, 'index']);
Route::post('/paciente', [pacienteController::class, 'store'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::get('/paciente/{id}', [pacienteController::class, 'show']);
Route::delete('/paciente/{id}', [pacienteController::class, 'destroy'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::put('/paciente/{id}', [pacienteController::class, 'update'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::patch('/paciente/{id}', [pacienteController::class, 'updatePartial'])->middleware(['auth:sanctum', 'abilities:server-admin']);

/* ------ INVENTARIO ------ */
Route::get('inventarios', [InventarioController::class, 'index']);
Route::get('inventarios/{id}', [InventarioController::class, 'show']);
Route::post('inventarios', [InventarioController::class, 'store'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::put('inventarios/{id}', [InventarioController::class, 'update'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::patch('inventarios/{id}', [InventarioController::class, 'updatePartial'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::delete('inventarios/{id}', [InventarioController::class, 'destroy'])->middleware(['auth:sanctum', 'abilities:server-admin']);
