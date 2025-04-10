<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use \Illuminate\Http\UploadedFile as UF;
use Tests\TestCase;

class DoctorCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_create(): void
    {
        $token = $this->createAdmin();
        $response = $this->createDoctor($token);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Doctor creado exitosamente',
                'status' => 201,
            ])
            ->assertJsonStructure([
                'doctor' => [
                    'id',
                    'nombre',
                    'apellido',
                    'clinica_diaria',
                    'imagen',
                    'email'
                ]
            ]);

        $this->assertDatabaseHas('doctores', [
            'nombre' => 'Doc',
            'apellido' => 'Tor',
            'clinica_diaria' => 5
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'doctor@gmail.com',
            'type' => 'doctor'
        ]);
    }

    public function test_doctor_index(): void
    {
        $token = $this->createAdmin();
        $this->createDoctor($token);

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->get('/api/doctores');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'doctores' => [
                    '*' => [
                        'id',
                        'nombre',
                        'apellido',
                        'clinica_diaria',
                        'imagen',
                        'email'
                    ]
                ],
                'status'
            ]);
    }

    public function test_doctor_show(): void
    {
        $token = $this->createAdmin();
        $doctor = $this->createDoctor($token);
        $doctorId = $doctor->json('doctor.id');

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->get("/api/doctores/{$doctorId}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'doctor' => [
                    'nombre',
                    'apellido',
                    'clinica_diaria',
                    'imagen',
                    'email'
                ],
                'status'
            ]);
    }

    public function test_doctor_update(): void
    {
        $token = $this->createAdmin();
        $doctor = $this->createDoctor($token);
        $doctorId = $doctor->json('doctor.id');

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->post("/api/doctores/update/{$doctorId}", [
            'nombre' => 'Doctor',
            'apellido' => 'Actualizado',
            'email' => 'doctor.actualizado@gmail.com',
            'password' => 'nueva123',
            'clinica_diaria' => 10,
            'imagen' => UF::fake()->image('new_doctor.jpg'),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Doctor actualizado exitosamente',
                'status' => 200,
            ]);

        $this->assertDatabaseHas('doctores', [
            'id' => $doctorId,
            'nombre' => 'Doctor',
            'apellido' => 'Actualizado',
            'clinica_diaria' => 10
        ]);
    }

    public function test_doctor_partial_update(): void
    {
        $token = $this->createAdmin();
        $doctor = $this->createDoctor($token);
        $doctorId = $doctor->json('doctor.id');

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->post("/api/doctores/patch/{$doctorId}", [
            'nombre' => 'SoloNombreCambiado',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Doctor actualizado exitosamente',
                'status' => 200,
            ]);

        $this->assertDatabaseHas('doctores', [
            'id' => $doctorId,
            'nombre' => 'SoloNombreCambiado',
            'apellido' => 'Tor' // Mantiene el valor original
        ]);
    }

    public function test_doctor_delete(): void
    {
        $token = $this->createAdmin();
        $doctor = $this->createDoctor($token);
        $doctorId = $doctor->json('doctor.id');

        // Verificar que existe antes de eliminar
        $this->assertDatabaseHas('doctores', ['id' => $doctorId]);

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->delete("/api/doctores/{$doctorId}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Doctor eliminado correctamente',
                'status' => 200,
            ]);

        // Verificar que ya no existe
        $this->assertDatabaseMissing('doctores', ['id' => $doctorId]);
        $this->assertDatabaseMissing('users', ['email' => 'doctor@gmail.com']);
    }

    function createAdmin() {
        $admin = [
            'email' => 'admin@gmail.com',
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

    function createDoctor($authToken){
        $response = $this->withHeaders([
            'Authorization' => $authToken
            ])->post('/api/doctores', [
            'nombre' => 'Doc',
            'apellido' => 'Tor',
            'email' => 'doctor@gmail.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'clinica_diaria' => 5,
            'imagen' => UF::fake()->image('doctor.jpg'),
        ]);

        return $response;
    }

    public function test_doctor_index_with_areas(): void
    {
        $token = $this->createAdmin();

        // 1. Crear un doctor
        $doctor = $this->createDoctor($token);
        $doctorId = $doctor->json('doctor.id');

        // 2. Crear áreas y asignarlas al doctor
        $area1 = $this->createArea($token, 'Cardiología');
        $area2 = $this->createArea($token, 'Pediatría');

        // Asignar áreas al doctor
        $this->assignAreasToDoctor($token, $doctorId, [$area1->json('area.id'), $area2->json('area.id')]);

        // 3. Hacer la petición
        $response = $this->withHeaders([
            'Authorization' => $token
        ])->get('/api/doctores/areas');

        // 4. Verificaciones
        $response->assertStatus(200)
            ->assertJsonStructure([
                'areas_doctores' => [
                    '*' => [
                        'doctor_id',
                        'nombre_doctor',
                        'areas_asignadas' => []
                    ]
                ],
                'status'
            ])
            ->assertJsonCount(1, 'areas_doctores') // Verifica que hay 1 doctor
            ->assertJsonFragment([
                'nombre_doctor' => 'Doc Tor',
                'areas_asignadas' => ['Cardiología', 'Pediatría']
            ]);

        // Verificar base de datos
        $this->assertDatabaseHas('areas_doctores', [
            'doctor_id' => $doctorId,
            'area_id' => $area1->json('area.id')
        ]);
        $this->assertDatabaseHas('areas_doctores', [
            'doctor_id' => $doctorId,
            'area_id' => $area2->json('area.id')
        ]);
    }

    // Métodos auxiliares que necesitarás añadir a tu clase de test
    function createArea($authToken, $nombreArea) {
        return $this->withHeaders([
            'Authorization' => $authToken,
            'Accept' => 'application/json'
        ])->post('/api/areas', [
            'nombre' => $nombreArea
        ]);
    }

    function assignAreasToDoctor($authToken, $doctorId, array $areaIds) {
        foreach ($areaIds as $areaId) {
            $this->withHeaders([
                'Authorization' => $authToken,
                'Accept' => 'application/json'
            ])->post('/api/areas_doctores', [
                'doctor_id' => $doctorId,
                'area_id' => $areaId
            ]);
        }
    }
}
