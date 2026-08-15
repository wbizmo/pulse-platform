<?php

namespace Tests\Feature;

use App\Actions\Media\DeleteMedia;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MediaSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function super(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->attach(Role::where('name', 'super_admin')->firstOrFail());

        return $user;
    }

    private function media(User $user): Media
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/originals/example.png', 'image');

        return Media::create(['user_id' => $user->id, 'name' => 'Example', 'original_name' => 'example.png', 'file_name' => 'example.png', 'mime_type' => 'image/png', 'extension' => 'png', 'disk' => 'public', 'path' => 'media/originals/example.png', 'url' => '/storage/media/originals/example.png', 'size' => 5, 'width' => 1, 'height' => 1, 'type' => 'image']);
    }

    public function test_media_routes_deny_anonymous_direct_access(): void
    {
        $this->get(route('admin.media'))->assertRedirect(route('login'));
        $this->post(route('admin.media.store'))->assertRedirect(route('login'));
    }

    public function test_authorized_upload_derives_metadata_and_uses_opaque_path(): void
    {
        Storage::fake('public');
        $actor = $this->super();
        $file = UploadedFile::fake()->image('../résumé.png', 40, 30);
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post(route('admin.media.store'), ['files' => [$file]])->assertRedirect(route('admin.media'));
        $media = Media::firstOrFail();
        $this->assertSame('image/png', $media->mime_type);
        $this->assertSame(40, $media->width);
        $this->assertSame(30, $media->height);
        $this->assertDoesNotMatchRegularExpression('/résumé|\.\.|\\\\/', $media->path);
        Storage::disk('public')->assertExists($media->path);
        $this->assertDatabaseHas('audit_logs', ['action' => 'media.created', 'target_id' => $media->id]);
    }

    public function test_svg_executable_and_malformed_images_are_rejected_without_storage(): void
    {
        Storage::fake('public');
        $actor = $this->super();
        foreach ([
            UploadedFile::fake()->createWithContent('attack.svg', '<svg onload="alert(1)"><script>alert(1)</script></svg>'),
            UploadedFile::fake()->createWithContent('shell.jpg', '<?php echo 1;'),
        ] as $file) {
            $this->actingAs($actor)->withSession(['mfa_passed' => true])->post(route('admin.media.store'), ['files' => [$file]])->assertSessionHasErrors('files.0');
        }
        $this->assertDatabaseCount('media', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_referenced_media_is_protected_by_action_and_foreign_keys(): void
    {
        $actor = $this->super();
        $media = $this->media($actor);
        Page::create(['title' => 'Page', 'slug' => 'page', 'featured_media_id' => $media->id]);
        Post::create(['title' => 'Post', 'slug' => 'post', 'featured_media_id' => $media->id]);
        try {
            app(DeleteMedia::class)->execute($media, $actor);
            $this->fail('Deletion should fail.');
        } catch (ValidationException) {
        }
        $this->assertDatabaseHas('media', ['id' => $media->id]);
        $this->expectException(QueryException::class);
        DB::table('media')->where('id', $media->id)->delete();
    }

    public function test_content_requests_reject_forged_featured_media_ids(): void
    {
        $actor = $this->super();
        $payload = ['title' => 'Forged', 'slug' => 'forged', 'status' => 'draft', 'template' => 'default', 'featured_media_id' => 999999];
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post(route('admin.pages.store'), $payload)->assertSessionHasErrors('featured_media_id');
        unset($payload['template']);
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post(route('admin.posts.store'), $payload)->assertSessionHasErrors('featured_media_id');
    }

    public function test_library_is_paginated_and_media_screen_uses_custom_dialog(): void
    {
        $actor = $this->super();
        $this->media($actor);
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->get(route('admin.media'))->assertOk()->assertSee('data-confirm-title="Delete media file?"', false)->assertDontSee('confirm(', false);
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->get(route('admin.media.library'))->assertOk()->assertJsonStructure(['data', 'links', 'current_page', 'last_page']);
    }
}
