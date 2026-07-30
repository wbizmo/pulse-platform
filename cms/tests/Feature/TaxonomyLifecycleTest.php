<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TaxonomyLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function super(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'super_admin')->firstOrFail());

        return $user;
    }

    public function test_names_and_slugs_are_normalized_and_case_equivalent_names_are_rejected(): void
    {
        $actor = $this->super();
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post(route('admin.tags.store'), [
            'name' => "  Product\tNews  ",
        ])->assertRedirect();

        $this->assertDatabaseHas('tags', ['name' => 'Product News', 'normalized_name' => 'product news', 'slug' => 'product-news']);
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post(route('admin.tags.store'), [
            'name' => 'PRODUCT NEWS', 'slug' => 'different',
        ])->assertInvalid('normalized_name');
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post(route('admin.tags.store'), [
            'name' => 'Another', 'slug' => 'product-news',
        ])->assertInvalid('slug');
    }

    public function test_database_enforces_normalized_name_and_pivot_uniqueness(): void
    {
        Tag::create(['name' => 'Alpha', 'normalized_name' => 'alpha', 'slug' => 'alpha']);
        $this->expectException(QueryException::class);
        Tag::create(['name' => 'ALPHA', 'normalized_name' => 'alpha', 'slug' => 'alpha-2']);
    }

    public function test_assigned_taxonomy_cannot_be_deleted_and_attempt_is_not_a_delete_audit(): void
    {
        $actor = $this->super();
        $category = Category::create(['name' => 'News', 'normalized_name' => 'news', 'slug' => 'news']);
        Post::create(['title' => 'Assigned', 'slug' => 'assigned', 'category_id' => $category->id]);

        $this->actingAs($actor)->withSession(['mfa_passed' => true])
            ->delete(route('admin.categories.destroy', $category))->assertInvalid('taxonomy');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'taxonomy.deleted']);
    }

    public function test_public_archives_exclude_non_public_posts_and_are_paginated(): void
    {
        $tag = Tag::create(['name' => 'Release', 'normalized_name' => 'release', 'slug' => 'release']);
        foreach ([
            ['Public', 'published', now()],
            ['Draft', 'draft', null],
            ['Future', 'scheduled', now()->addHour()],
            ['Archived', 'archived', null],
        ] as [$title, $status, $publishedAt]) {
            $post = Post::create(['title' => $title, 'slug' => strtolower($title), 'status' => $status, 'published_at' => $publishedAt]);
            $post->tags()->attach($tag);
        }

        $this->get(route('frontend.blog.tag', $tag->slug))
            ->assertOk()->assertSee('Public')->assertDontSee('Draft')->assertDontSee('Future')->assertDontSee('Archived');
    }

    public function test_invalid_and_duplicate_taxonomy_ids_are_rejected(): void
    {
        $actor = $this->super();
        $payload = ['title' => 'Post', 'slug' => 'post', 'status' => 'draft', 'tags' => [999, 999]];

        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post(route('admin.posts.store'), $payload)
            ->assertInvalid(['tags.0', 'tags.1']);
    }

    public function test_taxonomy_administration_is_paginated_and_mutations_are_audited(): void
    {
        $actor = $this->super();
        foreach (range(1, 21) as $number) {
            Category::create(['name' => "Category $number", 'normalized_name' => "category $number", 'slug' => "category-$number"]);
        }

        $this->actingAs($actor)->withSession(['mfa_passed' => true])->get(route('admin.categories'))
            ->assertOk()->assertSee('Pagination');
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post(route('admin.categories.store'), ['name' => 'Audited'])
            ->assertRedirect();
        $this->assertDatabaseHas('audit_logs', ['action' => 'taxonomy.created']);
    }

    public function test_orphaned_pivot_rows_are_prevented_by_foreign_keys(): void
    {
        $this->expectException(QueryException::class);
        DB::table('post_tag')->insert(['post_id' => 999, 'tag_id' => 999, 'created_at' => now(), 'updated_at' => now()]);
    }
}
