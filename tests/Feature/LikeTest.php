<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $post = Post::factory()->create();

        $this->post(route('posts.like', $post))->assertRedirect('/login');
        $this->delete(route('posts.unlike', $post))->assertRedirect('/login');
    }

    public function test_user_can_like_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)->post(route('posts.like', $post))->assertRedirect();

        $this->assertDatabaseHas('likes', ['user_id' => $user->id, 'post_id' => $post->id]);
    }

    public function test_liking_twice_keeps_only_one_like_and_does_not_crash(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)->post(route('posts.like', $post))->assertRedirect();
        $this->actingAs($user)->post(route('posts.like', $post))->assertRedirect();

        $this->assertSame(1, DB::table('likes')->where('user_id', $user->id)->where('post_id', $post->id)->count());
    }

    public function test_user_can_unlike_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $user->likedPosts()->attach($post->id);

        $this->actingAs($user)->delete(route('posts.unlike', $post))->assertRedirect();

        $this->assertDatabaseMissing('likes', ['user_id' => $user->id, 'post_id' => $post->id]);
    }

    public function test_unliking_a_post_that_was_not_liked_is_harmless(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)->delete(route('posts.unlike', $post))->assertRedirect();

        $this->assertDatabaseCount('likes', 0);
    }

    public function test_one_user_unliking_does_not_remove_another_users_like(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $post = Post::factory()->create();
        $a->likedPosts()->attach($post->id);
        $b->likedPosts()->attach($post->id);

        $this->actingAs($a)->delete(route('posts.unlike', $post));

        $this->assertDatabaseHas('likes', ['user_id' => $b->id, 'post_id' => $post->id]);
        $this->assertDatabaseMissing('likes', ['user_id' => $a->id, 'post_id' => $post->id]);
    }

    public function test_like_counts_are_shown_on_the_feed(): void
    {
        $viewer = User::factory()->create();
        $post = Post::factory()->create();
        $post->likedBy()->attach(User::factory(2)->create()->pluck('id'));

        $this->actingAs($viewer)->get('/')->assertSee('2 likes');
    }

    public function test_like_button_shows_whether_you_liked_the_post(): void
    {
        $viewer = User::factory()->create();
        $liked = Post::factory()->create(['body' => 'liked-post']);
        Post::factory()->create(['body' => 'plain-post']);
        $viewer->likedPosts()->attach($liked->id);

        $this->actingAs($viewer)->get('/')
            ->assertSee('aria-pressed="true"', false)
            ->assertSee('aria-pressed="false"', false);
    }

    public function test_like_state_is_shown_on_the_detail_page(): void
    {
        $viewer = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($viewer)->get(route('posts.show', $post))->assertSee('aria-pressed="false"', false);

        $viewer->likedPosts()->attach($post->id);

        $this->actingAs($viewer)->get(route('posts.show', $post))->assertSee('aria-pressed="true"', false);
    }

    public function test_database_rejects_a_duplicate_like(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $user->likedPosts()->attach($post->id);

        $this->expectException(UniqueConstraintViolationException::class);

        DB::table('likes')->insert([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
