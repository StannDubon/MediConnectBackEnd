<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Area;
use Tests\TestCase;

class AreaCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_area_create(): void
    {
        $token = $this->createAdmin();
        $response = $this->createArea($token);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Área creada exitosamente',
                'status' => 200,
            ])
            ->assertJsonStructure([
                'area' => [
                    'id',
                    'nombre'
                ]
            ]);

        $this->assertDatabaseHas('areas', [
            'nombre' => 'Nueva Área'
        ]);
    }

    public function test_area_index(): void
    {
        $token = $this->createAdmin();
        $this->createArea($token);

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->get('/api/areas');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'areas' => [
                    '*' => [
                        'id',
                        'nombre'
                    ]
                ],
                'status'
            ]);
    }

    public function test_area_show(): void
    {
        $token = $this->createAdmin();
        $area = $this->createArea($token);
        $areaId = $area->json('area.id');

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->get("/api/areas/{$areaId}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'area' => [
                    'id',
                    'nombre'
                ],
                'status'
            ]);
    }

    public function test_area_update(): void
    {
        $token = $this->createAdmin();
        $area = $this->createArea($token);
        $areaId = $area->json('area.id');

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->put("/api/areas/{$areaId}", [
            'nombre' => 'Área Actualizada'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Área actualizada exitosamente',
                'status' => 200,
            ]);

        $this->assertDatabaseHas('areas', [
            'id' => $areaId,
            'nombre' => 'Área Actualizada'
        ]);
    }

    public function test_area_partial_update(): void
    {
        $token = $this->createAdmin();
        $area = $this->createArea($token);
        $areaId = $area->json('area.id');

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->patch("/api/areas/{$areaId}", [
            'nombre' => 'Área Parcialmente Actualizada'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Área actualizada exitosamente',
                'status' => 200,
            ]);

        $this->assertDatabaseHas('areas', [
            'id' => $areaId,
            'nombre' => 'Área Parcialmente Actualizada'
        ]);
    }

    public function test_area_delete(): void
    {
        $token = $this->createAdmin();
        $area = $this->createArea($token);
        $areaId = $area->json('area.id');

        // Verificar que existe antes de eliminar
        $this->assertDatabaseHas('areas', ['id' => $areaId]);

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->delete("/api/areas/{$areaId}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Área eliminada',
                'status' => 200,
            ]);

        // Verificar que ya no existe
        $this->assertDatabaseMissing('areas', ['id' => $areaId]);
    }

    public function test_empty_partial_update_returns_same_area(): void
    {
        $token = $this->createAdmin();
        $area = $this->createArea($token);
        $areaId = $area->json('area.id');
        $originalName = $area->json('area.nombre');

        $response = $this->withHeaders([
            'Authorization' => $token
        ])->patch("/api/areas/{$areaId}", []);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Por favor digite el campo que desea actualizar',
                'status' => 200,
            ])
            ->assertJsonPath('area.nombre', $originalName);
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

    function createArea($authToken) {
        $response = $this->withHeaders([
            'Authorization' => $authToken
        ])->post('/api/areas', [
            'nombre' => 'Nueva Área'
        ]);

        return $response;
    }
}