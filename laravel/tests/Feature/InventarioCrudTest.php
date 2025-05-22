<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;
use App\Models\Inventario;

class InventarioCrudTest extends TestCase
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

    private function createInventarioRaw($nombre = 'Paracetamol', $cantidad = 10)
    {
        return Inventario::create([
            'nombre' => $nombre,
            'cantidad' => $cantidad
        ]);
    }

    public function test_index_returns_all_inventarios()
    {
        $token = $this->createAdminToken();
        $this->createInventarioRaw('Aspirina', 20);
        $this->createInventarioRaw('Ibuprofeno', 50);

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->get('/api/inventarios');

        $response->assertStatus(200)
                ->assertJsonFragment(['nombre' => 'Aspirina'])
                ->assertJsonFragment(['nombre' => 'Ibuprofeno']);
    }

    public function test_create_inventario()
    {
        $token = $this->createAdminToken();

        $data = [
            'nombre' => 'Amoxicilina',
            'cantidad' => 100
        ];

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->post('/api/inventarios', $data);

        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'Item creado exitosamente',
                    'status' => 201
                ]);
    }

    public function test_show_inventario()
    {
        $token = $this->createAdminToken();
        $inventario = $this->createInventarioRaw('Omeprazol', 30);

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->get("/api/inventarios/{$inventario->id}");

        $response->assertStatus(200)
                ->assertJsonFragment(['nombre' => 'Omeprazol']);
    }

    public function test_update_inventario()
    {
        $token = $this->createAdminToken();
        $inventario = $this->createInventarioRaw('Paracetamol', 15);

        $data = [
            'nombre' => 'Paracetamol Forte',
            'cantidad' => 20
        ];

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->put("/api/inventarios/{$inventario->id}", $data);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Item actualizado exitosamente',
                    'status' => 200
                ]);
    }

    public function test_update_partial_inventario()
    {
        $token = $this->createAdminToken();
        $inventario = $this->createInventarioRaw('Ciprofloxacino', 50);

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->patch("/api/inventarios/{$inventario->id}", [
            'cantidad' => 60
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Item parcialmente actualizado',
                    'status' => 200
                ]);
    }

    public function test_delete_inventario()
    {
        $token = $this->createAdminToken();
        $inventario = $this->createInventarioRaw('Metformina', 25);

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->delete("/api/inventarios/{$inventario->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Item eliminado exitosamente',
                    'status' => 200
                ]);

        $this->assertDatabaseMissing('inventario', [
            'id' => $inventario->id
        ]);
    }
}
