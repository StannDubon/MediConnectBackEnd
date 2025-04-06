<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\areaController;
use App\Http\Controllers\doctorController;

/*
    NO INTENTEN HACER MAS ARCHIVOS ROUTE
    LA VALIDACION CSRF HACE CUCHE
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', function(Request $request){
    $user = User::where('email', $request->input('email'))->first();

    if(!$user || !Hash::check($request->password, $user->password)){
        return response()->json([
            'message' => 'Credenciales incorrectas',
        ], 401);
    }

    return response()->json([
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
        ],
        'token' => $user->createToken('api')->plainTextToken,
    ]);
});

/* ------ AREAS ------ */
Route::get('/areas', [areaController::class, 'index']);
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
Route::put('/doctores/{id}', [doctorController::class, 'update']);