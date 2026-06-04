<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNavigationSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_navigation_save_accepts_an_empty_items_payload(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->post(route('admin.navigation.save-all'), [
            'alignment' => 'center',
            'logo_position' => 'right',
        ]);

        $response->assertRedirect(route('admin.navigation.index'));
        $response->assertSessionHas('success', 'Navigation saved.');
        $this->assertDatabaseHas('nav_settings', [
            'alignment' => 'center',
            'logo_position' => 'right',
        ]);
    }
}
