<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminUploadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploads_page_renders_for_admin_user(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->get(route('admin.uploads.index'))
            ->assertOk()
            ->assertSee('Uploads')
            ->assertSee('Content Media')
            ->assertSee('/uploads/content/')
            ->assertSee('Hide')
            ->assertSee('WebP')
            ->assertSee('MP4')
            ->assertSee('Delete selected');
    }

    public function test_admin_can_upload_multiple_content_media_files(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->postJson(route('admin.uploads.store'), [
            'media' => [
                UploadedFile::fake()->create('Hero One.jpg', 3072, 'image/jpeg'),
                UploadedFile::fake()->create('Walkthrough.mp4', 1024, 'video/mp4'),
            ],
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(2, 'uploads');

        $uploads = $response->json('uploads');
        $this->assertSame(['image', 'video'], array_column($uploads, 'type'));

        foreach ($uploads as $upload) {
            $this->assertStringStartsWith(asset('uploads/content/'), $upload['url']);
            $path = public_path('uploads/content/'.$upload['name']);
            $this->assertTrue(File::exists($path));
            File::delete($path);
        }
    }

    public function test_legacy_image_field_still_uploads_content_images(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->postJson(route('admin.uploads.store'), [
            'images' => [
                UploadedFile::fake()->create('Hero Two.png', 100, 'image/png'),
            ],
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('uploads.0.type', 'image');

        File::delete(public_path('uploads/content/'.$response->json('uploads.0.name')));
    }

    public function test_admin_can_delete_a_content_image(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);
        $filename = 'codex-single-delete-test.jpg';
        $path = public_path('uploads/content/'.$filename);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, 'test image');

        $this->actingAs($user)
            ->deleteJson(route('admin.uploads.destroy', $filename))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'deleted' => [$filename],
            ]);

        $this->assertFalse(File::exists($path));
    }

    public function test_admin_can_delete_selected_content_images(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);
        $filenames = [
            'codex-bulk-delete-one.jpg',
            'codex-bulk-delete-two.webp',
        ];

        File::ensureDirectoryExists(public_path('uploads/content'));
        foreach ($filenames as $filename) {
            File::put(public_path('uploads/content/'.$filename), 'test image');
        }

        $this->actingAs($user)
            ->deleteJson(route('admin.uploads.destroy-many'), [
                'filenames' => $filenames,
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'deleted' => $filenames,
            ]);

        foreach ($filenames as $filename) {
            $this->assertFalse(File::exists(public_path('uploads/content/'.$filename)));
        }
    }

    public function test_upload_delete_rejects_paths_outside_content_uploads(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->deleteJson(route('admin.uploads.destroy-many'), [
                'filenames' => ['../secret.jpg'],
            ])
            ->assertStatus(422);
    }
}
