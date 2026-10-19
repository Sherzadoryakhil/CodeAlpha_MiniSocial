<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
    }

    // A real file on disk (not a fake one), so the server checks its true content,
    // exactly like it does for a real upload. The reported type is a lie on purpose.
    private function realFile(string $name, string $content, string $reportedMime): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'cly');
        file_put_contents($path, $content);

        return new UploadedFile($path, $name, $reportedMime, null, true);
    }

    // ---------- CSRF ----------

    public function test_every_state_changing_route_is_inside_the_web_group_that_enforces_csrf(): void
    {
        $unsafe = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => array_intersect(['POST', 'PUT', 'PATCH', 'DELETE'], $route->methods()) !== []);

        $this->assertNotEmpty($unsafe);

        foreach ($unsafe as $route) {
            $this->assertContains('web', $route->gatherMiddleware(), 'Route without the web group: ' . $route->uri());
        }
    }

    public function test_forms_contain_a_csrf_token(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->get('/login')->assertSee('name="_token"', false);
        $this->get('/register')->assertSee('name="_token"', false);

        foreach (['/', route('posts.show', $post), route('profile.edit')] as $url) {
            $this->actingAs($user)->get($url)->assertSee('name="_token"', false);
        }
    }

    // ---------- Headers ----------

    public function test_security_headers_are_sent(): void
    {
        $this->get('/login')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $this->actingAs(User::factory()->create())->get('/')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    // ---------- Authentication and authorization ----------

    public function test_guests_cannot_reach_any_protected_page_or_action(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create();
        $user = User::factory()->create();

        $requests = [
            ['get', '/'],
            ['get', "/posts/{$post->id}"],
            ['get', "/posts/{$post->id}/edit"],
            ['post', '/posts'],
            ['patch', "/posts/{$post->id}"],
            ['delete', "/posts/{$post->id}"],
            ['post', "/posts/{$post->id}/comments"],
            ['get', "/comments/{$comment->id}/edit"],
            ['patch', "/comments/{$comment->id}"],
            ['delete', "/comments/{$comment->id}"],
            ['post', "/posts/{$post->id}/like"],
            ['delete', "/posts/{$post->id}/like"],
            ['get', '/profile/edit'],
            ['patch', '/profile'],
            ['get', "/users/{$user->username}"],
            ['post', "/users/{$user->username}/follow"],
            ['delete', "/users/{$user->username}/follow"],
            ['get', "/users/{$user->username}/followers"],
            ['get', "/users/{$user->username}/following"],
            ['post', '/logout'],
        ];

        foreach ($requests as [$method, $uri]) {
            $this->{$method}($uri)->assertRedirect('/login');
        }
    }

    public function test_out_of_scope_routes_are_not_exposed(): void
    {
        $user = User::factory()->create();

        foreach (['/forgot-password', '/reset-password/abc', '/verify-email', '/confirm-password'] as $uri) {
            $this->get($uri)->assertNotFound();
            $this->actingAs($user)->get($uri)->assertNotFound();
        }

        $this->actingAs($user)->put('/password', [])->assertNotFound();
        $this->post('/forgot-password', [])->assertNotFound();
    }

    public function test_login_is_locked_after_five_wrong_passwords(): void
    {
        User::factory()->create(['email' => 'lock@example.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'lock@example.com', 'password' => 'wrong-password']);
        }

        // Even the correct password is refused now.
        $this->post('/login', ['email' => 'lock@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registration_is_throttled(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/register', [])->assertSessionHasErrors();
        }

        $this->post('/register', [])->assertStatus(429)->assertSee('Too many requests');
    }

    // ---------- Mass assignment and hidden data ----------

    public function test_protected_user_fields_cannot_be_mass_assigned(): void
    {
        $user = (new User)->fill([
            'name' => 'A',
            'username' => 'a_b',
            'email' => 'a@b.co',
            'bio' => 'x',
            'id' => 99,
            'profile_photo_path' => 'evil.jpg',
            'email_verified_at' => now(),
            'remember_token' => 'token',
        ]);

        $this->assertNull($user->id);
        $this->assertNull($user->profile_photo_path);
        $this->assertNull($user->email_verified_at);
        $this->assertNull($user->remember_token);
        $this->assertSame('a_b', $user->username);
    }

    public function test_post_and_comment_ownership_cannot_be_mass_assigned(): void
    {
        $post = (new Post)->fill(['body' => 'b', 'user_id' => 5, 'id' => 7]);
        $this->assertNull($post->user_id);
        $this->assertNull($post->id);

        $comment = (new Comment)->fill(['body' => 'b', 'user_id' => 5, 'post_id' => 6, 'id' => 8]);
        $this->assertNull($comment->user_id);
        $this->assertNull($comment->post_id);
        $this->assertNull($comment->id);
    }

    public function test_passwords_are_never_included_when_a_user_is_serialized(): void
    {
        $array = User::factory()->create()->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    // ---------- Injection ----------

    public function test_user_supplied_text_is_escaped_on_profile_pages(): void
    {
        $viewer = User::factory()->create();
        $user = User::factory()->create([
            'name' => '<script>alert(1)</script>',
            'bio' => '<img src=x onerror=alert(1)>',
        ]);

        $this->actingAs($viewer)->get(route('profile.show', $user))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_sql_injection_in_a_username_lookup_just_returns_404(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/users/' . rawurlencode("' OR 1=1 --"))
            ->assertNotFound();
    }

    public function test_line_breaks_do_not_use_up_the_character_limit_twice(): void
    {
        $user = User::factory()->create();
        // 400 lines: 800 characters on screen, 1200 characters if each break counted as two.
        $body = str_repeat("a\r\n", 400);

        $this->actingAs($user)->post('/posts', ['body' => $body])->assertSessionHasNoErrors();

        $this->assertSame(rtrim(str_repeat("a\n", 400)), Post::first()->body);
    }

    // ---------- Uploads ----------

    public function test_disguised_files_are_rejected_as_post_images(): void
    {
        $user = User::factory()->create();

        $bad = [
            $this->realFile('shell.jpg', '<?php echo "hacked";', 'image/jpeg'),
            $this->realFile('page.png', '<html><script>alert(1)</script></html>', 'image/png'),
            $this->realFile('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>', 'image/svg+xml'),
        ];

        foreach ($bad as $file) {
            $this->actingAs($user)->post('/posts', ['body' => 'text', 'image' => $file])
                ->assertSessionHasErrors('image');
        }

        $this->assertDatabaseCount('posts', 0);
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_disguised_files_are_rejected_as_profile_photos(): void
    {
        $user = User::factory()->create();

        $bad = [
            $this->realFile('shell.jpg', '<?php echo "hacked";', 'image/jpeg'),
            $this->realFile('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>', 'image/svg+xml'),
        ];

        foreach ($bad as $file) {
            $this->actingAs($user)->patch('/profile', [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'photo' => $file,
            ])->assertSessionHasErrors('photo');
        }

        $this->assertNull($user->fresh()->profile_photo_path);
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }
}
