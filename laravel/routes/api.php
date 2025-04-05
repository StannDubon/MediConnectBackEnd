<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\areaController;

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
Route::post('/areas', [areaController::class, 'store']);