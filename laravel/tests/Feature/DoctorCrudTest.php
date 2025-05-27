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
                    'imagen',
                    'email',
                    'area_doctor'
                ]
            ]);

        $this->assertDatabaseHas('doctores', [
            'nombre' => 'Doc',
            'apellido' => 'Tor',
            'area_doctor' => 'Quiroparacticoooo' 
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
            'imagen' => UF::fake()->image('doctor.jpg'),
            'area_doctor' => 'Quiroparactico',
        ]);

        return $response;
    }
}
