<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Models\User;
use App\Models\Paciente;

class PacienteCrudTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminToken()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'type' => 'admin'
        ]);

        return 'Bearer ' . $admin->createToken('admin_token', ['*'])->plainTextToken;
    }

    private function createPacienteRaw($nombre = 'Juan', $apellido = 'Pérez')
    {
        $paciente = Paciente::create([
            'nombre' => $nombre,
            'apellido' => $apellido
        ]);

        $user = User::create([
            'name' => "$nombre $apellido",
            'email' => strtolower($nombre) . '.' . strtolower($apellido) . '@example.com',
            'password' => Hash::make('secret'),
            'type' => 'patient',
            'paciente_id' => $paciente->id
        ]);

        return $paciente;
    }

    public function test_index_returns_all_pacientes()
    {
        $token = $this->createAdminToken();
        $this->createPacienteRaw('Ana', 'López');
        $this->createPacienteRaw('Carlos', 'Gómez');

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->get('/api/paciente');

        $response->assertStatus(200)
                ->assertJsonFragment(['nombre' => 'Ana'])
                ->assertJsonFragment(['nombre' => 'Carlos']);
    }

    public function test_create_paciente()
    {
        $token = $this->createAdminToken();

        $data = [
            'nombre' => 'Mario',
            'apellido' => 'Bros',
            'email' => 'mario.bros@example.com',
            'password' => '12345678'
        ];

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->post('/api/paciente', $data);

        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'Paciente creado exitosamente',
                    'status' => 201
                ]);
    }

    public function test_show_paciente()
    {
        $token = $this->createAdminToken();
        $paciente = $this->createPacienteRaw('Luigi', 'Verde');

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->get("/api/paciente/{$paciente->id}");

        $response->assertStatus(200)
                ->assertJsonFragment(['nombre' => 'Luigi']);
    }

    // public function test_update_paciente()
    // {
    //     $token = $this->createAdminToken();
    //     $paciente = $this->createPacienteRaw('Peach', 'Rosada');

    //     $user = $paciente->user;

    //     $data = [
    //         'nombre' => 'Peach',
    //         'apellido' => 'Actualizada',
    //         'email' => 'peach.new@example.com',
    //         'password' => 'newpass123'
    //     ];

    //     $response = $this->withHeaders([
    //         'Authorization' => $token
    //     ])->put("/api/paciente/{$paciente->id}", $data);

    //     $response->assertStatus(200)
    //             ->assertJson([
    //                 'message' => 'Paciente actualizado',
    //                 'status' => 200
    //             ]);
    // }

    public function test_update_partial_paciente()
    {
        $token = $this->createAdminToken();
        $paciente = $this->createPacienteRaw('Toad', 'Rápido');

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->patch("/api/paciente/{$paciente->id}", [
            'nombre' => 'ToadNuevo'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Paciente actualizado',
                    'status' => 200
                ]);
    }

    // public function test_delete_paciente()
    // {
    //     $token = $this->createAdminToken();
    //     $paciente = $this->createPacienteRaw('Bowser', 'Koopa');

    //     $response = $this->withHeaders([
    //         'Authorization' => $token
    //     ])->delete("/api/paciente/{$paciente->id}");

    //     $response->assertStatus(200)
    //             ->assertJson([
    //                 'message' => 'Paciente eliminado',
    //                 'status' => 200
    //             ]);

    //     $this->assertDatabaseMissing('pacientes', [
    //         'id' => $paciente->id
    //     ]);
    // }
}
