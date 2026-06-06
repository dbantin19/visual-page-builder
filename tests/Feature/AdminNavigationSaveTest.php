<?php

namespace Tests\Feature;

use App\Models\NavMenuItem;
use App\Models\Page;
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
            'vertical_padding' => 'thick',
        ]);

        $response->assertRedirect(route('admin.navigation.index'));
        $response->assertSessionHas('success', 'Navigation saved.');
        $this->assertDatabaseHas('nav_settings', [
            'alignment' => 'center',
            'logo_position' => 'right',
            'vertical_padding' => 'thick',
        ]);
    }

    public function test_navigation_save_creates_new_page_items_from_payload(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $page = Page::create([
            'name' => 'About',
            'slug' => 'about',
            'is_published' => true,
        ]);

        $response = $this->actingAs($user)->post(route('admin.navigation.save-all'), [
            'alignment' => 'left',
            'logo_position' => 'left',
            'vertical_padding' => 'standard',
            'items' => [
                [
                    'id' => '',
                    'parent_id' => '',
                    'sort_order' => 0,
                    'label' => 'About',
                    'page_id' => $page->id,
                    'url' => '',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.navigation.index'));
        $response->assertSessionHas('success', 'Navigation saved.');

        $this->assertDatabaseHas('nav_menu_items', [
            'label' => 'About',
            'page_id' => $page->id,
            'url' => null,
            'parent_id' => null,
            'sort_order' => 0,
        ]);
    }

    public function test_navigation_save_keeps_existing_items_and_inserts_new_page_items_in_order(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $home = NavMenuItem::create([
            'label' => 'Home',
            'url' => '/home',
            'sort_order' => 0,
        ]);

        $about = Page::create([
            'name' => 'About',
            'slug' => 'about',
            'is_published' => true,
        ]);

        $response = $this->actingAs($user)->post(route('admin.navigation.save-all'), [
            'alignment' => 'center',
            'logo_position' => 'right',
            'vertical_padding' => 'standard',
            'items' => [
                [
                    'id' => '',
                    'parent_id' => '',
                    'sort_order' => 0,
                    'label' => 'About',
                    'page_id' => $about->id,
                ],
                [
                    'id' => $home->id,
                    'parent_id' => '',
                    'sort_order' => 1,
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.navigation.index'));

        $this->assertDatabaseHas('nav_menu_items', [
            'id' => $home->id,
            'sort_order' => 1,
        ]);
        $this->assertDatabaseHas('nav_menu_items', [
            'label' => 'About',
            'page_id' => $about->id,
            'sort_order' => 0,
        ]);
    }

    public function test_published_pages_are_shown_in_alphabetical_order(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        Page::create([
            'name' => 'Home',
            'slug' => 'home',
            'is_published' => true,
        ]);
        Page::create([
            'name' => 'Garage Door Repair',
            'slug' => 'garage-door-repair',
            'is_published' => true,
        ]);
        Page::create([
            'name' => 'Garage Doors',
            'slug' => 'garage-doors',
            'is_published' => true,
        ]);

        $response = $this->actingAs($user)->get(route('admin.navigation.index'));

        $response->assertOk();
        $response->assertSeeInOrder([
            'Published Pages',
            'Garage Doors',
            'Garage Door Repair',
            'Home',
        ]);
    }
}
