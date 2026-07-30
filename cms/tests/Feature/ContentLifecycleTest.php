<?php

namespace Tests\Feature;

use App\Actions\Content\SaveContent;
use App\Domain\Content\ContentStatus;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ContentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_routes_never_leak_non_effective_content(): void
    {
        Page::create(['title' => 'Draft', 'slug' => 'draft', 'status' => 'draft', 'template' => 'default']);
        Page::create(['title' => 'Future', 'slug' => 'future', 'status' => 'scheduled', 'published_at' => now()->addHour(), 'template' => 'default']);
        Page::create(['title' => 'Archived', 'slug' => 'archived', 'status' => 'archived', 'template' => 'default']);
        Page::create(['title' => 'Public', 'slug' => 'public', 'status' => 'published', 'published_at' => now(), 'template' => 'default']);

        foreach (['draft', 'future', 'archived'] as $slug) {
            $this->get('/'.$slug)->assertNotFound();
        }
        $this->get('/public')->assertOk();
        $this->get('/sitemap.xml')->assertSee('/public', false)->assertDontSee('/draft', false)->assertDontSee('/future', false);
    }

    public function test_scheduled_publication_is_idempotent(): void
    {
        $post = Post::create(['title' => 'Due', 'slug' => 'due', 'status' => 'scheduled', 'published_at' => now()->subMinute()]);
        $this->artisan('content:publish-scheduled')->assertSuccessful();
        $this->artisan('content:publish-scheduled')->assertSuccessful();
        $this->assertSame(ContentStatus::Published, $post->refresh()->status);
        $this->assertDatabaseCount('audit_logs', 1);
    }

    public function test_optimistic_lock_rejects_a_stale_editor(): void
    {
        $user = User::factory()->create();
        $page = Page::create(['author_id' => $user->id, 'title' => 'Original', 'slug' => 'original', 'status' => 'draft', 'template' => 'default']);
        $data = ['title' => 'First', 'slug' => 'first', 'status' => 'draft', 'template' => 'default', 'lock_version' => 0];
        app(SaveContent::class)->execute($page, $data, $user);

        $this->expectException(ValidationException::class);
        app(SaveContent::class)->execute($page, $data + ['title' => 'Stale'], $user);
    }
}
