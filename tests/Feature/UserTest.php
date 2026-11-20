<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_profile(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Ahmad Dahlan',
            'phone' => '081234567890',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Profil pengguna berhasil diambil.',
                'data' => [
                    'name' => 'Ahmad Dahlan',
                    'phone' => '081234567890',
                    'formatted_phone' => '0812-3456-7890',
                    'role' => 'user',
                    'role_label' => 'Member',
                ],
            ]);
    }

    public function test_authenticated_user_can_update_profile_name_and_phone(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Nama Lama',
            'phone' => '081234567890',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/user', [
                'name' => 'Nama Baru',
                'phone' => '081299998888',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Profil pengguna berhasil diperbarui.',
                'data' => [
                    'name' => 'Nama Baru',
                    'phone' => '081299998888',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru',
            'phone' => '081299998888',
        ]);
    }

    public function test_user_can_update_pin_with_correct_current_pin(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'pin' => '111111',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/user', [
                'current_pin' => '111111',
                'new_pin' => '222222',
            ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue(Hash::check('222222', $user->pin));
    }

    public function test_user_cannot_update_pin_with_wrong_current_pin(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'pin' => '111111',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/user', [
                'current_pin' => '999999',
                'new_pin' => '222222',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_pin']);
    }
}
