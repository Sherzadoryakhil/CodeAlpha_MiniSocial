<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $post = Post::factory()->create();

        $this->get('/')->assertRedirect('/login');
        $this->post('/posts', ['body' => 'x'])->assertRedirect('/login');
        $this->get(route('posts.show', $post))->assertRedirect('/login');
        $this->get(route('posts.edit', $post))->assertRedirect('/login');
        $this->patch(route('posts.update', $post), ['body' => 'x'])->assertRedirect('/login');
        $this->delete(route('posts.destroy', $post))->assertRedirect('/login');
    }

    public function test_feed_shows_newest_posts_first(): void
    {
        $user = User::factory()->create();
        Post::factory()->create(['user_id' => $user->id, 'body' => 'older-post-body', 'created_at' => now()->subDays(2)]);
        Post::factory()->create(['user_id' => $user->id, 'body' => 'newer-post-body', 'created_at' => now()->subDay()]);

        $this->actingAs($user)->get('/')
            ->assertOk()
            ->assertSeeInOrder(['newer-post-body', 'older-post-body']);
    }

    public function test_feed_is_paginated_with_ten_posts_per_page(): void
    {
        $user = User::factory()->create();
        for ($i = 1; $i <= 12; $i++) {
            Post::factory()->create([
                'user_id' => $user->id,
                'body' => 'pbody-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT) . '-end',
                'created_at' => now()->subMinutes(100 - $i),
            ]);
        }

        $this->actingAs($user)->get('/')
            ->assertSee('pbody-012-end')
            ->assertSee('pbody-003-end')
            ->assertDontSee('pbody-002-end');

        $this->actingAs($user)->get('/?page=2')
            ->assertSee('pbody-002-end')
            ->assertSee('pbody-001-end')
            ->assertDontSee('pbody-012-end');
    }

    public function test_user_can_create_a_text_post(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/posts', ['body' => 'Hello Connectly'])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('posts', ['user_id' => $user->id, 'body' => 'Hello Connectly', 'image_path' => null]);
    }

    public function test_user_can_create_a_post_with_an_image(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/posts', [
            'body' => 'With picture',
            'image' => UploadedFile::fake()->image('holiday.jpg', 400, 300),
        ])->assertSessionHasNoErrors();

        $path = Post::first()->image_path;
        $this->assertStringStartsWith('post-images/', $path);
        $this->assertStringNotContainsString('holiday', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_post_validation_rules(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/posts', ['body' => ''])->assertSessionHasErrors('body');
        $this->actingAs($user)->post('/posts', ['body' => str_repeat('a', 1001)])->assertSessionHasErrors('body');
        $this->actingAs($user)->post('/posts', ['body' => str_repeat('a', 1000)])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('posts', 1);
    }

    public function test_invalid_post_images_are_rejected(): void
    {
        $user = User::factory()->create();

        $bad = [
            UploadedFile::fake()->create('script.php', 10, 'application/x-php'),
            UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
            UploadedFile::fake()->create('vector.svg', 10, 'image/svg+xml'),
            UploadedFile::fake()->image('huge.jpg')->size(3000),
        ];

        foreach ($bad as $file) {
            $this->actingAs($user)->post('/posts', ['body' => 'text', 'image' => $file])
                ->assertSessionHasErrors('image');
        }

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_author_cannot_be_forged_through_the_form(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->post('/posts', ['body' => 'Mine', 'user_id' => $other->id]);

        $this->assertDatabaseHas('posts', ['body' => 'Mine', 'user_id' => $user->id]);
    }

    public function test_post_text_is_escaped(): void
    {
        $user = User::factory()->create();
        Post::factory()->create(['user_id' => $user->id, 'body' => '<script>alert(1)</script>']);

        $this->actingAs($user)->get('/')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_any_logged_in_user_can_view_a_post(): void
    {
        $author = User::factory()->create();
        $viewer = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id, 'body' => 'viewable-body']);

        $this->actingAs($viewer)->get(route('posts.show', $post))
            ->assertOk()->assertSee('viewable-body');

        $this->actingAs($viewer)->get('/posts/999999')->assertNotFound();
    }

    public function test_owner_sees_edit_and_delete_but_others_do_not(): void
    {
        $author = User::factory()->create();
        $viewer = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id]);

        $this->actingAs($author)->get(route('posts.show', $post))
            ->assertSee(route('posts.edit', $post));

        $this->actingAs($viewer)->get(route('posts.show', $post))
            ->assertDontSee(route('posts.edit', $post));
    }

    public function test_owner_can_update_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id, 'body' => 'before']);

        $this->actingAs($user)->get(route('posts.edit', $post))->assertOk()->assertSee('before');

        $this->actingAs($user)->patch(route('posts.update', $post), ['body' => 'after'])
            ->assertRedirect(route('posts.show', $post))
            ->assertSessionHas('success');

        $this->assertSame('after', $post->fresh()->body);
    }

    public function test_update_can_replace_and_remove_the_image(): void
    {
        $user = User::factory()->create();
        Storage::disk('public')->put('post-images/old.jpg', 'x');
        $post = Post::factory()->create(['user_id' => $user->id, 'image_path' => 'post-images/old.jpg']);

        $this->actingAs($user)->patch(route('posts.update', $post), [
            'body' => 'text',
            'image' => UploadedFile::fake()->image('new.png', 200, 200),
        ])->assertSessionHasNoErrors();

        $new = $post->fresh()->image_path;
        Storage::disk('public')->assertMissing('post-images/old.jpg');
        Storage::disk('public')->assertExists($new);

        $this->actingAs($user)->patch(route('posts.update', $post), [
            'body' => 'text',
            'remove_image' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertNull($post->fresh()->image_path);
        Storage::disk('public')->assertMissing($new);
    }

    public function test_other_users_cannot_edit_update_or_delete_a_post(): void
    {
        $author = User::factory()->create();
        $intruder = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id, 'body' => 'original']);

        $this->actingAs($intruder)->get(route('posts.edit', $post))->assertForbidden();
        $this->actingAs($intruder)->patch(route('posts.update', $post), ['body' => 'hacked'])->assertForbidden();
        $this->actingAs($intruder)->delete(route('posts.destroy', $post))->assertForbidden();

        $this->assertSame('original', $post->fresh()->body);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_owner_can_delete_a_post_with_its_image_and_comments(): void
    {
        $user = User::factory()->create();
        Storage::disk('public')->put('post-images/pic.jpg', 'x');
        $post = Post::factory()->create(['user_id' => $user->id, 'image_path' => 'post-images/pic.jpg']);
        Comment::factory()->create(['post_id' => $post->id, 'user_id' => $user->id]);

        $this->actingAs($user)->delete(route('posts.destroy', $post))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('comments', ['post_id' => $post->id]);
        Storage::disk('public')->assertMissing('post-images/pic.jpg');
    }
}
