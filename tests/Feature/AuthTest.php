<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_successfully_with_phone_and_pin(): void
    {
        $payload = [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'pin' => '123456',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Registrasi akun Cinema XXI berhasil.',
                'data' => [
                    'user' => [
                        'name' => 'Budi Santoso',
                        'phone' => '081234567890',
                        'formatted_phone' => '0812-3456-7890',
                        'role' => 'user',
                        'role_label' => 'Member',
                    ],
                    'token_type' => 'Bearer',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));

        // Pastikan user tersimpan di database
        $this->assertDatabaseHas('users', [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'role' => 'user',
        ]);

        // Pastikan PIN di-hash (enkapsulasi & keamanan data)
        $user = User::where('phone', '081234567890')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('123456', $user->pin));
        $this->assertNotEquals('123456', $user->pin);
    }

    public function test_user_registration_normalizes_international_phone_format(): void
    {
        $payload = [
            'name' => 'Dewi Lestari',
            'phone' => '+6281987654321',
            'pin' => '654321',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'phone' => '081987654321',
        ]);
    }

    public function test_user_registration_validates_required_fields(): void
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validasi gagal, silakan periksa input data Anda.',
            ])
            ->assertJsonValidationErrors(['name', 'phone', 'pin']);
    }

    public function test_user_registration_validates_unique_phone(): void
    {
        User::factory()->create([
            'phone' => '081234567890',
        ]);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Duplikat User',
            'phone' => '081234567890',
            'pin' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
            ])
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_user_registration_validates_pin_must_be_six_digits(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Testing PIN',
            'phone' => '081234567899',
            'pin' => '1234', // Kurang dari 6 digit
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pin']);
    }

    public function test_user_can_login_with_phone_and_pin(): void
    {
        User::factory()->create([
            'name' => 'Login User',
            'phone' => '081234567890',
            'pin' => '123456',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'phone' => '081234567890',
            'pin' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Login berhasil.',
                'data' => [
                    'user' => [
                        'phone' => '081234567890',
                    ],
                    'token_type' => 'Bearer',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_user_cannot_login_with_incorrect_pin(): void
    {
        User::factory()->create([
            'phone' => '081234567890',
            'pin' => '123456',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'phone' => '081234567890',
            'pin' => '999999',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_authenticated_user_can_access_me_endpoint(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Akun Aktif',
            'phone' => '081234567890',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'name' => 'Akun Aktif',
                    'phone' => '081234567890',
                ],
            ]);
    }

    public function test_authenticated_user_can_logout_and_revoke_token(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Logout berhasil. Token telah dicabut.',
            ]);

        // Cek bahwa token sudah terhapus
        $this->assertCount(0, $user->tokens);
    }

    public function test_unauthenticated_user_cannot_access_protected_endpoint(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }
}
