<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Doctor;
use App\Models\User;

class DoctorCrudTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Ejecutar migraciones para las tablas necesarias
        $this->artisan('migrate');
    }

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

        $this->assertDatabaseHas('doctors', [
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
            'imagen' => UploadedFile::fake()->image('new_doctor.jpg'),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Doctor actualizado exitosamente',
                'status' => 200,
            ]);

        $this->assertDatabaseHas('doctors', [
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

        $this->assertDatabaseHas('doctors', [
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
        $this->assertDatabaseHas('doctors', ['id' => $doctorId]);

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->delete("/api/doctores/{$doctorId}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Doctor eliminado correctamente',
                'status' => 200,
            ]);

        // Verificar que ya no existe
        $this->assertDatabaseMissing('doctors', ['id' => $doctorId]);
        $this->assertDatabaseMissing('users', ['email' => 'doctor@gmail.com']);
    }

    private function createAdmin(): string
    {
        $admin = [
            'email' => 'admin@gmail.com',
            'password' => '123456',
        ];

        $this->post('/api/signup/admin', [
            'name' => 'admin',
            'email' => $admin['email'],
            'password' => $admin['password'],
            'password_confirmation' => $admin['password'],
        ])->assertStatus(201);

        $loginResponse = $this->post('/api/login', $admin)
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => [
                    'type',
                    'nombre',
                    'apellido',
                ]
            ]);

        return "Bearer " . $loginResponse->json('token');
    }

    private function createDoctor(string $authToken)
    {
        Storage::fake('public');

        $response = $this->withHeaders([
            'Authorization' => $authToken
        ])->post('/api/doctores', [
            'nombre' => 'Doc',
            'apellido' => 'Tor',
            'email' => 'doctor@gmail.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'clinica_diaria' => 5,
            'imagen' => UploadedFile::fake()->image('doctor.jpg'),
        ]);

        return $response;
    }
}