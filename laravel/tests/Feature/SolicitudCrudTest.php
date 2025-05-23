<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Paciente;
use App\Models\Area;
use App\Models\Doctor;
use App\Models\AreaDoctor;
use Illuminate\Support\Facades\Hash;
use \Illuminate\Http\UploadedFile as UF;

class SolicitudCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(): string
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'type' => 'admin'
        ]);

        return 'Bearer ' . $admin->createToken('admin_token', ['*'])->plainTextToken;
    }

    protected function createPaciente(array $data = []): array
    {
        $defaults = [
            'nombre' => 'Juan',
            'apellido' => 'Pérez'
        ];
        $data = array_merge($defaults, $data);

        $paciente = Paciente::create($data);

        $user = User::create([
            'name' => "{$data['nombre']} {$data['apellido']}",
            'email' => strtolower($data['nombre']) . '.' . strtolower($data['apellido']) . '@example.com',
            'password' => Hash::make('secret'),
            'type' => 'patient',
            'paciente_id' => $paciente->id
        ]);

        return [
            'paciente' => $paciente,
            'user' => $user,
            'token' => 'Bearer ' . $user->createToken('patient_token')->plainTextToken
        ];
    }

    protected function createArea(string $token, array $data = []): array
    {
        $defaults = [
            'nombre' => 'Area ' . rand(1, 100)
        ];
        $data = array_merge($defaults, $data);

        $response = $this->withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json'
        ])->post('/api/areas', $data);

        return $response->json();
    }

    protected function createDoctor(string $token, array $data = []): array
    {
        $defaults = [
            'nombre' => 'Doctor',
            'apellido' => 'Example',
            'email' => 'doctor' . rand(1, 1000) . '@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'clinica_diaria' => 5,
            'imagen' => UF::fake()->image('new_doctor.jpg'),
        ];
        $data = array_merge($defaults, $data);

        $response = $this->withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json'
        ])->post('/api/doctores', $data);

        return $response->json();
    }

    protected function createAreaDoctor(string $token, int $areaId, int $doctorId): array
    {
        $response = $this->withHeaders([
            'Authorization' => $token
        ])->post('/api/areas_doctores', [
            'area_id' => $areaId,
            'doctor_id' => $doctorId
        ]);

        return $response->json();
    }

    public function test_solicitud_create()
    {
        $token = $this->createAdmin();
        $pac = $this->createPaciente();
        $doc = $this->createDoctor($token);
        $area = $this->createArea($token);
        $areaDoc = $this->createAreaDoctor($token, $doc['doctor']['id'], $area['area']['id']);

        $data = [
            'areas_doctores_id' => $areaDoc['area_doctor']['id'],
            'pacientes_id' => $pac['paciente']['id'],
            'motivo' => 'arroz con papas',
        ];

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->post('/api/solicitud', $data);

        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'Solicitud enviada exitosamente',
                    'status' => 200
                ]);
    }

    public function test_solicitud_index()
    {
        $token = $this->createAdmin();

        // 1. Primero crear una solicitud
        $pac = $this->createPaciente();
        $doc = $this->createDoctor($token);
        $area = $this->createArea($token);
        $areaDoc = $this->createAreaDoctor($token, $doc['doctor']['id'], $area['area']['id']);

        // Crear solicitud
        $this->withHeaders(['Authorization' => $token])
            ->post('/api/solicitud', [
                'areas_doctores_id' => $areaDoc['area_doctor']['id'],
                'pacientes_id' => $pac['paciente']['id'],
                'motivo' => 'Consulta de prueba'
            ]);

        // 2. Ahora hacer el GET
        $response = $this->withHeaders([
            'Authorization' => $token
        ])->get('/api/solicitud');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'areas_doctores_id',
                        'pacientes_id',
                        'motivo',
                        'notas',
                        'created_at',
                        'updated_at'
                    ]
                ]);
    }
}