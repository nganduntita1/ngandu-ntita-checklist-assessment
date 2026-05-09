<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('POST /api/login', function () {
    it('returns token and role for valid admin credentials', function () {
        $user = User::factory()->admin()->create([
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ])
            ->assertJson([
                'success' => true,
                'data'    => [
                    'user' => ['role' => 'admin'],
                ],
            ]);

        expect($response->json('data.token'))->not->toBeNull();
    });

    it('returns token and role for valid auditor credentials', function () {
        $user = User::factory()->auditor()->create([
            'email'    => 'auditor@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'auditor@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'user' => ['role' => 'auditor'],
                ],
            ]);

        expect($response->json('data.token'))->not->toBeNull();
    });

    it('returns 401 for invalid credentials', function () {
        User::factory()->create([
            'email'    => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'user@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    });

    it('returns 401 for non-existent user', function () {
        $response = $this->postJson('/api/login', [
            'email'    => 'nobody@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    });
});

describe('POST /api/logout', function () {
    it('revokes the token so subsequent requests return 401', function () {
        $user = User::factory()->create();

        // Create a real Sanctum token directly (bypasses the TransientToken issue in tests)
        $tokenResult = $user->createToken('api-token');
        $plainToken  = $tokenResult->plainTextToken;

        // Logout using the real bearer token
        $this->postJson('/api/logout', [], ['Authorization' => "Bearer {$plainToken}"])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // The token should now be deleted from the DB
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenResult->accessToken->id,
        ]);

        // Reset the auth guard so the next request re-authenticates from scratch
        $this->app['auth']->forgetGuards();

        // Subsequent request with the same (now revoked) token should be rejected
        $this->getJson('/api/templates', ['Authorization' => "Bearer {$plainToken}"])
            ->assertStatus(401);
    });
});

describe('Protected endpoints', function () {
    it('returns 401 for unauthenticated requests', function () {
        $response = $this->getJson('/api/templates');

        $response->assertStatus(401);
    });
});
