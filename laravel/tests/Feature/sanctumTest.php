<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;



class sanctumTest extends TestCase
{

    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_user_can_login(): void
    {

        $user = User::factory()->create([
            'email' => 'prueba@gmail.com',
            'name' => 'shoshosho'
        ]);

        $response = $this->post('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'user' => ['email', 'name'],
            'token',
        ]);
    }

    public function test_user_can_see_auth_routes(): void
    {

        $user = User::factory()->create([
            'email' => 'prueba@gmail.com',
            'name' => 'shoshosho'
        ]);

        $response = $this->post('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Frontend
        $token = $response->json('token');

        $response = $this
                    ->withHeaders(['Authorization' => "Bearer {$token}"])
                    ->get('/api/user');

        $response->assertJson([
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name
        ]);
    }
}
