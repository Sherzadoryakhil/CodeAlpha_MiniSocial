<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function makeComments(Post $post, User $author, int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            Comment::factory()->create([
                'post_id' => $post->id,
                'user_id' => $author->id,
                'body' => 'cbody-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT) . '-end',
                'created_at' => now()->subMinutes(100 - $i),
                'updated_at' => now()->subMinutes(100 - $i),
            ]);
        }
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $comment = Comment::factory()->create();

        $this->post(route('posts.comments.store', $comment->post_id), ['body' => 'x'])->assertRedirect('/login');
        $this->get(route('comments.edit', $comment))->assertRedirect('/login');
        $this->patch(route('comments.update', $comment), ['body' => 'x'])->assertRedirect('/login');
        $this->delete(route('comments.destroy', $comment))->assertRedirect('/login');
    }

    public function test_user_can_add_a_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('posts.comments.store', $post), ['body' => 'Nice post!'])
            ->assertSessionHas('success');

        $comment = Comment::first();
        $response->assertRedirect(route('posts.show', $post) . '#comment-' . $comment->id);
        $this->assertSame($user->id, $comment->user_id);
        $this->assertSame($post->id, $comment->post_id);
        $this->assertSame('Nice post!', $comment->body);
    }

    public function test_comment_validation_rules(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)->post(route('posts.comments.store', $post), ['body' => ''])->assertSessionHasErrors('body');
        $this->actingAs($user)->post(route('posts.comments.store', $post), ['body' => str_repeat('a', 501)])->assertSessionHasErrors('body');
        $this->actingAs($user)->post(route('posts.comments.store', $post), ['body' => str_repeat('a', 500)])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('comments', 1);
    }

    public function test_user_and_post_cannot_be_forged_through_the_form(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->create();
        $otherPost = Post::factory()->create();

        $this->actingAs($user)->post(route('posts.comments.store', $post), [
            'body' => 'Forge attempt',
            'user_id' => $other->id,
            'post_id' => $otherPost->id,
        ]);

        $this->assertDatabaseHas('comments', ['body' => 'Forge attempt', 'user_id' => $user->id, 'post_id' => $post->id]);
    }

    public function test_detail_page_shows_all_comments_oldest_first(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $this->makeComments($post, $user, 5);

        $this->actingAs($user)->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('Comments (5)')
            ->assertSeeInOrder([
                'cbody-001-end', 'cbody-002-end', 'cbody-003-end', 'cbody-004-end', 'cbody-005-end',
            ]);
    }

    public function test_feed_shows_only_the_latest_two_comments(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $this->makeComments($post, $user, 4);

        $this->actingAs($user)->get('/')
            ->assertOk()
            ->assertSeeInOrder(['cbody-003-end', 'cbody-004-end'])
            ->assertDontSee('cbody-001-end')
            ->assertDontSee('cbody-002-end')
            ->assertSee('View all 4 comments');
    }

    public function test_comment_text_is_escaped(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        Comment::factory()->create(['post_id' => $post->id, 'user_id' => $user->id, 'body' => '<b>bold</b>']);

        $this->actingAs($user)->get(route('posts.show', $post))
            ->assertDontSee('<b>bold</b>', false)
            ->assertSee('&lt;b&gt;bold&lt;/b&gt;', false);
    }

    public function test_only_the_author_sees_edit_and_delete_links(): void
    {
        $author = User::factory()->create();
        $viewer = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $author->id]);

        $this->actingAs($author)->get(route('posts.show', $post))->assertSee(route('comments.edit', $comment));
        $this->actingAs($viewer)->get(route('posts.show', $post))->assertDontSee(route('comments.edit', $comment));
    }

    public function test_author_can_edit_a_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id, 'body' => 'before']);

        $this->actingAs($user)->get(route('comments.edit', $comment))->assertOk()->assertSee('before');

        $this->actingAs($user)->patch(route('comments.update', $comment), ['body' => 'after'])
            ->assertRedirect(route('posts.show', $comment->post_id) . '#comment-' . $comment->id)
            ->assertSessionHas('success');

        $this->assertSame('after', $comment->fresh()->body);
    }

    public function test_edit_validation_rejects_empty_and_too_long_comments(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id, 'body' => 'keep me']);

        $this->actingAs($user)->patch(route('comments.update', $comment), ['body' => ''])->assertSessionHasErrors('body');
        $this->actingAs($user)->patch(route('comments.update', $comment), ['body' => str_repeat('a', 501)])->assertSessionHasErrors('body');

        $this->assertSame('keep me', $comment->fresh()->body);
    }

    public function test_other_users_cannot_edit_update_or_delete_a_comment(): void
    {
        $author = User::factory()->create();
        $intruder = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $author->id, 'body' => 'original']);

        $this->actingAs($intruder)->get(route('comments.edit', $comment))->assertForbidden();
        $this->actingAs($intruder)->patch(route('comments.update', $comment), ['body' => 'hacked'])->assertForbidden();
        $this->actingAs($intruder)->delete(route('comments.destroy', $comment))->assertForbidden();

        $this->assertSame('original', $comment->fresh()->body);
    }

    public function test_post_author_cannot_delete_other_peoples_comments(): void
    {
        $postAuthor = User::factory()->create();
        $commenter = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $postAuthor->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $commenter->id]);

        $this->actingAs($postAuthor)->delete(route('comments.destroy', $comment))->assertForbidden();

        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_author_can_delete_a_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->delete(route('comments.destroy', $comment))
            ->assertRedirect(route('posts.show', $comment->post_id) . '#comments')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }
}
