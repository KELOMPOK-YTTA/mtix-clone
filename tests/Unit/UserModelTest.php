<?php

namespace Tests\Unit;

use App\Models\Abstract\BaseUser;
use App\Models\User;
use ReflectionClass;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    /**
     * Uji konsep PBO: ABSTRAKSI (BaseUser adalah abstract class dan tidak bisa di-instantiate langsung).
     */
    public function test_base_user_is_an_abstract_class(): void
    {
        $reflection = new ReflectionClass(BaseUser::class);
        $this->assertTrue($reflection->isAbstract(), 'BaseUser harus berupa class abstrak.');
        $this->assertTrue($reflection->hasMethod('getRoleLabel'), 'BaseUser harus memiliki method getRoleLabel.');
        $this->assertTrue($reflection->getMethod('getRoleLabel')->isAbstract(), 'Method getRoleLabel harus bersifat abstrak.');
    }

    /**
     * Uji konsep PBO: INHERITANCE (User mewarisi BaseUser).
     */
    public function test_user_inherits_from_base_user(): void
    {
        $user = new User;
        $this->assertInstanceOf(BaseUser::class, $user, 'User harus merupakan turunan dari BaseUser.');
    }

    /**
     * Uji konsep PBO: POLIMORFISME (getRoleLabel menghasilkan output sesuai dengan nilai role).
     */
    public function test_polymorphic_role_label(): void
    {
        $memberUser = new User(['role' => 'user']);
        $adminUser = new User(['role' => 'admin']);
        $customUser = new User(['role' => 'guest']);

        $this->assertEquals('Member', $memberUser->getRoleLabel());
        $this->assertEquals('Administrator', $adminUser->getRoleLabel());
        $this->assertEquals('Unknown', $customUser->getRoleLabel());
    }

    /**
     * Uji konsep PBO: ENKAPSULASI (Atribut sensitif disembunyikan dan fillable dilindungi).
     */
    public function test_encapsulation_hidden_and_fillable_attributes(): void
    {
        $user = new User([
            'name' => 'Test User',
            'phone' => '081234567890',
            'pin' => '123456',
            'role' => 'user',
        ]);

        $array = $user->toArray();

        // Enkapsulasi: PIN tidak boleh terlihat di toArray() / JSON
        $this->assertArrayNotHasKey('pin', $array, 'PIN harus dienkapsulasi dan disembunyikan.');
        $this->assertArrayNotHasKey('remember_token', $array);

        // Enkapsulasi: Fillable attributes
        $this->assertContains('name', $user->getFillable());
        $this->assertContains('phone', $user->getFillable());
        $this->assertContains('pin', $user->getFillable());
        $this->assertContains('role', $user->getFillable());
    }

    /**
     * Uji method konkret pada BaseUser (getFormattedPhone dan isAdmin).
     */
    public function test_base_user_helper_methods(): void
    {
        $user = new User([
            'phone' => '081234567890',
            'role' => 'admin',
        ]);

        $this->assertEquals('0812-3456-7890', $user->getFormattedPhone());
        $this->assertTrue($user->isAdmin());

        $member = new User(['role' => 'user']);
        $this->assertFalse($member->isAdmin());
    }
}
