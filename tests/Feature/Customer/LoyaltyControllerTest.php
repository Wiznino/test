<?php

namespace Tests\Feature\Customer;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_login_does_not_award_daily_points_until_they_claim_them(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->post(route('login'), [
            'identifier' => $customer->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('loyalty_transactions', [
            'user_id' => $customer->id,
            'type' => 'daily_login',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'loyalty_points' => 0,
        ]);

        $this->get(route('dashboard'))
            ->assertSee('Claim +0.1 point')
            ->assertSee(route('loyalty.daily-login.claim'), false);

        $this->get(route('loyalty.index'))
            ->assertSee('Claim +0.1 point')
            ->assertSee(route('loyalty.daily-login.claim'), false);
    }

    public function test_customer_can_claim_daily_points_only_once_per_day(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);
        $reference = 'daily-login-'.$customer->id.'-'.now()->toDateString();

        $this->actingAs($customer)
            ->post(route('loyalty.daily-login.claim'))
            ->assertRedirect()
            ->assertSessionHas('success', 'Daily sign-in reward claimed: +0.1 point.');

        $this->post(route('loyalty.daily-login.claim'))
            ->assertRedirect()
            ->assertSessionHas('success', 'You have already claimed today’s sign-in reward.');

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'loyalty_points' => 0.1,
        ]);
        $this->assertDatabaseHas('loyalty_transactions', [
            'user_id' => $customer->id,
            'type' => 'daily_login',
            'points' => 0.1,
            'reference' => $reference,
        ]);
        $this->assertDatabaseCount('loyalty_transactions', 1);

        $this->get(route('dashboard'))
            ->assertSee('Sign-in point claimed today')
            ->assertDontSee('Claim +0.1 point');
    }

    public function test_guest_cannot_claim_daily_points(): void
    {
        $this->post(route('loyalty.daily-login.claim'))
            ->assertRedirect(route('login'));
    }

    public function test_non_customer_cannot_claim_daily_points(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $this->actingAs($vendor)
            ->post(route('loyalty.daily-login.claim'))
            ->assertForbidden();

        $this->assertDatabaseMissing('loyalty_transactions', [
            'user_id' => $vendor->id,
            'type' => 'daily_login',
        ]);
    }
}
