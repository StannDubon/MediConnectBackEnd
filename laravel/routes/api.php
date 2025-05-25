<?php

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Controllers\ExamenesController;
use App\Http\Controllers\doctorController;
use App\Http\Controllers\authController;
use App\Http\Controllers\pacienteController;

Route::post('/signup/admin', [authController::class, 'signupAdmin']);
Route::post('/signup/doctor', [authController::class, 'signupDoctor']);
Route::post('/signup/patient', [authController::class, 'signupPatient']);

Route::post('/login', [authController::class, 'login']);
Route::post('/logout', [authController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [authController::class, 'user'])->middleware('auth:sanctum');

/* ------ DOCTORES ------ */
Route::get('/doctores', [doctorController::class, 'index']);
Route::get('/doctores/{id}', [doctorController::class, 'show']);
Route::delete('/doctores/{id}', [doctorController::class, 'destroy'])->middleware(['auth:sanctum', 'abilities:server-admin,server-doctor']);
Route::post('/doctores', [doctorController::class, 'store'])->middleware(['auth:sanctum', 'abilities:server-admin']);
Route::post('/doctores/update/{id}', [doctorController::class, 'update'])->middleware(['auth:sanctum', 'abilities:server-admin,server-doctor']);
Route::post('/doctores/patch/{id}', [doctorController::class, 'updatePartial'])->middleware(['auth:sanctum', 'abilities:server-admin,server-doctor']);

/* ------ EXAMENES  ------ */
Route::get('/examenes', [ExamenesController::class, 'index']);
Route::get('/examenes/{id}', [ExamenesController::class, 'show']);
Route::post('/examenes', [ExamenesController::class, 'store']);
Route::put('/examenes/{id}', [ExamenesController::class, 'update']);
Route::delete('/examenes/{id}', [ExamenesController::class, 'destroy']);
Route::get('/pacientes/{id}/examenes', [examenesController::class, 'porPaciente']);



/* ------ PACIENTES ------ */
Route::get('/paciente', [pacienteController::class, 'index']);
Route::post('/paciente', [pacienteController::class, 'store'])->middleware(['auth:sanctum', 'abilities:server-admin,server-doctor']);
Route::get('/paciente/{id}', [pacienteController::class, 'show']);
Route::delete('/paciente/{id}', [pacienteController::class, 'destroy'])->middleware(['auth  :sanctum', 'abilities:server-admin,server-doctor']);
Route::post('/paciente/update/{id}', [pacienteController::class, 'update']);
Route::patch('/paciente/{id}', [pacienteController::class, 'updatePartial']);


