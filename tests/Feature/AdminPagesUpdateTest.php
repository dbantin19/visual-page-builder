<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_a_page_redirects_back_to_the_edit_page(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $page = Page::create([
            'name' => 'Garage Door Repair',
            'slug' => 'garage-door-repair',
            'is_published' => true,
            'is_indexed' => true,
        ]);

        $response = $this->actingAs($user)->put(route('admin.pages.update', $page), [
            'name' => 'Garage Door Repair',
            'slug' => 'garage-door-repair',
            'meta_title' => 'Garage Door Repair',
            'meta_description' => 'Garage Door Repair',
            'is_published' => '1',
            'is_indexed' => '1',
            'head_section' => '',
            'body_section' => '',
        ]);

        $response->assertRedirect(route('admin.pages.edit', $page));
        $response->assertSessionHas('success', 'Page updated.');

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'meta_title' => 'Garage Door Repair',
            'meta_description' => 'Garage Door Repair',
        ]);
    }

    public function test_builder_draft_save_does_not_change_live_page_content(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $page = Page::create([
            'name' => 'Home',
            'slug' => 'home',
            'is_published' => true,
            'is_indexed' => true,
            'content' => '<section>Live content</section>',
            'builder_data' => json_encode(['components' => [['content' => 'Live content']], 'styles' => []]),
        ]);

        $response = $this->actingAs($user)->postJson(route('admin.pages.builder.save', $page), [
            'html' => '<section>Draft content</section>',
            'css' => '',
            'components' => json_encode([['content' => 'Draft content']]),
            'styles' => json_encode([]),
            'publish' => false,
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $page->refresh();

        $this->assertSame('<section>Live content</section>', $page->content);
        $this->assertStringContainsString('Live content', $page->builder_data);
        $this->assertSame('<section>Draft content</section>', $page->draft_content);
        $this->assertStringContainsString('Draft content', $page->draft_builder_data);
        $this->assertTrue($page->is_published);

        $this->get(route('pages.show', $page->slug))
            ->assertOk()
            ->assertSee('Live content', false)
            ->assertDontSee('Draft content', false);
    }

    public function test_builder_publish_updates_live_content_and_clears_draft(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $page = Page::create([
            'name' => 'Home',
            'slug' => 'home',
            'is_published' => false,
            'is_indexed' => true,
            'content' => '<section>Old live content</section>',
            'builder_data' => json_encode(['components' => [['content' => 'Old live content']], 'styles' => []]),
            'draft_content' => '<section>Old draft content</section>',
            'draft_builder_data' => json_encode(['components' => [['content' => 'Old draft content']], 'styles' => []]),
        ]);

        $response = $this->actingAs($user)->postJson(route('admin.pages.builder.save', $page), [
            'html' => '<section>Published content</section>',
            'css' => '.hero{color:red;}',
            'components' => json_encode([['content' => 'Published content']]),
            'styles' => json_encode([]),
            'publish' => true,
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $page->refresh();

        $this->assertStringContainsString('.hero{color:red;}', $page->content);
        $this->assertStringContainsString('Published content', $page->content);
        $this->assertStringContainsString('Published content', $page->builder_data);
        $this->assertNull($page->draft_content);
        $this->assertNull($page->draft_builder_data);
        $this->assertTrue($page->is_published);
    }
}
