<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile as UF;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Tests\TestCase;

class AreaDoctorCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_area_doctor_create(): void
    {
        $token = $this->createAdmin();
        $area = $this->createArea($token)->json('area.id');
        $doctor = $this->createDoctor($token)->json('doctor.id');

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->post('/api/areas_doctores', [
            'area_id' => $area,
            'doctor_id' => $doctor
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Referencia creada exitosamente',
                'status' => 200,
            ])
            ->assertJsonStructure([
                'area_doctor' => [
                    'id',
                    'area_id',
                    'doctor_id'
                ]
            ]);

        $this->assertDatabaseHas('areas_doctores', [
            'area_id' => $area,
            'doctor_id' => $doctor
        ]);
    }

    public function test_area_doctor_index(): void
    {
        $token = $this->createAdmin();
        $reference = $this->createAreaDoctor($token);
        $reference->assertStatus(201);

        $response = $this->withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json'
        ])->get('/api/areas_doctores');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'areas_doctores' => [
                    '*' => [
                        'id',
                        'doctor_id',
                        'area_id'
                    ]
                ],
                'status'
            ]);
    }

    public function test_area_doctor_show(): void
    {
        $token = $this->createAdmin();
        $reference = $this->createAreaDoctor($token);
        $reference->assertStatus(201);
        $referenceId = $reference->json('area_doctor.id');

        $response = $this->withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json'
        ])->get("/api/areas_doctores/{$referenceId}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'area_doctor' => [
                    'id',
                    'doctor_id',
                    'area_id'
                ],
                'status'
            ]);
    }

    public function test_area_doctor_delete(): void
    {
        $token = $this->createAdmin();
        $response = $this->createAreaDoctor($token);
        $response->assertStatus(201);
        $referenceId = $response->json('area_doctor.id');

        // Verificar que existe antes de eliminar
        $this->assertDatabaseHas('areas_doctores', ['id' => $referenceId]);

        $response = $this->withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json'
        ])->delete("/api/areas_doctores/{$referenceId}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Referencia eliminada',
                'status' => 200,
            ]);

        // Verificar que ya no existe
        $this->assertDatabaseMissing('areas_doctores', ['id' => $referenceId]);
    }

    public function test_area_doctor_update(): void
    {
        $token = $this->createAdmin();
        $reference = $this->createAreaDoctor($token);
        $reference->assertStatus(201);
        $referenceId = $reference->json('area_doctor.id');

        $area = $this->createArea($token)->json('area.id');
        $doctor = $this->createDoctor($token)->json('doctor.id');

        $response = $this->withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json'
        ])->put("/api/areas_doctores/{$referenceId}", [
            'area_id' => $area,
            'doctor_id' => $doctor
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Referencia actualizada exitosamente',
                'status' => 200,
            ]);

        $this->assertDatabaseHas('areas_doctores', [
            'id' => $referenceId,
            'area_id' => $area,
            'doctor_id' => $doctor
        ]);
    }

    public function test_area_doctor_partial_update(): void
    {
        $token = $this->createAdmin();
        $reference = $this->createAreaDoctor($token);
        $reference->assertStatus(201);
        $referenceId = $reference->json('area_doctor.id');

        $area = $this->createArea($token)->json('area.id');
        $doctor = $this->createDoctor($token)->json('doctor.id');

        $responseA = $this->withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json'
        ])->patch("/api/areas_doctores/{$referenceId}", [
            'area_id' => $area
        ]);

        $responseA->assertStatus(200)
            ->assertJson([
                'message' => 'Referencia actualizada exitosamente',
                'status' => 200,
            ]);

        $this->assertDatabaseHas('areas_doctores', [
            'id' => $referenceId,
            'area_id' => $area
        ]);

        $responseB = $this->withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json'
        ])->patch("/api/areas_doctores/{$referenceId}", [
            'doctor_id' => $doctor
        ]);

        $responseB->assertStatus(200)
            ->assertJson([
                'message' => 'Referencia actualizada exitosamente',
                'status' => 200,
            ]);

        $this->assertDatabaseHas('areas_doctores', [
            'id' => $referenceId,
            'doctor_id' => $doctor
        ]);
    }



    function createAreaDoctor($authToken) {
        $area = $this->createArea($authToken)->json('area.id');
        $doctor = $this->createDoctor($authToken)->json('doctor.id');

        $response = $this->withHeaders([
            'Authorization' => $authToken
        ])->post('/api/areas_doctores', [
            'area_id' => $area,
            'doctor_id' => $doctor
        ]);

        return $response;
    }

    function createArea($authToken){
        $response = $this->withHeaders([
            'Authorization' => $authToken,
            'Accept' => 'application/json'
        ])->post('/api/areas', [
            'nombre' => 'Area' . rand(1, 100)
        ]);

        return $response;
    }

    function createDoctor($authToken){
        $response = $this->withHeaders([
            'Authorization' => $authToken,
            'Accept' => 'application/json'
            ])->post('/api/doctores', [
            'nombre' => 'Doc',
            'apellido' => 'Tor',
            'email' => 'doctor' . rand(1, 100) . '@gmail.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'clinica_diaria' => 5,
            'imagen' => UF::fake()->image('doctor.jpg'),
        ]);

        return $response;
    }

    function createAdmin() {
        $admin = [
            'email' => 'admin' . rand(1, 100) . '@gmail.com',
            'password' => '123456',
        ];
        $signupResponse = $this->post('/api/signup/admin', [
            'name' => 'admin',
            'email' => $admin['email'],
            'password' => $admin['password'],
            'password_confirmation' => $admin['password'],
        ]);

        $signupResponse->assertStatus(201);
        $loginResponse = $this->post('/api/login', $admin);

        $loginResponse->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'token',
                    'user' => [
                        'type',
                        'nombre',
                        'apellido',
                    ]
                ]);

        $token = $loginResponse->json('token');
        return "Bearer " . $token;
    }
}
