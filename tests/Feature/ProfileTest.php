<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_profile_page_is_displayed(): void
    {
        $user = User::factory()->anggota()->create();

        $response = $this->actingAs($user)->get(route('member.profile'));

        $response
            ->assertOk()
            ->assertSeeVolt('pages.member-profile');
    }

    public function test_member_profile_information_can_be_updated(): void
    {
        $user = User::factory()->anggota()->create();

        $this->actingAs($user);

        $component = Volt::test('pages.member-profile')
            ->set('full_name', 'Hafizh Baru')
            ->set('phone', '081233344455')
            ->set('address', 'Jl. Baru No. 10')
            ->call('updateProfile');

        $component
            ->assertHasNoErrors()
            ->assertNoRedirect();

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'full_name' => 'Hafizh Baru',
            'phone' => '081233344455',
            'address' => 'Jl. Baru No. 10',
        ]);
    }
}
