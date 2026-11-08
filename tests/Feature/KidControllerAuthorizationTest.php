<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Kid;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KidControllerAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function makeFamilyWithKid(): array
    {
        $owner = User::factory()->create();
        $family = Family::create(['name' => 'Family', 'owner_user_id' => $owner->id]);
        $family->members()->attach($owner->id, ['role' => 'owner']);

        $kid = Kid::create([
            'family_id' => $family->id,
            'name' => 'Kid',
            'username' => 'kid_'.uniqid(),
            'birthday' => '2015-01-01',
            'password' => bcrypt('secret123'),
            'password_plaintext' => 'secret123',
            'color' => '#8b5cf6',
            'balance' => 10,
            'points' => 10,
            'max_points' => 10,
        ]);

        return [$owner, $family, $kid];
    }

    public function test_parent_cannot_deposit_to_another_familys_kid(): void
    {
        [, , $victimKid] = $this->makeFamilyWithKid();
        $attacker = User::factory()->create();
        $attackerFamily = Family::create(['name' => 'Attacker Family', 'owner_user_id' => $attacker->id]);
        $attackerFamily->members()->attach($attacker->id, ['role' => 'owner']);

        $response = $this->actingAs($attacker)->post(route('kids.deposit', $victimKid), [
            'amount' => 500,
        ]);

        $response->assertForbidden();
        $this->assertEquals(10, $victimKid->fresh()->balance);
    }

    public function test_parent_cannot_spend_another_familys_kid_balance(): void
    {
        [, , $victimKid] = $this->makeFamilyWithKid();
        $attacker = User::factory()->create();
        $attackerFamily = Family::create(['name' => 'Attacker Family', 'owner_user_id' => $attacker->id]);
        $attackerFamily->members()->attach($attacker->id, ['role' => 'owner']);

        $response = $this->actingAs($attacker)->post(route('kids.spend', $victimKid), [
            'amount' => 5,
        ]);

        $response->assertForbidden();
        $this->assertEquals(10, $victimKid->fresh()->balance);
    }

    public function test_parent_cannot_adjust_points_for_another_familys_kid(): void
    {
        [, , $victimKid] = $this->makeFamilyWithKid();
        $attacker = User::factory()->create();
        $attackerFamily = Family::create(['name' => 'Attacker Family', 'owner_user_id' => $attacker->id]);
        $attackerFamily->members()->attach($attacker->id, ['role' => 'owner']);

        $response = $this->actingAs($attacker)->post(route('kids.points', $victimKid), [
            'points' => -10,
        ]);

        $response->assertForbidden();
        $this->assertEquals(10, $victimKid->fresh()->points);
    }

    public function test_parent_cannot_read_another_familys_kid_transactions(): void
    {
        [, , $victimKid] = $this->makeFamilyWithKid();
        $attacker = User::factory()->create();
        $attackerFamily = Family::create(['name' => 'Attacker Family', 'owner_user_id' => $attacker->id]);
        $attackerFamily->members()->attach($attacker->id, ['role' => 'owner']);

        $response = $this->actingAs($attacker)->get(route('kids.transactions', $victimKid));

        $response->assertForbidden();
    }

    public function test_owning_parent_can_still_deposit_to_their_own_kid(): void
    {
        [$owner, , $kid] = $this->makeFamilyWithKid();

        $response = $this->actingAs($owner)->post(route('kids.deposit', $kid), [
            'amount' => 20,
        ]);

        $response->assertRedirect();
        $this->assertEquals(30, $kid->fresh()->balance);
    }

    public function test_update_balance_creates_a_transaction_record(): void
    {
        [$owner, , $kid] = $this->makeFamilyWithKid();

        $this->actingAs($owner)->patch(route('kids.updateBalance', $kid), [
            'amount' => 15,
        ]);

        $this->assertEquals(25, $kid->fresh()->balance);
        $this->assertDatabaseHas('transactions', [
            'kid_id' => $kid->id,
            'type' => 'adjustment',
            'amount' => 15.00,
        ]);
    }

    public function test_update_balance_records_positive_amount_for_negative_adjustment(): void
    {
        [$owner, , $kid] = $this->makeFamilyWithKid();

        $this->actingAs($owner)->patch(route('kids.updateBalance', $kid), [
            'amount' => -4,
        ]);

        $this->assertEquals(6, $kid->fresh()->balance);
        $this->assertDatabaseHas('transactions', [
            'kid_id' => $kid->id,
            'type' => 'adjustment',
            'amount' => 4.00,
        ]);
    }

    public function test_kid_login_is_rate_limited_after_repeated_failures(): void
    {
        [, , $kid] = $this->makeFamilyWithKid();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/kid/login', [
                'username' => $kid->username,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/kid/login', [
            'username' => $kid->username,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('username');
        $errors = session('errors')->get('username');
        $this->assertStringContainsString('Too many login attempts', $errors[0]);
    }
}
