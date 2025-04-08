<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use \Illuminate\Http\UploadedFile as UF;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_signup(): void
    {
        $admin = [
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => '123456',
            'password_confirmation' => '123456',
        ];

        $response = $this->post('/api/signup/admin', $admin);

        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'Admin registrado',
                ]);
    }

    public function test_doctor_signup(): void
    {
        $doctor = [
            'nombre' => 'John',
            'apellido' => 'Doe',
            'email' => 'doctor@gmail.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'clinica_diaria' => 5,
            'imagen' => UF::fake()->image('doctor.jpg'),
        ];

        $response = $this->post('/api/signup/doctor', $doctor);

        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'Doctor registrado',
                ]);
    }

    public function test_patient_signup(): void
    {
        $patient = [
            'nombre' => 'Jane',
            'apellido' => 'Doe',
            'email' => 'patient@gmail.com',
            'password' => '123456',
            'password_confirmation' => '123456',
        ];

        $response = $this->post('/api/signup/patient', $patient);

        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'Paciente registrado',
                ]);
    }

    public function test_admin_login(): void
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
        ]);

        $response = $this->post('/api/login', $admin);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'token',
                    'user' => [
                        'type',
                        'nombre',
                        'apellido',
                    ]
                ]);
    }

    public function test_doctor_login(): void
    {
        $doctor = [
            'email' => 'doctor@gmail.com',
            'password' => '123456',
        ];

        $this->post('/api/signup/doctor', [
            'nombre' => 'John',
            'apellido' => 'Doe',
            'email' => $doctor['email'],
            'password' => $doctor['password'],
            'password_confirmation' => $doctor['password'],
            'clinica_diaria' => 5,
            'imagen' => UF::fake()->image('doctor.jpg'),
        ]);

        $response = $this->post('/api/login', $doctor);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'token',
                    'user' => [
                        'type',
                        'nombre',
                        'apellido',
                        'clinica_diaria',
                        'imagen',
                    ]
                ]);
    }

    public function test_patient_login(): void
    {
        $patient = [
            'email' => 'patient@gmail.com',
            'password' => '123456',
        ];

        $this->post('/api/signup/patient', [
            'nombre' => 'Jane',
            'apellido' => 'Doe',
            'email' => $patient['email'],
            'password' => $patient['password'],
            'password_confirmation' => $patient['password'],
        ]);

        $response = $this->post('/api/login', $patient);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'token',
                    'user' => [
                        'type',
                        'nombre',
                        'apellido',
                    ]
                ]);
    }
}
