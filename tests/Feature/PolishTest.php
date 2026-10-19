<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolishTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_pages_have_their_own_titles(): void
    {
        $author = User::factory()->create(['name' => 'Jane Doe']);
        $post = Post::factory()->create(['user_id' => $author->id]);

        $this->get('/login')->assertSee('<title>Log in - ', false);
        $this->get('/register')->assertSee('<title>Register - ', false);

        $this->actingAs($author)->get('/')->assertSee('<title>Home - ', false);
        $this->actingAs($author)->get(route('posts.show', $post))->assertSee('<title>Post by Jane Doe - ', false);
        $this->actingAs($author)->get(route('profile.show', $author))->assertSee('<title>Jane Doe - ', false);
        $this->actingAs($author)->get(route('profile.edit'))->assertSee('<title>Edit profile - ', false);
        $this->actingAs($author)->get(route('follows.followers', $author))->assertSee('<title>Followers of Jane Doe - ', false);
    }

    public function test_pages_have_a_skip_link(): void
    {
        $this->actingAs(User::factory()->create())->get('/')
            ->assertSee('Skip to main content')
            ->assertSee('id="main-content"', false);
    }

    public function test_success_messages_can_be_dismissed(): void
    {
        $this->actingAs(User::factory()->create())
            ->followingRedirects()
            ->post('/posts', ['body' => 'Hello'])
            ->assertSee('Your post was published.')
            ->assertSee('btn-close', false);
    }

    public function test_a_failed_form_shows_a_summary_and_the_field_message(): void
    {
        $this->actingAs(User::factory()->create())
            ->from('/')
            ->followingRedirects()
            ->post('/posts', ['body' => ''])
            ->assertSee('Please fix the highlighted fields')
            ->assertSee('Please write something before posting.');
    }

    public function test_friendly_not_found_page(): void
    {
        $this->get('/no-such-page')->assertNotFound()->assertSee('Page not found');

        $this->actingAs(User::factory()->create())->get('/posts/99999')
            ->assertNotFound()->assertSee('Page not found');
    }

    public function test_friendly_forbidden_page(): void
    {
        $post = Post::factory()->create();

        $this->actingAs(User::factory()->create())->get(route('posts.edit', $post))
            ->assertForbidden()->assertSee('not allowed');
    }
}
