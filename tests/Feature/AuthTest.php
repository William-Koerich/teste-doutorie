<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function testGetToken()
    {
        $user = User::factory()->create([
            'name'=> 'William',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin'),
        ]);

        $response = $this->postJson('api/v1/auth/token', [
            'name' =>  'William',
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $response->assertStatus(200);
    }
}
