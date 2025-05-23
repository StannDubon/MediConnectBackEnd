<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Paciente;
use App\Models\Examenes;

class ExamenControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_puede_crear_un_examen()
    {
        $paciente = Paciente::create([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            // agrega más campos si tu tabla lo necesita
        ]);

        $response = $this->postJson('/api/examenes', [
            'paciente_id' => $paciente->id,
            'titulo' => 'Examen de sangre',
            'descripcion' => 'Prueba general',
            'fecha' => '2025-05-23'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'examen']);
    }

    public function test_puede_listar_examenes()
    {
        $paciente = Paciente::create([
            'nombre' => 'Ana',
            'apellido' => 'García',
        ]);

        Examenes::create([
            'paciente_id' => $paciente->id,
            'titulo' => 'Eco',
            'descripcion' => 'Corazón',
            'fecha' => now()
        ]);

        $response = $this->getJson('/api/examenes');

        $response->assertStatus(200)
            ->assertJsonStructure([[
                'id',
                'titulo',
                'descripcion',
                'fecha',
                'paciente_id'
            ]]);
    }

    public function test_puede_mostrar_un_examen()
    {
        $paciente = Paciente::create([
            'nombre' => 'Lucía',
            'apellido' => 'Martínez',
        ]);

        $examen = Examenes::create([
            'paciente_id' => $paciente->id,
            'titulo' => 'Rayos X',
            'descripcion' => 'Tórax',
            'fecha' => '2025-05-23',
        ]);

        $response = $this->getJson("/api/examenes/{$examen->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $examen->id]);
    }

    public function test_puede_actualizar_un_examen()
    {
        $paciente = Paciente::create([
            'nombre' => 'Carlos',
            'apellido' => 'Sánchez',
        ]);

        $examen = Examenes::create([
            'paciente_id' => $paciente->id,
            'titulo' => 'Original',
            'descripcion' => 'Inicial',
            'fecha' => '2025-05-01',
        ]);

        $response = $this->putJson("/api/examenes/{$examen->id}", [
            'titulo' => 'Modificado',
            'descripcion' => 'Actualizado',
            'fecha' => '2025-06-01'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['titulo' => 'Modificado']);
    }

    public function test_puede_eliminar_un_examen()
    {
        $paciente = Paciente::create([
            'nombre' => 'María',
            'apellido' => 'Rodríguez',
        ]);

        $examen = Examenes::create([
            'paciente_id' => $paciente->id,
            'titulo' => 'Eliminar',
            'descripcion' => 'Temporal',
            'fecha' => '2025-05-23',
        ]);

        $response = $this->deleteJson("/api/examenes/{$examen->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Examen eliminado']);
    }
}
